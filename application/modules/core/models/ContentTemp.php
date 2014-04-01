<?php
/**
 *	Model content temp
*
* @category   WicaWeb
* @package    Core_Model
* @copyright  Copyright (c) WicaWeb - Mushoq
* @license    GNP
* @version    1.0
* @author	  David Rosales
*/

class Core_Model_ContentTemp extends Core_Model_Factory{
	
	/**
	 * Searchs for related section content
	 * @param int $serial_sec
	 * @return object array
	 */
	public function getTempContentsBySection($serial_sec = 0, $website_id = 0, $internal_name = null, $content_type_id = 0)
	{
		$table = new Zend_Db_Table('wc_content_temp');
		$select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
		$select->setIntegrityCheck(false)
		->joinLeft('wc_content_by_section_temp', 'wc_content_by_section_temp.content_temp_id = wc_content_temp.id', array('serial_cbs'=>'wc_content_by_section_temp.id', 'weight'=>'wc_content_by_section_temp.weight', 'column_number'=>'wc_content_by_section_temp.column_number', 'align'=>'wc_content_by_section_temp.align'))		
		->joinLeft('wc_section_temp', 'wc_section_temp.id = wc_content_by_section_temp.section_temp_id', array('section_name'=>'wc_section_temp.title', 'article'=>'wc_section_temp.article', 'section_id'=>'wc_section_temp.section_id'))
		->join('wc_content_type', 'wc_content_type.id = wc_content_temp.content_type_id', array('type'=>'wc_content_type.name'));
		if($website_id)
			$select->where('wc_content_temp.website_id = ?', $website_id);
		if($serial_sec)
			//$select->where('wc_content_by_section_temp.section_id = ?', $serial_sec);
			$select->where('wc_section_temp.section_id = ?', $serial_sec);
		if($internal_name)
			$select->where('LOWER(wc_content_temp.internal_name) LIKE _utf8 "%'.$internal_name.'%" COLLATE utf8_bin');
		if($content_type_id)
			$select->where('wc_content_type.id = ?', $content_type_id);
	
		$select->order(array('weight ASC'));
	
		$rows = $table->fetchAll($select);
		//Zend_Debug::dump($select->__toString());die;
		return $rows;
	}
	
	/**
	 * Searchs for temp contents
	 * @return array $modules
	 */
	public static function getTempContentsId()
	{
		//get table adapter
		$adapter = Zend_Db_Table::getDefaultAdapter();
	
		$sql = 'SELECT DISTINCT ct.content_id
		FROM wc_content_field_temp ft
		JOIN wc_content_temp ct ON ct.id = ft.content_temp_id 
		WHERE ft.content_id IS NULL';
			
		$data = $adapter->query($sql);
	
		$result = $data->fetchall();
	
		$temp_contents = array();
		if($result){
			$temp_contents = $result;
		}
	
		return $temp_contents;
	}	
}