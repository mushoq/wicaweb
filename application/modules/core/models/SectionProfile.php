<?php
/**
 *	Model section profile
 *
 * @category   WicaWeb
 * @package    Core_Model
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   David Rosales
 */

class Core_Model_SectionProfile extends Core_Model_Factory
{		
	/**
	 * Searchs for published sections by profile
	 * @param int $profile_id
	 * @return object array
	 */
	public function getPublishedSectionsByProfile($profile_id, $website_id = 0)
	{			
		$table = new Zend_Db_Table('wc_section_profile');
		$select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
		$select->setIntegrityCheck(false)
		->join('wc_section', 'wc_section.id = wc_section_profile.section_id', array('website_id'=>'wc_section.website_id'))
		->where('wc_section_profile.profile_id = ?', $profile_id);
		
		if($website_id)
			$select->where('wc_section.website_id = ?', $website_id);
// 		Zend_Debug::dump($select->__toString());
		$rows = $table->fetchAll($select);

		return $rows;
	}

	/**
	 * Searchs for temp sections by profile
	 * @param int $profile_id
	 * @return object array
	 */
	/*public function getTempSectionsByProfile($profile_id, $website_id = 0)
	{
		$table = new Zend_Db_Table('wc_section_profile');
		$select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
		$select->setIntegrityCheck(false)
		->join('wc_section_temp', 'wc_section_temp.id = wc_section_profile.section_temp_id', array('website_id'=>'wc_section_temp.website_id'))
		->where('wc_section_profile.profile_id = ?', $profile_id);
	
		if($website_id)
			$select->where('wc_section_temp.website_id = ?', $website_id);
// 		Zend_Debug::dump($select->__toString());
		$rows = $table->fetchAll($select);
	
		return $rows;
	}*/
}