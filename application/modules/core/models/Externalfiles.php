<?php
/**
 *	Model Externalfiles contains specific functions and vars for external files.
 *
 * @category   WicaWeb
 * @package    Core_Model
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Esteban
 */

class Core_Model_Externalfiles extends Core_Model_Factory
{
		
	public static $confirm = array('yes'=>'Yes', 'no'=>'No');
	
	public function getNextOrderNumber($file_type,$website_id){
		$table = new Zend_Db_Table('wc_external_files');
		$select = $table->select()->from('wc_external_files',array('max(order_number)'))->where('type = ?', $file_type)->where('website_id = ?', $website_id);					
		$row = $table->fetchRow($select);		
		$current = 0;
		foreach($row as $r){
			$current = $r;
		}
		
		$next = $current + 1;
		return $next;
	}
}