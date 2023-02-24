<?php

/**
 * 型式データ管理モデル
 *
 *
 */
App::uses('AppModel','Model');
 
class ModelData extends AppModel{
	//model name
	var $name = 'ModelData';
	
	//using table
	var $useTable = 'tbl_model_data';
}