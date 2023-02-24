<?php

/**
 * 車名マスタ管理クラス
 *
 *
 */
include_once('common/model/Model.php');

class YearMst extends Model{
	public function __construct(){
		parent::__construct();
		$this->setTableName('tbl_year_mst');
		$this->setColumnName('target_year');
	}
}