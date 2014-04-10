<?php
/**
 *	Model Dictionary contains specific functions for wc_dictionary table
 *
 * @category   WicaWeb
 * @package    Core_Model
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Diego Pérez
 */

class Core_Model_Dictionary extends Core_Model_Factory
{		
	public static $status_dictionary = array('active'=>'Active','inactive'=>'Inactive');
	
	/**
	 * This function get the last saved dictionary.
	 * @param 
	 * @return array $last_dictionary_saved
	 */
	public static function getLastDictionaryId(){
	
		//get table adapter
		$adapter = Zend_Db_Table::getDefaultAdapter();
	
		$sql = 'SELECT MAX(id) as last_dictionary FROM wc_dictionary';
			
		$data = $adapter->query($sql);
	
		$result = $data->fetchall();
	
		$last_dictionary = array();
		if($result){
			$last_dictionary = $result;
		}
	
		return $last_dictionary;
	}
	
}