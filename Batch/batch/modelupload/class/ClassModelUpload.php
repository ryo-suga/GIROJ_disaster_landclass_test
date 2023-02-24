<?php

//定数定義
//=====================================================================================================================
/**
　* 更新10/16
 * バッチ処理実行クラス
 */
class ClassModelUpload {
	private $dbh        	= null;
	private $filepath_csv 	= null;
	private $input_file		= null;
	private $filename_this  = null;
	private $file_obj 		= null;
	private $csv_table  	= array();
	private $console_msg 	= array();
	private $error_log_msgs	= array();
	private $maker_mst  	= null;
	private $car_mst    	= null;
	private $model_data 	= null;
	private $year_mst		= null;
	private $tg_year		= null;
	
	//コンソール出力メッセージ用フラグ
	private $flg_file_notfound		= false;
	private $flg_operation_fail		= false;
	private $flg_csv_name_error		= false;
	private $flg_file_found_multi	= false;
	private $flg_data_error			= false;
	private $flg_fatal_error		= false;
	
	function __construct() {
	
		//includeファイル存在チェック
		if(!include_once(PATH_MAKER_MST.ConfigModelUpload::getNameMakerMst())){
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgIncludeNotfoundMakerMst(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
		}
		if(!include_once(PATH_CAR_MST.ConfigModelUpload::getNameCarMst())){ 
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgIncludeNotfoundCarMst(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
		}
		if(!include_once(PATH_MODEL_DATA.ConfigModelUpload::getNameModelData())) {
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgIncludeNotfoundModelData(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
		}
		if(!include_once(PATH_YEAR_MST.ConfigModelUpload::getNameYearMst())) {
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgIncludeNotfoundYearMst(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
		}
		if(!$this->checkErrorFlg()) {
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgIncludeFileLack(), CONFIG::$LOG_LEVEL_NULL);
			$this->closeBatch();
		}
	}
	
	public function execFlow() {
		$is_error = false;

		$this->addErrorLogMessage(ConfigModelUpload::getMsgBatchFileBegin(), CONFIG::$LOG_LEVEL_INFO);
		//set
		$this->setFilepathCSV(PATH_CSV);

		//プロセスに失敗した場合、その時点でクローズする。
		//------------------------------------------------------------------------------------------------------------------
		//バッチのファイル名確認
		$this->checkBatchFileName();
		if(!$this->checkErrorFlg()) {
			$is_error = true;
			$this->closeBatch();
		}

		//同一プロセス実行確認
		$this->setNameThis(basename($_SERVER['PHP_SELF']));
		$this->isProcessing($this->getNameThis());
		if(!$this->checkErrorFlg()) {
			$is_error = true;
			$this->closeBatch();
		}

		//アップロードファイル確認
		$this->checkUploadFile();
		if(!$this->checkErrorFlg()) {
			$is_error = true;
			$this->closeBatch();
		}

		//CSVデータフォーマットチェック
		$this->checkDataFormat();
		if(!$this->checkErrorFlg()) {
			$is_error = true;
			$this->closeBatch();
		}		

		//DBトランザクション
		$this->setTransaction();
		if(!$this->checkErrorFlg()) {
			$is_error = true;
			$this->closeBatch();
		}

		//エラーなしの場合、完了処理
		//------------------------------------------------------------------------------------------------------------------
		//対象ファイル削除
		$result = false;
		try {
			$result = unlink($this->getInputFile());
		} catch (Exception $e) {}
		if(!$result) {
			//削除失敗
			$is_error = true;
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgBatchSuccessDeleteFail(), CONFIG::$LOG_LEVEL_INFO);
			$this->addConsoleMessage(ConfigModelUpload::getMsgBatchSuccessDeleteFail());
		}
		if(!$is_error) {
			//正常完了
			$this->addErrorLogMessage(ConfigModelUpload::getMsgBatchFileDone(), CONFIG::$LOG_LEVEL_INFO);
			$this->addConsoleMessage(ConfigModelUpload::getMsgBatchSuccess());
		}
		$this->closeBatch();
	}
	
	/**
	 * バッチファイル名確認
	 *
	 */
	public function checkBatchFileName() {
		$ret = false;		
		
		//バッチファイル名が指定したものと不一致なら、処理終了
		if(basename($_SERVER['PHP_SELF']) != ConfigModelUpload::getNameExec()) {
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgBatchfileName(), CONFIG::$LOG_LEVEL_WARN);
			$this->setFlgOperationFail(true);
			return $ret;
		}
		
		$ret= true;
		return $ret;
	}

	/**
	 * アップロードファイル確認
	 *
	 */
	public function checkUploadFile() {
		$is_error = false;
		$csv_table = array();

		// 1) ファイルが複数存在するか
		//-------------------------------------------------------------------------------------------------------------
		$result = $this->isNotFoundMulti();
		if(!$result) { 
			$is_error = true;
			return !$is_error;
		}	
		//ファイル情報セット
		$files = glob($this->getFilepathCSV().'*');
		$this->setInputFile($files['0']);
		
		// 2) ファイルが取得できるか
		//-------------------------------------------------------------------------------------------------------------
		$result = $this->isAvailableInputFile();
		if(!$result) {
			$is_error = true;
			return !$is_error;
		}	
		//ファイルオブジェクトセット	
		$this->setFileObj(new SplFileObject($this->getInputFile(), $open_mode = 'r'));

		// 3) ファイル名フォーマット確認
		//-------------------------------------------------------------------------------------------------------------
		$result = $this->isValid_Name($this->getFileObj()->getFileName());
		if(!$result) { 
			$is_error = true;
			return !$is_error;
		}

		//対象年取得
		$this->setTgYear(substr($this->getFileObj()->getFileName(),-8,4));
		//ファイル内容取得
		$csv_table = $this->convCSVFileToCSVArray($this->getFileObj());
		$this->setCSVTable($csv_table);
		
		// 4) ファイルが空か？
		//-------------------------------------------------------------------------------------------------------------
		$result = $this->isNullCsvFile($this->getCSVTable());
		if(!$result) { 
			$is_error = true;
			return !$is_error;
		}

		return !$is_error;
	}

	/**
	 * 	CSVデータフォーマットチェック
	 *
	 */
	public function checkDataFormat() {
		$is_error = false;

		//(1行目スキップ,最終行は改行とする)
		$csvTable = $this->getCSVTable();
		for($i = 1; $i < sizeof($this->getCSVTable()) -1; $i++){
			$row_valid_fail = false;
			$csv_row = $csvTable[$i];

			for($j = 0; $j < sizeof($csv_row); $j++) {
				$result = $this->isValidColData($csv_row[$j], $j);
				if(!$result) {
					$row_valid_fail = true;
				}
			}			
			if($row_valid_fail) {
				$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgCsvrowFormat().($i+1), CONFIG::$LOG_LEVEL_ERROR);
				$this->setFlgDataError(true);
				$is_error = true;
			}
		}	
		return !$is_error;
	}

	/**
	 * DBトランザクション
	 *
	 */
	public function setTransaction() {
		$is_error = false;
		
		//DB接続
		$result = $this->setDBConnection(Config::getDsn(), Config::getUser(), Config::getPassword(),array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
		if(!$result) return false;
	
		//DBモデルをセット
		$this->setDBModels();

		try {
			$this->getDBH()->beginTransaction();
			$result = $this->execDBProcess();
			if($result) {
				$this->getDBH()->commit();
			} else {
				$this->getDBH()->rollBack();
				$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgTransactionFail(), CONFIG::$LOG_LEVEL_FATAL);
				$this->setFlgFatalError(true);
				$is_error = true;
			}
		} catch (Exception $e) { $this->getDBH()->rollBack(); $this->addErrorLogMessage(ConfigModelUpload::getErrorMsgTransactionFail(), CONFIG::$LOG_LEVEL_FATAL); $is_error = true;}
		
		//PDOのコネクション破棄
		$this->setDBH(null);
		
		return !$is_error;
	}
	
	//プロセス重複確認
	//==================================================================================================================
	/**
	 * バッチ実行可・不可判断
	 * @return boolean 不可:false
	 */
	public function isProcessing($filename) {
		$is_processing = false;

		//自身と同一のファイル名をプロセスリストから検索する。
		$command = "ps -ef | grep '$filename' | grep -v 'grep'";
		$output = array();
		$ret = null;

		exec($command, $output, $ret);

		// psコマンドで結果なしの場合は、1が返る
		if ($ret != 0) {
			$is_processing = true;
			return $is_processing;
		}

		// 2以上なら別の処理が実行中と判断（自身+同名の別のバッチ処理の計）
		if (count($output) >= 2) {
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgIsProcessing(), CONFIG::$LOG_LEVEL_WARN);
			$this->setFlgOperationFail(true);
			return $is_processing;
		} else {
			$is_processing = true;
			return $is_processing;
		}
	}

	//ファイル状態検査
	//==================================================================================================================
	/**
	 * インプットファイルが対象フォルダに一つか確認
	 * @return boolean エラーなし:true
	 */
	public function isNotFoundMulti() {
		$is_error = false;

		//フォルダー内にファイルが複数ある場合はエラー
		$count_files = 0;
		foreach (glob($this->getFilepathCSV().'*') as $file) {
			$count_files++;
		}
		if($count_files > 1) {
			//エラーメッセージ記録
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgCsvFoundMulti(), CONFIG::$LOG_LEVEL_ERROR);
			$this->setFlgFileFoundMulti(true);
			$is_error = true;
		}

		return !$is_error;
	}
	/**
	 * インプットファイルが取得可能か
	 * @return boolean エラーなし:true
	 */
	public function isAvailableInputFile() {
		$is_error = false;
		
		//ファイル取得
		try {
			$file_obj = new SplFileObject($this->getInputFile(), $open_mode = 'r');
		} catch (Exception $e ) {
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgCsvNotfound().$this->getInputFile(), CONFIG::$LOG_LEVEL_WARN);
			$this->setFlgFileNotFound(true);
			$is_error = true;
			return !$is_error;
		}
	
		return !$is_error;
	}
	 
	/**
	 * ファイル名検査
	 * @return boolean エラーなし:true
	 */
	public function isValid_Name( $filename ) {
		$is_error = false;

		//ファイル名のバリデーション
		//-----------------------------------------------------------------------------------------
		//csvか
		if(substr($filename, -4) != '.csv' ) {
			$is_error = true;
		}
		
		//末尾が年号（半角数字）か
		$tg_year = substr($filename, -8,4);
		if(!preg_match('/^[0-9]{4,4}$/',$tg_year)) {
			$is_error = true;
		}
		if( !(2017 <= $tg_year && $tg_year <= 2100 )) {
			$is_error = true;
		}
		
		//事前指定部分の一致
		if(substr($filename, 0, strlen($filename)-8) != ConfigModelUpload::getPregCsvNameDefined()) {
			$str = substr($filename, 0, strlen($filename)-8);
			$is_error = true;
		}

		if($is_error) {
			//エラーメッセージ記録
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgCsvNameNotcorrect(), CONFIG::$LOG_LEVEL_ERROR);
			$this->setFlgCsvNameError(true);
		}
		
		return !$is_error;
	}
	
	/**
	 * CSVファイル空チェック
	 * @return boolean エラーなし:true
	 */
	public function isNullCsvFile($csv_filetable_array) {	
		$is_error = false;
		
		//ファイルが空か？
		if(!isset($csv_filetable_array[1][0])) {
			$is_error = true;
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgCsvIsempty(), CONFIG::$LOG_LEVEL_ERROR);
			$this->setFlgDataError(true);
		}
		
		return !$is_error;
	}

		
	/**
	 * CSV項目データフォーマットチェック
	 * @param $col_data, int $col_num
	 * @return boolean;
	 */
	public function isValidColData($col_data, $col_num) {
		$is_error = false;

		//空ではない
		if(isset($col_data) && $col_data != ''){
			//フォーマットチェック
			if(!preg_match(ConfigModelUpload::getColPreg($col_num), $col_data)) {
				$is_error = true; 
				$this->addErrorLogMessage(ConfigModelUpload::getColName($col_num).' : '.ConfigModelUpload::getColValidFailMsg($col_num), CONFIG::$LOG_LEVEL_ERROR);
				$this->setFlgDataError(true);
			}
		}
		//空を許可する項目か？
		else if(!in_array($col_num, ConfigModelUpload::getNullAllowColumns())) {
			$is_error = true;
			$this->addErrorLogMessage(ConfigModelUpload::getColName($col_num).' : '.ConfigModelUpload::getMsgCantBeNull(), CONFIG::$LOG_LEVEL_ERROR);
			$this->setFlgDataError(true);
		}

		return !$is_error;
	}

	/**
	 * File型→CSV配列変換
	 * @param $file_obj;
	 * @return $csv_table;
	 */
	public function convCSVFileToCSVArray($file_obj) {
		$csv_table = array();
		
		while( $csv_row = $file_obj->fgetcsv()) {
			array_push($csv_table, $csv_row);
		}
		
		return $csv_table;
	}

	//DBトランザクション
	//==================================================================================================================
	/**
	 * DBトランザクション処理
	 * @return boolean 処理結果 完了:true
	 */
	public function execDBProcess() {
		$is_error = false;
		
		$result_delete_all = $this->deleteDBRecords();
		if(!$result_delete_all) {
			$is_error =true;
			return !$is_error;
		}

		//対象年データをDB登録
		//-------------------------------------------------------------------------------------
		$result = $this->insertYearMst($this->tg_year);
		if(!$result) $is_error = true;

		$namelist_maker = array();
		$namelist_maker_car = array();
		
		//csvデータ行ループ　最終行は改行とする。
		$csvTable = $this->getCSVTable();
		for($i = 1 ; $i < sizeof($this->getCSVTable())-1 ;$i++) {
			$csv_row 	= $csvTable[$i];
			$model		= trim($csv_row[0]);
			$maker_name = trim($csv_row[1]);
			$car_name   = trim($csv_row[2]);
		
			//メーカー名確認
			//---------------------------------------------------------------------------------
			//既出のメーカ名か？
			if(!in_array($maker_name, $namelist_maker)) {
				//メーカ名リストに追加
				$namelist_maker[] = $maker_name;
			}
			//メーカ名・車名確認
			//---------------------------------------------------------------------------------				
			$flg_exist = false;
			foreach ($namelist_maker_car as $nmc_row) {
				//既出のメーカー名・車名の組み合わせか？
				if($nmc_row['maker_name'] == $maker_name && $nmc_row['car_name'] == $car_name) {
					$flg_exist = true;
				}
			}
			if(!$flg_exist) {
				//メーカ名・車名リストに追加
				$work_array = array();
				$work_array['maker_name'] = $maker_name;
				$work_array['car_name']   = $car_name;
				$namelist_maker_car[]     = $work_array;
			}
			
			//型式データ重複確認
			//---------------------------------------------------------------------------------
			$is_existing = false;
			$is_existing = $this->isExistingKeyModelData($model, $maker_name, $car_name);
			if($is_existing) { $is_error = true;}

			//型式データをDB登録
			//---------------------------------------------------------------------------------				
			$result = $this->insertModelData($csv_row);
			if(!$result) $is_error = true;
		}

		//メーカー名をDB登録
		//-------------------------------------------------------------------------------------
		foreach($namelist_maker as $maker_name) {
			$result = $this->insertMakerMst($maker_name);
			if(!$result) {
				$this->addErrorLogMessage($maker_name, CONFIG::$LOG_LEVEL_FATAL);					
				$this->setFlgFatalError(true);				
				$is_error = true;
			}
		}

		//メーカー名・車名をDB登録
		//-------------------------------------------------------------------------------------
		foreach($namelist_maker_car as $add_record ) {
			$result = $this->insertCarMst($add_record);
			if(!$result) {
				$this->addErrorLogMessage($add_record[0].' '.$add_record[1], CONFIG::$LOG_LEVEL_FATAL);
				$this->setFlgFatalError(true);
				$is_error = true;
			}
		}
		
		if($is_error) {
			return false;
		}
		return true;
	}

	//DB処理
	//==================================================================================================================
	/**
	 * DB接続情報セッター
	 * @return boolean 有効：true;
	 */
	public function setDBConnection($dsn,$user,$pass,$array = null) {
		$ret = false;
		
		try {
			$pdo = new PDO($dsn, $user, $pass,$array);		
		} catch (PDOException $e) {
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgDBconnectFail(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);			
			return $ret;
		}
		$this->setDBH($pdo);
		
		$ret = true;
		return $ret;
	}
	
	/**
	 * DBモデル情報　セッター(全て)
	 * @return boolean 完了：true;
	 */
	public function setDBModels() {
		$ret = false;
		
		$this->setMakerMst(new MakerMst());
			$this->getMakerMst()->setColumnValue('cre_usr',ConfigModelUpload::getCreateUser());
			$this->getMakerMst()->setColumnValue('cre_prg',ConfigModelUpload::getCreateProgram());
			$this->getMakerMst()->setColumnValue('upd_usr',ConfigModelUpload::getUpdateUser());
			$this->getMakerMst()->setColumnValue('upd_prg',ConfigModelUpload::getUpdateProgram());
		$this->setCarMst(new CarMst());
			$this->getCarMst()->setColumnValue('cre_usr',ConfigModelUpload::getCreateUser());
			$this->getCarMst()->setColumnValue('cre_prg',ConfigModelUpload::getCreateProgram());
			$this->getCarMst()->setColumnValue('upd_usr',ConfigModelUpload::getUpdateUser());
			$this->getCarMst()->setColumnValue('upd_prg',ConfigModelUpload::getUpdateProgram());
		$this->setModelData(new ModelData());
			$this->getModelData()->setColumnValue('cre_usr',ConfigModelUpload::getCreateUser());
			$this->getModelData()->setColumnValue('cre_prg',ConfigModelUpload::getCreateProgram());
			$this->getModelData()->setColumnValue('upd_usr',ConfigModelUpload::getUpdateUser());
			$this->getModelData()->setColumnValue('upd_prg',ConfigModelUpload::getUpdateProgram());
		$this->setYearMst(new YearMst());
			$this->getYearMst()->setColumnValue('cre_usr',ConfigModelUpload::getCreateUser());
			$this->getYearMst()->setColumnValue('cre_prg',ConfigModelUpload::getCreateProgram());
			$this->getYearMst()->setColumnValue('upd_usr',ConfigModelUpload::getUpdateUser());
			$this->getYearMst()->setColumnValue('upd_prg',ConfigModelUpload::getUpdateProgram());
		
		$ret = true;
		return $ret;
	}
	
	/**
	 * DB内レコード削除(4 TABLE分まとめて実行)
	 * @return boolean 処理結果 完了:true
	 */
	public function deleteDBRecords() {
		$is_error = false;
		
		//モデルが正しくセットされているか？
		if(!($this->getMakerMst() != null || $this->getMakerMst() instanceof Model)) $is_error = true;
		if(!($this->getCarMst()   != null || $this->getCarMst()   instanceof Model)) $is_error = true;
		if(!($this->getModelData()!= null || $this->getModelData()instanceof Model)) $is_error = true;
		if(!($this->getYearMst()  != null || $this->getYearMst()  instanceof Model)) $is_error = true;
		if($is_error) {
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgTabledeleteFail(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
			return !$is_error;
		}
		
		$ret = $this->getMakerMst()->deleteAllRecords($this->getDBH());	
		if(!$ret) $is_error = true;
		$ret = $this->getCarMst()->deleteAllRecords($this->getDBH());
		if(!$ret) $is_error = true;
		$ret = $this->getModelData()->deleteAllRecords($this->getDBH());
		if(!$ret) $is_error = true;
		$ret = $this->getYearMst()->deleteAllRecords($this->getDBH());
		if(!$ret) $is_error = true;
		if($is_error) {
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgTabledeleteFail(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
		}

		return !$is_error;
	}

	/**
	 * 型式マスター登録
	 * @return boolean 処理結果 完了:true
	 */
	public function insertModelData($add_record) {
		$ret = false;
		
		$model_data = $this->getModelData();
		for($i = 0; $i<sizeof($add_record); $i++) {
			$model_data->setColumnValue(ConfigModelUpload::getColNameDB($i),$add_record[$i]);
		}
		$result = $model_data->insertRecord($this->getDBH());
		if(!$result) {
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgTableinsertFailModelData(), CONFIG::$LOG_LEVEL_FATAL);				
			$this->setFlgFatalError(true);
			return $ret;
		}
		
		$ret = true;
		return $ret;
	}

	/**
	 * 型式マスター重複チェック
	 * @return boolean 処理結果 完了:true
	 */
	public function isExistingKeyModelData($model, $maker_name, $car_name) {
		$ret = false;
		
		$model_data = $this->getModelData();
		$model_data->setColumnValue(ConfigModelUpload::getColNameDB(0),$model);
		$model_data->setColumnValue(ConfigModelUpload::getColNameDB(1),$maker_name);
		$model_data->setColumnValue(ConfigModelUpload::getColNameDB(2),$car_name);
		
		$is_exist = $model_data->isExist($this->getDBH());
		if($is_exist) {
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgTableinsertFailExist().$model.', '.$maker_name.', '.$car_name, CONFIG::$LOG_LEVEL_ERROR);
			$this->setFlgFatalError(true);
			$ret = true;
			return $ret;
		}
		//無効値
		else if($is_exist == -1) {
			$ret = true;
		}
		return $ret;
	}

	/**
	 * メーカ名マスター登録
	 * @return boolean 処理結果 完了:true
	 */
	public function insertMakerMst($maker_name) {
		$ret = false;
		$this->getMakerMst()->setColumnValue('maker_name',$maker_name);

		$result = $this->getMakerMst()->insertRecord($this->getDBH());
		if(!$result) {
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgTableinsertFailMakerMst(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
			return $ret;
		}
		
		$ret = true;
		return $ret;
	}

	/**
	 * 車名マスター登録
	 * @return boolean 処理結果 完了:true
	 */
	public function insertCarMst($add_record) {
		$ret = false;

		$this->getCarMst()->setColumnValue('maker_name',$add_record['maker_name']);
		$this->getCarMst()->setColumnValue('car_name',$add_record['car_name']);

		$result = $this->getCarMst()->insertRecord($this->getDBH());
		if(!$result) {
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgTableinsertFailCarMst(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
			return $ret;
		}
		
		$ret = true;
		return $ret;
	}
	
	/**
	 * 対象年マスター登録
	 * @return boolean 処理結果 完了:true
	 */
	public function insertYearMst($year) {
		$ret = false;

		$this->getYearMst()->setColumnValue('target_year', $year);
		$result = $this->getYearMst()->insertRecord($this->getDBH());

		if(!$result) {
			$this->addErrorLogMessage(ConfigModelUpload::getErrorMsgTableinsertFailYearMst(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
			return $ret;
		}

		$ret = true;
		return $ret;
	}
	
	//メッセージ処理系
	//==================================================================================================================	
	/**
	 * エラーログ記録　レベル：ERROR
	 * @param  string
	 * @return boolean 処理結果 完了:true
	 */
	public function addErrorLogMessage($message,$level ) {
		$ret = false;
		
		$this->error_log_msgs[] = array( 'message' => "$message", 'level' => $level);
		$ret = true;
		
		return $ret;
	}

	/**
	 * エラーログ出力
	 * @return boolean 処理結果 完了:true
	 */
	public function outputErrorLog() {
		$ret = false;

		$now = new DateTime();
		foreach($this->error_log_msgs as $msg) {
			try{
				$date = $now->format('Y-m-d H:i:s');
				$log = $date ." [" .$msg['level']. "] " .$msg['message']. PHP_EOL;
				error_log($log, 3, PATH_ERROR_LOG.ConfigModelUpload::getNameErrorLog());
			} catch (Exception $e) {}
		}
		
		$ret = true;
		return $ret;
	}

	/**
	 * コンソール出力メッセージ記録
	 * @param  string
	 * @return boolean 処理結果 完了:true
	 */
	public function addConsoleMessage($message) {
		$ret = false;
		
		$this->console_msg[] = "$message \n";
		$ret = true;
		
		return $ret;
	}

	/**
	 * コンソール出力
	 * @return boolean 処理結果 完了:true
	 */
	public function outputConsole() {
		$ret = false;
		foreach($this->console_msg as $msg) {
			//コンソール文字コード変換(出力用バッファ（文字コード変換付き）を終了)
			ob_end_clean();
			echo $msg;
		}
		$ret = true;
		
		return $ret;
	}
	//エラーフラグ確認
	//==================================================================================================================	
	/**
	 * エラーフラグ確認
	 * @return boolean 
	 */
	public function checkErrorFlg() {
		$is_error = false;
		
		if($this->getFlgFileNotFound()){
			$this->addConsoleMessage(ConfigModelUpload::getMsgFileNotfound());
			$is_error = true;
			return !$is_error;
		}
		if($this->getFlgOperationFail()){
			$this->addConsoleMessage(ConfigModelUpload::getMsgOperationFail());
			$is_error = true;
			return !$is_error;
		}
		if($this->getFlgCsvNameError()){
			$this->addConsoleMessage(ConfigModelUpload::getMsgCsvNameError());
			$is_error = true;
			return !$is_error;
		}		
		if($this->getFlgFileFoundMulti()){
			$this->addConsoleMessage(ConfigModelUpload::getMsgFileFoundMulti());
			$is_error = true;
			return !$is_error;
		}
		if($this->getFlgDataError()){
			$this->addConsoleMessage(ConfigModelUpload::getMsgDataError());
			$is_error = true;
			return !$is_error;
		}
		if($this->getFlgFatalError()){
			$this->addConsoleMessage(ConfigModelUpload::getMsgFatalError());
			$is_error = true;
			return !$is_error;
		}
		return !$is_error;
	}
	/**
	 * エラーフラグリセット
	 */
	public function resetErrorFlg() {
		$this->setFlgFileNotFound(false);
		$this->setFlgOperationFail(false);
		$this->setFlgCsvNameError(false);
		$this->setFlgFileFoundMulti(false);
		$this->setFlgDataError(false);
		$this->setFlgFatalError(false);
	}
	//バッチ終了処理
	//==================================================================================================================
	/**
	 * バッチ終了処理（エラーログ、コンソールメッセージの出力）
	 * @return boolean 
	 */
	public function closeBatch() {
		$this->outputErrorLog();
		$this->outputConsole();
		die;
	}
	
	//setter,getter
	//==================================================================================================================
	//フラグコントロール
	//---------------------------------------------------------------------------------------------
	public function setFlgFileNotFound($bool){
		$this->flg_file_notfound = $bool;
		return true;
	}
	public function getFlgFileNotFound(){
		return $this->flg_file_notfound;
	}
	public function setFlgOperationFail($bool){
		$this->flg_operation_fail = $bool;
		return true;
	}
	public function getFlgOperationFail(){
		return $this->flg_operation_fail;
	}
	public function setFlgCsvNameError($bool){
		$this->flg_csv_name_error = $bool;
		return true;
	}
	public function getFlgCsvNameError(){
		return $this->flg_csv_name_error;
	}
	public function setFlgFileFoundMulti($bool){
		$this->flg_file_found_multi = $bool;
		return true;
	}
	public function getFlgFileFoundMulti(){
		return $this->flg_file_found_multi;
	}
	public function setFlgDataError($bool){
		$this->flg_data_error = $bool;
		return true;
	}
	public function getFlgDataError(){
		return $this->flg_data_error;
	}
	public function setFlgFatalError($bool){
		$this->flg_fatal_error = $bool;
		return true;
	}
	public function getFlgFatalError(){
		return $this->flg_fatal_error;
	}

	//DB
	//---------------------------------------------------------------------------------------------
	public function setDBH($pdo = null) {
		$this->dbh = $pdo;
		return true;
	}
	public function getDBH() {
		return $this->dbh;
	}

	//CSV
	//---------------------------------------------------------------------------------------------
	public function setFilepathCSV($path) {
		$this->filepath_csv = $path;
		return true;
	}
	public function getFilepathCSV() {
		return $this->filepath_csv;
	}
	public function setInputFile($file_access) {
		$this->input_file = $file_access;
		return true;
	}
	public function getInputFile() {
		return $this->input_file;
	}
	public function setFileObj($file_obj) {
		$this->file_obj = $file_obj;
		return true;
	}
	public function getFileObj() {
		return $this->file_obj;
	}
	public function setCSVTable($csv_tabledata) {
		$this->csv_table = $csv_tabledata;
		return true;
	}
	public function getCSVTable() {
		return $this->csv_table;
	}

	//This
	//---------------------------------------------------------------------------------------------
	public function setNameThis($filename) {
		$this->filename_this = $filename;
		return true;
	}
	public function getNameThis() {
		return $this->filename_this;
	}

	//Model
	//---------------------------------------------------------------------------------------------
	public function setMakerMst($model_name) {
		$this->maker_mst = $model_name;
		return true;
	}
	public function getMakerMst() {
		return $this->maker_mst;
	}
	public function setCarMst($model_name) {
		$this->car_mst = $model_name;
		return true;
	}
	public function getCarMst() {
		return $this->car_mst;
	}
	public function setModelData($model_name) {
		$this->model_data = $model_name;
		return true;
	}
	public function getModelData() {
		return $this->model_data;
	}
	public function setYearMst($model_name) {
		$this->year_mst = $model_name;
		return true;
	}
	public function getYearMst(){
		return $this->year_mst;
	}
	public function setTgYear($year) {
		$this->tg_year = $year;
		return true;
	}
	public function getTgYear() {
		return $this->tg_year;
	}
	
}
?>