<?php

/**
 * 月次アクセスログ取得バッチ
 */
ini_set('memory_limit', '512M');

require_once('/var/www/web/batch/www/root/php-work/batch/GetMonthlyAccessLog/config/ConfigGetMonthlyAccessLog.php');


// メインフロー実行
$class_batch = new GetMonthlyAccessLog();
$class_batch->execFlow($argc, $argv);

/**
 * バッチ処理実行クラス
 */
class GetMonthlyAccessLog {
	private $dbh        	= null;
	private $filename_csv	= null;
	private $filename_this  = null;
	private $file_obj 		= null;
	private $csv_table  	= array();
	private $console_msg 	= array();
	private $error_log_msgs	= array();
	private $access_log  	= null;
    private $yyyymm         = null;
	
	//コンソール出力メッセージ用フラグ
    private $flg_parameter_fail     = false;
	private $flg_operation_fail     = false;
    private $flg_target_date_fail   = false;
	private $flg_data_error 	    = false;
	private $flg_fatal_error 	    = false;
	
	function __construct() {
	
		//includeファイル存在チェック
		if(!include_once(ConfigGetMonthlyAccessLog::getNameAccessLog())){
			$this->addErrorLogMessage(ConfigGetMonthlyAccessLog::getErrorMsg('CLASS_NOTFOUND_ACCESS_LOG'), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
		}
		if(!$this->checkErrorFlg()) {
			$this->addErrorLogMessage(ConfigGetMonthlyAccessLog::getErrorMsg('CHECK_INCLUDE_FILE_LACK'), CONFIG::$LOG_LEVEL_FATAL);
			$this->outputErrorLog();
			$this->outputConsole();
			die;
		}
	}
	
	public function execFlow($argc, $argv) {
		
		// 処理開始
		$this->addErrorLogMessage(ConfigGetMonthlyAccessLog::getMsgBatchFileBegin(), CONFIG::$LOG_LEVEL_INFO);
		
		//同一プロセス実行確認
		$result = $this->isProcessing(basename($_SERVER['PHP_SELF']));
		if(!$this->checkErrorFlg()) {
			$this->outputErrorLog();
			$this->outputConsole();
			die;
		}

		//引数確認
		$this->checkParams($argc, $argv);
		if(!$this->checkErrorFlg()) {
			$this->outputErrorLog();
			$this->outputConsole();
			die;
		}
		
        // 引数セット
		$this->setTargetYYYYMM($argv[1]);
		
		//メイン処理
		$result = $this->createAccessLogCsv();
		if(!$this->checkErrorFlg()) {
			$this->outputErrorLog();
			$this->outputConsole();
			die;
		}

		//完了時のメッセージ表示。
		$this->addErrorLogMessage(ConfigGetMonthlyAccessLog::getMsgBatchFileDone(), CONFIG::$LOG_LEVEL_INFO);
		$this->outputErrorLog();
		$this->addConsoleMessage(ConfigGetMonthlyAccessLog::getConsoleMsg('SUCCESS'));
		$this->outputConsole();
	}
	
	//引数情報確認
	//------------------------------------------------------------------------------------------------------------------
	public function checkParams($argc, $argv) {
		$ret = false;
		
		if($argc <= 1 || $argc >= 3) {
			$this->addErrorLogMessage(ConfigGetMonthlyAccessLog::getErrorMsg('USAGE'), CONFIG::$LOG_LEVEL_WARN);
			$this->setFlgOperationFail(true);
			return $ret;
		}

		// 桁数チェック
		if (6 !== strlen($argv[1])) {
			$this->addErrorLogMessage(ConfigGetMonthlyAccessLog::getErrorMsg('USAGE'), CONFIG::$LOG_LEVEL_WARN);
			$this->setFlgParameterFail(true);
			return $ret;
		}

        // フォーマットチェック
        if ($argv[1] !== date('Ym', strtotime($argv[1] . "01"))) {
            $this->addErrorLogMessage(ConfigGetMonthlyAccessLog::getErrorMsg('USAGE'), CONFIG::$LOG_LEVEL_WARN);
			$this->setFlgParameterFail(true);
			return $ret;
        }

		$ret= true;
		return $ret;
	}

	//アクセスログCSV作成
	//------------------------------------------------------------------------------------------------------------------
	public function createAccessLogCsv() {
		$is_error = false;
		
		//DB接続
		$result = $this->setDBConnection(Config::getDsn(), Config::getUser(), Config::getPassword(),array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
		if(!$result) {
			return false;
		}

		// アクセスログテーブルのインスタンス生成
		$this->setAccessLog(new AccessLog());

		$row = null;
		try {
			// 月次アクセスログ取得
    	    $row = $this->selectMonthlyAccessLog();
		} catch (Exception $e) {
			// エラーログはセット済み
			$is_error = true;
			//PDOのコネクション破棄
			$this->setDBH(null);
			return !$is_error;
		}
		
		try {
            $handler = fopen(PATH_OUTPUT_CSV . ConfigGetMonthlyAccessLog::getOutputFileName($this->getTargetYYYYMM()), "w");
			if (!$handler) {
				// ファイルアクセス失敗
				throw new Exception("CSVファイルアクセス失敗");
			}

            // 2017/10/27 CSVファイルの文字コードをUTF-8ではなくSJISにして欲しいという要望があったため、
            //            文字コード変換処理を追加。
            // ヘッダ出力
            $header = ConfigGetMonthlyAccessLog::getColName();
            mb_convert_variables('sjis', 'utf-8', $header);
            fputcsv($handler, $header);
            // データ出力
            foreach((array)$row as $record) {
                $access_date = date_create($record['access_date']);
				// 2017/11/01 プロキシ経由の場合はsrc_ipが複数になる場合がある(「クライアントIP, プロキシ1 IP, プロキシ2 IP・・・」)
				//            そのため、カンマ以降は切り捨ててクライアントIPのみ取得するよう変更
				$src_ip = explode(',', $record['src_ip']); 
                $outputStr = array(date_format($access_date, 'Y-m-d'), date_format($access_date, 'H:i:s'), $record['model'], $src_ip[0], $record['user_agent']);
                mb_convert_variables('sjis', 'utf-8', $outputStr);
                fputcsv($handler, $outputStr);
            }

            fclose($handler);

		} catch (Exception $e) { 
            $this->addErrorLogMessage(ConfigGetMonthlyAccessLog::getErrorMsg('OUTPUT_CSV_FAILED'), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
            $is_error = true;
        }
		
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
			$this->addErrorLogMessage(ConfigGetMonthlyAccessLog::getErrorMsg('PROCESS_ALREADY_EXISTS'), CONFIG::$LOG_LEVEL_WARN);
			$this->setFlgOperationFail(true);
			return $is_processing;
		} else {
			$is_processing = true;
			return $is_processing;
		}
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
			$this->addErrorLogMessage(ConfigGetMonthlyAccessLog::getErrorMsg('DB_CONNECT_FAILED'), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);			
			return $ret;
		}
		$this->setDBH($pdo);
		
		$ret = true;
		return $ret;
	}
	
	/**
	 * 月次アクセスログ取得
	 * @return boolean 処理結果 結果レコード
	 */
	public function selectMonthlyAccessLog() {

		try {
			$valueArray = array();
			array_push($valueArray, $this->getTargetYYYYMM());
			// 検索条件セット
			if (!$this->getAccessLog()->setSearchCondition('MONTHLY', $valueArray)) {
				$this->addErrorLogMessage(ConfigGetMonthlyAccessLog::getErrorMsg('FATAL_ERROR'), CONFIG::$LOG_LEVEL_FATAL);
				$this->setFlgFatalError(true);
				throw new Exception();
			}

			// ソート条件セット
			$this->getAccessLog()->setSortCondition('MONTHLY');

			// アクセスログ参照
			$row = $this->getAccessLog()->select($this->getDBH());
		
			return $row;
		} catch (PDOException $e) {
			$this->addErrorLogMessage(ConfigGetMonthlyAccessLog::getErrorMsg('DB_ACCESS_FAILED_TBL_ACCESS_LOG'), CONFIG::$LOG_LEVEL_FATAL);
			$this->setFlgFatalError(true);
			throw $e;
		}
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
				error_log($log, 3, ConfigGetMonthlyAccessLog::getNameErrorLog());
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
		
        if($this->getFlgParameterFail()){
			$this->addConsoleMessage(ConfigGetMonthlyAccessLog::getConsoleMsg('TARGET_DATE_FAILED'));
			$is_error = true;
			return !$is_error;
		}
		if($this->getFlgDataError()){
			$this->addConsoleMessage(ConfigGetMonthlyAccessLog::getConsoleMsg('DATA_ERROR'));
			$is_error = true;
			return !$is_error;
		}
		if($this->getFlgOperationFail()){
			$this->addConsoleMessage(ConfigGetMonthlyAccessLog::getConsoleMsg('OPERATION_FAILED'));
			$is_error = true;
			return !$is_error;
		}
		if($this->getFlgFatalError()){
			$this->addConsoleMessage(ConfigGetMonthlyAccessLog::getConsoleMsg('FATAL_ERROR'));
			$is_error = true;
			return !$is_error;
		}
		return !$is_error;
	}
	 
	//setter,getter
	//==================================================================================================================
    // エラーフラグ
	public function setFlgParameterFail($bool){
		$this->flg_parameter_fail = $bool;
		return true;
	}
	public function getFlgParameterFail(){
		return $this->flg_parameter_fail;
	}
    public function setFlgOperationFail($bool){
		$this->flg_operation_fail = $bool;
		return true;
	}
	public function getFlgOperationFail(){
		return $this->flg_operation_fail;
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

    // DBハンドラ
	public function setDBH($pdo = null) {
		$this->dbh = $pdo;
		return true;
	}
	public function getDBH() {
		return $this->dbh;
	}

    // モデル
	public function setAccessLog($model_name) {
		$this->access_log = $model_name;
		return true;
	}
	public function getAccessLog() {
		return $this->access_log;
	}

    // 引数(対象年月)
    public function setTargetYYYYMM($yyyymm) {
        $this->targetYYYYMM = $yyyymm;
    }
    public function getTargetYYYYMM() {
        return $this->targetYYYYMM;
    }	
}
?>