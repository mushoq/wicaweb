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

class Zend_View_Helper_ExternalCssHelper extends Zend_View_Helper_Abstract
{  
    function externalCssHelper() {

    	$front_ids = New Zend_Session_Namespace('ids');
        
        $external_file =  new Core_Model_Externalfiles();
        $data = $external_file->personalized_find('wc_external_files', array(array('website_id','=',$front_ids->website_id)),'order_number');

        $arr_helper = array();
        
        foreach($data as $d){
        	$filename = $d->path;
        	$extension = pathinfo($filename, PATHINFO_EXTENSION);
        	if($extension == 'css'){
        		$file_uri = APPLICATION_PATH. '/../public/css/external/' . $filename;
        		$short_uri = '/css/external/' . $filename;
        		if (file_exists($file_uri)) 
        			$arr_helper[] = '<link type="text/css" href="'.$short_uri.'" rel="Stylesheet" />';
        	}
        }
        
        $scripts = '';
        
        if($arr_helper && count($arr_helper)>0){
        	foreach ($arr_helper as $ext_css){
        		$scripts.= $ext_css;
        	}
        }
	
		return $scripts;
    }
}