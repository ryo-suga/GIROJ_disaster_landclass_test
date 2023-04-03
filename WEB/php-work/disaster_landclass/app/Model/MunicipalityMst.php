<?php

/**
 * 市区町村マスタモデル
 *
 *
 */
App::uses('AppModel','Model');

class MunicipalityMst extends AppModel{
	//model name
	var $name = 'MunicipalityMst';
	
	//using table
	var $useTable = 'tbl_municipality_mst';
}