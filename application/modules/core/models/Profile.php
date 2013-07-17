<?php
/**
 *	Model profile
 *
 * @category   WicaWeb
 * @package    Core_Model
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   David Rosales
 */

class Core_Model_Profile extends Core_Model_Factory
{		
	public static $status_profile = array(''=>'Selected','active'=>'Active','inactive'=>'Inactive');
	
	/**
	 * Searchs for related module action by profile
	 * @param int $profile
	 * @return array
	 */
	public function getModuleActionByProfile($profile)
	{
		$table = new Zend_Db_Table('wc_profile');
		$select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
		$select->setIntegrityCheck(false)
		->join('wc_module_action_profile', 'wc_module_action_profile.profile_id = wc_profile.id', array('action_id'=>'wc_module_action_profile.module_action_id'))
		->join('wc_module_action', 'wc_module_action_profile.module_action_id = wc_module_action.id', array('action_name'=>'wc_module_action.action', 'action_title'=>'wc_module_action.title'))
		->join('wc_module', 'wc_module_action.module_id = wc_module.id', array('module_id'=>'wc_module.id', 'module_name'=>'wc_module.name', 'module_action'=>'wc_module.action'))
		->where('wc_profile.id = ?', $profile);
			
		$result = $table->fetchAll($select);

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
				$obj = (object)$temp;
				array_push($return, $obj);
			}
		}
		return $return;
	}
}