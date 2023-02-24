<?php
ob_start(function($buf){ return mb_convert_encoding($buf, 'sjis', 'utf8'); });
/**
 * バッチ処理　コンフィグ　親
 * パスは末尾に/（スラッシュ）をつける。
 *　更新：2017/10/16
 */

class CONFIG {
		
	//パス情報
    //==================================================================================================================
	public static $path_root 	 = '/var/www/web/batch/www/root/php-work/';
	public static $path_db_model = 'common/model/';
	
    //DBアクセス情報
    //==================================================================================================================
    private static $dbname   = 'model_class_search';
    private static $hostname = '172.27.112.46';
    private static $charset  = 'utf8';
	private static $user 	 = 'appuser';
    private static $password = 'passw0rd';
	
	//エラーログ　エラーレベル
    //==================================================================================================================	
	public static $LOG_LEVEL_INFO  = 'INFO';
	public static $LOG_LEVEL_WARN  = 'WARN';
	public static $LOG_LEVEL_ERROR = 'ERROR';
	public static $LOG_LEVEL_FATAL = 'FATAL';
	public static $LOG_LEVEL_NULL  = ' ';

	//getter
    //==================================================================================================================
	/**
     * 
     * @return  $dsn DSN情報
     */
    public static function getDsn() {
		$dsn = 'mysql:host='.CONFIG::$hostname.';dbname='.CONFIG::$dbname;
        return $dsn;
    }

    /**
     * 
     * @return  $user User情報
     */
    public static function getUser() {
        return CONFIG::$user;
    }

    /**
     * 
     * @return  $password Password情報
     */
    public static function getPassword() {
        return CONFIG::$password;
    }
}
?>
