<?php

/**
 * 等地データ管理クラス
 * 更新 2023/04/25
 */
include_once('/var/www/landclass_batch/common/model/Model.php');

class LandClassData extends Model {
	public function __construct(){
		parent::__construct();

		$this->setTableName('tbl_landclass_data');
		$this->setColumnName('prefectures');
		$this->setColumnName('municipality');
		$this->setColumnName('landclass');
		$this->setColumnName('landclass_name');
	}
	/**
	 * landclass_name　キー項目存在確認　
	 *　@param 等値名、都道府県、市区町村
	 *　@return boolean レコード有無
	 */
	public function isExist($dbh = null) {
		$ret = false;
		if(!isset($dbh) || $dbh == null) {
			$ret = -1;
			return $ret;
		}
		try {
			$sql = "SELECT count(*) FROM ".$this->getTableName()
					." WHERE landclass_name = \"".$this->getColumnValue('landclass_name')
					."\" AND prefectures = \"".$this->getColumnValue('prefectures')
					."\" AND municipality = \"".$this->getColumnValue('municipality')."\"";
			$stmt = $dbh->query($sql);
			$row_count = $stmt->fetchColumn();

			if($row_count > 0) {
				$ret = true;
			}
        } catch(Exception $e) {
			$ret = -1;
		}
		return $ret;
	}
}