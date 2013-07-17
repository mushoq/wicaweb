<?php
/**
 *	Model WebsiteState contains specific functions for wc_website_state table
 *
 * @category   WicaWeb
 * @package    Core_Model
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Esteban
 */

class Core_Model_WebsiteState extends Core_Model_Factory
{
	public static $type = array('online'=>'Online', 'offline'=>'Offline', 'comingsoon'=>'Coming Soon');
	public static $status = array('active'=>'Activo','inactive'=>'Inactivo');
}