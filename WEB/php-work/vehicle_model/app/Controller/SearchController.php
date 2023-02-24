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
	public $layout = 'layout_model_class_search';

	//利用モデル
	var $uses = array (
		'MakerMst',
		'CarMst',
		'ModelData',
		'YearMst',
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
		$is_error = false;
		
		//ドロップダウンリスト作成
		//-----------------------------------------------------------------------------------------	
		//自動車タイプリスト
		$car_types = Configure::read('CAR_TYPE_MAP');

		if($is_error) {
			$this->set('error_msgs',$error_msgs);			
			return false;
		}

		//フォーム反映
		//-----------------------------------------------------------------------------------------
		$this->set('car_types', $car_types);
		$this->set('maker_name', null);
		$this->set('car_name', null);
	}
	
	/**
	 * 検索・検索結果表示 型式
	 *　@param $form_info
	 */
	public function searchModel() {
		if(!$this->request->is('ajax')) {
			$this->showErrorPage_400();
		}
		if(!isset($_POST)){
			$this->showErrorPage_400();
		}

		$this->autoRender = false;

		$form_info   	= $_POST;
		$is_error    	= false;
		$error_msgs  	= array();
		$return_records	= array();
		$return_json 	= array();//JSON出力調整

		if(isset($_POST)){
			$form_info = $_POST;
		}

		//入力情報確認
		//-----------------------------------------------------------------------------------------
		if(!isset($form_info['car_type_model']) || $form_info['car_type_model'] === '') {
			$is_error = true;
			$error_msgs[] = NOT_SELECT_CAR_TYPE;
		}
		if(!isset($form_info['model']) || $form_info['model'] === '') {
			$is_error = true;
			$error_msgs[] = NOT_INPUT_MODEL;
		}
		if(!$is_error) {
			//全角→半角変換
			$form_info['model'] = mb_convert_kana($form_info['model'], 'a','utf8');
			//検査
			if(!preg_match(PREG_MODEL,$form_info['model'] )) {
				$is_error = true;
				$error_msgs[] = INVALID_MODEL;
			}
		}

		//バリデーション
		//-----------------------------------------------------------------------------------------
		if(!$is_error) {
			if(!preg_match(PREG_CAR_TYPE, $form_info['car_type_model'])
			 ||!preg_match(PREG_MODEL, $form_info['model'])) {
				$is_error = true;
				$error_msgs[] = INVALID_PARAM;
			 }
		}
		//エラー確認
		//-----------------------------------------------------------------------------------------
		if($is_error){
			$return_json = array('is_error' => $is_error,
								 'result_data' => $error_msgs
								);
			return json_encode($return_json);
		}

		//検索条件設定、実行
		//-----------------------------------------------------------------------------------------
		$result_list 	= array();
		$fields	 		= array('model','maker_name','car_name');
		$order			= array('maker_name asc', 'car_name asc','model asc');
		$conditions		= array();
		if( isset($form_info)){
			$conditions = array('model' => $form_info['model'],'car_type'=> $form_info['car_type_model']);
		}
		$result = $this->_selectModelData($fields, $order,  $conditions, $result_list);
		if(!$result) {
			$this->showErrorPage_500();
		}
		
		if( !$is_error && count($result_list) <= 0) {
			//エラーが無く、取得件数0以下なら警告表示
			$is_error = true;
			$error_msgs[] = NOT_FOUND;
		}

		//エラー確認
		//-----------------------------------------------------------------------------------------
		if($is_error){
			$return_json = array('is_error' => $is_error,
								 'result_data' => $error_msgs
								 );
			return json_encode($return_json);
		}
		
		//DB検索結果フォーム反映
		//-----------------------------------------------------------------------------------------
		//JSON出力調整
		for ($i = 0; $i < sizeof($result_list); $i++) {
			$return_records[$i] = $result_list[$i]['ModelData'];
		}
		//検索結果。
		$return_json = array('is_error'    => $is_error,
							 'result_data' => $return_records
							 );
		return json_encode($return_json);		
	}

	/**
	 * 検索・検索結果表示　メーカー名、車名
	 *　@param $form_info
	 */
	public function searchMakerCar() {
		if(!$this->request->is('ajax')) {
			$this->showErrorPage_400();
		}
		if(!isset($_POST)){
			$this->showErrorPage_400();
		}

		$this->autoRender = false;

		//利用変数
		$form_info 		= $_POST;
		$is_error	    = false;
		$error_msgs 	= array();
		$return_records	= array();
		$return_json 	= array();//JSON出力調整
		
		if(isset($_POST)){
			$form_info = $_POST;
		}

		//入力情報確認
		//-----------------------------------------------------------------------------------------
		if(!isset($form_info['car_type_maker_car']) || $form_info['car_type_maker_car'] === '') {
			$is_error = true;
			$error_msgs[] = NOT_SELECT_CAR_TYPE;
		}

		if(!isset($form_info['maker_name']) || $form_info['maker_name'] === '') {
			$is_error = true;
			$error_msgs[] = NOT_SELECT_MAKER_NAME;
		}
		if(!isset($form_info['car_name']) || $form_info['car_name'] === ''){
			$is_error = true;
			$error_msgs[] = NOT_SELECT_CAR_NAME;
		}
		
		//バリデーション
		//-----------------------------------------------------------------------------------------
		if(!$is_error){
			if(!preg_match(PREG_CAR_TYPE, $form_info['car_type_maker_car'])
			 ||!preg_match(PREG_MAKER_NAME, $form_info['maker_name'])
			 ||!preg_match(PREG_CAR_NAME, $form_info['car_name'])) {
				$is_error = true;
				$error_msgs[] = INVALID_PARAM;
			 }
		}
		
		//エラー確認
		//-----------------------------------------------------------------------------------------
		if($is_error){
			$return_json = array('is_error'    => $is_error,
								 'result_data' => $error_msgs
								 );
			return json_encode($return_json);
		}

		//検索条件設定、実行
		//-----------------------------------------------------------------------------------------
		$result_list	= array();
		$fields			= array('model','maker_name','car_name');
		$order			= array('maker_name asc', 'car_name asc','model asc');
		$conditions  	= array();
		if(isset($form_info)) {
			$conditions = array('car_type' => $form_info['car_type_maker_car'],'maker_name' => $form_info['maker_name'], 'car_name' => $form_info['car_name']);
		}
		$result = $this->_selectModelData($fields, $order,  $conditions, $result_list);
		if(!$result) {
			//DB取得失敗エラー
			$this->showErrorPage_500();
		}

		//検索エラーが無く、取得件数0以下なら警告表示
		if( !$is_error && count($result_list) <= 0) {
			$is_error = true;
			$error_msgs[] = NOT_FOUND;
		}

		//エラー確認
		//-----------------------------------------------------------------------------------------
		if($is_error){
			$return_json = array('is_error'    => $is_error,
								 'result_data' => $error_msgs
								 );
			return json_encode($return_json);
		}
		
		//DB検索結果フォーム反映
		//-----------------------------------------------------------------------------------------
		//JSON出力調整
		for ($i = 0; $i < sizeof($result_list); $i++) {
			$return_records[$i] = $result_list[$i]['ModelData'];
		}
			
		//検索結果が１なら詳細画面へ遷移。
		$return_json = array('is_error'    => $is_error,
							 'result_data' => $return_records
							 );
		return json_encode($return_json);
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
		//バリデーション
		//-----------------------------------------------------------------------------------------
		if(!preg_match(PREG_MODEL, $form_info['model'])
		 ||!preg_match(PREG_CAR_NAME, $form_info['car_name'])
		 ||!preg_match(PREG_MAKER_NAME, $form_info['maker_name'])) {
			$is_error = true;
			$error_msgs[] = INVALID_PARAM;
		}
		
		//エラー確認
		//-----------------------------------------------------------------------------------------
		if($is_error){
			$return_json = array('is_error'    => $is_error,
								 'result_data' => $error_msgs
								 );
			return json_encode($return_json);
		}
		
		//型式データ取得
		//-----------------------------------------------------------------------------------------
		$result_detail  = array();
		$fields 		= array('release_date','model','maker_name','car_name','interpersonal_class','objectve_class',
								'personal_accident_class','vehicle_class',
								'last_interpersonal_class','last_objectve_class',
								'last_personal_accident_class','last_vehicle_class',
								'collateral_event' ,'last_collateral_event', 'car_type');
		$order 			= array();
		$conditions 	= array('model' => $form_info['model'], 'maker_name' => $form_info['maker_name'],
								'car_name' => $form_info['car_name']);
		$result = $this->_selectModelData($fields, $order, $conditions, $result_detail);
		if(!$result) {
			$this->showErrorPage_500();
		}
		
		//自動車タイプが軽かどうか
		$is_kei = ($result_detail[0]['ModelData']['car_type'] === CAR_TYPE_KEI);
		
		//対象年データ取得
		//-----------------------------------------------------------------------------------------
		$target_year  = array();
		$order		 = array();
		$conditions	 = array();
		$result = $this->_selectYearMst($order, $conditions, $target_year);	
		if(!$result) {
			return false;
			$this->showErrorPage_500();
		}
		
		//AEB割引期間確認
		//-----------------------------------------------------------------------------------------
		//保証終了日付作成
		$release_date = $result_detail[0]['ModelData']['release_date'];
		$announcement_date = new DateTime($release_date);
		$announcement_month = (int)$announcement_date->format('m');
		$abs_guarantee_end_year   = (int)$announcement_date->format('Y') + 3;
		if($announcement_month < 4 ) { 
			$abs_guarantee_end_year -= 1; 
		}

		//保証期間内確認：対象年度(n)
		$disp_guarantee = '対象外';
		$tg_year = $target_year[0]['YearMst']['target_year'];
		//php5.2.2以降ではDatetime型を比較演算子で比較可
		if( $tg_year <= $abs_guarantee_end_year ) {
			$disp_guarantee = '対象';
		}
		
		//保証期間内確認：前年度(n-1)
		$last_disp_guarantee = '対象外';
		$lastyear = $tg_year -1;
		//php5.2.2以降ではDatetime型を比較演算子で比較可
		if( $lastyear <= $abs_guarantee_end_year ) {
			$last_disp_guarantee = '対象';
		}
	
		//結果を返却配列に反映
		//-----------------------------------------------------------------------------------------
		$result_record = $result_detail[0]['ModelData'];
		unset($result_record['release_date']);	//returnとして不要である項目を削除
		//AEB割引確認結果反映
		$result_record['start_year'] 	 	= $target_year[0]['YearMst']['target_year'];
		$result_record['last_start_year'] 	= $target_year[0]['YearMst']['target_year']-1;
		$result_record['disp_guarantee']	= $disp_guarantee;
		$result_record['last_disp_guarantee'] = $last_disp_guarantee;
		//自動車タイプ「軽」フラグ
		$result_record['is_kei'] 			= $is_kei;
			
		//AccessLog書き込み
		//-----------------------------------------------------------------------------------------
		$maker_name = $result_detail[0]['ModelData']['maker_name'];
		$car_name	= $result_detail[0]['ModelData']['car_name'];
		$model	  	= $result_detail[0]['ModelData']['model'];
		$result = $this->_insertAccessLog($maker_name, $car_name, $model);
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
		$return_json = array( 'is_error' 	=> $is_error,
							  'result_data' => $result_record
							 );
		return json_encode($return_json);
	}

	/**
	 * メーカーリスト取得
	 *　@param $form_info
	 */
	public function getMakerlist() {
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
		$return_json[0] = array('view' => 'メーカーを選択');
		$return_json[0] += array('value' => '');
		
		//入力チェック
		//-----------------------------------------------------------------------------------------
		//メーカ名未選択の場合
		if( !isset($form_info['car_type_maker_car']) || $form_info['car_type_maker_car'] == '') {
			return json_encode($return_json);
		}
		//バリデーション
		if(!preg_match(PREG_CAR_TYPE, $form_info['car_type_maker_car'])) {
			return json_encode($return_json);
		}
	
		//検索処理
		//-----------------------------------------------------------------------------------------
		$maker_names = array();
		$fields = array('DISTINCT maker_name');
		$order 	= array('maker_name asc');
		$conditions = array('car_type'=> $form_info['car_type_maker_car']); 
		$result = true;
		$result = $this->_selectModelData($fields, $order, $conditions, $maker_names);
		if(!$result) {
			//DB取得失敗エラー
			return false;
			$this->showErrorPage_500();
		}
		else {
			//return用に配列内容調整
			for($i = 0; $i < sizeof($maker_names); $i++) {
				$return_json[$i+1] =  array('view'  => $maker_names[$i]['ModelData']['maker_name']);
				$return_json[$i+1] += array('value' => $maker_names[$i]['ModelData']['maker_name']);
			}
		}
		return json_encode($return_json);
	}

	
	
	/**
	 * 車名リスト取得
	 *　@param $form_info
	 */
	public function getCarlist() {
		if(!$this->request->is('ajax')){
			return false;
			$this->showErrorPage_400();
		}
		if(!isset($_POST)){
			return false;
			$this->showErrorPage_400();
		}
		
		//オートレンダーオフ
		$this->autoRender = false;
		
		//ローカル変数
		$form_info = $_POST;
		$return_json = array();
		$return_json[0] = array('view' => '車名を選択');
		$return_json[0] += array('value' => '');

		//入力チェック
		//-----------------------------------------------------------------------------------------	
		//項目未選択
		if( !isset($form_info['car_type_maker_car']) || $form_info['car_type_maker_car'] == '') {
			return json_encode($return_json);
		}
		if( !isset($form_info['maker_name']) || $form_info['maker_name'] == '') {
			return json_encode($return_json);
		}

		//バリデーション
		if(!preg_match(PREG_CAR_TYPE, $form_info['car_type_maker_car'])) {
			return json_encode($return_json);
		}
		if(!preg_match(PREG_MAKER_NAME, $form_info['maker_name'])) {
			return json_encode($return_json);
		}
		
		//検索処理
		//-----------------------------------------------------------------------------------------
		$car_names = array();
		$fields = array('DISTINCT car_name');
		$order 	= array('car_name asc');
		$conditions = array();
		$conditions = array('car_type' =>$form_info['car_type_maker_car'], 'maker_name'=> $form_info['maker_name']);
		$result = $this->_selectModelData($fields, $order, $conditions, $car_names);
		if(!$result) {
			//DB取得失敗エラー
			return false;
			$this->showErrorPage_500();
		}
		else {
			//return用に配列内容調整
			for($i = 0; $i < sizeof($car_names); $i++) {
				$return_json[$i+1] =  array('view'  => $car_names[$i]['ModelData']['car_name']);
				$return_json[$i+1] += array('value' => $car_names[$i]['ModelData']['car_name']);
			}
		}
		return json_encode($return_json);
	}
	
	
	//DBモデルアクセス
	//=============================================================================================
	//SELECT
	//---------------------------------------------------------------------------------------------
	/**
	 * MakerMst取得
	 * @param $fields, $order, $conditions
	 * @param &$records
	 * @return bool
	 */
	private function _selectMakerMst($fields, $order, $conditions, &$records) {
		$ret = false;
		
		//検索実行
		$records = $this->MakerMst->find(
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
	 * MakerMst取得(List)
	 * @param $order,$conditions, &$records
	 * @return bool
	 */
	private function _selectMakerMstList($order, $conditions, &$records) {
		$ret = false;
		
		//検索実行
		$records = $this->MakerMst->find(
			'list',
			array(
				'fields'	 => array('maker_name','maker_name'),
				'order'		 => $order,
				'conditions' => $conditions
			)
		);
		
		$ret = true;
		return $ret;
	}
	 
	/**
	 * CarMst取得
	 * @param $fields, $order, $conditions
	 * @param &$records
	 * @return bool
	 */
	private function _selectCarMst($fields, $order, $conditions, &$records) {
		$ret = false;
		
		//検索実行
		$records = $this->CarMst->find(
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
	 * CarMst取得(List)
	 * @param $order,$conditions, &$records
	 * @return bool
	 */
	private function _selectCarMstList($order, $conditions, &$records) {
		$ret = false;
		
		//検索実行
		$records = $this->CarMst->find(
			'list',
			array(
				'fields'	 => array('car_name','car_name'),
				'order'		 => $order,
				'conditions' => $conditions
			)
		);
		
		$ret = true;
		return $ret;
	}

 	/**
	 * ModelData取得
	 * @param $fields, $order, $conditions
	 * @param &$records
	 * @return bool
	 */
	private function _selectModelData($fields, $order, $conditions, &$records) {
		$ret = false;
		
		//検索実行
		$records = $this->ModelData->find(
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
	 * YearMst取得
	 * @param $fields, $order, $conditions
	 * @param &$records
	 * @return bool
	 */
	private function _selectYearMst($order, $conditions, &$records) {
		$ret = false;
				
		//検索実行
		$records = $this->YearMst->find(
			'all',
			array(
				'fields'	 => array('target_year'),
				'order'		 => $order,
				'conditions' => $conditions
			)
		);

		$ret = true;
		return $ret;
	}
	
	/**
	 * AccessLog書き込み
	 * @param $maker_name, $car_name, $model
	 * @return bool
	 */
	private function _insertAccessLog($maker_name, $car_name, $model) {
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
				'maker_name'  => $maker_name,
				'car_name'	  => $car_name,
				'model'  	  => $model,
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