<?php
/**
 *	Loads external js files added by the user according the corresponding website template 
 *
 * @category   WicaWeb
 * @package    Core_Helper
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Santiago Arellano
 */

class Zend_View_Helper_TemplateJavascriptFilesHelper extends Zend_View_Helper_Abstract
{  
    function templateJavascriptFilesHelper() {

    	$front_ids = New Zend_Session_Namespace('ids');

    	if($front_ids->website_id)
    	{
	        $website =  new Core_Model_Website();
	        $data_website = $website->find('wc_website', array('id' => $front_ids->website_id));
	        
	        $website_template = new Core_Model_WebsiteTemplate();
	        $data_template = $website_template->find('wc_website_template', array('id' => $data_website[0]->template_id));
	
	        $js_links = '';
	        $js_files = null;
	        if($data_template[0]->js_files){
		        $js_files = explode(',',$data_template[0]->js_files);
		        array_pop($js_files);
	        }
	        
	        if($js_files)
		        if(is_dir ( APPLICATION_PATH . '/../public/js/templates/')){
			        foreach($js_files as $js){
			        		$file_uri = APPLICATION_PATH. '/../public/js/templates/' . $data_template[0]->name .'/'. $js;
			        		$short_uri = '/js/templates/' . $data_template[0]->name .'/'. $js;
			        		if (file_exists($file_uri)) 
			        			$js_links .= '<script type="text/javascript" src="'.$short_uri.'"></script>';     	
			        }
		        }
			return $js_links;
    	}
    }
}