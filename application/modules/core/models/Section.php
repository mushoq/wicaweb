<?php
/**
 *	Model section contains specific functions 
 *
 * @category   WicaWeb
 * @package    Core_Model
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   David Rosales
 */

class Core_Model_Section extends Core_Model_Factory
{		
	public static $status_publication = array('published'=>'Published','nonpublished'=>'Nonpublished');
	public static $section_type = array('public'=>'Public','private'=>'Private');
	public static $target_type = array('self'=>'Self','blank'=>'Blank');
	public static $confirm = array('yes'=>'Yes','no'=>'No');
	
	/**
	 * Get sections with variable content
	 * @return array $sections
	 */
	public static function getSectionWithVariableContent()
	{
		//get table adapter
		$adapter = Zend_Db_Table::getDefaultAdapter();
	
		$sql = "SELECT s.id,s.section_parent_id,s.website_id,s.section_template_id,s.internal_name,s.title,s.subtitle,s.title_browser,s.synopsis,s.keywords,s.type,s.approved,s.homepage,s.article
		FROM wc_section s
		JOIN wc_section_module_area m ON s.id=m.section_id
		JOIN wc_area a ON m.area_id = a.id
		WHERE a.type ='variable' AND m.module_description_id is NULL";
			
		$data = $adapter->query($sql);
	
		$result = $data->fetchall();
	
		$sections = array();
		if($result){
			$sections = $result;
		}
	
		return $sections;
	}
        public static function saveOrderArticle($data, $idArticle) {
            //get table adapter
            $field = key($data);
            $order = $data[$field];
		$adapter = Zend_Db_Table::getDefaultAdapter();
                $sql = "UPDATE wc_section SET " . $field." = ".$order. " WHERE id=$idArticle";
                return  $adapter->query($sql);
        }
        public static function saveConfigArticle($data, $idArticle) {
             //get table adapter
            $field = key($data);
            $order = $data[$field];
		$adapter = Zend_Db_Table::getDefaultAdapter();
                $sql = "UPDATE wc_section SET " . $field . " = '".$order. "' WHERE id=$idArticle"; 
                return  $adapter->query($sql);
        }
		
}