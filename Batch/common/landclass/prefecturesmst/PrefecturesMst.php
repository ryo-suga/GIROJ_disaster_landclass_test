<?php

/**
 * 都道府県マスタ管理クラス
 *
 *
 */
include_once('/var/www/landclass_batch/common/landclass/Landclass.php');

class PrefecturesMst extends Landclass {

	public function __construct(){
		parent::__construct();
		$this->setTableName('tbl_prefectures_mst');
		$this->setColumnName('prefectures');
	}
}