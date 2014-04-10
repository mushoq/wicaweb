<?php
/**
 *	Model Website contains specific functions
 *
 * @category   WicaWeb
 * @package    Core_Model
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Esteban
 */

class Core_Model_Website extends Core_Model_Factory
{
		
	public static $confirm = array('yes'=>'Yes', 'no'=>'No');
	public static $comments_enum = array('none'=>'None','all'=>'All','section'=>'Section','article'=>'Article');
	public static $comments_type = array('internal'=>'Internal','external'=>'External');
	public static $hour_format = array('12H' => '12H','24H' => '24H');
		
}