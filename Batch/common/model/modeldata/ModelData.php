<?php

/**
 * 型式データ管理クラス
 *
 *
 */
include_once('common/model/Model.php');

class ModelData extends Model {
	public function __construct(){
		parent::__construct();
		$this->setTableName('tbl_model_data');
		$this->setColumnName('maker_name');
		$this->setColumnName('car_name');
		$this->setColumnName('model');
		$this->setColumnName('release_date');
		$this->setColumnName('car_type');
		$this->setColumnName('area_code');
		$this->setColumnName('interpersonal_class');
		$this->setColumnName('last_interpersonal_class');
		$this->setColumnName('objectve_class');
		$this->setColumnName('last_objectve_class');
		$this->setColumnName('personal_accident_class');
		$this->setColumnName('last_personal_accident_class');
		$this->setColumnName('vehicle_class');
		$this->setColumnName('last_vehicle_class');
		$this->setColumnName('collateral_event');
		$this->setColumnName('last_collateral_event');
	}
	/**
	 * tbl_model_data　キー項目存在確認
	 *　@param モデル名、メーカー名、車名
	 *　@return boolean レコード有無
	 */
	public function isExist($dbh =　null) {
		$ret = false;
		if(!isset($dbh) || $dbh == null) {
			$ret = -1;
			return $ret;
		}	
		try {
			$sql = "SELECT count(*) FROM ".$this->getTableName()
					." WHERE model = \"".$this->getColumnValue('model')
					."\" AND maker_name = \"".$this->getColumnValue('maker_name')
					."\" AND car_name = \"".$this->getColumnValue('car_name')."\"";
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