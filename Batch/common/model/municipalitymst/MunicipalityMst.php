<?php

/**
 * 市区町村マスタ管理クラス
 * 更新 2023/04/21
 *
 */
include_once('/var/www/landclass_batch/common/model/Model.php');

class MunicipalityMst extends Model {

	public function __construct(){
		parent::__construct();
		$this->setTableName('tbl_municipality_mst');
		$this->setColumnName('prefectures');
		$this->setColumnName('municipality');
	}
}