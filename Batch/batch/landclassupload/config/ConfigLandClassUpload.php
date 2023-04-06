<?php
require_once("/var/www/landclass_batch/common/config/CONFIG.php");

/**
 * バッチ処理用コンフィグファイル。バッチ起動ファイル内で最初に読み込む。
 * パスは末尾に/（スラッシュ）を付ける
 *　更新：2023/04/06
 */
//定数定義　パス情報
//=====================================================================================================================

define('PATH_ERROR_LOG', CONFIG::$path_root.'output/landclassupload/');
define('PATH_CSV',		 CONFIG::$path_root.'input/landclassupload/');
define('PATH_PREFECTURES_MST', CONFIG::$path_root.CONFIG::$path_db_landclass.'prefecturesmst/');
define('PATH_MUNICIPALITY_MST',	CONFIG::$path_root. CONFIG::$path_db_landclass.'municipalitymst/');
define('PATH_LANDCLASS_DATA',CONFIG::$path_root.CONFIG::$path_db_landclass.'landclassdata/');
define('PATH_LANDCLASS_MST',CONFIG::$path_root.CONFIG::$path_db_landclass.'landclassmst/');

class ConfigLandClassUpload {

    //DB操作情報
    //=================================================================================================================
	private static $create_user    = 'BATCH';
    private static $update_user    = 'BATCH';
	private static $create_program = 'LandClassUpload';
	private static $update_program = 'LandClassUpload';
	
    //バッチファイル関連情報
    //=================================================================================================================

	private static $name_exec	 	        = 'LandClassUpload.php';
	private static $name_batch_class        = 'ClassLandClassUpload.php';
	private static $name_error_log	        = 'error.log';
	private static $name_prefectures_mst   	= 'PrefecturesMst.php';
	private static $name_municipality_mst 	= 'MunicipalityMst.php';
	private static $name_landclass_data	    = 'LandClassData.php';
	private static $name_landclass_mst	    = 'LandClassMst.php';

    //CSV名前情報
    //=================================================================================================================
	private static $preg_csv_name_defined	= 'landclass_';	
	
    //CSV項目関連情報
    //=================================================================================================================
	//CSV項目名
	private static $col_names = array(	
		0  => '都道府県',
		1  => '市区町村',
		2  => '等地',
		3  => '等地名',

	);
	
	private static $col_names_DB = array(
		0  => 'prefectures',
		1  => 'municipality',
		2  => 'landclass',
		3  => 'landclass_name',

	);
		
	//CSV項目バリデーション
	//-----------------------------------------------------------------------------------------------------------------
	private static $col_pregs = array(
		0  => '/^[一-龠々]{1,4}$/u',
		1  => '/^[一-龠々ぁ-んァ-ヶー]{1,20}$/u',
		2  => '/^[0-9]{1,2}$/',
		3  => '/^[一-龠々ぁ-んァ-ヶー]{1,10}$/u',
	);
	
	private static $col_valid_fail_msgs = array(
		0  => '全角漢字のみ1～4文字',
		1  => '全角漢字カナのみ20文字',
		2  => '半角数のみ2文字',
		3  => '等地名',
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
	private static $error_msg_include_file_lack				        = '管理クラス格納フォルダを確認してください';
	private static $error_msg_include_notfound_prefectures_mst	    = '都道府県マスタ管理クラスがありません';
	private static $error_msg_include_notfound_municipality_mst	    = '市区町村マスタ管理クラスがありません';	
	private static $error_msg_include_notfound_landclass_data	    = '等地管理クラスがありません';		
	private static $error_msg_batchfile_name						= 'バッチファイル名不一致：再設定してください';
	private static $error_msg_is_processing							= '同じバッチファイルが実行されています';
	private static $error_msg_csv_notfound							= 'アップロードファイルが見つかりません。';
	private static $error_msg_batch_success_delete_fail 			= "取り込みは完了しましたが、CSVファイルの削除に失敗しました。（ファイルアクセス権限などを確認してください）";
	private static $error_msg_csv_found_multi						= 'フォルダー内にアップロードファイルが複数見つかりました。';
	private static $error_msg_csv_name_notcorrect					= '取り込みファイル名が不正です';
	private static $error_msg_csv_isempty							= 'CSVが空です';
	private static $error_msg_csvrow_format							= '→CSVデータフォーマットエラー　行位置　： ';
	private static $error_msg_dbconnect_fail						= 'ＤＢ接続に失敗しました';
	private static $error_msg_transaction_fail						= 'トランザクション実行エラー、ロールバック実行';
	private static $error_msg_tabledelete_fail						= '既存のテーブルレコード消去に失敗しました';
	private static $error_msg_tableinsert_fail_exist				= 'キー項目の重複があります。 ※重複したキー ：　';
	private static $error_msg_tableinsert_fail_landclass_mst		= '同じ等地名のファイルが存在します';
	private static $error_msg_tableinsert_fail_display_order        = 'ファイル名に同じ表示順が存在します';
	private static $error_msg_tableinsert_fail_landclass_data		= '⇒データ登録失敗（ＤＢ処理エラー）：　等地データ';
	private static $error_msg_tableinsert_fail_prefectures_mst		= '⇒データ登録失敗（ＤＢ処理エラー）：　都道府県マスター';
	private static $error_msg_tableinsert_fail_municipality_mst		= '⇒データ登録失敗（ＤＢ処理エラー）：　市区町村マスター';

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
		return ConfigLandClassUpload::$create_user;
	}
	public static function getUpdateUser(){
		return ConfigLandClassUpload::$update_user;
	}
	public static function getCreateProgram(){
		return ConfigLandClassUpload::$create_program;
	}
	public static function getUpdateProgram(){
		return ConfigLandClassUpload::$update_program;
	}

	//バッチファイル関連情報
	//-----------------------------------------------------------------------------------------------------------------
	/**
     * 
     * @return  実行（バッチ）ファイル名
     */
	public static function getNameExec() {
		return ConfigLandClassUpload::$name_exec;
	}
	/**
     * 
     * @return  バッチクラスファイル名
     */
    public static function getNameBatchClass() {
        return ConfigLandClassUpload::$name_batch_class;
    }
	/**
     * 
     * @return  エラーログファイル名
     */
    public static function getNameErrorLog() {
        return ConfigLandClassUpload::$name_error_log;
    }
	/**
	 * モデル名情報の取得関数
	 * @return modelname;
	 */

	public static function getNamePrefecturesMst() {
	    return ConfigLandClassUpload::$name_prefectures_mst;
	}
	public static function getNameMunicipalityMst() {
		return ConfigLandClassUpload::$name_municipality_mst;
	}
	public static function getNameLandClassData() {
		return ConfigLandClassUpload::$name_landclass_data;
	}
	public static function getNameLandClassMst() {
		return ConfigLandClassUpload::$name_landclass_mst;
	}

    //CSV名前情報
	//-----------------------------------------------------------------------------------------------------------------
	/**
	 * CSVファイル名確認
     * @return  $preg_csv_name_defined
     */	
	public static function getPregCsvNameDefined() {
		return ConfigLandClassUpload::$preg_csv_name_defined;
	}
	
	//CSV項目情報
	//-----------------------------------------------------------------------------------------------------------------
	/**
	 * 各項目名取得関数
	 * @param 	$col_num
     * @return  $name
     */
	public static function getColName($col_num) {
		return ConfigLandClassUpload::$col_names[$col_num];
	}
	/**
	 * 各項目名(DB)取得関数
	 * @param 	$col_num
     * @return  $name
     */
	public static function getColNameDB($col_num) {
		return ConfigLandClassUpload::$col_names_DB[$col_num];
	}
	/**
	 * バリデーション条件の取得関数
     * @return  $name
     */
	public static function getColPreg($col_num) {
		return ConfigLandClassUpload::$col_pregs[$col_num];
	}
	public static function getNullAllowColumns() {
		return ConfigLandClassUpload::$null_allow_colnums;
	}

	//メッセージ情報
	//-----------------------------------------------------------------------------------------------------------------
	/**
	 * バリデーション失敗時のメッセージ取得
     * @return  $name
     */
	public static function getColValidFailMsg($col_num) {
		return ConfigLandClassUpload::$col_valid_fail_msgs[$col_num];
	}
	public static function getMsgCantBeNull(){
		return ConfigLandClassUpload::$msg_cantbe_null;
	}
	/**
	 * メッセージ取得(ログファイル出力)
     * @return String
     */
	public static function getMsgBatchfileBegin()	{ return ConfigLandClassUpload::$msg_batchfile_begin;}
	public static function getMsgBatchfileDone()	{ return ConfigLandClassUpload::$msg_batchfile_done;}
	/**
	 * エラーメッセージ取得(ログファイル出力)
     * @return String
     */
	public static function getErrorMsgIncludeFileLack() 				{ return ConfigLandClassUpload::$error_msg_include_file_lack;}
	public static function getErrorMsgIncludeNotfoundPrefecturesMst() 	{ return ConfigLandClassUpload::$error_msg_include_notfound_prefectures_mst;}
	public static function getErrorMsgIncludeNotfoundMunicipalityMst() 	{ return ConfigLandClassUpload::$error_msg_include_notfound_municipality_mst;}
	public static function getErrorMsgIncludeNotfoundLandClassData()	{ return ConfigLandClassUpload::$error_msg_include_notfound_landclass_data;}
	public static function getErrorMsgBatchfileName() 					{ return ConfigLandClassUpload::$error_msg_batchfile_name;}
	public static function getErrorMsgIsProcessing() 					{ return ConfigLandClassUpload::$error_msg_is_processing;}
	public static function getErrorMsgCsvNotfound() 					{ return ConfigLandClassUpload::$error_msg_csv_notfound;}
	public static function getErrorMsgCsvFoundMulti() 					{ return ConfigLandClassUpload::$error_msg_csv_found_multi;}
	public static function getErrorMsgBatchSuccessDeleteFail() 			{ return ConfigLandClassUpload::$error_msg_batch_success_delete_fail;}
	public static function getErrorMsgCsvNameNotcorrect() 				{ return ConfigLandClassUpload::$error_msg_csv_name_notcorrect;}
	public static function getErrorMsgCsvIsempty() 						{ return ConfigLandClassUpload::$error_msg_csv_isempty;}
	public static function getErrorMsgCsvrowFormat() 					{ return ConfigLandClassUpload::$error_msg_csvrow_format;}
	public static function getErrorMsgDBconnectFail() 					{ return ConfigLandClassUpload::$error_msg_dbconnect_fail;}
	public static function getErrorMsgTransactionFail() 				{ return ConfigLandClassUpload::$error_msg_transaction_fail;}
	public static function getErrorMsgTabledeleteFail() 				{ return ConfigLandClassUpload::$error_msg_tabledelete_fail;}	
	public static function getErrorMsgTableinsertFailExist()			{ return ConfigLandClassUpload::$error_msg_tableinsert_fail_exist;}	
	public static function getErrorMsgTableinsertFailLandClassMst()		{ return ConfigLandClassUpload::$error_msg_tableinsert_fail_landclass_mst;}
	public static function getErrorMsgTableinsertFailDisplayOrder()		{ return ConfigLandClassUpload::$error_msg_tableinsert_fail_display_order;}
	public static function getErrorMsgTableinsertFailLandClassData()	{ return ConfigLandClassUpload::$error_msg_tableinsert_fail_landclass_data;}
	public static function getErrorMsgTableinsertFailPrefecturesMst() 	{ return ConfigLandClassUpload::$error_msg_tableinsert_fail_prefectures_mst;}
	public static function getErrorMsgTableinsertFailMunicipalityMst() 	{ return ConfigLandClassUpload::$error_msg_tableinsert_fail_municipality_mst;}
	public static function getErrorMsg() 								{ return ConfigLandClassUpload::$error_msg_;}

	/**
	 * メッセージ取得（コンソール出力)
     * @return String
     */
	public static function getMsgBatchSuccess() { return ConfigLandClassUpload::$msg_batch_success;}
	public static function getMsgBatchSuccessDeleteFail() { return ConfigLandClassUpload::$msg_batch_success_delete_fail; }
	public static function getMsgFileNotfound()	{ return ConfigLandClassUpload::$msg_file_notfound;}
	public static function getMsgOperationFail(){ return ConfigLandClassUpload::$msg_operation_fail;}
	public static function getMsgCsvNameError() { return ConfigLandClassUpload::$msg_csv_name_error;}
	public static function getMsgFileFoundMulti(){return ConfigLandClassUpload::$msg_file_found_multi;}
	public static function getMsgDataError() 	{ return ConfigLandClassUpload::$msg_data_error;}
	public static function getMsgFatalError() 	{ return ConfigLandClassUpload::$msg_fatal_error;}


}
?>
