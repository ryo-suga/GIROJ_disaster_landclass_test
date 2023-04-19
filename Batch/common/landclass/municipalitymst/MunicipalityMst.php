<?php

/**
 * 市区町村マスタ管理クラス
 *
 *
 */
include_once('/var/www/landclass_batch/common/landclass/Landclass.php');

class MunicipalityMst extends Landclass {

	public function __construct(){
		parent::__construct();
		$this->setTableName('tbl_municipality_mst');
		$this->setColumnName('prefectures');
		$this->setColumnName('municipality');
	}
}