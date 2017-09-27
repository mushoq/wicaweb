<?php
/**
 *	Functionallity on website main page
 *
 * @category   WicaWeb
 * @package    Core_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.1
 * @author      Jose Luis Landazuri - David Rosales
 */

class Default_IndexController extends Zend_Controller_Action
{
	/**
	 * Loads header, footer and menu
	 */
    public function init()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
//     	GlobalFunctions::move_to_storage();
    	//Checks if WicaWeb was installed
    	$installation_file = GlobalFunctions::checkInstallationFile();
    	
    	if($installation_file)
    	{
    		Zend_Session::namespaceUnset('ids');
                
                $website = new Core_Model_Website();
                
                //Check the current url and get the website ID.
                $websiteId = $this->getRequest()->getParam('siteid');
                if ($websiteId && $websiteId<=count($website->find('wc_website')))
                {
                    $website_obj = $website->personalized_find('wc_website', 
                    array(array('id','=',$websiteId)));
                    
                    //if the ID given was wrong then go to the default page.
                    if (!$website_obj)
                    {
                        $website_obj = $website->personalized_find('wc_website', 
                        array(array('default_page','=','yes')));     
                    }   
                }
                //if the parameter 'ID' does not exist then go to the default page.
                else 
                {
                    $website_obj = $website->personalized_find('wc_website', 
                    array(array('default_page','=','yes')));   
                }           
    		    		
	    	//TODO:get the current website id when the site is not the default one
	    	if(count($website_obj)>0)
	    	{ 
		    	$website_data = get_object_vars($website_obj[0]); //make an array of the object data
		    	$front_ids = New Zend_Session_Namespace('ids');
		    	$front_ids->website_id = $website_data['id'];
		    	
		    	//get the states data
		    	$website_state_model = new Core_Model_WebsiteState();
		    	$website_state_obj = $website_state_model->personalized_find('wc_website_state',array(array('website_id','=',$front_ids->website_id),array('status','=','active')));
		    	$website_state_data = get_object_vars($website_state_obj[0]); //make an array of the object data
		    	$this->view->state = $website_state_data;
		    	
		    	/********
		    	 * website template
		    	 */
		    	$template_id = $website_data['template_id'];
		    	$template_model = new Core_Model_WebsiteTemplate();
		    	$template_aux = $template_model->find('wc_website_template', array('id'=>$template_id));
		    	$template_data = get_object_vars($template_aux[0]); //make an array of the object data
		    	$this->view->template_file = $template_data['file_name'];    
		
		    	/********
		    	 * template render
		    	 */
		    	if(count($template_data)>0)
		    	{
		    		$filename_tpl = $template_data['file_name'];
		    	}
		    	else
		    	{
		    		$filename_tpl = "";
		    	}
		    			
		    	if($filename_tpl)
		    	{
		    		$render = "";
		    		$webtemplate = fopen(APPLICATION_PATH."/modules/default/views/scripts/partials/".$filename_tpl, "r");
		    		if($webtemplate)
		    		{
		    			//Output a line of the file until the end is reached    				
		    			while(!feof($webtemplate))
		    			{
		    				$render.= fgets($webtemplate);
		    			}
		    			fclose($webtemplate);
		    		}
		    	}
		    	$this->view->templaterender = $render;    	
		    	
		    	/********
		    	 * header
		    	*/
		    	$header_data['logo'] = $website_data['logo'];
		    	$header_data['name'] = $website_data['name'];
		    	$header_data['icon'] = $website_data['icon'];
		    	$header_data['analytics'] = $website_data['analytics'];
		    	$header_data['meta_descr'] = $website_data['description'];
		    	$header_data['meta_keywords'] = $website_data['keywords'];
		    	$header_data['meta_author'] = $website_data['copyright'];
                        $header_data['website_url'] = $website_data['website_url'];
		    	$this->view->header = $header_data;
		    	 
		    	/********
		    	 * footer
		    	 */
		    	$footer_data['copyright'] = $website_data['copyright'];
		    	$this->view->footer = $footer_data;
		    	    	
		        /*******
		    	 * menu
		    	 */        	
		    	$section = new Core_Model_Section();
		    	//find existent sections on db according website and to be displayed on menu
		    	$sections_list = $section->personalized_find('wc_section',array(array('website_id','=',$front_ids->website_id), array('display_menu','=','yes'), array('approved','=','yes'), array('publication_status','=','published')),'order_number');
                        
		    	//section_id passed in URL
                        
                        
		    	if($this->_getParam('section_name')){
                            $sections_list_all = $section->personalized_find('wc_section',array(array('website_id','=',$front_ids->website_id), array('approved','=','yes'), array('publication_status','=','published')),'order_number');
                            $section_name = $this->_getParam('section_name');
                            foreach ($sections_list_all as $sec)
		    		{
                                
                                //echo GlobalFunctions::formatFilename($sec->title).'<br>';
		    			if(GlobalFunctions::formatFilename($sec->title) == $section_name){
                                            $section_id = $sec->id;
                                        }
		    		}
                                if(!$section_id){
                                    $this->_helper->redirector ( 'error','page' );
                                }
                                
                        }elseif($this->_getParam('id')){
                             $section_id = $this->_getParam('id');
                        }else{
                            $section_id = 1;
                        }
		    	$sections_arr = array();    	
		    	//sections list array
		    	if($sections_list)
		    	{
		    		foreach ($sections_list as $sec)
		    		{
		    			$sections_arr[] = array('id'=>$sec->id,
		    					'section_parent_id'=>$sec->section_parent_id,
		    					'title'=>$sec->title,
                                                        'homepage'=>$sec->homepage
		    			);
		    		}
		    	}
		   	    	    	
		    	//string with sections tree html
		    	$html_list = '';
		    	
		    	if(count($sections_arr)>0)
		    	{
		    		//sections tree - parents and children as array
		    		$sections_tree = GlobalFunctions::buildSectionTree($sections_arr);
		    		//sections tree as menu
		    		$html_list = GlobalFunctions::buildHtmlSectionMenu($sections_tree, false, NULL, $section_id);    		
		    	}    	
		    	$this->view->menu = $html_list;
                        /*******
		    	 * menu 2
		    	 */        	
		    	$section2 = new Core_Model_Section();
		    	//find existent sections on db according website and to be displayed on menu
		    	$sections_list2 = $section2->personalized_find('wc_section',array(array('website_id','=',$front_ids->website_id), array('display_menu2','=','yes'), array('approved','=','yes'), array('publication_status','=','published')),'order_number');
		    	//section_id passed in URL
		    	$section_id = $this->_getParam('id');
		    	$sections_arr2 = array();    	
		    	//sections list array
		    	if($sections_list2)
		    	{
		    		foreach ($sections_list2 as $sec2)
		    		{
		    			$sections_arr2[] = array('id'=>$sec2->id,
		    					'section_parent_id'=>$sec2->section_parent_id,
		    					'title'=>$sec2->title
		    			);
		    		}
		    	}
		    	//string with sections tree html
		    	$html_list2 = '';
		    	if(count($sections_arr2)>0)
		    	{
		    		//sections tree - parents and children as array
		    		$sections_tree2 = GlobalFunctions::buildSectionTree($sections_arr2);
		    		//sections tree as menu
		    		$html_list2 = GlobalFunctions::buildHtmlSectionMenu2($sections_tree2, false, NULL, $section_id);    		
		    	}    	
		    	$this->view->menu2 = $html_list2;
	    	}
	    	else{
	    		return $this->nowebsiteAction();
	    	}  	
		}else
		{
			//Redirect to installation
			$this->_helper->_redirector->gotoUrl('/installer/index/index');
		}
    }    

	/**
	 * Loads section contents according id or specific section
	 */
	public function indexAction()
    {
    	$front_ids = New Zend_Session_Namespace('ids');
    	
    	// translate library
    	$lang = Zend_Registry::get ( 'Zend_Translate' );
    	
    	/******************
    	 * public user
    	*/    	
    	if($this->_getParam('email') && $this->_getParam('rollback') && $this->_getParam('key') )
    	{    	
    		$email = $this->_getParam('email');
    		$activation_key = $this->_getParam('key');
    	
    		$public_user = new Core_Model_PublicUser();
    		$public_user_data = $public_user->find('wc_public_user',array('email'=>$email,'activation_key'=>$activation_key));
    	
    		if($public_user_data)
    		{
    			$public_user_data[0]->password = $public_user_data[0]->old_password;
    			$public_user->save('wc_public_user', $public_user_data[0]);
    			$this->_helper->flashMessenger->addMessage ( array (
    					'success' => $lang->translate ( 'The password reset has been canceled' )
    			) );
    			$this->_redirect ( '#' );
    		}else
    		{
    			$this->_helper->flashMessenger->addMessage ( array (
    					'danger' => $lang->translate ( 'Some errors occurred during the process' )
    			) );
    			$this->_redirect ( '#' );
    		}
    	}
    	else
    	{
	    	//activation account
	    	if($this->_getParam('email') && $this->_getParam('key'))
	    	{
			$activation_email = $this->_getParam('email');    	
		    	$activation_key = $this->_getParam('key');
		    	
		    	$public_user = new Core_Model_PublicUser();
		    	$public_user_data = $public_user->find('wc_public_user',array('email'=>$activation_email,'activation_key'=>$activation_key));
		    	
		    	if($public_user_data)
		    	{
					$public_user_data[0]->status = 'active';
					$public_user->save('wc_public_user', $public_user_data[0]);
					$this->_helper->flashMessenger->addMessage ( array (
							'success' => $lang->translate ( 'Your account has been activated' )
					) );
					$this->_redirect ( '#' );
		    	}
		    	else
		    	{
					$this->_helper->flashMessenger->addMessage ( array (
							'danger' => $lang->translate ( 'Your account hasn\'t been activated' )
					) );
					$this->_redirect ( '#' );
		    	}		    	
	    	}
    	}
    	
    	if(isset($_SESSION['external_user']))
    	{    		
    		$this->view->change_psw = 0;
    		
    		$public_user = new Core_Model_PublicUser();
    		$public_user_data = $public_user->find('wc_public_user',array('id'=>$_SESSION['external_user']));
    		
    		if($public_user_data)
    		{
    			if($public_user_data[0]->password != $public_user_data[0]->old_password){
    				$this->view->change_psw = 1;
    			}
    			else
    			if($public_user_data[0]->password == $public_user_data[0]->old_password){
    				$this->view->change_psw = 0;
    			}
    		}
    		
    	}    	
    	
    	/******************
    	 * sections content
    	 */    	
    	$website = new Core_Model_Website();
    	$website_obj = $website->find('wc_website', array('id'=>$front_ids->website_id));
    	
    	$area_tpl = array();
    	//find areas according website template config
    	if(count($website_obj)>0)
    	{
    		$website_template = new Core_Model_WebsiteTemplate();
    		$web_tpl = $website_template->find('wc_website_template',array('id'=>$website_obj[0]->template_id));
    		 
    		$template_areas = new Core_Model_Area();
    		$area_tpl = $template_areas->find('wc_area',array('template_id'=>$web_tpl[0]->id));
    	
    		$area_content_variable = $template_areas->find('wc_area',array('template_id'=>$web_tpl[0]->id,'type'=>'variable'));
    	}
    	$this->view->areas = $area_tpl;
          	
    	//section_id passed in URL
    	if($this->_getParam('section_name')){
            $section = new Core_Model_Factory();
            $sections_list_all = $section->personalized_find('wc_section',array(array('website_id','=',$front_ids->website_id), array('approved','=','yes'), array('publication_status','=','published')),'order_number');
            $section_name = $this->_getParam('section_name');
            foreach ($sections_list_all as $sec)
            {

            //echo GlobalFunctions::formatFilename($sec->title).'<br>';
                    if(GlobalFunctions::formatFilename($sec->title) == $section_name){
                        $section_id = $sec->id;
                    }
            }
            if(!$section_id){
                $this->_helper->redirector ( 'error','page' );
            }

        }elseif($this->_getParam('id')){
             $section_id = $this->_getParam('id');
        }else{
            $section_id = 1;
        }
    	$this->view->section_id = $section_id;
    	//will contain section object data as array
    	$section_arr = array();
    	//section model
    	$section_obj = new Core_Model_Section();

    	//contents per section
    	$contents_list = array();
    	//section area for variable content
    	$section_area = 0;
    	
    	if($section_id)
    	{    		
    		//finds section data stored in db
    		$section_data_arr = $section_obj->find('wc_section', array('id'=>$section_id, 'website_id'=>$front_ids->website_id));    		
    	}
    	else 
    	{
    		$section_data_arr = $section_obj->find('wc_section', array('homepage'=>'yes', 'website_id'=>$front_ids->website_id));
    		if(count($section_data_arr)>0)
    			$section_id = $section_data_arr[0]->id;
    	}

    	$contents_list = array();
    	$module_contents = array();
    	
    	if($section_id)
    	{
	    	/******************
	    	 * section prints
	    	 */
	    	$website_printer_option = $website->find('wc_website',array('id'=>$front_ids->website_id, 'prints'=>'yes'));
	    	if(count($website_printer_option)>0)
	    	{
	    		//Printer counter is enabled    		
	    		/*if(!$section_id){
	    			$section_id = 1;
	    		}*/
	
	    		$section_prints = new Core_Model_SectionPrints();
	
	    		//save data in wc_section_prints    		
	    		$section_prints_obj = $section_prints->getNewRow('wc_section_prints');
	    		
	    		//Check if section_id has data in wc_section_prints table
	    		$section_prints_data = $section_prints->find('wc_section_prints',array('section_id'=>$section_id));
	    		
	    		if(count($section_prints_data)>0)
	    		{ 
	    			//Update count
	    			$count = $section_prints_data[0]->count+1;
	    			$section_prints_obj->id = $section_prints_data[0]->id; 
	    			$section_prints_obj->section_id = GlobalFunctions::value_cleaner($section_id);
	    			$section_prints_obj->count = GlobalFunctions::value_cleaner($count);
	    		}
	    		else
	    		{ 
	    			//Initialize count
	    			$count = 1;
	    			$section_prints_obj->section_id = GlobalFunctions::value_cleaner($section_id);
	    			$section_prints_obj->count = GlobalFunctions::value_cleaner($count);
	    		}
	    		
	    		// Save data
	    		$saved_section_print = $section_prints->save('wc_section_prints',$section_prints_obj);    	
	    	}
	    	
	    	$module_contents = array();
	    	/******************
	    	 * section contents by section and external module contents
	    	 */
	    	if(count($section_data_arr)>0)
	    	{
	    		//external modules
	    		$module_obj = new Core_Model_Module();
	    		$modules = $module_obj->getExternalModules();
	    		
	    		//related section_id content data    		
		    	foreach ($section_data_arr as $k=>$section)
		    	{	    		
		    		$this->view->title_browser = str_replace('"','-',str_replace('\\','',$section->title));
                                $this->view->synopsis_browser = strip_tags($section->synopsis);
                                $this->view->author_browser = $section->author;
                                $this->view->id_browser = $section->id;
                                $this->view->article_browser = $section->article;
                                $this->view->website_id = $front_ids->website_id;
                                $this->view->site_url = $_SERVER['REQUEST_URI'];
                                $this->view->image_browser = '';
                                if($section->title_browser)
                                            $this->view->title_browser = $section->title_browser;
                                
                                
                                $content = new Core_Model_Content();
                                /* METATAG OPEN GRAPH IMAGE */ 
                                $image_browser = $content->getContentsBySection($section->id, $front_ids->website_id, null, 2);
                                if(count($image_browser)>0) {
                                    $this->view->image_browser = GlobalFunctions::getContentPreviewForArticle($image_browser[0],500);
                                } else {
                                    $this->view->image_browser = null;
                                }
	
		    		//id is related to section or article when searching for section_area
		    		//same name $section_id guarantees sections build correctly
		    		if($section->article == 'no')
		    		{
						$section_id = $section->id;
		    		}
		    		else
		    		{
		    			$section_parent = $section_obj->find('wc_section', array('id'=>$section->section_parent_id));
		    			if(count($section_parent)>0)
		    			{
		    				foreach ($section_parent as $parent)
		    				{
		    					$section_id = $parent->id;
		    				}
		    			}
		    		}
		    		//section template
		    		$section_template = new Core_Model_SectionTemplate();
		    		$section_tpl = $section_template->find('wc_section_template',array('id'=>$section->section_template_id));
		    		$section_filename_tpl = $section_tpl[0]->file_name;
		    		$column_number = $section_tpl[0]->column_number;
		    		
		    		//section area
		    		$section_module_area = new Core_Model_SectionModuleArea();
		    		$area = $section_module_area->find('wc_section_module_area',array('section_id'=>$section_id));
		    		if(count($area)>0)
		    		{
		    			$area_aux = new Core_Model_Area();
		    			$area_data = $area_aux->find('wc_area',array('id'=>$area[0]->area_id));
		    			if(count($area_data)>0)
		    			{	    				
		    				$section_area = $area_data[0]->area_number;
		    				$section_area_name = $area_data[0]->name;
		    				$area_sec_width = $area_data[0]->width;
		    			}
		    		}
		    		
		    		//content
		    		$content_arr = array();
		    		$temp = array();
		    		
		    		$content = new Core_Model_Content();
		    		$content_temp = new Core_Model_ContentTemp();
		    				    				    		
		    		$temp_contents = $content_temp->getTempContentsId();
		    		if(count($temp_contents)>0)
		    			foreach ($temp_contents as $k => $arr)
		    			{
							$temp[] = $arr['content_id'];	    			
			    		}
		    		
		    		$contents = $content->getContentsBySection($section->id, $front_ids->website_id, null, null, $temp);
		    		//contents per section
		    		if(count($contents)>0)
		    		{	    			
		    			foreach ($contents as $key => $v)
		    			{
		    				$content_arr[] = array('section_id'=>$section->id,'content_id'=>$v->id,'title'=>$v->title,'section'=>$v->section_name,'internal_name'=>$v->internal_name, 'columns'=>$v->column_number);
		    			}	    			
		    		}
                                //Products List
                                $product_arr = array();
                                $product = new Products_Model_Products();
                                $products = $product->rendercontents($section->id);
                                
                                //Product Catalog
                                $product_id = $this->_getParam('product_id');
                                if($product_id)
                                    $section_filename_tpl = "product.phtml";
                                
                                $catalog = new Products_Model_ProductCatalog();
                                $product_catalog = $catalog->find('product_catalog', array('product_id'=>$product_id));

                                $product_obj = new Products_Model_Products();
                                $product = $product_obj->find('product', array('id'=>$product_id));
                                
                                if(count($product)>0)
		    		{
                                    $product_arr[] = array('product'=>$product,'product_catalog'=>$product_catalog);
                                }
		    		//articles
                                $content_field = new Core_Model_ContentField();
                                
		    		$article_arr = array();
		    		$articles_list = $section_obj->find('wc_section', array('section_parent_id'=>$section->id,'article'=>'yes'), array('order_number'=>'ASC'));
		    		if(count($articles_list)>0)
		    		{
		    			foreach ($articles_list as &$art)
		    			{
		    				$art->title = GlobalFunctions::truncate($art->title, 100, false);
		    				$art->synopsis = GlobalFunctions::truncate($art->synopsis, 240, false);
		    			    					
		    				//search for an image	    				
		    				$pictures_list = $content->getContentsBySection($art->id, $front_ids->website_id, null, 2);
                                                
                                                
		    				if(count($pictures_list)>0)
		    				{
                                                        $data_content_field = $content_field->find ( 'wc_content_field', array ('content_id' => $pictures_list[0]->id) );
		    					$art->image = $data_content_field[4]->value;
		    				}
		    				else
		    				{
		    					$art->image = null;
		    				}
		    				$article_arr[] = array('section_id'=>$section->id, 'article_id'=>$art->id, 'title'=>$art->title, 'synopsis'=>$art->synopsis, 'image'=>$art->image, 'feature'=>$art->feature, 'publish_date'=>$art->publish_date, 'expire_date'=>$art->expire_date);
		    			}
		    		}
                                
                                $section_images = NULL;
                                $section_image = new Core_Model_Content();
                                $images = $section_image->find('wc_section_image', array('section_id' =>$section->id));

                                if (count($images) > 0) {
                                    foreach($images as $image){
                                        if (isset($image->file_name)) {
                                            $section_images[] = $image->file_name;
                                        }
                                    }
                                }

                                $section_floors = array();
                                if($section->section_template_id == 8){
                                    $subsections = $section_obj->find('wc_section', array('section_parent_id'=>$section->id, 'website_id'=>$front_ids->website_id),array('order_number'=>'ASC'));  
                                    foreach($subsections as $key => $subsection){
                                        $section_tpl = $section_template->find('wc_section_template',array('id'=>$subsection->section_template_id));
                                        /* CONTENTS */
                                        $subsection_contents = $content->getContentsBySection($subsection->id, $front_ids->website_id, null, null, array());
                                        //contents per section
                                        $subsection_content_arr = array();
                                        if(count($subsection_contents)>0)
                                        {	    			
                                                foreach ($subsection_contents as $key => $v)
                                                {
                                                        $subsection_content_arr[] = array('section_id'=>$subsection->id,'content_id'=>$v->id,'title'=>$v->title,'section'=>$v->section_name,'internal_name'=>$v->internal_name, 'columns'=>$v->column_number);
                                                }	    			
                                        }
                                        $subsection_images = NULL;
                                        
                                        $subsection_image = $section_image->find('wc_section_image', array('section_id' =>$subsection->id));

                                        if (count($subsection_image) > 0) {
                                            foreach($subsection_image as $image){
                                                if (isset($image->file_name)) {
                                                    $subsection_images[] = $image->file_name;
                                                }
                                            }
                                        }
                                        $subsection_article_arr = array();
                                        $subsection_articles_list = $section_obj->find('wc_section', array('section_parent_id'=>$subsection->id,'article'=>'yes'), array('order_number'=>'ASC'));
                                        if(count($subsection_articles_list)>0)
                                        {
                                                foreach ($subsection_articles_list as &$art)
                                                {
                                                        $art->title = GlobalFunctions::truncate($art->title, 100, false);
                                                        $art->synopsis = GlobalFunctions::truncate($art->synopsis, 240, false);

                                                        //search for an image	    				
                                                        $pictures_list = $content->getContentsBySection($art->id, $front_ids->website_id, null, 2);


                                                        if(count($pictures_list)>0)
                                                        {
                                                                $data_content_field = $content_field->find ( 'wc_content_field', array ('content_id' => $pictures_list[0]->id) );
                                                                $art->image = $data_content_field[4]->value;
                                                        }
                                                        else
                                                        {
                                                                $art->image = null;
                                                        }
                                                        $subsection_article_arr[] = array('section_id'=>$subsection->id, 'article_id'=>$art->id, 'title'=>$art->title, 'synopsis'=>$art->synopsis, 'image'=>$art->image, 'feature'=>$art->feature, 'publish_date'=>$art->publish_date, 'expire_date'=>$art->expire_date);
                                                }
                                                
                                        }
                                        $section_floors[] = array(
                                            'section_filename_tpl'=>$section_tpl[0]->file_name,
                                            'col_number'=>$section_tpl[0]->column_number,
                                            'section_title'=>$subsection->title,
                                            'section_subtitle'=>$subsection->subtitle,
                                            'title'=>$section->title,
                                            'subtitle'=>$section->subtitle,
                                            'synopsis'=>$subsection->synopsis,
                                            'publish_date'=>$subsection->publish_date,
                                            'expire_date'=>$subsection->expire_date,
                                            'section_id'=>$subsection->id,
                                            'contents'=>$subsection_content_arr,
                                            'section_images'=>$subsection_images,
                                            'articles'=>$subsection_article_arr,
                                        );
                                    }
                                }
		    		$crums = GlobalFunctions::getCrums($section->id);
		    		//contents list
		    		$contents_list[$section->id]['order_number'] = $section->order_number;
		    		$contents_list[$section->id]['filename'] = $section_filename_tpl;
		    		$contents_list[$section->id]['area'] = $section_area_name;
		    		$contents_list[$section->id]['area_width'] = $area_sec_width;
		    		$contents_list[$section->id]['column_number'] = $column_number;
		    		$contents_list[$section->id]['section_title'] = $section->title;
		    		$contents_list[$section->id]['section_subtitle'] = $section->subtitle;
		    		
		    		if($section->type == 'private'){
		    			$contents_list[$section->id]['private'] = true;
		    		}else
		    			$contents_list[$section->id]['private'] = false;	    		
		    		
		    		$contents_list[$section->id]['content'] = $content_arr;
		    		$contents_list[$section->id]['article'] = $article_arr;
                                $contents_list[$section->id]['products'] = $products;
                                $contents_list[$section->id]['product_catalog'] = $product_arr;
                                $contents_list[$section->id]['crums'] = $crums;
                                $contents_list[$section->id]['section_images'] = $section_images;
                                $contents_list[$section->id]['section_floors'] = $section_floors;
		    		
		    		/******
		    		 * External module contents within section selected
		    		 */	    
		    		if(count($modules)>0)
		    		{
		    			foreach ($modules as $mod)
		    			{
		    				//model
		    				$funct_name = $mod['name'].'_Model_'.$mod['name'];
		    				$module_obj = new $funct_name();
		    				$contents_mod = $module_obj->rendercontents($section->id,'no');
		    				
		    				$module_contents[$mod['id'].'_'.$section->id]['module_name'] = $mod['name'];
		    				$module_contents[$mod['id'].'_'.$section->id]['section'] = $section->id;
		    				$module_contents[$mod['id'].'_'.$section->id]['partial'] = $mod['partial'];
		    				$module_contents[$mod['id'].'_'.$section->id]['contents'] = $contents_mod;
		    				if($section->type == 'private')
		    					$module_contents[$mod['id'].'_'.$section->id]['private'] = 1;
		    				else
		    					$module_contents[$mod['id'].'_'.$section->id]['private'] = 0;
		    			}
		    		}	    		
		    	}
	    	}
	
	    	//sections different from selected
	    	$section_arr = array();
	    	
	    	if($section_id){
                    
                $table = new Zend_Db_Table('wc_section');
		$select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
		$select->setIntegrityCheck(false)
		->join('wc_section_module_area', 'wc_section_module_area.section_id = wc_section.id', array('id'=>'wc_section.id'));	
                $select->where("wc_section.website_id = ?", $front_ids->website_id);
                $select->where("wc_section.id != ?", $section_id);
                $select->where("wc_section.article = ?", 'no');
                $select->where("wc_section.approved = ?", 'yes');
                $select->where("wc_section.publication_status = ?", 'published');
		$select->where("wc_section_module_area.area_id != ?", $area_content_variable[0]->id);
		$section_arr = $table->fetchAll($select);
                }   
	    		//$section_arr = $section_obj->personalized_find('wc_section',array(array('website_id','=',$front_ids->website_id), array('id','!=',$section_id), array('article','=','no'), array('approved','=','yes'), array('publication_status','=','published')),'order_number');
	    	
	    	if(count($section_arr)>0)
	    	{
		    	foreach ($section_arr as $k => $section)
		    	{
		    		//section template
		    		$section_template = new Core_Model_SectionTemplate();
		    		$section_tpl = $section_template->find('wc_section_template',array('id'=>$section->section_template_id));
		    		$section_filename_tpl = $section_tpl[0]->file_name;
		    		$column_number = $section_tpl[0]->column_number;
		    		
		    		$content_arr = array();
		    		 
		    		$section_other_areas = true;
		    		 
		    		//section area
		    		$section_module_area = new Core_Model_SectionModuleArea();
		    		$area = $section_module_area->find('wc_section_module_area',array('section_id'=>$section->id));
	   			    			    		
		    		if(count($area)>0)
		    		{
		    			$area_aux = new Core_Model_Area();
		    			$area_data = $area_aux->find('wc_area',array('id'=>$area[0]->area_id));
	
		    			if(count($area_data)>0)
		    			{
		    				if($area_data[0]->area_number == $section_area)
		    					$section_other_areas = false;
		    				else{
		    					$area_sec = $area_data[0]->area_number;
		    					$area_sec_name = $area_data[0]->name;
		    					$area_sec_width = $area_data[0]->width;
		    				}
		    			}	    			
		    		}
		    		 
		    		if($section_other_areas)
		    		{
		    			//content
		    			$content_arr = array();
		    			$content = new Core_Model_Content();
		    			
		    			$content = new Core_Model_Content();
		    			$content_temp = new Core_Model_ContentTemp();
		    				    				    		
			    		$temp_contents = $content_temp->getTempContentsId();
			    		if(count($temp_contents)>0)
			    			foreach ($temp_contents as $k => $arr)
			    			{
								$temp[] = $arr['content_id'];	    			
				    		}
			    		
			    		$contents = $content->getContentsBySection($section->id, $front_ids->website_id, null, null, $temp);   				    				    			
		    			 
		    			if(count($contents)>0)
		    			{
		    				foreach ($contents as $key => $v)
		    				{
		    					$content_arr[] = array('section_id'=>$section->id,'content_id'=>$v->id,'title'=>$v->title,'section'=>$v->section_name,'internal_name'=>$v->internal_name,'columns'=>$v->column_number);
		    				}	    				
		    			}
	
		    			//articles
		    			$article_arr = array();
		    			$articles_list = $section_obj->find('wc_section', array('section_parent_id'=>$section->id,'article'=>'yes'), array('order_number'=>'ASC'));
		    			if(count($articles_list)>0)
		    			{
		    				foreach ($articles_list as &$art)
		    				{
		    					$art->title = GlobalFunctions::truncate($art->title, 100, false);
		    					$art->synopsis = GlobalFunctions::truncate($art->synopsis, 240, false);
		    					 
		    					//search for an image
		    					$pictures_list = $content->getContentsBySection($art->id, $front_ids->website_id, null, 2);
		    					if(count($pictures_list)>0)
		    					{
		    						$art->image = $pictures_list[0];
		    					}
		    					else
		    					{
		    						$art->image = null;
		    					}
		    					$article_arr[] = array('section_id'=>$section->id, 'article_id'=>$art->id, 'title'=>$art->title, 'synopsis'=>$art->synopsis, 'image'=>$art->image, 'feature'=>$art->feature, 'publish_date'=>$art->publish_date, 'expire_date'=>$art->expire_date);
		    				}
		    			}
	
		    			$contents_list[$section->id]['order_number'] = $section->order_number;
		    			$contents_list[$section->id]['filename'] = $section_filename_tpl;
		    			$contents_list[$section->id]['area'] = $area_sec_name;
		    			$contents_list[$section->id]['area_width'] = $area_sec_width;
		    			$contents_list[$section->id]['column_number'] = $column_number;
		    			$contents_list[$section->id]['section_title'] = $section->title;
		    			$contents_list[$section->id]['section_subtitle'] = $section->subtitle;
	
		    			if($section->type == 'private'){
		    				$contents_list[$section->id]['private'] = 1;
		    			}else
		    				$contents_list[$section->id]['private'] = 0;
		    			
		    			$contents_list[$section->id]['content'] = $content_arr;
		    			$contents_list[$section->id]['article'] = $article_arr;
		    			$contents_list[$section->id]['products'] = $products;
                                        $contents_list[$section->id]['crums'] = $crums;
                                        $contents_list[$section->id]['section_images'] = $section_images;
                                        $contents_list[$section->id]['section_floors'] = $section_floors;
		    			/******
		    			 * External module contents within section selected
		    			*/
		    			if(count($modules)>0)
		    			{
		    				foreach ($modules as $mod)
		    				{
		    					//model
			    				$funct_name = $mod['name'].'_Model_'.$mod['name'];
			    				$module_obj = new $funct_name();
			    				$contents_mod = $module_obj->rendercontents($section->id,'no');
			    				
			    				$module_contents[$mod['id'].'_'.$section->id]['module_name'] = $mod['name'];
			    				$module_contents[$mod['id'].'_'.$section->id]['section'] = $section->id;
			    				$module_contents[$mod['id'].'_'.$section->id]['partial'] = $mod['partial'];
			    				$module_contents[$mod['id'].'_'.$section->id]['contents'] = $contents_mod;
			    				if($section->type == 'private')		    				
			    					$module_contents[$mod['id'].'_'.$section->id]['private'] = true;
			    				else
			    					$module_contents[$mod['id'].'_'.$section->id]['private'] = false;
		    				}
		    			}
		    			
		    		}
		    	}
	    	}
			/******
			 * Ordering section contents array according section order
			 */
	    	$sort_col = array();
	    	foreach ($contents_list as $key=> $row)
	    	{
	    		$sort_col[$key] = $row['order_number'];
	    	}    	
	    	array_multisort($sort_col, SORT_ASC, $contents_list);    		
    	}    		
    	
    	$this->view->section_contents = $contents_list;
    	
    	//band to know if it is on storage
    	$this->view->storage = 'no';
    	
    	//enable show old publications
    	if(count($website_obj)>0)
    		$show_old = $website_obj[0]->section_storage;
    	else
    		$show_old = 'no';
    	$this->view->showold = $show_old; 
    	//external modules contents
    	$this->view->module_contents = $module_contents;
    }
    
    /**
     * Loads sitemap
     */
    public function sitemapAction()
    {
    	$lang = Zend_Registry::get('Zend_Translate');
    	$front_ids = New Zend_Session_Namespace('ids');
    	 
    	/******************
    	 * sections content
    	*/
    	$website = new Core_Model_Website();
    	$website_obj = $website->find('wc_website', array('id'=>$front_ids->website_id));
    	 
    	$area_tpl = array();
    	//find areas according website template config
    	if(count($website_obj)>0)
    	{
    		$website_template = new Core_Model_WebsiteTemplate();
    		$web_tpl = $website_template->find('wc_website_template',array('id'=>$website_obj[0]->template_id));
    	
    		$template_areas = new Core_Model_Area();
    		$area_tpl = $template_areas->find('wc_area',array('template_id'=>$web_tpl[0]->id));
    		
    		$area_content_variable = $template_areas->find('wc_area',array('template_id'=>$web_tpl[0]->id,'type'=>'variable'));
    	}
    	
    	$this->view->areas = $area_tpl;    	 
    	    	 
    	//will contain section object data as array
    	$section_arr = array();
    	//section model
    	$section_obj = new Core_Model_Section();
    	
    	//contents per section
    	$contents_list = array();
    	//section area for variable content
    	$area_sec = 0;
    	    	    	
    	//sections that are not display on variable area
    	$section_arr = $section_obj->personalized_find('wc_section',array(array('website_id','=',$front_ids->website_id), array('article','=','no'), array('approved','=','yes'), array('publication_status','=','published')),'order_number');
    	 
    	if(count($section_arr)>0)
    	{
    		foreach ($section_arr as $k => $section)
    		{
    			//section template
    			$section_template = new Core_Model_SectionTemplate();
    			$section_tpl = $section_template->find('wc_section_template',array('id'=>$section->section_template_id));
    			$section_filename_tpl = $section_tpl[0]->file_name;
    			$column_number = $section_tpl[0]->column_number;
    			 
    			$content_arr = array();
    			
    			//section area
    			$section_module_area = new Core_Model_SectionModuleArea();
    			$area = $section_module_area->find('wc_section_module_area',array('section_id'=>$section->id));
    			if(count($area)>0)
    			{
    				$area_aux = new Core_Model_Area();
    				$area_data = $area_aux->find('wc_area',array('id'=>$area[0]->area_id));
    				if(count($area_data)>0)
    				{
    					$area_sec = $area_data[0]->area_number;
    					$area_sec_name = $area_data[0]->name;
    					$area_sec_width = $area_data[0]->width;
    				}
    			}
    			
    			//contents per section
    			if($area_sec != $area_content_variable[0]->area_number)
    			{
    				//content
    				$content = new Core_Model_Content();
    				$contents = $content->getContentsBySection($section->id, $front_ids->website_id);
    				
    				foreach ($contents as $key => $v)
    					$content_arr[] = array('section_id'=>$section->id,'content_id'=>$v->id,'title'=>$v->title,'section'=>$v->section_name,'internal_name'=>$v->internal_name, 'columns'=>$v->column_number);
    				$contents_list[$section->id]['order_number'] = $section->order_number;
    				$contents_list[$section->id]['filename'] = $section_filename_tpl;
    				$contents_list[$section->id]['area'] = $area_sec_name;
    				$contents_list[$section->id]['area_width'] = $area_sec_width;
    				$contents_list[$section->id]['column_number'] = $column_number;
    				$contents_list[$section->id]['section_title'] = $section->title;
    				$contents_list[$section->id]['section_subtitle'] = $section->subtitle;  				
    				$contents_list[$section->id]['content'] = $content_arr;
    			}    			
    		}
    	}
    	  	    		    	    	            	
    	/***************
    	 * sitemap tree
    	 */
    	$website_data = get_object_vars($website_obj[0]); //make an array of the object data
    	
    	$section = new Core_Model_Section();
    	//find existent sections on db according website
    	$sections_list = $section->personalized_find('wc_section', array(
    			array('website_id','=',$front_ids->website_id),
    			array('publication_status','=','published'),
    			array('type','=','public'), 
    			array('article','=','no')
    	));
    	
    	$sections_arr = array();
    	 
    	//sections list array
    	if($sections_list)
    	{
    		foreach ($sections_list as $sec)
    		{
    			//section module area
    			$section_module_area = new Core_Model_SectionModuleArea();
    			$area = $section_module_area->find('wc_section_module_area',array('section_id'=>$sec->id));
    			$area_type = "";
    			if(count($area)>0)
    			{
    				$area_sec = $area[0]->area_id;
    				$template_areas = new Core_Model_Area();
    				$area_tpl = $template_areas->find('wc_area',array('id'=>$area_sec));
    				$area_type = $area_tpl[0]->type;    				
    			}
    			
    			$sections_arr[] = array('id'=>$sec->id,
    					'section_parent_id'=>$sec->section_parent_id,
    					'title'=>$sec->title,
    					'area_type'=>$area_type
    			);
    		}
    	}
    	
    	//string with sections tree html
    	$html_list = '';
    	$content_arr = array();
    	 
    	if(count($sections_arr)>0)
    	{
    		//sections tree - parents and children as array
    		$sections_tree = GlobalFunctions::buildSectionTree($sections_arr);
    		//sections tree as list
    		$list = GlobalFunctions::buildHtmlSitemapTree($sections_tree,false,$website_data['sitemap_level']);
    		$html_list = '<ul class="sections_tree_internal">'.$list.'</ul>';
    	}

    	if($html_list)
    	{
    		$content_arr[] = array('section_id'=>NULL,'content_id'=>NULL,'title'=>$lang->translate('Sitemap'), 'content'=>$html_list, 'columns'=>'1');
    		$contents_list[0]['order_number'] = '';
    		$contents_list[0]['filename'] = 'sectemplate.phtml';
    		$contents_list[0]['area'] = 'wica_area_content';
    		$contents_list[0]['area_width'] = 'col-md-1';
    		$contents_list[0]['column_number'] = 1;
    		$contents_list[0]['section_title'] = '';
    		$contents_list[0]['section_subtitle'] = '';
    		$contents_list[0]['content'] = $content_arr;
  		    		
    		$this->view->title_browser = $lang->translate('Sitemap');
    	}
		    			
    	$this->view->section_contents = $contents_list;
    }
    /**
     * Function to send an email from form on front
     */
    public function sendformemailAction(){
    	
    	$lang = Zend_Registry::get('Zend_Translate');
        $appUser = new Zend_Session_Namespace('user');
    	
		$this->_helper->layout->disableLayout ();
		// disable autorendering for this action
		$this->_helper->viewRenderer->setNoRender();
    	
		$formData = $this->_request->getPost ();
		$body='';
			
		//Dictionary Validation
		$dictionary_coincidence = false;
		$website = new Core_Model_Website();
		
		$website_list = $website->find('wc_website',array('id'=>$formData['website_id'],'dictionary'=>'yes'));
		//Check if dictionary option is enabled in current website
		if(count($website_list)>0){
			//Check dictionaries list by current website
			$dictionary = new Core_Model_Dictionary();
			$dictionary_list = $dictionary->find('wc_dictionary',array('website_id'=>$formData['website_id'],'status'=>'active'));
			if($dictionary_list){
				$word = new Core_Model_Word();
				//Get words list by dictionaries list by current website
				foreach ($dictionary_list as $dl){
					$word_item = $word->find('wc_word',array('dictionary_id'=>$dl->id));
					if($word_item){
						foreach ($word_item as $wi){
							$words[] = $wi->expression;
						}
							
					}
				}
				
				//Copy $formData for validate
				$formText = $formData;
				//Delete website_id element of array
				unset($formText['website_id']);
				//Array of data fields to string
				$formText = implode(' ',$formText);
				//String to lowercase
				$formText = mb_strtolower($formText,'UTF-8'); 
				
				//Checks if dictionary word is into string of data fields.
				foreach($words as $word){
					if(substr_count($formText,$word)>0){ //Found dictionary word in string data fields.
						$dictionary_coincidence = true;
						break;
					}
				}
				
			}
		}

		//End Dictionary Validation
		if($dictionary_coincidence==false){
			foreach($formData as $key=>$data){
                            
				if($key!='captcha' && $key !='website_id' && $key != 'g-recaptcha-response')
					$body.='<p><b>'.str_replace ( '_', ' ', $key ).':</b> '.$data.'</p>';
			}
                        
			if ($this->getRequest ()->isPost ()) {
				if(isset($formData['g-recaptcha-response'])){
                                        $captcha=$formData['g-recaptcha-response'];
                                        if(!$captcha){
                                            echo json_encode ('error_captcha');
                                            exit;
                                        }
					$secretKey = "6LfGUiUTAAAAACJWMwLaqIJnWsY8MzMumwxmVsJb";
                                        $ip = $_SERVER['REMOTE_ADDR'];
                                        $response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secretKey."&response=".$captcha."&remoteip=".$ip);
                                        $responseKeys = json_decode($response,true);
                                        if(intval($responseKeys["success"]) !== 1) {
                                            echo json_encode ('error_captcha');
                                            exit;
                                        }
					
						 
						
                                                $website = new Core_Model_Website();
                                                $website_data = $website->find('wc_website',array('id'=>$formData['website_id']));

						//Get id field for email/s directions to send email
						$field = new Core_Model_Field();
						$field_email = $field -> find('wc_field',array('content_type_id'=>'4','name'=>'Email'));
						if(count($field_email)>0){
							$field_email_id = $field_email[0]->id;
						}
                                                
                                                //Get Attached files
                                                $newFilename = $appUser->upload_file;
						
						//Get email/s directions for current content_id
						$content = new Core_Model_ContentField();
						$content_email = $content->find('wc_content_field',array('field_id'=>$field_email_id,'content_id'=>$formData['form_id']));
						$subject = 'Formulario de sitio: '. utf8_decode($website_data[0]->name);
                                                
						$sended = GlobalFunctions::sendMail($formData['website_id'], $content_email[0]->value, $subject, $newFilename, $body);
                                                if($sended == 'send'){
                                                $this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success send')));
                                                    echo json_encode ('success_captcha');
                                                }else{
                                                    $this->_helper->flashMessenger->addMessage(array('danger'=>$lang->translate('Errors sending')));
                                                    echo json_encode ('error_sending');						
                                                }
					
					 
					 
				}else{
                                    
					//get smpt credential from website information
					$website = new Core_Model_Website();
					$website_data = $website->find('wc_website',array('id'=>$formData['website_id']));
					
					//Get id field for email/s directions to send email
					$field = new Core_Model_Field();
					$field_email = $field -> find('wc_field',array('content_type_id'=>'4','name'=>'Email'));
					if(count($field_email)>0){
						$field_email_id = $field_email[0]->id;
					}
					
					//Get email/s directions for current content_id
					$content = new Core_Model_ContentField();
					$content_email = $content->find('wc_content_field',array('field_id'=>$field_email_id,'content_id'=>$formData['form_id']));
                                        
                                        //Get Attached files
                                        $newFilename = $appUser->upload_file;
					
					$subject = 'Formulario de sitio: '. utf8_decode($website_data[0]->name);
                                                
                                        $sended = GlobalFunctions::sendMail($formData['website_id'], $content_email[0]->value, $subject, $newFilename, $body);
                                                if($sended == 'send'){
                                                $this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success send')));
                                                    echo json_encode ('success_captcha');
                                                }else{
                                                    $this->_helper->flashMessenger->addMessage(array('danger'=>$lang->translate('Errors sending')));
                                                    echo json_encode ('error_sending');						
                                                }
                                        
				}
			
			}
				
		}else{
			//Found coincidences with dictionary
			$this->_helper->flashMessenger->addMessage(array('danger'=>$lang->translate('Your language is inappropriate to send this form')));
			echo json_encode ('error_dictionary');
		}
    }
    
    /**
     * shows the view in case there are no websites created
     */
    public function nowebsiteAction()
    {
    	$this->view->no_websites = 'no';
    	$this->render('nowebsite');
    }
    
    /**
     * set error flash message on error sending mail
     */
    public function seterrormessageAction(){
    	$lang = Zend_Registry::get('Zend_Translate');
    	 
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action
    	$this->_helper->viewRenderer->setNoRender();
    	
    	//error message
    	if($this->_helper->flashMessenger->addMessage(array('danger'=>$lang->translate('Errors sending'))))
	    	echo json_encode ('flash_error');
    }
    
    /**
     * login external users
     */
    public function externalloginAction(){
    	
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action
    	$this->_helper->viewRenderer->setNoRender();
    	
    	$formData = $this->_request->getPost ();
    	$username = $formData['username'];
    	$password = $formData['password'];
    	
		$table = new Zend_Db_Table('wc_public_user');	
    	$select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
    	$select->setIntegrityCheck(false);
    	$select->where("LOWER(username) = LOWER(?)",GlobalFunctions::value_cleaner($username));
    	$select->where("password = MD5(?)",GlobalFunctions::value_cleaner($password));
    	$select->where("status = ?",'active');    	

    	$result = $table->fetchAll($select);

    	if($result->count()>0){
			echo json_encode($result[0]->id);
    		$_SESSION['external_user'] = $result[0]->id;
                $_SESSION['external_user_name'] = $result[0]->name;
    	}else{
    		
    		$select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
    		$select->setIntegrityCheck(false);
    		$select->where("LOWER(username) = LOWER(?)",GlobalFunctions::value_cleaner($username));
    		$select->where("password = MD5(?)",GlobalFunctions::value_cleaner($password));
    		$result = $table->fetchAll($select);
    		if($result->count()>0){
    			echo json_encode('inactive');
    		}else{
    			echo json_encode('error');
    		}
    	}
    }
    
    /**
     * Logout external users
     */
    public function externallogoutAction(){
    	 
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action
    	$this->_helper->viewRenderer->setNoRender();
    	
    	if(isset($_SESSION['external_user'])){
    		unset($_SESSION['external_user']);
                unset($_SESSION['external_user_name']);
    	}
    	echo json_encode('logout');
    	
    	
    }
    /**
     * Public user registration
     */
    public function registerAction(){
    	
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action
    	$this->_helper->viewRenderer->setNoRender();
    	
    	//translate library
    	$lang = Zend_Registry::get ( 'Zend_Translate' );
    	    	
    	$formData = $this->_request->getPost ();
    	if($this->_request->getPost ()){
    		
    		$public_user = new Core_Model_PublicUser();
    		$public_user_obj = $public_user->getNewRow ( 'wc_public_user' );
    		$public_user_obj->name = $formData['public_user_name_'.$formData['area']];
    		$public_user_obj->lastname = $formData['public_user_last_name_'.$formData['area']];
    		$public_user_obj->identification = $formData['public_user_identification_'.$formData['area']];
    		$public_user_obj->email = $formData['public_user_email_'.$formData['area']];
    		$public_user_obj->phone = $formData['public_user_phone_'.$formData['area']];
    		$public_user_obj->username = $formData['public_user_username_'.$formData['area']];
    		$public_user_obj->password = MD5($formData['public_user_password_'.$formData['area']]);
    		$public_user_obj->old_password = MD5($formData['public_user_password_'.$formData['area']]);
    		$public_user_obj->status = 'inactive';
    		$public_user_obj->activation_key = GlobalFunctions::generateActivationKey(2, 29, true, 12);
    		
    		if($public_user->save('wc_public_user', $public_user_obj)){
    			
    			//get smpt credential from website information
    			$website = new Core_Model_Website();
    			$website_data = $website->find('wc_website',array('id'=>$formData['website_id']));
    			
                        $body = '<br><br> '.$lang->translate("To complete this registry is necessary to do").'
	    						<a href="'.$website_data[0]->website_url.'/default/index/index?email='.urlencode($public_user_obj->email).'&key='.urlencode($public_user_obj->activation_key).'" style="cursor: pointer;"> '.$lang->translate("click here").' </a>
	    						'.$lang->translate("to activate your account, or copy the next link on your browser").': <br> <br>
	    						<a href="'.$website_data[0]->website_url.'/default/index/index?email='.urlencode($public_user_obj->email).'&key='.urlencode($public_user_obj->activation_key).'">'.$website_data[0]->website_url.'/index?email='.urlencode($public_user_obj->email).'&key='.urlencode($public_user_obj->activation_key).'</a>';
                        
                        $sended = GlobalFunctions::sendMail($formData['website_id'], $formData['public_user_email_'.$formData['area']], 'Registration', null, $body);
                        if($sended == 'send'){
                            $this->_helper->flashMessenger->addMessage ( array (
                                'success' => $lang->translate ( 'Your registration has been completed, please check your email' )
                            ) );
                            echo json_encode(TRUE); 
                        }else{
                            $this->_helper->flashMessenger->addMessage ( array (
                                'danger' => $lang->translate ( 'The activation email has not been sent')
                            ) );
                            echo json_encode(TRUE);					
                        }  			

    		}else{
    			$this->_helper->flashMessenger->addMessage ( array (
    					'danger' => $lang->translate ( 'Your registration has not been completed' )
    			) );    			
    			echo json_encode(TRUE);
    		}
    		
    	}
    	
    }
    
    /**
     * Check if email already has been registered
     */
    public function checkregistermailAction(){
    	
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action
    	$this->_helper->viewRenderer->setNoRender();
    	 
    	//translate library
    	$lang = Zend_Registry::get ( 'Zend_Translate' );
    	$email = $this->_request->getPost ('public_user_email_'.$this->_request->getPost ('area'));
        $id = $this->_request->getPost ('public_user_id_'.$this->_request->getPost ('area'));
        if($id){
            $public_user = new Core_Model_PublicUser();
            $public_user_data = $public_user->find('wc_public_user',array('email'=>$email));
            if($public_user_data){
                    echo json_encode(FALSE);
            }else{
                    echo json_encode(TRUE);
            }
        } 
    	if($this->_request->getPost ()){
            $public_user = new Core_Model_PublicUser();
            $public_user_data = $public_user->find('wc_public_user',array('email'=>$email));
            if($public_user_data){
                    echo json_encode(FALSE);
            }else{
                    echo json_encode(TRUE);
            }
    	}    	
    }
    /**
     * Check if email already has been registered
     */
    public function profileAction(){
    	
    	$this->_helper->layout->disableLayout ();
        if(isset($_SESSION['external_user'])){
            $this->_helper->layout->disableLayout ();
            $public_user = new Core_Model_PublicUser();
            $public_user_data = $public_user->find('wc_public_user',array('id'=>$_SESSION['external_user']));   
            $this->view->user_data = $public_user_data[0];
            print_r($public_user_data[0]);
        } else {
            $this->_helper->viewRenderer->setNoRender();
            echo $this->view->previewHelper(null,null,$this->view->area);
        }    	     	
    }
    
    /**
     * Check if email already has been registered
     */
    public function profileOldAction(){
    	
    	$this->_helper->layout->disableLayout ();
  	     	
    }
    
    /**
     * Check if username already has been registered
     */
    public function checkusernameAction(){
    	 
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action
    	$this->_helper->viewRenderer->setNoRender();
    
    	//translate library
    	$lang = Zend_Registry::get ( 'Zend_Translate' );
    	$username = $this->_request->getPost ('public_user_username_'.$this->_request->getPost ('area'));
    
    	if($this->_request->getPost ()){
    		$public_user = new Core_Model_PublicUser();
    		$public_user_data = $public_user->find('wc_public_user',array('username'=>$username));
    		if($public_user_data){
    			echo json_encode(FALSE);
    		}else{
    			echo json_encode(TRUE);
    		}
    	}
    }    
    
    /**
     * Send email with new password
     */
    public function forgotpassAction(){
    	 
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action
    	$this->_helper->viewRenderer->setNoRender();
    	 
    	//translate library
    	$lang = Zend_Registry::get ( 'Zend_Translate' );
    
    	$email = $this->_request->getPost ('public_for_user_email');
    	 
    	if($this->_request->getPost ()){
    
    		//Generate new password
    		$new_password = GlobalFunctions::generateActivationKey(2, 29, true, 12);
    		
    		$public_user = new Core_Model_PublicUser();
    		$public_user_data = $public_user->find('wc_public_user', array('email'=>$email));

    		$public_user_data[0]->password = MD5($new_password);
    		$public_user_data[0]->activation_key = GlobalFunctions::generateActivationKey(2, 29, true, 12);
    
    		if($public_user->save('wc_public_user', $public_user_data[0])){
    			 
    			//get smpt credential from website information
    			$website = new Core_Model_Website();
    			$website_data = $website->find('wc_website',array('id'=>$this->_request->getPost ('website_id')));
    			
                        $body = '<br><br>'.$lang->translate("Your password has been reset successfully").'<br/><br/>
	    						'.$lang->translate("User").': '.$public_user_data[0]->username.'<br/>
	    						'.$lang->translate("Password").': '.$new_password.'<br/><br/>
	    						'.$lang->translate("If you don't want to reset your password, please").' <br> <br>
	    						<a href="'.$website_data[0]->website_url.'/default/index/index?email='.urlencode($email).'&rollback=yes&key='.urlencode($public_user_data[0]->activation_key).'" style="cursor: pointer;"> '.$lang->translate("click here").' </a>';
                        
                        $sended = GlobalFunctions::sendMail($this->_request->getPost ('website_id'), $email, 'Restore Password', null, $body);
                        if($sended == 'send'){
                            $this->_helper->flashMessenger->addMessage ( array (
                                'success' => $lang->translate ( 'You will receive an email with your new password' )
                            ) );
                            echo json_encode(TRUE);
                        }else{
                            $this->_helper->flashMessenger->addMessage ( array (
                                'danger' => $lang->translate ( 'The email with your new password has not been sent')
                            ) );
                            echo json_encode(TRUE);					
                        }
    			

    		}else{
    			$this->_helper->flashMessenger->addMessage ( array (
    					'danger' => $lang->translate ( 'Some errors occurred during the process' )
    			) );    			
    			echo json_encode(TRUE);
    		}
    
    	}
    	 
    } 
    
    /**
     * Change password
     */
    public function changepswAction(){
    	 
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action
    	$this->_helper->viewRenderer->setNoRender();
    
    	//translate library
    	$lang = Zend_Registry::get ( 'Zend_Translate' );
    	$password = $this->_request->getPost ('password');
    
    	if($this->_request->getPost ()){
    		$public_user = new Core_Model_PublicUser();
    		$public_user_data = $public_user->find('wc_public_user',array('id'=>$_SESSION['external_user']));
    		
    		$public_user_data[0]->password = MD5($password);
    		$public_user_data[0]->old_password = MD5($password);
    		
    		if($public_user->save('wc_public_user', $public_user_data[0])){    		
				$this->_helper->flashMessenger->addMessage ( array (
						'success' => $lang->translate ( 'Your password has been changed' )
				) );
				echo json_encode(TRUE);
	    	}else{
				$this->_helper->flashMessenger->addMessage ( array (
						'danger' => $lang->translate ( 'Your password has not been changed' )
				) );
				echo json_encode(FALSE);
	    	}
    	}
    }    

    public function createdatesessionAction(){
    	
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action
    	$this->_helper->viewRenderer->setNoRender();
    	
    	//translate library
    	$lang = Zend_Registry::get ( 'Zend_Translate' );
    	$_SESSION['show_publish_date'] = $this->_request->getPost ('show_publish_date');  

    	if($_SESSION['show_publish_date']){
    		echo json_encode(TRUE);
    	}
    	else
    		echo json_encode(FALSE);
    	 	
    }
    
    public function uploadfileAction() 
	{			
		$this->_helper->layout->disableLayout ();
		// disable autorendering for this action only:
		$this->_helper->viewRenderer->setNoRender();
		$appUser = new Zend_Session_Namespace('user');				
		$formData  = $this->_request->getPost();
		
		$directory = $formData['directory'];			
		$maxSize = $formData['maxSize'];	

		$directory = APPLICATION_PATH. '/../'. $directory;
		if ($_FILES["section_photos"]["size"] <= $maxSize) {//DETERMINING IF THE SIZE OF THE FILE UPLOADED IS VALID
			$path_parts = pathinfo($_FILES["section_photos"]["name"]);
			$extensions = array(0 => 'jpg', 1 => 'jpeg', 2 => 'png', 3 => 'gif', 4 => 'JPG', 5 => 'JPEG', 6 => 'PNG', 7 => 'GIF', 8 => 'doc', 9 => 'docx', 10 => 'pdf', 11 => 'xls', 12 => 'xlsx', 13=>'zip', 14 => 'rar');

            if (in_array($path_parts['extension'], $extensions)) {//DETERMINING IF THE EXTENSION OF THE FILE UPLOADED IS VALID
				if (is_dir($directory)) {
					do {
						$tempName = 'file_' . time() . '.' . $path_parts['extension'];
					} while (file_exists($directory . $tempName));
					move_uploaded_file($_FILES["section_photos"]["tmp_name"], $directory . $tempName);
                                        $appUser->upload_file = $tempName;
					echo $tempName;
				} else {//ITS NOT A DIRECTORY
					echo 3;
				}
			} else {//INCORRECT EXTENSION
				echo 2;
			}
		} else {//INCORRECT SIZE
			echo 1;
		}
	}
        
        /**
	 * Deletes the section temp picture
	 */
	public function deletetemppictureAction()
	{
		$this->_helper->layout->disableLayout ();
		// disable autorendering for this action only:
		$this->_helper->viewRenderer->setNoRender();
	
		$formData  = $this->_request->getPost();	
		$temp_file = $formData['file_tmp'];
				
		if ($temp_file) 
		{			
			if (file_exists(APPLICATION_PATH. '/../'. 'public/uploads/tmp/' . $temp_file)) 
			{
				unlink(APPLICATION_PATH. '/../'. 'public/uploads/tmp/'. $temp_file);
			}
		}
	}
        
    public function deletefileAction()
	{
		$this->_helper->layout->disableLayout ();
		// disable autorendering for this action only:
		$this->_helper->viewRenderer->setNoRender();
	
		$formData  = $this->_request->getPost();
	
		$file = $formData['file'];
	
		if ($file) 
		{
			if (file_exists(APPLICATION_PATH. '/../'. 'public/uploads/tmp/' . $file))
			{
				unlink(APPLICATION_PATH. '/../'. 'public/uploads/tmp/'. $file);
			}
		}
	}
    
    /**
     * Loads rss
     */
    public function rssAction()
    {
        $lang = Zend_Registry::get('Zend_Translate');
     
        $front_ids = New Zend_Session_Namespace('ids');
        
        $this->_helper->layout->disableLayout ();
        // disable autorendering for this action
        $this->_helper->viewRenderer->setNoRender();


           $website = new Core_Model_Website();
           $website_obj = $website->personalized_find('wc_website', 
                   array(array('default_page','=','yes'))); 

           $website_data = get_object_vars($website_obj[0]); //make an array of the object data

           $header_data['logo'] = $website_data['logo'];
           $header_data['name'] = $website_data['name'];
           $header_data['website_url'] = $website_data['website_url'];
           $header_data['analytics'] = $website_data['analytics'];
           $header_data['meta_descr'] = $website_data['description'];
           $header_data['meta_keywords'] = $website_data['keywords'];
           $header_data['meta_author'] = $website_data['copyright'];
           $this->view->header = $header_data;        

           $content = new Core_Model_Content();
           $section_obj = new Core_Model_Section();


           $section = new Core_Model_Section();
        //find existent rss on db according website
           /*$article_rss_arr = $this->getDataToPortal($section_obj->find_between('wc_section', 
                   array(array('article','=','yes'),
                       array('rss_available','=','yes'), 
                       array('website_id','=',1), 
                       array(date('Y-m-d H:i:s'),'BETWEEN','publish_date','expire_date')), 
                   array('order_number ASC'),800), $content, $section, 1, $section_obj, false, true, 500);        
            */
           $article_rss_arr = $section_obj->find_between('wc_section', 
                       array(array('article','=','yes'), 
                       array('rss_available','=','yes'), 
                       array('website_id','=',1), 
                       array(date('Y-m-d H:i:s'),'BETWEEN','publish_date','expire_date')), 
                   array('order_number ASC'),800);        

           //print_r($article_rss_arr);

           echo '<?xml version="1.0" encoding="UTF-8" ?>
   <rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
   <channel>
     <title>'.$header_data['name'].'</title>
     <description>'.$header_data['meta_descr'].'</description>
       <pubDate>'. date("D, j M Y h:i:sa").'</pubDate>
       <generator>Wicaweb RSS Generator ('.$header_data['website_url'].')</generator>
       <link>'.$header_data['website_url'].'</link>
       ';

       foreach ($article_rss_arr as $art) {
           //$art->title = GlobalFunctions::truncate(str_replace('\\','',$art->title), 100, false);
           //$art->synopsis = GlobalFunctions::truncate($art->synopsis, 240, false);
       echo '
         <item>
           <title>'.str_replace('\\','',$art->title).'</title>
           <description>'. strip_tags( str_replace('\\','',html_entity_decode($art->synopsis) ) ).'</description>
           <pubDate>Thu, 23 Apr 2015 20:50:00 +0000</pubDate>
           <link>http://'.$header_data['website_url'].'/articulo/'.$art->id.'/'.str_replace(' ','-',trim($art->title)).'</link>
           <guid>http://'.$header_data['website_url'].'/articulo/'.$art->id.'/'.str_replace(' ','-',trim($art->title)).'</guid>
           <enclosure type="image/jpeg" length="111990" url="';
           //search for an image         
           //$pictures_list = $art->getContentsBySection($art->id, $front_ids, null, 2);
           //print_r($pictures_list);
       echo '"/>
        </item>';
              }

    echo '</channel>
    </rss>';
        
    }
    
}
