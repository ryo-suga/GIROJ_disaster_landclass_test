<?php

/**
 * 都道府県マスタ管理クラス
 * 更新 2023/04/21
 *
 */
 include_once(__DIR__. '/../Model.php');

class PrefecturesMst extends Model {

	public function __construct(){
		parent::__construct();
		$this->setTableName('tbl_prefectures_mst');
		$this->setColumnName('prefectures');
	}
}