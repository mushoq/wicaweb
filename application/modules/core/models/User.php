<?php
/**
 *	Model User contains specific functions for wc_user table
 *
 * @category   WicaWeb
 * @package    Core_Model
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Esteban
 */

class Core_Model_User extends Core_Model_Factory
{		
	public static $status_user = array('active'=>'Active','inactive'=>'Inactive');
	
	/**
	 * This function recovers the info to create the control panel.
	 * @param int $profile
	 * @return array $modules
	 */
	public static function getUserModules($profile){

		//get table adapter
		$adapter = Zend_Db_Table::getDefaultAdapter();
						
		$sql = 'SELECT m.id, m.name, m.action, m.image 
				FROM wc_module_action_profile map
				JOIN wc_module_action ma ON map.module_action_id = ma.id
				JOIN wc_module m ON ma.module_id = m.id
				WHERE map.profile_id = ?
				GROUP BY ma.module_id';
		 
		$data = $adapter->query($sql,array($profile));
		
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