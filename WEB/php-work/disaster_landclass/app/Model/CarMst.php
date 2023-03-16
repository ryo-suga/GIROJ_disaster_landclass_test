<?php

/**
 * 車名マスタモデル
 *
 *
 */
App::uses('AppModel','Model');

class CarMst extends AppModel{
	//model name
	var $name = 'CarMst';
	
	//using table
	var $useTable = 'tbl_car_mst';
}