<?php
/**
 *	Model module action 
 *
 * @category   WicaWeb
 * @package    Core_Model
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Diego Perez
 */

class Core_Model_ModuleDescription extends Core_Model_Factory
{	
	/**
	 * This function get the last module description.
	 * @param
	 * @return array $last_module_description_saved
	 */
	public static function getLastModuleDescriptionId(){
	
		//get table adapter
		$adapter = Zend_Db_Table::getDefaultAdapter();
	
		$sql = 'SELECT MAX(id) as last_module_description FROM wc_module_description';
			
		$data = $adapter->query($sql);
	
		$result = $data->fetchall();
	
		$last_module_description = array();
		if($result){
			$last_module_description = $result;
		}
	
		return $last_module_description;
	}	
}
