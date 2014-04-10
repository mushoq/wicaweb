<?php
/**
 * Loads css files according module, controller and action
 *
 * @category   wicaWeb
 * @package    Core_view_helpers
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license	   GNP
 * @author	   Santiago Arellano
 * @version    1.0
 */

class Zend_View_Helper_CssHelper extends Zend_View_Helper_Abstract
{  
    function cssHelper() {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $arrControllerName = explode('_',$request->getControllerName());
        
        $file_uri = APPLICATION_PATH. '/../public/css/modules/' . $request->getModuleName(). '/' . $arrControllerName[0] . '/' . $request->getActionName() . '.css';
		$short_uri = '/css/modules/' . $request->getModuleName(). '/' . $arrControllerName[0] . '/' . $request->getActionName() . '.css';
       
        if (file_exists($file_uri)) 
            return '<link type="text/css" href="'.$short_uri.'" rel="Stylesheet" />';
        
    }
}