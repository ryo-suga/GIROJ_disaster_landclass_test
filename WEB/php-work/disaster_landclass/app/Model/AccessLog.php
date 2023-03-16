<?php

/**
 * アクセスログモデル
 *
 *
 */
App::uses('AppModel','Model');
 
class AccessLog extends AppModel{
	//model name
	var $name = 'AccessLog';
	
	//using table
	var $useTable = 'tbl_access_log';

	public function isValid($maker_name, $car_name, $model){
		$is_error = false;

		//メーカー名
        //--------------------------------------------------------------------------------------------------------------
        if( $maker_name ==='') { $is_error = true;} 
        if( !preg_match('/^[ァ-ヶ､ー -\~･]{1,20}$/u', $maker_name)) {
            $is_error = true;
        }
     
        //車名
        //--------------------------------------------------------------------------------------------------------------
        if( $car_name ==="")  { $is_error = true;} 
		if( !preg_match('/^[ァ-ヶ､ー -\~･]{1,80}$/u', $car_name)) 	{
            $is_error = true;
        }
		
		//型式
        //--------------------------------------------------------------------------------------------------------------
        if( $model === "") { $is_error = true;}
		if( !preg_match('/^[ -\~]{1,15}$/',$model )) {
			$is_error = true;
        }
		
		return !$is_error;
	}
}