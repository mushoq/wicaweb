<?php
/**
 *	Model content contains specific functions
*
* @category   WicaWeb
* @package    Core_Model
* @copyright  Copyright (c) WicaWeb - Mushoq
* @license    GNP
* @version    1.1
* @author	  Santiago Arellano, José Luis Landazuri
*/

class Core_Model_Content extends Core_Model_Factory
{
	
	public static $status_values = array('active'=>'Activo', 'inactive'=>'Inactivo');
	public static $type_values = array (
									'internal_link' =>  'Internal Link' ,
									'external_link' =>  'External Link' ,
									'e_mail' =>  'E-mail' ,
									'file' =>  'File'  
							);
	public static $captcha_values = array (
									'yes' =>  'Yes' ,
									'no' =>  'No'  
							);
	public static $dinamic_values = array (
									'yes' =>  'Yes' ,
									'no' =>  'No' 
							);
	public static $target_values = array (
									'_blank' =>  'Blank' ,
									'_self' =>  'Self' 
							);
	public static $resizeimg_values = array (
									'yes' =>  'Yes' ,
									'no' =>  'No'
							);
	public static $background_values =array (
									'transparent' => 'Transparent',
									'opaque' => 'Opaque'
							);
       public static $watermarkimg_values = array (
									'yes' =>  'Yes' ,
									'no' =>  'No');
       public static $zoom_values = array ('yes' =>'Yes',
                                           'no' =>'No'); 
	
	
	/**
	 * Searchs for related section content
	 * @param int $serial_sec
	 * @return object array
	 */
	public function getContentsBySection($serial_sec = 0, $website_id = 0, $internal_name = null, $content_type_id = 0, $temp_contents_id = null)
	{				
		$table = new Zend_Db_Table('wc_content');
		$select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART); 
		$select->setIntegrityCheck(false)		
		->joinLeft('wc_content_by_section', 'wc_content_by_section.content_id = wc_content.id', array('serial_cbs'=>'wc_content_by_section.id', 'weight'=>'wc_content_by_section.weight', 'column_number'=>'wc_content_by_section.column_number', 'align'=>'wc_content_by_section.align'))				
		->joinLeft('wc_section', 'wc_section.id = wc_content_by_section.section_id', array('section_name'=>'wc_section.title', 'article'=>'wc_section.article', 'section_id'=>'wc_section.id'))		
		->join('wc_content_type', 'wc_content_type.id = wc_content.content_type_id', array('type'=>'wc_content_type.name'));
		if($website_id)
			$select->where('wc_content.website_id = ?', $website_id);		
		if($serial_sec)					
			$select->where('wc_content_by_section.section_id = ?', $serial_sec);		
		if($internal_name)
			$select->where('LOWER(wc_content.internal_name) LIKE _utf8 "%'.$internal_name.'%" COLLATE utf8_bin');
		if($content_type_id)
			$select->where('wc_content_type.id = ?', $content_type_id);
		if($temp_contents_id)
			$select->where('wc_content.id NOT IN('.implode(',', $temp_contents_id).')');
		
		
		$select->order(array('weight ASC'));
		
		$rows = $table->fetchAll($select);
//   		Zend_Debug::dump($select->__toString());die;
		return $rows;	
	}

}