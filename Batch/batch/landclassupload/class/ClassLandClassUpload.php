<?php

//定数定義
//=====================================================================================================================
/**
　* 更新2023/04/12 ループでファイルごとに取り込みできるが、エラー対応ができていない
 * バッチ処理実行クラス
 */
class ClassLandClassUpload {
	private $dbh        		= null;
	private $filepath_csv 		= null;
	private $input_file			= null;
	private $filename_this  	= null;
	private $file_obj 			= null;
	private $csv_table  		= array();
	private $console_msg 		= array();
	private $error_log_msgs	    = array();
	private $prefectures_mst  	= null;
	private $municipality_mst  	= null;
	private $landclass_data 	= null;
	private $landclass_mst 	    = null;
	private $landclass_csv 	    = array();
	private $landclass_names    = array();
	private $display_orders     = array();

	//コンソール出力メッセージ用フラグ
	private $flg_file_notfound		= false;
	private $flg_operation_fail		= false;
	private $flg_csv_name_error		= false;
	private $flg_file_found_multi	= false;
	private $flg_data_error			= false;
	private $flg_fatal_error		= false;

	function __construct() {

		//includeファイル存在チェック
		if(!include_once(PATH_PREFECTURES_MST.ConfigLandClassUpload::getNamePrefecturesMst())){
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgIncludeNotfoundPrefecturesMst(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
		}
		if(!include_once(PATH_MUNICIPALITY_MST.ConfigLandClassUpload::getNameMunicipalityMst())){
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgIncludeNotfoundMunicipalityMst(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
		}
		if(!include_once(PATH_LANDCLASS_DATA.ConfigLandClassUpload::getNameLandClassData())) {
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgIncludeNotfoundLandClassData(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
		}
		if(!include_once(PATH_LANDCLASS_MST.ConfigLandClassUpload::getNameLandClassMst())) {
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgIncludeNotfoundLandClassMst(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
		}
		if(!$this->checkErrorFlg()) {
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgIncludeFileLack(), CONFIG::$LOG_LEVEL_NULL);
			$this->closeBatch();
		}
	}

	public function execFlow() {
		$is_error = false;

		$this->addErrorLogMessage(ConfigLandClassUpload::getMsgBatchFileBegin(), CONFIG::$LOG_LEVEL_INFO);
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
		
		$is_error = false;

		//DB接続
		$result = $this->setDBConnection(Config::getDsn(), Config::getUser(), Config::getPassword(),array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

		if(!$result) {
			$this->closeBatch();
			return false;
		}
	

		if(!$this->checkErrorFlg()) {
			$is_error = true;
			$this->closeBatch();
		}

		//DBモデルをセット
		$this->setDBModels();
		$this->getDBH()->beginTransaction();

		$this->files = glob($this->getFilepathCSV().'*');
		if (1 > count($this->files)) {
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgCsvNotfound().$this->getInputFile(), CONFIG::$LOG_LEVEL_WARN);
			$this->addConsoleMessage(ConfigLandClassUpload::getMsgFileNotfound());
			//$this->setFlgFileNotFound(true);
			$this->closeBatch();
			$is_error = true;
			return !$is_error;
		}
		
		for($this->num = 0 ; $this->num < count($this->files) ; $this->num++){ 
			
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
			
			//DBトランザクション開始
			$this->setTransaction();
			if(!$this->checkErrorFlg()) {
				$is_error = true;
				$this->closeBatch();
			}
			
		}
		
			//エラーなしの場合、完了処理
			//------------------------------------------------------------------------------------------------------------------
			//対象ファイル削除
		for($this->num = 0 ; $this->num < count($this->files) ; $this->num++){
			$result = false;
			try {
				$result = unlink($this->deletefiles[$this->num]);
			} catch (Exception $e) {}
			if(!$result && $this->num == count($this->files)-1 ) {
				//削除失敗
				$is_error = true;
				$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgBatchSuccessDeleteFail(), CONFIG::$LOG_LEVEL_INFO);
				$this->addConsoleMessage(ConfigLandClassUpload::getMsgBatchSuccessDeleteFail());
			}
			if(!$is_error && $this->num == count($this->files)-1 ) {
				//正常完了
				$this->addErrorLogMessage(ConfigLandClassUpload::getMsgBatchFileDone(), CONFIG::$LOG_LEVEL_INFO);
				$this->addConsoleMessage(ConfigLandClassUpload::getMsgBatchSuccess());
			}	
		}

		//コミット
		$this->getDBH()->commit();

		//PDOのコネクション破棄
		$this->setDBH(null);

		$this->closeBatch();
	}

	/**
	 * バッチファイル名確認
	 *
	 */
	public function checkBatchFileName() {
		$ret = false;

		//バッチファイル名が指定したものと不一致なら、処理終了
		if(basename($_SERVER['PHP_SELF']) != ConfigLandClassUpload::getNameExec()) {
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgBatchfileName(), CONFIG::$LOG_LEVEL_WARN);
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

		// 1) ファイル情報セット
		//-------------------------------------------------------------------------------------------------------------
		$setfiles = $this->files;
		$this->deletefiles = $setfiles;
		
		if (1 > count($this->files)) {
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgCsvNotfound().$this->getInputFile(), CONFIG::$LOG_LEVEL_WARN);
			$this->setFlgFileNotFound(true);
			$is_error = true;
			return !$is_error;
		}

		$this->setInputFile($setfiles[$this->num]);

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
		$result = $this->isValid_Name(mb_convert_encoding($this->getFileObj()->getFileName(), "SJIS", "auto"));

		if(!$result) {
			$is_error = true;
			return !$is_error;
		}

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

		//(最終行は改行とする)
		$csvTable = $this->getCSVTable();
		for($i = 0; $i < sizeof($csvTable); $i++){
			$row_valid_fail = false;
			$csv_row = $csvTable[$i];

			for($j = 0; $j < sizeof($csv_row); $j++) {
				$result = $this->isValidColData($csv_row[$j], $j);
				if(!$result) {
					$row_valid_fail = true;
				}
			}
			if($row_valid_fail) {
				$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgCsvrowFormat().($i+1), CONFIG::$LOG_LEVEL_ERROR);
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

		try {
			$result = $this->execDBProcess();
			if($result) {
				
			} else {
				$this->getDBH()->rollBack();
				$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgTransactionFail(), CONFIG::$LOG_LEVEL_FATAL);
				$this->setFlgFatalError(true);
				$is_error = true;
			}
		} catch (Exception $e) { $this->getDBH()->rollBack(); $this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgTransactionFail(), CONFIG::$LOG_LEVEL_FATAL); $is_error = true;}

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
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgIsProcessing(), CONFIG::$LOG_LEVEL_WARN);
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
	 * インプットファイルが取得可能か
	 * @return boolean エラーなし:true
	 */
	public function isAvailableInputFile() {
		$is_error = false;

		//ファイル取得
		try {
			$file_obj = new SplFileObject($this->getInputFile(), $open_mode = 'r');
		} catch (Exception $e ) {
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgCsvNotfound().$this->getInputFile(), CONFIG::$LOG_LEVEL_WARN);
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

		//アンダーバーが2つあるか
		$count = substr_count($filename, "_");
		if($count != 2){
			$is_error = true;
		}

		//CSVファイルから等地名を取得
		$landclass_csv = explode('_',$filename);
		$landclass_csvname = $landclass_csv[1];
		$display_order = strstr($landclass_csv[2],'.',true);
		$this->landclass_csvname = mb_convert_encoding($landclass_csvname, "UTF-8","shift-jis");

		//表示順をチェック
		if(!preg_match('/^[1-9]{1,1}$/',$display_order)) {
			$is_error = true;
		}

		$this->landclass_names[] = $this->landclass_csvname;
		$this->display_orders[] = $display_order;

		if($is_error) {
			//エラーメッセージ記録
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgCsvNameNotcorrect(), CONFIG::$LOG_LEVEL_ERROR);
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
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgCsvIsempty(), CONFIG::$LOG_LEVEL_ERROR);
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
			if(!preg_match(ConfigLandClassUpload::getColPreg($col_num), $col_data)) {
				$is_error = true;
				$this->addErrorLogMessage(ConfigLandClassUpload::getColName($col_num).' : '.ConfigLandClassUpload::getColValidFailMsg($col_num), CONFIG::$LOG_LEVEL_ERROR);
				$this->setFlgDataError(true);
			}
		}
		//空を許可する項目か？
		else {
			$is_error = true;
			$this->addErrorLogMessage(ConfigLandClassUpload::getColName($col_num).' : '.ConfigLandClassUpload::getMsgCantBeNull(), CONFIG::$LOG_LEVEL_ERROR);
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
		$locale = setlocale(LC_CTYPE, 0);
		setlocale(LC_ALL, 'ja_JP.UTF-8');

		$csv_table = array();

		while( $csv_row = $file_obj->fgetcsv()) {
			if (count($csv_row) < 2){
				break;
			}
			array_push($csv_row, $this->landclass_csvname);
			array_push($csv_table, $csv_row);
		}
		setlocale(LC_ALL, $locale);
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

		$namelist_prefectures = array();
		$namelist_prefectures_municipality = array();

		//csvデータ行ループ　最終行は改行とする。
		$csvTable = $this->getCSVTable();
		for($i = 0 ; $i < sizeof($csvTable); $i++) {
			$csv_row 	= $csvTable[$i];

			$prefectures_name   = trim($csv_row[0]);
			$municipality_name  = trim($csv_row[1]);
			$landclass_name		    = trim($csv_row[3]);

			//都道府県確認
			//---------------------------------------------------------------------------------
			//既出の都道府県か？
			if(!in_array($prefectures_name, $namelist_prefectures)) {
				//都道府県リストに追加
				$namelist_prefectures[] = $prefectures_name;
			}

			//都道府県・市区町村確認
			//---------------------------------------------------------------------------------
			$flg_exist = false;
			foreach ($namelist_prefectures_municipality as $nmc_row) {
				//既出の都道府県・市区町村の組み合わせか？
				if($nmc_row['prefectures'] == $prefectures_name && $nmc_row['municipality'] == $municipality_name) {
					$flg_exist = true;
				}
			}

			if(!$flg_exist) {
				//都道府県・市区町村に追加
				$work_array = array();
				$work_array['prefectures'] = $prefectures_name;
				$work_array['municipality']   = $municipality_name;
				$namelist_prefectures_municipality[]     = $work_array;
			}

			//等地名に重複がないか
			$landclass_count = array_count_values($this->landclass_names);
			$max = max($landclass_count);
			if ($max != 1){
				$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgTableinsertFailSameLandClassName(), CONFIG::$LOG_LEVEL_FATAL);
				$this->setFlgFileFoundMulti(true);
				return false;
			}

			//表示順に重複がないか
			$order_count = array_count_values($this->display_orders);
			$max = max($order_count);
			if ($max != 1){
				$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgTableinsertFailDisplayOrder(), CONFIG::$LOG_LEVEL_FATAL);
				$this->setFlgFileFoundMulti(true);
				return false;
			}

			//等地データ重複確認
			//---------------------------------------------------------------------------------
			$is_existing = $this->isExistingKeyLandClassData($landclass_name, $prefectures_name, $municipality_name);
			if($is_existing) { $is_error = true;}

			//等地データをDB登録
			//---------------------------------------------------------------------------------	
			$result = $this->insertLandClassData($csv_row);
			if(!$result) $is_error = true;

		}

		//等地名マスタをDB登録
		//-------------------------------------------------------------------------------------
		for ($num = 0 ; $num < count($this->display_orders) ; $num++){

			$result = $this->insertLandClassMst($num);

			if(!$result) {
				$this->addErrorLogMessage($prefectures_name, CONFIG::$LOG_LEVEL_FATAL);
				$this->setFlgFatalError(true);
				$is_error = true;
			}
		}

		//都道府県をDB登録
		//-------------------------------------------------------------------------------------
		foreach($namelist_prefectures as $prefectures_name) {
			$result = $this->insertPrefecturesMst($prefectures_name);
			if(!$result) {
				$this->addErrorLogMessage($prefectures_name, CONFIG::$LOG_LEVEL_FATAL);
				$this->setFlgFatalError(true);
				$is_error = true;
			}
		}

		//都道府県・市区町村をDB登録
		//-------------------------------------------------------------------------------------
		foreach($namelist_prefectures_municipality as $add_record ) {
			$result = $this->insertMunicipalityMst($add_record);
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
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgDBconnectFail(), CONFIG::$LOG_LEVEL_FATAL);
			$this->addConsoleMessage(ConfigLandClassUpload::getMsgFatalError());
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

		$this->setPrefecturesMst(new PrefecturesMst());
			$this->getPrefecturesMst()->setColumnValue('cre_usr',ConfigLandClassUpload::getCreateUser());
			$this->getPrefecturesMst()->setColumnValue('cre_prg',ConfigLandClassUpload::getCreateProgram());
			$this->getPrefecturesMst()->setColumnValue('upd_usr',ConfigLandClassUpload::getUpdateUser());
			$this->getPrefecturesMst()->setColumnValue('upd_prg',ConfigLandClassUpload::getUpdateProgram());
		$this->setMunicipalityMst(new MunicipalityMst());
			$this->getMunicipalityMst()->setColumnValue('cre_usr',ConfigLandClassUpload::getCreateUser());
			$this->getMunicipalityMst()->setColumnValue('cre_prg',ConfigLandClassUpload::getCreateProgram());
			$this->getMunicipalityMst()->setColumnValue('upd_usr',ConfigLandClassUpload::getUpdateUser());
			$this->getMunicipalityMst()->setColumnValue('upd_prg',ConfigLandClassUpload::getUpdateProgram());
		$this->setLandClassData(new LandClassData());
			$this->getLandClassData()->setColumnValue('cre_usr',ConfigLandClassUpload::getCreateUser());
			$this->getLandClassData()->setColumnValue('cre_prg',ConfigLandClassUpload::getCreateProgram());
			$this->getLandClassData()->setColumnValue('upd_usr',ConfigLandClassUpload::getUpdateUser());
			$this->getLandClassData()->setColumnValue('upd_prg',ConfigLandClassUpload::getUpdateProgram());
		$this->setLandClassMst(new LandClassMst());
			$this->getLandClassMst()->setColumnValue('cre_usr',ConfigLandClassUpload::getCreateUser());
			$this->getLandClassMst()->setColumnValue('cre_prg',ConfigLandClassUpload::getCreateProgram());
			$this->getLandClassMst()->setColumnValue('upd_usr',ConfigLandClassUpload::getUpdateUser());
			$this->getLandClassMst()->setColumnValue('upd_prg',ConfigLandClassUpload::getUpdateProgram());
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
		
		if(!($this->getPrefecturesMst() != null || $this->getPrefecturesMst() instanceof LandClass)) $is_error = true;
		if(!($this->getMunicipalityMst()   != null || $this->getMunicipalityMst()   instanceof LandClass)) $is_error = true;
		if($this->num == 0){ 
		if(!($this->getLandClassData()!= null || $this->getLandClassData()instanceof LandClass)) $is_error = true;
		}
		if(!($this->getLandClassMst()!= null || $this->getLandClassMst()instanceof LandClass)) $is_error = true;
		
		if($is_error) {
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgTabledeleteFail(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
			return !$is_error;
		}
		
		$ret = $this->getPrefecturesMst()->deleteAllRecords($this->getDBH());
		if(!$ret) $is_error = true;
		$ret = $this->getMunicipalityMst()->deleteAllRecords($this->getDBH());
		if(!$ret) $is_error = true;
		if($this->num == 0){ 
		$ret = $this->getLandClassData()->deleteAllRecords($this->getDBH());
		if(!$ret) $is_error = true;
		} 
		$ret = $this->getLandClassMst()->deleteAllRecords($this->getDBH());
		if(!$ret) $is_error = true;
		
		if($is_error) {
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgTabledeleteFail(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
		}

		return !$is_error;
	}

	/**
	 * 等地データ登録
	 * @return boolean 処理結果 完了:true
	 */
	public function insertLandClassData($add_record) {
		$ret = false;
		$landclass_data = $this->getLandClassData();
		for($i = 0; $i<sizeof($add_record); $i++) {
			$landclass_data->setColumnValue(ConfigLandClassUpload::getColNameDB($i),$add_record[$i]);
		}

		$result = $landclass_data->insertRecord($this->getDBH());

		if(!$result) {
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgTableinsertFailLandClassData(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
			return $ret;
		}
		$ret = true;
		return $ret;
	}

	/**
	 * 等地マスタ登録
	 * @return boolean 処理結果 完了:true
	 */
	public function insertLandClassMst($num) {
		$ret = false;
		$this->getLandClassMst()->setColumnValue('landclass_name',$this->landclass_names[$num]);
		$this->getLandClassMst()->setColumnValue('display_order',$this->display_orders[$num]);

		$result = $this->getLandClassMst()->insertRecord($this->getDBH());

		if(!$result) {
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgTableinsertFailLandClassMst(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
			return $ret;
		}
		$ret = true;
		return $ret;
	}

	/**
	 * 等地データ重複チェック
	 * @return boolean 処理結果 完了:true
	 */
	public function isExistingKeyLandClassData($landclass_name, $prefectures_name, $municipality_name) {
		$ret = false;

		$landclass_data = $this->getLandClassData();
		$landclass_data->setColumnValue(ConfigLandClassUpload::getColNameDB(0),$prefectures_name);
		$landclass_data->setColumnValue(ConfigLandClassUpload::getColNameDB(1),$municipality_name);
		$landclass_data->setColumnValue(ConfigLandClassUpload::getColNameDB(3),$landclass_name);


		$is_exist = $landclass_data->isExist($this->getDBH());
		if($is_exist) {
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgTableinsertFailExist().$prefectures_name.', '.$municipality_name.', '.$landclass_name, CONFIG::$LOG_LEVEL_ERROR);
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
	 * 都道府県マスター登録
	 * @return boolean 処理結果 完了:true
	 */
	public function insertPrefecturesMst($prefectures_name) {
		$ret = false;
		$this->getPrefecturesMst()->setColumnValue('prefectures',$prefectures_name);
		$result = $this->getPrefecturesMst()->insertRecord($this->getDBH());
		if(!$result) {
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgTableinsertFailPrefecturesMst(), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
			return $ret;
		}

		$ret = true;
		return $ret;
	}

	/**
	 * 市区町村マスター登録
	 * @return boolean 処理結果 完了:true
	 */
	public function insertMunicipalityMst($add_record) {
		$ret = false;

		$this->getMunicipalityMst()->setColumnValue('prefectures',$add_record['prefectures']);
		$this->getMunicipalityMst()->setColumnValue('municipality',$add_record['municipality']);

		$result = $this->getMunicipalityMst()->insertRecord($this->getDBH());
		if(!$result) {
			$this->addErrorLogMessage(ConfigLandClassUpload::getErrorMsgTableinsertFailMunicipalityMst(), CONFIG::$LOG_LEVEL_FATAL);
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
				error_log($log, 3, PATH_ERROR_LOG.ConfigLandClassUpload::getNameErrorLog());
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
			$this->addConsoleMessage(ConfigLandClassUpload::getMsgFileNotfound());
			$is_error = true;
			return !$is_error;
		}
		if($this->getFlgOperationFail()){
			$this->addConsoleMessage(ConfigLandClassUpload::getMsgOperationFail());
			$is_error = true;
			return !$is_error;
		}
		if($this->getFlgCsvNameError()){
			$this->addConsoleMessage(ConfigLandClassUpload::getMsgCsvNameError());
			$is_error = true;
			return !$is_error;
		}
		if($this->getFlgFileFoundMulti()){
			$this->addConsoleMessage(ConfigLandClassUpload::getMsgFileFoundMulti());
			$is_error = true;
			return !$is_error;
		}
		if($this->getFlgDataError()){
			$this->addConsoleMessage(ConfigLandClassUpload::getMsgDataError());
			$is_error = true;
			return !$is_error;
		}
		if($this->getFlgFatalError()){
			$this->addConsoleMessage(ConfigLandClassUpload::getMsgFatalError());
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
		//$this->csv_table = array_merge($this->csv_table,$csv_tabledata);
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

	//LandClass
	//---------------------------------------------------------------------------------------------
	public function setPrefecturesMst($landclass_name) {
		$this->prefectures_mst = $landclass_name;
		return true;
	}

	public function getPrefecturesMst() {
		return $this->prefectures_mst;
		
	}

	public function setMunicipalityMst($landclass_name) {
		$this->municipality_mst = $landclass_name;
		return true;
	}

	public function setLandClassMst($landclass_name) {
		$this->landclass_mst = $landclass_name;
		return true;
	}

	public function getMunicipalityMst() {
		return $this->municipality_mst;
	}

	public function getLandClassMst() {
		return $this->landclass_mst;
	}

	public function setLandclassData($landclass_name) {
		$this->landclass_data = $landclass_name;
		return true;
	}

	public function getLandClassData() {
		return $this->landclass_data;
	}

}
?>
