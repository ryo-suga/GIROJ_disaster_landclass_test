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

	public function isValid($prefectures, $municipality){
		$is_error = false;

		//都道府県名
        //--------------------------------------------------------------------------------------------------------------
        if( $prefectures ==='') { $is_error = true;} 
        //if( !preg_match('/^[一-龠々]{1,4}$/u', $prefectures)) {
        if( !preg_match(PREG_PREFECTURES_NAME, $prefectures)) {
            $is_error = true;
        }
     
        //市区町村名
        //--------------------------------------------------------------------------------------------------------------
        if( $municipality ==="")  { $is_error = true;} 
		if( !preg_match(PREG_MUNICIPALITY_NAME, $municipality)) 	{
            $is_error = true;
        }
		
		return !$is_error;
	}
}