<?php
require_once("/var/www/web/batch/www/root/php-work/common/config/CONFIG.php");

/**
 * バッチ処理用コンフィグファイル。バッチ起動ファイル内で最初に読み込む。
 * パスは末尾に/（スラッシュ）を付ける
 *　更新：2017/10/16
 */
//定数定義　パス情報
//=====================================================================================================================
define('PATH_ERROR_LOG', CONFIG::$path_root.'output/modelupload/');
define('PATH_CSV',		 CONFIG::$path_root.'input/modelupload/');
define('PATH_MAKER_MST', CONFIG::$path_db_model.'makermst/');
define('PATH_CAR_MST',	 CONFIG::$path_db_model.'carmst/');
define('PATH_MODEL_DATA',CONFIG::$path_db_model.'modeldata/');
define('PATH_YEAR_MST',	 CONFIG::$path_db_model.'yearmst/');

class ConfigModelUpload {

    //DB操作情報
    //=================================================================================================================
	private static $create_user    = 'BATCH';
    private static $update_user    = 'BATCH';
    private static $create_program = 'ModelUpload';
	private static $update_program = 'ModelUpload';
	
    //バッチファイル関連情報
    //=================================================================================================================
	private static $name_exec	 	 = 'ModelUpload.php';
	private static $name_batch_class = 'ClassModelUpload.php';
	private static $name_error_log	 = 'error.log';
	private static $name_car_mst   	 = 'CarMst.php';
	private static $name_maker_mst 	 = 'MakerMst.php';
	private static $name_model_data	 = 'ModelData.php';
	private static $name_year_mst  	 = 'YearMst.php';

    //CSV名前情報
    //=================================================================================================================
	private static $preg_csv_name_defined	= 'kataclass_';	
	
    //CSV項目関連情報
    //=================================================================================================================
	//CSV項目名
	private static $col_names = array(
		0  => '型式',
		1  => 'メーカー名',
		2  => '車名',
		3  => '発売年月日',
		4  => '自動車ﾀｲﾌﾟ',
		5  => '国産/外車コード',
		6  => '対人クラス(n)',
		7  => '対人クラス(n-1)',
		8  => '対物クラス(n)',
		9  => '対物クラス(n-1)',
		10 => '搭傷クラス(n)',
		11 => '搭傷クラス(n-1)',
		12 => '車両クラス(n)',
		13 => '車両クラス(n-1)',
		14 => '担保種目(n)',
		15 => '担保種目(n-1)',
	);
	
	private static $col_names_DB = array(
		0  => 'model',
		1  => 'maker_name',
		2  => 'car_name',
		3  => 'release_date',
		4  => 'car_type',
		5  => 'area_code',
		6  => 'interpersonal_class',
		7  => 'last_interpersonal_class',
		8  => 'objectve_class',
		9  => 'last_objectve_class',
		10 => 'personal_accident_class',
		11 => 'last_personal_accident_class',
		12 => 'vehicle_class',
		13 => 'last_vehicle_class',
		14 => 'collateral_event',
		15 => 'last_collateral_event',
	);
	
	//発売年月日、坦種目(n)、坦種目(n-1)は現状、nullを許可する。判定利用情報。
	private static $null_allow_colnums = array(3,14,15);
	
	//CSV項目バリデーション
	//-----------------------------------------------------------------------------------------------------------------
	private static $col_pregs = array(
		0  => '/^[ -\~]{1,15}$/',
		1  => '/^[（）＆ァ-ヶ､ー -\~･]{1,20}$/u',
		2  => '/^[（）ァ-ヶ､ー -\~･]{1,80}$/u',
		3  => '/^[0-9]{8,8}$/',
		4  => '/^[124]{1,1}$/',
		5  => '/^[1-2]{1,1}$/',
		6  => '/^[0-9 -\/:-@[-`\{\~]{1,8}$/',
		7  => '/^[0-9 -\/:-@[-`\{\~]{1,8}$/',
		8  => '/^[0-9 -\/:-@[-`\{\~]{1,8}$/',
		9  => '/^[0-9 -\/:-@[-`\{\~]{1,8}$/',
		10 => '/^[0-9 -\/:-@[-`\{\~]{1,8}$/',
		11 => '/^[0-9 -\/:-@[-`\{\~]{1,8}$/',
		12 => '/^[0-9 -\/:-@[-`\{\~]{1,8}$/',
		13 => '/^[0-9 -\/:-@[-`\{\~]{1,8}$/',
		14 => '/^[0-9 -\/:-@[-`\{\~]{1,8}$/',
		15 => '/^[0-9 -\/:-@[-`\{\~]{1,8}$/',
	);

	private static $col_valid_fail_msgs = array(
		0  => '半角英数記号、1～15文字',
		1  => '半角英数記号全角カナ）、（のみ20文字',
		2  => '半角英数記号全角カナ）、（のみ80文字',
		3  => '半角数字のみ8文字',
		4  => '数字 1 or 2 or 4のみ1文字',
		5  => '数字 1 or 2のみ1文字',
		6  => '記号・数字1-8',
		7  => '記号・数字1-8',
		8  => '記号・数字1-8',
		9  => '記号・数字1-8',
		10 => '記号・数字1-8',
		11 => '記号・数字1-8',
		12 => '記号・数字1-8',
		13 => '記号・数字1-8',
		14 => '記号・数字1-8',
		15 => '記号・数字1-8',
	);
	
	private static $msg_cantbe_null = '空欄不可';

    //メッセージ情報（ログファイル出力）
    //=================================================================================================================
	//メッセージ
	//-----------------------------------------------------------------------------------------------------------------
	private static $msg_batchfile_begin = 'バッチファイル処理開始';
	private static $msg_batchfile_done  = 'バッチファイル処理正常完了';

	//エラーメッセージ
	//-----------------------------------------------------------------------------------------------------------------
	private static $error_msg_include_file_lack				= '管理クラス格納フォルダを確認してください';
	private static $error_msg_include_notfound_maker_mst	= 'メーカー名マスタ管理クラスがありません';
	private static $error_msg_include_notfound_car_mst		= '車名マスタ管理クラスがありません';	
	private static $error_msg_include_notfound_model_data	= '型式管理クラスがありません';	
	private static $error_msg_include_notfound_year_mst		= '対象年管理クラスがありません';	
	private static $error_msg_batchfile_name				= 'バッチファイル名不一致：再設定してください';
	private static $error_msg_is_processing					= '同じバッチファイルが実行されています';
	private static $error_msg_csv_notfound					= 'アップロードファイルが見つかりません。';
	private static $error_msg_batch_success_delete_fail 	= "取り込みは完了しましたが、CSVファイルの削除に失敗しました。（ファイルアクセス権限などを確認してください）";
	private static $error_msg_csv_found_multi				= 'フォルダー内にアップロードファイルが複数見つかりました。';
	private static $error_msg_csv_name_notcorrect			= '取り込みファイル名が不正です';
	private static $error_msg_csv_isempty					= 'CSVが空です';
	private static $error_msg_csvrow_format					= '→CSVデータフォーマットエラー　行位置　： ';
	private static $error_msg_dbconnect_fail				= 'ＤＢ接続に失敗しました';
	private static $error_msg_transaction_fail				= 'トランザクション実行エラー、ロールバック実行';
	private static $error_msg_tabledelete_fail				= '既存のテーブルレコード消去に失敗しました';
	private static $error_msg_tableinsert_fail_exist		= 'キー項目の重複があります。 ※重複したキー ：　';
	private static $error_msg_tableinsert_fail_year_mst		= '⇒データ登録失敗（ＤＢ処理エラー）：　対象年マスター';
	private static $error_msg_tableinsert_fail_model_data	= '⇒データ登録失敗（ＤＢ処理エラー）：　型式データ';
	private static $error_msg_tableinsert_fail_maker_mst	= '⇒データ登録失敗（ＤＢ処理エラー）：　メーカー名マスター';
	private static $error_msg_tableinsert_fail_car_mst		= '⇒データ登録失敗（ＤＢ処理エラー）：　車名マスター';

    //メッセージ情報（コンソール出力）
    //=================================================================================================================
	private static $msg_batch_success 			  = "取り込み成功";
	private static $msg_batch_success_delete_fail = "取り込み成功。アップロードファイル削除失敗（詳細はログをご確認下さい）";
	private static $msg_file_notfound			  = "取り込み対象無し（詳細はログをご確認下さい）";
	private static $msg_operation_fail			  = "操作関連ミス（詳細はログをご確認下さい）";
	private static $msg_csv_name_error			  = "ファイル名不正（詳細はログをご確認下さい）";
	private static $msg_file_found_multi		  = "取り込み対象複数（詳細はログをご確認下さい）";
	private static $msg_data_error				  = "データ処理失敗（詳細はログをご確認下さい）";
	private static $msg_fatal_error				  = "異常終了（詳細はログをご確認下さい）";
 
	//getter
    //=================================================================================================================
	//DB操作情報
	//-----------------------------------------------------------------------------------------------------------------
	/**
     * DB操作情報取得
     * @return  DB操作情報
     */
	public static function getCreateUser(){
		return ConfigModelUpload::$create_user;
	}
	public static function getUpdateUser(){
		return ConfigModelUpload::$update_user;
	}
	public static function getCreateProgram(){
		return ConfigModelUpload::$create_program;
	}
	public static function getUpdateProgram(){
		return ConfigModelUpload::$update_program;
	}

	//バッチファイル関連情報
	//-----------------------------------------------------------------------------------------------------------------
	/**
     * 
     * @return  実行（バッチ）ファイル名
     */
	public static function getNameExec() {
		return ConfigModelUpload::$name_exec;
	}
	/**
     * 
     * @return  バッチクラスファイル名
     */
    public static function getNameBatchClass() {
        return ConfigModelUpload::$name_batch_class;
    }
	/**
     * 
     * @return  エラーログファイル名
     */
    public static function getNameErrorLog() {
        return ConfigModelUpload::$name_error_log;
    }
	/**
	 * モデル名情報の取得関数
	 * @return modelname;
	 */
	public static function getNameCarMst() {
	    return ConfigModelUpload::$name_car_mst;
	}
	public static function getNameMakerMst() {
		return ConfigModelUpload::$name_maker_mst;
	}
	public static function getNameModelData() {
		return ConfigModelUpload::$name_model_data;
	}
	public static function getNameYearMst() {
		return ConfigModelUpload::$name_year_mst;
	}

    //CSV名前情報
	//-----------------------------------------------------------------------------------------------------------------
	/**
	 * CSVファイル名確認
     * @return  $preg_csv_name_defined
     */	
	public static function getPregCsvNameDefined() {
		return ConfigModelUpload::$preg_csv_name_defined;
	}
	
	//CSV項目情報
	//-----------------------------------------------------------------------------------------------------------------
	/**
	 * 各項目名取得関数
	 * @param 	$col_num
     * @return  $name
     */
	public static function getColName($col_num) {
		return ConfigModelUpload::$col_names[$col_num];
	}
	/**
	 * 各項目名(DB)取得関数
	 * @param 	$col_num
     * @return  $name
     */
	public static function getColNameDB($col_num) {
		return ConfigModelUpload::$col_names_DB[$col_num];
	}
	/**
	 * バリデーション条件の取得関数
     * @return  $name
     */
	public static function getColPreg($col_num) {
		return ConfigModelUpload::$col_pregs[$col_num];
	}
	public static function getNullAllowColumns() {
		return ConfigModelUpload::$null_allow_colnums;
	}

	//メッセージ情報
	//-----------------------------------------------------------------------------------------------------------------
	/**
	 * バリデーション失敗時のメッセージ取得
     * @return  $name
     */
	public static function getColValidFailMsg($col_num) {
		return ConfigModelUpload::$col_valid_fail_msgs[$col_num];
	}
	public static function getMsgCantBeNull(){
		return ConfigModelUpload::$msg_cantbe_null;
	}
	/**
	 * メッセージ取得(ログファイル出力)
     * @return String
     */
	public static function getMsgBatchfileBegin()	{ return ConfigModelUpload::$msg_batchfile_begin;}
	public static function getMsgBatchfileDone()	{ return ConfigModelUpload::$msg_batchfile_done;}
	/**
	 * エラーメッセージ取得(ログファイル出力)
     * @return String
     */
	public static function getErrorMsgIncludeFileLack() 		{ return ConfigModelUpload::$error_msg_include_file_lack;}
	public static function getErrorMsgIncludeNotfoundMakerMst() { return ConfigModelUpload::$error_msg_include_notfound_maker_mst;}
	public static function getErrorMsgIncludeNotfoundCarMst() 	{ return ConfigModelUpload::$error_msg_include_notfound_car_mst;}
	public static function getErrorMsgIncludeNotfoundModelData(){ return ConfigModelUpload::$error_msg_include_notfound_model_data;}
	public static function getErrorMsgIncludeNotfoundYearMst()  { return ConfigModelUpload::$error_msg_include_notfound_year_mst;}
	public static function getErrorMsgBatchfileName() 			{ return ConfigModelUpload::$error_msg_batchfile_name;}
	public static function getErrorMsgIsProcessing() 			{ return ConfigModelUpload::$error_msg_is_processing;}
	public static function getErrorMsgCsvNotfound() 			{ return ConfigModelUpload::$error_msg_csv_notfound;}
	public static function getErrorMsgCsvFoundMulti() 			{ return ConfigModelUpload::$error_msg_csv_found_multi;}
	public static function getErrorMsgBatchSuccessDeleteFail() 	{ return ConfigModelUpload::$error_msg_batch_success_delete_fail;}
	public static function getErrorMsgCsvNameNotcorrect() 		{ return ConfigModelUpload::$error_msg_csv_name_notcorrect;}
	public static function getErrorMsgCsvIsempty() 				{ return ConfigModelUpload::$error_msg_csv_isempty;}
	public static function getErrorMsgCsvrowFormat() 			{ return ConfigModelUpload::$error_msg_csvrow_format;}
	public static function getErrorMsgDBconnectFail() 			{ return ConfigModelUpload::$error_msg_dbconnect_fail;}
	public static function getErrorMsgTransactionFail() 		{ return ConfigModelUpload::$error_msg_transaction_fail;}
	public static function getErrorMsgTabledeleteFail() 		{ return ConfigModelUpload::$error_msg_tabledelete_fail;}	
	public static function getErrorMsgTableinsertFailExist()	{ return ConfigModelUpload::$error_msg_tableinsert_fail_exist;}	
	public static function getErrorMsgTableinsertFailYearMst() 	{ return ConfigModelUpload::$error_msg_tableinsert_fail_year_mst;}
	public static function getErrorMsgTableinsertFailModelData(){ return ConfigModelUpload::$error_msg_tableinsert_fail_model_data;}
	public static function getErrorMsgTableinsertFailMakerMst() { return ConfigModelUpload::$error_msg_tableinsert_fail_maker_mst;}
	public static function getErrorMsgTableinsertFailCarMst() 	{ return ConfigModelUpload::$error_msg_tableinsert_fail_car_mst;}
	public static function getErrorMsg() 						{ return ConfigModelUpload::$error_msg_;}

	/**
	 * メッセージ取得（コンソール出力)
     * @return String
     */
	public static function getMsgBatchSuccess() { return ConfigModelUpload::$msg_batch_success;}
	public static function getMsgBatchSuccessDeleteFail() { return ConfigModelUpload::$msg_batch_success_delete_fail; }
	public static function getMsgFileNotfound()	{ return ConfigModelUpload::$msg_file_notfound;}
	public static function getMsgOperationFail(){ return ConfigModelUpload::$msg_operation_fail;}
	public static function getMsgCsvNameError() { return ConfigModelUpload::$msg_csv_name_error;}
	public static function getMsgFileFoundMulti(){return ConfigModelUpload::$msg_file_found_multi;}
	public static function getMsgDataError() 	{ return ConfigModelUpload::$msg_data_error;}
	public static function getMsgFatalError() 	{ return ConfigModelUpload::$msg_fatal_error;}


}
?>
