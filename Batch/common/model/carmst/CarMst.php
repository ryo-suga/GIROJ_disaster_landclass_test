<?php

/**
 * 車名マスタ管理クラス
 *
 *
 */
include_once('common/model/Model.php');

class CarMst extends Model {

	public function __construct(){
		parent::__construct();
		$this->setTableName('tbl_car_mst');
		$this->setColumnName('maker_name');
		$this->setColumnName('car_name');
	}
}