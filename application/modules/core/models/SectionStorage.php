<?php
/**
 *	Model section contains specific functions 
 *
 * @category   WicaWeb
 * @package    Core_Model
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Santiago Arellano
 */

class Core_Model_SectionStorage extends Core_Model_Factory
{		
	public static $status_publication = array('published'=>'Published','nonpublished'=>'Nonpublished');
	public static $section_type = array('public'=>'Public','private'=>'Private');
	public static $target_type = array('self'=>'Self','blank'=>'Blank');
	public static $confirm = array('yes'=>'Yes','no'=>'No');
		
}