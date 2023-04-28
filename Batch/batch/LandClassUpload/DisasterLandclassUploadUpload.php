<?php
/**
 * バッチ起動用PHPファイル
 */

//定数定義
//=====================================================================================================================
//パス情報
//---------------------------------------------------------------------------------------------------------------------
define('PATH_CONFIG_MODELUPLOAD',__DIR__ . '/config/');
define('PATH_BATCH_CLASS',__DIR__ . '/class/');

//ファイル情報
//---------------------------------------------------------------------------------------------------------------------
define('FILENAME_CONFIG_MODELUPLOAD','ConfigDisasterLandclassUpload.php');

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