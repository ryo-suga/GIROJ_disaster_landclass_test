<?php

/**
 * 対象年データモデル
 *
 *
 */
App::uses('AppModel','Model');
 
class YearMst extends AppModel{
	//model name
	var $name = 'YearMst';
	
	//using table
	var $useTable = 'tbl_year_mst';
}