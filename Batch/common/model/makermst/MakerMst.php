<?php

/**
 * メーカ名マスタ管理クラス
 *
 *
 */
include_once('common/model/Model.php');

class MakerMst extends Model{
	public function __construct(){
		parent::__construct();
		$this->setTableName('tbl_maker_mst');
		$this->setColumnName('maker_name');
	}
}