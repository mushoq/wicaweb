<?php
/**
 *	Loads external js files added by the user according the corresponding website 
 *
 * @category   WicaWeb
 * @package    Core_Helper
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Esteban
 */

class Zend_View_Helper_ExternalJavascriptHelper extends Zend_View_Helper_Abstract
{  
    function externalJavascriptHelper() {

    	$front_ids = New Zend_Session_Namespace('ids');
        
        $external_file =  new Core_Model_Externalfiles();
        $data = $external_file->personalized_find('wc_external_files', array(array('website_id','=',$front_ids->website_id)),'order_number');

        $arr_helper = array();
        
        foreach($data as $d){
        	$filename = $d->path;
        	$extension = pathinfo($filename, PATHINFO_EXTENSION);
        	if($extension == 'js'){
        		$file_uri = APPLICATION_PATH. '/../public/js/external/' . $filename;
        		$short_uri = '/js/external/' . $filename;
        		if (file_exists($file_uri)) 
        			$arr_helper[] = '<script type="text/javascript" src="'.$short_uri.'"></script>';
        	}
        }
        
        $scripts = '';
        
        if($arr_helper && count($arr_helper)>0){
        	foreach ($arr_helper as $ext_js){
        		$scripts.= $ext_js;
        	}
        }
	
		return $scripts;
    }
}