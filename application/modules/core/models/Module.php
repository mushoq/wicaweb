<?php
/**
 *	Model module contains specific functions 
 *
 * @category   WicaWeb
 * @package    Core_Model
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   David Rosales
 */

class Core_Model_Module extends Core_Model_Factory
{		
	
	public static $module_status = array(''=>'Selected','active'=>'Active','inactive'=>'Inactive');

	/**
	 * Searchs for external modules installed	 
	 * @return array $modules
	 */
	public static function getExternalModules()
	{	
		//get table adapter
		$adapter = Zend_Db_Table::getDefaultAdapter();
	
		$sql = 'SELECT m.id, m.name, m.action, m.image, m.partial,m.description 
		FROM wc_module m
		WHERE m.id NOT IN (1,2,3,4,5,6,7,8)';
			
		$data = $adapter->query($sql);
	
		$result = $data->fetchall();
	
		$return = array();
		if($result){
			foreach($result as $row)
			{
				$temp = array();
				foreach($row as $col=>$value)
				{
					$temp[$col] = utf8_encode($value);
				}
					
				//convert into object
				$obj = $temp;
				array_push($return, $obj);
			}
		}
		return $return;
	}
		
}
