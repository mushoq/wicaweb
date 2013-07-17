<?php

/**
 * Renders a Label to place text in the form
 *
 * @category   wicaWeb
 * @package    Core_view_helpers
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license	   GNP
 * @author	   Esteban
 * @version    1.0
 */

class Zend_View_Helper_FormLabelHelper extends Zend_View_Helper_FormElement
{  
    function formLabelHelper($name, $value = null ,$attribs = null) {  
		
        $class = '';

        if (isset($attribs['class'])) {
             $class = 'class = "'. $attribs['class'] .'"';
        }
    		
    	 
        return '<div '.$class.'><label><b>'.$value.'</b></label></div>';
    }
}