<?php

/**
 * 等地データ管理モデル
 *
 *
 */
App::uses('AppModel','Model');
 
class LandclassData extends AppModel{
	//model name
	var $name = 'LandclassData';
	
	//using table
	var $useTable = 'tbl_landclass_data';
}