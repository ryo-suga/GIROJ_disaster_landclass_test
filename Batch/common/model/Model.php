<?php

/**
 * マスタ管理クラス(親）
 */

abstract class Model {
	private $table_name = null;
	private $column_set = array();
	// 検索関連
	private $search_str = null;
	private $search_key = null;
	private $search_value = null;
	// ソート条件
	private $sort_str = null;
    
	public function __construct() {
		$now = new DateTime();
		$this->setColumnName('create_date');
		$this->setColumnName('cre_usr');
		$this->setColumnName('cre_prg');
		$this->setColumnName('update_date');
		$this->setColumnName('upd_usr');
		$this->setColumnName('upd_prg');
		$this->setColumnValue('create_date', $now->format('ymdHis'));
		$this->setColumnValue('update_date', $now->format('ymdHis'));
	}
	
    //setter
    //==================================================================================================================
	//検索条件の値セット
	protected function setSearchCondition($search_str, $search_key, $search_value) {
		$this->search_str = $search_str;
		$this->search_key = $search_key;
		$this->search_value = $search_value;
	}

	//ソート条件の値セット
	protected function setSortCondition($sort_str) {
		$this->sort_str = $sort_str;
	}

	//テーブル名セット
	public function setTableName($table_name) {
		$this->table_name = $table_name;
	}

	//列名のセット
    public function setColumnName($column_name) {
		$ret = false;

		if(!in_array($column_name,$this->column_set)) {
			$this->column_set[]['column_name'] = $column_name;
			$ret = true;
		}
		return $ret;
	}

	//列の値セット
	public function setColumnValue($column_name, $column_value) {
		$ret = false;
		
		for($i = 0; $i < sizeof($this->column_set); $i++) {
			$name = $this->column_set[$i]['column_name'];
			if($name == $column_name) {
				$this->column_set[$i]['column_value'] = $column_value;
				$ret = true;
			}
		}
		return $ret;
	}

    //getter
    //==================================================================================================================
    public function getTableName() {
        return $this->table_name;
    }

	public function getColumnSet() {
		return $this->column_set;
	}
	public function getColumnValue($column_name) {
		$column_value = '';
		
		for($i = 0; $i < sizeof($this->column_set); $i++) {
			$name = $this->column_set[$i]['column_name'];
			if($name == $column_name) {
				$column_value = $this->column_set[$i]['column_value'];
			}
		}
		return $column_value;
	}
    //SQL
    //==================================================================================================================
	/**
     * SELECT 条件に該当するレコードを参照
     * @param  
     * @return 結果データ
     */
    public function select($dbh = null) {
		if(!isset($dbh)) {
			throw new Exception("db handler is null");
		}
		
		$column_set = $this->getColumnSet();
		//列名をStringに列挙
		$prepare_str_columns = '';
		$prepare_str_placeholders = '';
		foreach($column_set as $column) {
			if($column != $column_set[0]) {
				$prepare_str_columns .= ',';
				$prepare_str_placeholders .= ',';
			}
			$prepare_str_columns .= $column['column_name'];
			$prepare_str_placeholders .= ':'.$column['column_name'];
		}
  		try {
			//クエリ作成
			$queryStr = 'SELECT '.$prepare_str_columns.' FROM '.$this->getTableName();
			
			// WHERE句結合
			if (null != $this->search_str) {
				$queryStr = $queryStr . " WHERE " . $this->search_str;
			}
			// ORDER BY句結合
			if (null != $this->sort_str) {
				$queryStr = $queryStr . " ORDER BY " . $this->sort_str;
			}

			// バインド
			$query = $dbh->prepare($queryStr);
			for($i=0; $i<count($this->search_key); ++$i) {
				$query->bindParam(':'.$this->search_key[$i], $this->search_value[$i], PDO::PARAM_STR);
			}

			// 実行
			$query->execute();

		} catch (PDOException $e ) {
			throw $e;
		}
		return $query->fetchAll();
	}

    /**
     * INSERT foreachで セッターで登録済みの全カラムをINSERT
     * @param  
     * @return boolean 完了：true
     */
    public function insertRecord($dbh = null) {
        $is_error = false;
		if(!isset($dbh)) {
			$is_error = true;
			return !$is_error;
		}
		
		$column_set = $this->getColumnSet();
		//列名をStringに列挙
		$prepare_str_columns = '';
		$prepare_str_placeholders = '';
		foreach($column_set as $column) {
			if($column != $column_set[0]) {
				$prepare_str_columns .= ',';
				$prepare_str_placeholders .= ',';
			}
			$prepare_str_columns .= $column['column_name'];
			$prepare_str_placeholders .= ':'.$column['column_name'];
		}
  		try {
			//クエリ作成
			$query = $dbh->prepare('INSERT INTO '.$this->getTableName().'('.$prepare_str_columns.') VALUES('.$prepare_str_placeholders.')');
			foreach($column_set as $column) {
				$query->bindParam(':'.$column['column_name'], $column['column_value'], PDO::PARAM_STR);
			}
			$ret = $query->execute();
		} catch (PDOException $e ) {
			$is_error = true;
			return !$is_error;
		}
		if(!$ret) {
			$is_error = true;
			return !$is_error;
		}

		return !$is_error;
	}
	
    /**
     * テーブル内全消去
     * @return boolean 完了：true
     */
    public function deleteAllRecords($dbh = null) {
		$is_error = false;
	
		if(!isset($dbh)) {
			$is_error = true;
			return !$is_error;
		}
		if($this->getTableName() === Null || $this->getTableName() === ''){
			$is_error = true;
			return !$is_error;

		}

  		try {
			$query = $dbh->prepare('DELETE FROM '.$this->getTableName());
			$ret = $query->execute();
		} catch (PDOException $e) {
			$is_error = true;
			return !$is_error;
		}
		if(!$ret) {
			$is_error = true;
			return !$is_error;
		}

		return !$is_error;
    }
}