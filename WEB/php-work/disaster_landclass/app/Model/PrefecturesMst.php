<?php

/**
 * 都道府県マスタモデル
 *
 *
 */
App::uses('AppModel','Model');

class PrefecturesMst extends AppModel{
	//model name
	var $name = 'PrefecturesMst';
	
	//using table
	var $useTable = 'tbl_prefectures_mst';
}