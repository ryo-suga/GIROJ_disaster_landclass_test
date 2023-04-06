<?php
/**
 * バッチ起動用PHPファイル
 */

//定数定義
//=====================================================================================================================
//パス情報
//---------------------------------------------------------------------------------------------------------------------
define('PATH_CONFIG_MODELUPLOAD','/var/www/landclass_batch/batch/landclassupload/config/');
define('PATH_BATCH_CLASS','/var/www/landclass_batch/batch/landclassupload/class/');

//ファイル情報
//---------------------------------------------------------------------------------------------------------------------
define('FILENAME_CONFIG_MODELUPLOAD','ConfigLandClassUpload.php');

//include
//=====================================================================================================================
require_once(PATH_CONFIG_MODELUPLOAD.FILENAME_CONFIG_MODELUPLOAD);
require_once(PATH_BATCH_CLASS.ConfigLandClassUpload::getNameBatchClass());

//バッチ実行処理
//=====================================================================================================================
//以降はPHPエラーを非表示。
error_reporting(0);

$class_batch = new ClassLandClassUpload();
$class_batch->execFlow();

?>