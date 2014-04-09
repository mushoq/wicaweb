<?php
/**
 *	Model content contains specific functions
*
* @category   WicaWeb
* @package    Core_Model
* @copyright  Copyright (c) WicaWeb - Mushoq
* @license    GNP
* @version    1.0
* @author	  Santiago Arellano
*/

class Core_Model_ContentStorage extends Core_Model_Factory
{
	/**
	 * Searchs for related section content
	 * @param int $serial_sec
	 * @return object array
	 */
	public function getContentsBySection($serial_sec = 0, $website_id = 0, $internal_name = null, $content_type_id = 0, $temp_contents_id = null)
	{
		$table = new Zend_Db_Table('wc_content_storage');
		$select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
		$select->setIntegrityCheck(false)
		->joinLeft('wc_content_by_section_storage', 'wc_content_by_section_storage.content_id = wc_content_storage.id', array('serial_cbs'=>'wc_content_by_section_storage.id', 'weight'=>'wc_content_by_section_storage.weight', 'column_number'=>'wc_content_by_section_storage.column_number', 'align'=>'wc_content_by_section_storage.align'))
		->joinLeft('wc_section_storage', 'wc_section_storage.id = wc_content_by_section_storage.section_id', array('section_name'=>'wc_section_storage.title', 'article'=>'wc_section_storage.article', 'section_id'=>'wc_section_storage.id'))
		->join('wc_content_type', 'wc_content_type.id = wc_content_storage.content_type_id', array('type'=>'wc_content_type.name'));
		if($website_id)
			$select->where('wc_content_storage.website_id = ?', $website_id);
		if($serial_sec)
			$select->where('wc_content_by_section_storage.section_id = ?', $serial_sec);
		if($internal_name)
			$select->where('LOWER(wc_content_storage.internal_name) LIKE _utf8 "%'.$internal_name.'%" COLLATE utf8_bin');
		if($content_type_id)
			$select->where('wc_content_type.id = ?', $content_type_id);
		if($temp_contents_id)
			$select->where('wc_content.id NOT IN('.implode(',', $temp_contents_id).')');		
	
		$select->order(array('weight ASC'));
	
		$rows = $table->fetchAll($select);
// 		   		Zend_Debug::dump($select->__toString());die;
		return $rows;
	}	
}