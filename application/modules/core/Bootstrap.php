<?php

/**
 *	Bootstrap allows files to be auto loaded
 *
 * @category   WicaWeb
 * @package    Core
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Santiago Arellano
 */

class Core_Bootstrap extends Zend_Application_Module_Bootstrap{

	
	/*
	 * Create init function to call Module Check Permissions
	 */
	protected function _initCheck(){
		
		$this->bootstrap('frontController');
		$layout = Zend_Controller_Action_HelperBroker::addHelper(
				new ModuleCheckPermissions());
	}
	
}

/**
 * To prevent pass without login 
 */
class ModuleCheckPermissions extends Zend_Controller_Action_Helper_Abstract
{
	public function preDispatch()
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		//Check module and controller name
		if($request->getModuleName()=='core' &&  ($request->getControllerName()!='index' && $request->getControllerName()!='error') )
		{
			if (!Zend_Auth::getInstance ()->hasIdentity ()) {

				throw new Zend_Exception("CUSTOM_EXCEPTION:".$lang->translate('No Access Permissions'));
		
			}

		}		
		
	}
}