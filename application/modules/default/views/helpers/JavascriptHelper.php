<?php
/**
 *	Loads js files according module, controller and action
 *
 * @category   WicaWeb
 * @package    Core_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Santiago Arellano
 */

class Zend_View_Helper_JavascriptHelper extends Zend_View_Helper_Abstract
{  
    function javascriptHelper() {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $arrControllerName = explode('_',$request->getControllerName());
        
        $file_uri = APPLICATION_PATH. '/../public/js/modules/' . $request->getModuleName(). '/' . $arrControllerName[0] . '/' . $request->getActionName() . '.js';
		$short_uri = '/js/modules/' . $request->getModuleName(). '/' . $arrControllerName[0] . '/' . $request->getActionName() . '.js';
       
        if (file_exists($file_uri)) 
            return '<script type="text/javascript" src="'.$short_uri.'"></script>';
        
    }
}