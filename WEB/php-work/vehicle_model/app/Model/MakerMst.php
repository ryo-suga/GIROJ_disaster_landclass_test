<?php

/**
 * メーカ名マスタモデル
 *
 *
 */
App::uses('AppModel','Model');
 
class MakerMst extends AppModel {
	//model name
	var $name = 'MakerMst';
	
	//using table
	var $useTable = 'tbl_maker_mst';
}