<?php
/**
 *	Loads external css files added by the user according the corresponding website template 
 *
 * @category   WicaWeb
 * @package    Core_Helper
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Santiago Arellano
 */

class Zend_View_Helper_TemplateCssFilesHelper extends Zend_View_Helper_Abstract
{  
    function templateCssFilesHelper() {

    	$front_ids = New Zend_Session_Namespace('ids');
        
    	if($front_ids->website_id)
    	{
	        $website =  new Core_Model_Website();
	        $data_website = $website->find('wc_website', array('id' => $front_ids->website_id));
	        
	        $website_template = new Core_Model_WebsiteTemplate();
	        $data_template = $website_template->find('wc_website_template', array('id' => $data_website[0]->template_id));
	
	        $css_links = '';
	        $media_css = null;
	        $css_files = null;
	        
	        if($data_template[0]->css_files){
		        $css_files = explode(',',$data_template[0]->css_files);
		        array_pop($css_files);
	        }
	        if($data_template[0]->media_css){
	        	$media_css = explode(',',$data_template[0]->media_css);
	        	array_pop($media_css);    
	        }    
	        
	        if($css_files)
		        if(is_dir ( APPLICATION_PATH . '/../public/css/templates/')){
			        foreach($css_files as $key_css => $css){
			        		$file_uri = APPLICATION_PATH. '/../public/css/templates/' . $data_template[0]->name .'/'. $css;
			        		$short_uri = '/css/templates/' . $data_template[0]->name .'/'. $css;
			        		if (file_exists($file_uri)){ 
			        			if($media_css[$key_css]!='null')
			        				$css_links .= '<link type="text/css" href="'.$short_uri.'" rel="Stylesheet" media="'.$media_css[$key_css].'"/>';  
			        			else
			        				$css_links .= '<link type="text/css" href="'.$short_uri.'" rel="Stylesheet"/>';
			        		}
			        }
		        }
			return $css_links;
    	}
    }
}