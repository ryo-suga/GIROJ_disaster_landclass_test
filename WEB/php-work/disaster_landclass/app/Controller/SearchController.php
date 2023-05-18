<?php
//
//-------------------------------------------------------------------------------------------------
//
//=================================================================================================

/**
 * 画面コントローラ
 *
 *
 */

App::uses('AppController','Controller');

class SearchController extends AppController{
	public $layout = 'layout_disaster_landclass_search';

	//利用モデル
	var $uses = array (
		'PrefecturesMst',
		'MunicipalityMst',
		'LandclassMst',
		'LandclassData',
		'AccessLog',
	);
	
	//indexアクション
	//=============================================================================================	
	public function index() {
		if($this->request->is('get')) {
			$this->_showIndex();
        }
		//post
		else {
			$this->showErrorPage_400();
		}
	}

	/**
	 * 初期表示
	 */
	private function _showIndex() {
		//ドロップダウンリスト作成
		//-----------------------------------------------------------------------------------------	
		$prefecture_list = Configure::read('PREFECTURE_LIST');
		//都道府県リスト
		$prefectures = array();
		$prefectures_name = array();
		$fields = array('prefectures');
		$order 	= array('create_date asc');
		$conditions = array();
		$result = true;
		$result = $this->_selectPrefecturesMst($fields, $order, $conditions, $prefectures);
		if(!$result) {
			//DB取得失敗エラー
			return false;
			$this->showErrorPage_500();
		}
		else {
			// //return用に配列内容調整(標準の都道府県順となるようソート)
			for($i = 0; $i < sizeof($prefecture_list); $i++) {
				for($j = 0; $j < sizeof($prefectures); $j++) {
					if (0 == strcmp($prefecture_list[$i], $prefectures[$j]['PrefecturesMst']['prefectures'])) {
						$prefectures_name[$prefecture_list[$i]] = $prefecture_list[$i];
						break;
					}
				}
			}
		}

		//フォーム反映
		//-----------------------------------------------------------------------------------------
		//都道府県、市区町村
		$this->set('prefectures_name', $prefectures_name);
		$this->set('municipality_name', null);
	}
	
	/**
	 * 検索・検索結果 詳細（クラス）表示
	 *　@param $form_info
	 */
	public function searchDetail() {
		$is_error = false;

		if(!$this->request->is('ajax')){
			$this->showErrorPage_400();
		}
		if(!isset($_POST)){
			$this->showErrorPage_400();
		}
		
		//ローカル変数
		$form_info = $_POST;
		$this->autoRender = false;
		$result_record    = array();  //詳細表示項目配列
		$return_json 	  = array();
		$error_msgs  	  = array();

		if(isset($_POST)){
			$form_info = $_POST;
		}
		
		//入力情報確認
		//-----------------------------------------------------------------------------------------
		if(!isset($form_info['prefectures']) || $form_info['prefectures'] === '') {
			$is_error = true;
			$error_msgs[] = NOT_SELECT_PREFECTURES;
		}

		if(!isset($form_info['municipality']) || $form_info['municipality'] === '') {
			$is_error = true;
			$error_msgs[] = NOT_SELECT_MUNICIPALITY;
		}
		
		//エラー確認
		//-----------------------------------------------------------------------------------------
		if($is_error){
			$return_json = array('is_error'    => $is_error,
								 'result_data' => $error_msgs
								 );
			return json_encode($return_json);
		}
		
		//等地データ取得
		//-----------------------------------------------------------------------------------------
		$result_detail  = array();
		$fields 		= array('LandclassData.landclass','LandclassData.landclass_name', 'LandclassData.prefectures', 'LandclassData.municipality');
		$order 			= array('LandclassMst.display_order asc');
		$conditions 	= array('LandclassData.prefectures' => $form_info['prefectures'], 'LandclassData.municipality' => $form_info['municipality']);
		$result = $this->_selectLandclassData($fields, $order, $conditions, LANDCLASS_NUM_MAX, $result_detail);
		if(!$result) {
			$this->showErrorPage_500();
		}
	
		//結果を返却配列に反映
		//-----------------------------------------------------------------------------------------
		//$result_record = $result_detail[0]['LandclassData'];
		$result_record = $result_detail;
			
		//AccessLog書き込み
		//-----------------------------------------------------------------------------------------
		$prefectures = $result_detail[0]['LandclassData']['prefectures'];
		$municipality = $result_detail[0]['LandclassData']['municipality'];
		$result = $this->_insertAccessLog($prefectures , $municipality);
		if(!$result) {
			//$is_error = true;
		}

		//エラー有無確認
		//-----------------------------------------------------------------------------------------	
		if($is_error){
			$return_json = array( 'is_error' 	=> $is_error,
								  'result_data' => $error_msgs
								 );
			return json_encode($return_json);
		}

		//正常完了
		$ret_data = array();
		for($i = 0; $i < sizeof($result_record); $i++) {
			$ret_data[$i] = array('prefectures' => $result_record[$i]['LandclassData']['prefectures']);
			$ret_data[$i] += array('municipality' => $result_record[$i]['LandclassData']['municipality']);
			$ret_data[$i] += array('landclass_name' => $result_record[$i]['LandclassData']['landclass_name']);
			$ret_data[$i] += array('landclass' => $result_record[$i]['LandclassData']['landclass']);
		}

		$return_json = array( 'is_error' 	=> $is_error,
							  'result_data' => $ret_data
							 );
		return json_encode($return_json);
	}

	/**
	 * 市区町村リスト取得
	 *　@param $form_info
	 */
	public function getMunicipalityList() {
		if(!$this->request->is('ajax')){
			$this->showErrorPage_400();
		}
		if(!isset($_POST)){
			$this->showErrorPage_400();
		}
		
		//オートレンダーオフ
		$this->autoRender = false;
		
		//ローカル変数
		$form_info = $_POST;
		$return_json = array();
		$return_json[0] = array('view' => '市区町村を選択');
		$return_json[0] += array('value' => '');
		
		//入力チェック
		//-----------------------------------------------------------------------------------------
		//都道府県未選択の場合
		if( !isset($form_info['prefectures']) || $form_info['prefectures'] == '') {
			return json_encode($return_json);
		}
		//バリデーション
		if(!preg_match(PREG_PREFECTURES_NAME, $form_info['prefectures'])) {
			return json_encode($return_json);
		}
			
		//検索処理
		//-----------------------------------------------------------------------------------------
		$municipality = array();
		$fields = array('municipality');
		$order 	= array('priority asc');
		$conditions = array('prefectures'=> $form_info['prefectures']); 
		$result = true;
		$result = $this->_selectMunicipalityMst($fields, $order, $conditions, $municipality);
		if(!$result) {
			//DB取得失敗エラー
			return false;
			$this->showErrorPage_500();
		}
		else {
			//return用に配列内容調整
			for($i = 0; $i < sizeof($municipality); $i++) {
				$return_json[$i+1] =  array('view'  => $municipality[$i]['MunicipalityMst']['municipality']);
				$return_json[$i+1] += array('value' => $municipality[$i]['MunicipalityMst']['municipality']);
			}
		}
		return json_encode($return_json);
	}

	//DBモデルアクセス
	//=============================================================================================
	//SELECT
	//---------------------------------------------------------------------------------------------
	/**
	 * PrefecturesMst取得
	 * @param $fields, $order, $conditions
	 * @param &$records
	 * @return bool
	 */
	 private function _selectPrefecturesMst($fields, $order, $conditions, &$records) {
		$ret = false;
		
		//検索実行
		$records = $this->PrefecturesMst->find(
			'all',
			array(
				'fields'	 => $fields,
				'order'		 => $order,
				'conditions' => $conditions
			)
		);
		
		$ret = true;
		return $ret;
	}

	/**
	 * MunicipalityMst取得
	 * @param $fields, $order, $conditions
	 * @param &$records
	 * @return bool
	 */
	 private function _selectMunicipalityMst($fields, $order, $conditions, &$records) {
		$ret = false;
		
		//検索実行
		$records = $this->MunicipalityMst->find(
			'all',
			array(
				'fields'	 => $fields,
				'order'		 => $order,
				'conditions' => $conditions
			)
		);
		
		$ret = true;
		return $ret;
	}

 	/**
	 * LandclassData取得
	 * @param $fields, $order, $conditions
	 * @param &$records
	 * @return bool
	 */
	private function _selectLandclassData($fields, $order, $conditions, $limit, &$records) {
		$ret = false;
		
		//検索実行
		$records = $this->LandclassData->find(
			'all',
			array(
				'fields'	 => $fields,
				'joins'		=> array(
					array(
						'table' => 'tbl_landclass_mst',
						'alias' => 'LandclassMst',
						'type' => 'LEFT',
						'conditions' => array($this->LandclassData->alias . '.landclass_name = LandclassMst.landclass_name'),
					)
				),
				'order'		 => $order,
				'conditions' => $conditions,
				'limit'		 => $limit
			)
		);
		
		$ret = true;
		return $ret;
	}
	
	/**
	 * AccessLog書き込み
	 * @param $prefectures, $municipality
	 * @return bool
	 */
	private function _insertAccessLog($prefectures , $municipality) {
		$ret = false;
		$src_ip = null;
		
		//クライアントip取得
		if(isset($_SERVER['HTTP_CLIENT_IP'])) {
			$src_ip	= $_SERVER['HTTP_CLIENT_IP'];
		} else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		 	$src_ip	= $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		 	$src_ip	= $_SERVER['REMOTE_ADDR'];
		}
		//ユーザーエージェント取得
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$add_record = array(
			'AccessLog' => array(
				'update_date' => date('Y/m/d H:i:s'),
				'access_date' => date('Y/m/d H:i:s'),
				'prefectures'  => $prefectures,
				'municipality' => $municipality,
				'src_ip'	  => $src_ip,
				'user_agent'  => $user_agent
			)
		);
		
		//書き込み実行
		$ret = $this->AccessLog->save($add_record);

		return $ret;
	}
	
	//エラーページ遷移
	//=============================================================================================
	//call時に以降の処理を中断する。
	//---------------------------------------------------------------------------------------------
	/**
	 *　400
	 */
	public function showErrorPage_400() {
		throw new NotFoundException();
		exit;
	}
	
	/**
	 *　500 
	 */
	public function showErrorPage_500() {
		throw new InternalErrorException();
		exit;
	}
}