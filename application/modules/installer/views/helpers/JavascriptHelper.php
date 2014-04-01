<?php
/**
 * Loads javascript files according module, controller and action
 *
 * @category   wicaWeb
 * @package    Core_view_helpers
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license	   GNP
 * @author	   Santiago Arellano
 * @version    1.0
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