<?php
require_once("/var/www/landclass_batch/common/config/CONFIG.php");
//更新 2023/04/21 
//定数定義　パス情報
//=====================================================================================================================
define('PATH_ERROR_LOG', Config::$path_root.'output/GetMonthlyAccessLog/');
define('PATH_OUTPUT_CSV', Config::$path_root.'output/GetMonthlyAccessLog/csv/');
define('PATH_ACCESS_LOG', Config::$path_root . Config::$path_db_model.'accessLog/');

class ConfigGetMonthlyAccessLog {
	
    //バッチファイル関連情報
    //=================================================================================================================
	private static $name_error_log	 = 'error.log';
	private static $name_access_log	 = 'AccessLog.php';
	private static $name_output_file_lead = 'ACCESS_LOG_';
	private static $name_output_file_ext = '.csv';
	
    //CSV項目関連情報
    //=================================================================================================================
	//CSV項目名
	private static $col_names = array(
		'年月日',
		'時間',
		'都道府県',
		'市区町村',
		'接続元IPアドレス',
		'ユーザーエージェント',
	);
	
    //メッセージ情報（ログファイル出力）
    //=================================================================================================================
	//メッセージ情報
	//-----------------------------------------------------------------------------------------------------------------
	private static $msg_batchfile_begin = 'バッチファイル処理開始';
	private static $msg_batchfile_done  = 'バッチファイル処理正常完了';

	//エラーメッセージ情報
	//-----------------------------------------------------------------------------------------------------------------

	private static $error_msg = array(
		"CHECK_INCLUDE_FILE_LACK"             	=> '管理クラス格納フォルダを確認してください',
		"CLASS_NOTFOUND_ACCESS_LOG"           	=> 'アクセスログ管理クラスがありません',
		"PROCESS_ALREADY_EXISTS"              	=> '同じバッチファイルが実行されています',
		"USAGE"									=> 'USAGE:GetMonthlyAccessLog.php "YYYYMM"',
		"DB_CONNECT_FAILED"                   	=> 'ＤＢ接続に失敗しました',
		"DB_ACCESS_FAILED_TBL_ACCESS_LOG"     	=> '⇒データ参照失敗（ＤＢ処理エラー）：　アクセスログ',
		"OUTPUT_CSV_FAILED"						=> 'CSVファイル出力失敗',
		"FATAL_ERROR"							=> '異常終了',
	);

    //メッセージ情報（コンソール出力）
    //=================================================================================================================
	private static $console_msg = array(
		"SUCCESS"							=> '出力成功',
		"TARGET_DATE_FAILED"				=> '対象年月不正（詳細はログをご確認下さい）',
		"OPERATION_FAILED"					=> '操作関連ミス（詳細はログをご確認下さい）',
		"FATAL_ERROR"						=> '異常終了（詳細はログをご確認下さい）',
	);

	//バッチファイル関連情報
	//-----------------------------------------------------------------------------------------------------------------
	/**
     * 
     * @return  エラーログファイル名
     */
    public static function getNameErrorLog() {
        return PATH_ERROR_LOG . ConfigGetMonthlyAccessLog::$name_error_log;
    }
	/**
	 * モデル名情報の取得関数
	 * @return modelname;
	 */
	public static function getNameAccessLog() {
	    return PATH_ACCESS_LOG . ConfigGetMonthlyAccessLog::$name_access_log;
	}
	/**
     * 
     * @return  出力ファイル名
     */
    public static function getOutputFileName($yyyymm) {
		return ConfigGetMonthlyAccessLog::$name_output_file_lead . $yyyymm . ConfigGetMonthlyAccessLog::$name_output_file_ext;
    }

	//CSV項目情報
	//-----------------------------------------------------------------------------------------------------------------
	/**
	 * 各項目名取得関数
	 * @param 	$col_num
     * @return  $name
     */
	public static function getColName() {
		return ConfigGetMonthlyAccessLog::$col_names;
	}

	//メッセージ情報
	//-----------------------------------------------------------------------------------------------------------------
	/**
	 * メッセージ取得(ログファイル出力)
     * @return String
     */
	public static function getMsgBatchfileBegin() {
		return ConfigGetMonthlyAccessLog::$msg_batchfile_begin;
	}
	public static function getMsgBatchfileDone() {
		return ConfigGetMonthlyAccessLog::$msg_batchfile_done;
	}
	
	/**
	 * エラーメッセージ取得(ログファイル出力)
     * @return String
     */
	public static function getErrorMsg($key) {
		if (array_key_exists($key, ConfigGetMonthlyAccessLog::$error_msg)) {
			return ConfigGetMonthlyAccessLog::$error_msg[$key];
		}
	}

	/**
	 * メッセージ取得（コンソール出力)
     * @return String
     */
	public static function getConsoleMsg($key) {
		if (array_key_exists($key, ConfigGetMonthlyAccessLog::$console_msg)) {
			return ConfigGetMonthlyAccessLog::$console_msg[$key];
		}
	}
}
?>
