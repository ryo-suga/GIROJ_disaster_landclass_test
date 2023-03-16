<?php
/**
 *　定数定義
 *
 *
 */
 
//自動車タイプ「軽」
//define('CAR_TYPE_KEI','47');
 
//都道府県マップ　　
//$config['PREFECTURES_MAP'] = array(
//	'1' => '北海道',
//	'2' => '青森県',
//	'3' => '岩手県',
//	'4' => '宮城県',
//	'5' => '秋田県',
//);
$new_mysqli = new mysqli('localhost', 'root', '', 'disaster_landclass_search');

$sql = 'select prefectures from tbl_prefectures_mst ORDER BY create_date ASC';

if ($PREFECTURES_MAP = $new_mysqli->query($sql)) {
//変更前
//foreach($PREFECTURES_MAP as $PREFECTURES_MAP_val){
//    $PREFECTURES_MAP .= "<option value='". $PREFECTURES_MAP_val['prefectures'];
//    $PREFECTURES_MAP .= "'>". $PREFECTURES_MAP_val['prefectures']. "</option>";
//}
//変更後
foreach($PREFECTURES_MAP as $PREFECTURES_MAP_val){
	$config['PREFECTURES_MAP'][$PREFECTURES_MAP_val['prefectures']] = $PREFECTURES_MAP_val['prefectures'];
	}
}

//国産/外車コード　　連想配列
$config['AREA_CODE_MAP'] = array(
	'1' => '国産',
	'2' => '外車',
);

//エラーコード
//=================================================================================================
define('NOT_FOUND','該当する型式はありません');
define('NOT_SELECT_CAR_TYPE','自動車タイプが選択されていません');
define('NOT_INPUT_MODEL','型式が入力されていません');
define('NOT_SELECT_MAKER_NAME','メーカーが選択されていません');
define('NOT_SELECT_CAR_NAME','車名が選択されていません');
define('INVALID_MODEL','型式が不正です');
define('INVALID_PARAM','入力値が不正です');
define('WF0001', 'DBアクセスに失敗しました');
define('ACCESSLOG_WRITE_ERROR', 'アクセスログ書き込みに失敗しました。');

//エラーページ用文字列
//=================================================================================================
//400
define('PAGE_NOT_FOUND', 'ページが見つかりません。');

//500
define('INTERNAL_ERROR', '現在サービスは利用できません');

//バリデーション
//=================================================================================================
//自動車ﾀｲﾌﾟ
define('PREG_CAR_TYPE', '/^[124]{1,1}$/');
//型式
define('PREG_MODEL', '/^[ -\~]{1,15}$/');
//メーカー名
define('PREG_MAKER_NAME', '/^[（）＆ァ-ヶ､ー -\~･]{1,20}$/u');
//車名
define('PREG_CAR_NAME', '/^[（）ァ-ヶ､ー -\~･]{1,80}$/u');


//取り込みファイル情報
//=================================================================================================
//Cake内保持
//-------------------------------------------------------------------------------------------------
//HTML(.ctp)
define('HEAD','include/head/head');
define('PAGE_TOP','include/others/pageTop');

//CSS
define('HEAD_CSS','include/head/head_css');

//JS
define('HEAD_JS','include/head/head_js');

//Cake外（hp_common)
//-------------------------------------------------------------------------------------------------
//HTML(.txt)
define('PATH_INCLUDE_HTML','/var/www/web/kata.giroj.or.jp/www/root/ssldocs/common/inc/');

define('HEADER',PATH_INCLUDE_HTML.'header.txt');
define('AUTO_MOBILE_VEHICLE_MODEL',PATH_INCLUDE_HTML.'m_rate_automobile_vehicle_model.txt');
define('SUBNAV_RATEMAKING',PATH_INCLUDE_HTML.'subnav_ratemaking.txt');
define('NAV_SP',PATH_INCLUDE_HTML.'nav_sp.txt');
define('FOOTER',PATH_INCLUDE_HTML.'footer.txt');
define('POP_AEB',PATH_INCLUDE_HTML.'pop_aeb.txt');
define('BREAD_CRUMB',PATH_INCLUDE_HTML.'breadCrumb.txt');
define('KATA_FAQ',PATH_INCLUDE_HTML.'vehicle_model_faq.txt');
define('KATA_DETAIL_ANNOTATION',PATH_INCLUDE_HTML.'katashiki_info.txt');

//エラーページ(html)
define('PATH_INCLUDE_ERROR_HTML', '/ratemaking/automobile/vehicle_model/common/error/');
define('ERROR_404',PATH_INCLUDE_ERROR_HTML.'404.html');
define('ERROR_503',PATH_INCLUDE_ERROR_HTML.'503.html');

//CSS
define('PATH_INCLUDE_CSS','../common/');

define('NORMALIZE',PATH_INCLUDE_CSS.'css/normalize');
define('FONTELLO',PATH_INCLUDE_CSS.'css/fontello');
define('FONTAWESOME',PATH_INCLUDE_CSS.'font-awesome-4.7.0/css/font-awesome.min');
define('BASEPC',PATH_INCLUDE_CSS.'css/base-pc');
define('BASESP',PATH_INCLUDE_CSS.'css/base-sp');
define('BASETB',PATH_INCLUDE_CSS.'css/base-tb');
//define('FORM',PATH_INCLUDE_CSS.'css/form');

//js
define('PATH_INCLUDE_JS','../common/js/');

define('JQUERY',PATH_INCLUDE_JS.'jquery-3.5.1.min.js');
define('JQUERY_MIGRATE',PATH_INCLUDE_JS.'jquery-migrate-1.4.1.min.js');
define('MAME',PATH_INCLUDE_JS.'mame');
define('COMMON',PATH_INCLUDE_JS.'common');
define('PLACEHOLDER',PATH_INCLUDE_JS.'jquery.ah-placeholder');