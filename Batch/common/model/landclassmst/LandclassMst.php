<?php

/**
 * 等地マスタ管理クラス
 * 更新 2023/04/28
 *
 */
 include_once(__DIR__. '/../Model.php');

class LandClassMst extends Model {

	public function __construct(){
		parent::__construct();
		$this->setTableName('tbl_landclass_mst');
		$this->setColumnName('landclass_name');
		$this->setColumnName('display_order');
	}
}