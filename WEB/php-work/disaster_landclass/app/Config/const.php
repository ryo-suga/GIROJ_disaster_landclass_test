<?php
/**
 *　定数定義
 *
 *
 */

 //都道府県
//=================================================================================================
$config['PREFECTURE_LIST'] = array(
	'北海道',
	'青森県'  ,
	'岩手県'  ,
	'宮城県'  ,
	'秋田県'  ,
	'山形県'  , 
	'福島県'  , 
	'茨城県'  , 
	'栃木県'  , 
	'群馬県'  , 
	'埼玉県'  ,
	'千葉県'  ,
	'東京都'  ,
	'神奈川県',
	'新潟県'  ,
	'富山県'  ,
	'石川県'  ,
	'福井県'  ,
	'山梨県'  ,
	'長野県'  ,
	'岐阜県'  ,
	'静岡県'  ,
	'愛知県'  ,
	'三重県'  ,
	'滋賀県'  ,
	'京都府'  ,
	'大阪府'  ,
	'兵庫県'  ,
	'奈良県'  ,
	'和歌山県',
	'鳥取県'  ,
	'島根県'  ,
	'岡山県'  ,
	'広島県'  ,
	'山口県'  ,
	'徳島県'  ,
	'香川県'  ,
	'愛媛県'  ,
	'高知県'  ,
	'福岡県'  ,
	'佐賀県'  ,
	'長崎県'  ,
	'熊本県'  ,
	'大分県'  ,
	'宮崎県'  ,
	'鹿児島県',
	'沖縄県'  
);

//エラーコード
//=================================================================================================
define('INVALID_PARAM','入力値が不正です');
define('NOT_SELECT_PREFECTURES','都道府県が選択されていません');
define('NOT_SELECT_MUNICIPALITY','市区町村が選択されていません');
//define('WF0001', 'DBアクセスに失敗しました');
//define('ACCESSLOG_WRITE_ERROR', 'アクセスログ書き込みに失敗しました。');

//エラーページ用文字列
//=================================================================================================
//400
define('PAGE_NOT_FOUND', 'ページが見つかりません。');

//500
define('INTERNAL_ERROR', '現在サービスは利用できません');

//バリデーション
//=================================================================================================
//都道府県名 TODO:バリデーション修正必要
define('PREG_PREFECTURES_NAME', '/^[一-龠々]{1,4}$/u');
//市区町村名
define('PREG_MUNICIPALITY_NAME', '/^[一-龠々ぁ-んァ-ヶー]{1,20}$/u');


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
define('RATE_FIRE_SUISAI',PATH_INCLUDE_HTML.'m_rate_fire_suisai.txt');
define('SUBNAV_RATEMAKING',PATH_INCLUDE_HTML.'subnav_ratemaking.txt');
define('NAV_SP',PATH_INCLUDE_HTML.'nav_sp.txt');
define('FOOTER',PATH_INCLUDE_HTML.'footer.txt');
define('POP_AEB',PATH_INCLUDE_HTML.'pop_aeb.txt');
define('BREAD_CRUMB',PATH_INCLUDE_HTML.'breadCrumb_suisai.txt');
define('SUISAI_FAQ',PATH_INCLUDE_HTML.'suisai_faq.txt');

//エラーページ(html)
define('PATH_INCLUDE_ERROR_HTML', '/ratemaking/fire/touchi/common/error/');
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

//その他設定値
//=================================================================================================
define('LANDCLASS_NUM_MAX', 6);