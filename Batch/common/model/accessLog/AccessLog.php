<?php

/**
 * 車名マスタ管理クラス
 *
 *
 */
include_once('common/model/Model.php');

class AccessLog extends Model{
	// 検索条件
	private $searchConditionNameArray = array(
		'MONTHLY' => 0,
	);
	private $seachStrArray = array(
		"date_format(access_date, '%Y%m') = :access_date",
	);
	private $seachKeyArray = array(
		array("access_date"),
	);

	// ソート条件
	private $sortConditionNameArray = array(
		'MONTHLY' => 0,
	);
	private $sortStrArray = array(
		"access_date, model",
	);

	public function __construct(){
		parent::__construct();
		$this->setTableName('tbl_access_log');
		$this->setColumnName('access_date');
		$this->setColumnName('maker_name');
		$this->setColumnName('car_name');
		$this->setColumnName('model');
		$this->setColumnName('src_ip');
		$this->setColumnName('user_agent');
	}

	// 検索条件セット
	public function setSearchCondition($key, $valueArray) {
		$ret = false;
		$index = $this->searchConditionNameArray[$key];
		if (count($this->seachKeyArray[$index]) != count($valueArray)) {
			return $ret;
		}

		if (array_key_exists($key, $this->searchConditionNameArray)) {
			parent::setSearchCondition($this->seachStrArray[$index], $this->seachKeyArray[$index], $valueArray);
		}

		$ret = true;
		return $ret;
	}

	// ソート条件セット
	public function setSortCondition($key) {
		$ret = false;
		$index = $this->sortConditionNameArray[$key];

		if (array_key_exists($key, $this->sortConditionNameArray)) {
			parent::setSortCondition($this->sortStrArray[$index]);
		}

		$ret = true;
		return $ret;
	}
}