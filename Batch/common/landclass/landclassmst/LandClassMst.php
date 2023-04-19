<?php

/**
 * 市区町村マスタ管理クラス
 *
 *
 */
include_once('/var/www/landclass_batch/common/landclass/Landclass.php');

class LandClassMst extends Landclass {

	public function __construct(){
		parent::__construct();
		$this->setTableName('tbl_landclass_mst');
		$this->setColumnName('landclass_name');
		$this->setColumnName('display_order');
	}
}