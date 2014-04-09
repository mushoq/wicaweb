<?php
/**
 *	Functinallity on banners 
 *
 * @category   WicaWeb
 * @package    Externaluser_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Paul Arevalo
 */

class Externaluser_ExternaluserController extends Zend_Controller_Action
{
	public function init(){

		//Create Zend layout
		$layout = new Zend_Layout();
		// Set a layout scripts path
		$layout->setLayoutPath(APPLICATION_PATH.'/modules/core/layouts/scripts/');
		// choose a different layout script:
		$layout->setLayout('core');
		
		//session
		$id = New Zend_Session_Namespace('id');

		/** sections tree**/
		
		//Create section and section temp model
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		$sections_arr = array();
		 
		//find existent sections on db according website
		$sections_list = $section->personalized_find('wc_section', array(array('website_id','=',$id->website_id)),array('article','order_number'));
		if(count($sections_list)>0)
		{
			foreach ($sections_list as $k => &$slt)
			{
				$sections_published_arr[] = $slt->id;
				$slt->temp = 0;
			}
		}
		$sections_list_temp = $section_temp->personalized_find('wc_section_temp', array(array('website_id','=',$id->website_id)),array('article','order_number'));
		if(count($sections_list_temp)>0)
		{
			foreach ($sections_list_temp as $k => &$stp)
			{
				$stp->temp = 1;
			}
		}
		 
		if(count($sections_list)>0 && count($sections_list_temp)>0)
		{
			$sections_copied_arr = array();
			//replacing sections that area eddited on temp
			foreach ($sections_list as $k => &$sbc)
			{
				foreach ($sections_list_temp as $p => &$sct)
				{
					if($sbc->id == $sct->section_id)
					{
						$sct->id = $sct->section_id;
						$sections_list_res[] = $sct;
						$sections_copied_arr[] = $sct->section_id;
					}
				}
			}
			 
			//adding sections created on temp
			if(count($sections_copied_arr)>0)
			{
				$section_pub_missing = array_diff($sections_published_arr, $sections_copied_arr);
				if(count($section_pub_missing)>0)
				{
					foreach ($section_pub_missing as $serial)
					{
						$section_obj = $section->find('wc_section', array('id'=>$serial));
						$section_obj[0]->temp = 0;
						$sections_list_res[] = $section_obj[0];
					}
				}
			}
			$sections_list = $sections_list_res;
		}

		//Check if user profile is admin profile
		if($id->user_profile == '1')
		{
			//sections list array
			if(count($sections_list)>0)
			{
				foreach ($sections_list as $sec)
				{
					$sections_arr[] = array('id'=>$sec->id,
							'temp'=>$sec->temp,
							'section_parent_id'=>$sec->section_parent_id,
							'title'=>$sec->title,
							'article'=>$sec->article,
							'order_number'=>$sec->order_number
					);
				}
			}
		
			/** Begin filter sections with variable area only (Banner Module Control)**/
			//Find all sections with variable content
			$sections_list_aux = $section->getSectionWithVariableContent();
				
			//sections list id array
			if(isset($sections_list_aux))
			{
				foreach ($sections_list_aux as $sec)
				{
					$sections_arr_id_aux[] = array('id'=>$sec['id']);
				}
			}
		
			//Get sections with content variable and non temp
			if(isset($sections_arr_id_aux)){
				foreach ($sections_arr as $sav){
					foreach ($sections_arr_id_aux as $sa)
					{
						if($sav['id']==$sa['id']){
							$sections_arr_list[] = $sav;
						}
					}
				}
			}
			
			/** End filter sections with fixed area only (End Banner Module Control)**/
		
			$sections_arr = array();
			
			if(isset($sections_arr_list)){
				$sections_arr = $sections_arr_list;
			}	
		}
		else //No Admin Profile
		{
			$subsection_arr = array();
			$section_aux = array();
			
			//Get of session var the sections allowed by actual user
			$user_allowed_sections_arr = explode(',',$id->user_allowed_sections);

			/** Begin filter sections with variable area only (Banner Module Control)**/
			
			//Find template according website
			$website = new Core_Model_Website();
			$website_data = $website->find('wc_website',array('id'=>$id->website_id));
			$template_id = $website_data[0]->template_id;
			  
			//Find variable area content by template
			$area = new Core_Model_Area();
			$area_data = $area->personalized_find('wc_area',array(array('template_id','=',$template_id),array('type','LIKE','variable')));
			$area_content_id = $area_data[0]->id;
		
			//Find sections with variable area content
			$section_area = new Core_Model_SectionModuleArea();
			$section_area_list = $section_area->find('wc_section_module_area',array('area_id'=>$area_content_id));
		
			//Get data of existent sections that contents variable content
			if($section_area_list){
				foreach ($section_area_list as $sal){
					$section_area_item = $section->find('wc_section',array('id'=>$sal->section_id));
					if($section_area_item){
						foreach ($section_area_item as $sei){
							$sections_list_aux[] = $sei;
						}
		
					}
				}
			}
		
			 
			//sections list array
			if($sections_list_aux)
			{
				foreach ($sections_list_aux as $sec)
				{
					$sections_arr_variable[] = array('id'=>$sec->id,
							'section_parent_id'=>$sec->section_parent_id,
							'title'=>$sec->title,
							'article'=>$sec->article
					);
				}
			}
		
			/** End filter sections with fixed area only (End Banner Module Control)**/
			
			//Get available sections for user
			
			foreach ($user_allowed_sections_arr as $serial)
			{
				foreach ($sections_list as $asc)
				{
					if($asc->id == $serial)
					{
						$section_aux[] = $asc;
					}
				}
			}
			$available_sections = $section_aux;
		
			foreach ($available_sections as $sec)
			{
				$sections_arr[] = array('id'=>$sec->id,
						'temp'=>$sec->temp,
						'section_parent_id'=>$sec->section_parent_id,
						'title'=>$sec->title,
						'article'=>$sec->article,
						'order_number'=>$sec->order_number
				);
		
				//parent allowed sections
				if($sec->section_parent_id)
				{
					$subsection_arr[] = self::buildSectionParentTree($branch = array(), $sec->section_parent_id);
				}
			}
		
			if(count($subsection_arr)>0)
			{
				//parent sections array
				foreach ($subsection_arr as $key => $sub)
				{
					foreach ($sub as $val)
					{
						$subsection_list[$val['id']] = $val['id'];
						$subsection_list_stt[$val['id']] = $val['temp'];
					}
				}
		
				$subsection_aux = array_unique($subsection_list);
				if(count($subsection_aux)>0)
				{
					foreach ($subsection_aux as $k => &$sbc)
					{
						foreach ($sections_arr as $sct)
						{
							if($sct['id'] == $sbc && $sct['temp'] == intval($subsection_list_stt[$sbc]))
							{
								unset($subsection_aux[$k]);
							}
						}
					}
					//non repeated sections
					foreach ($subsection_aux as $sec)
					{
						if($subsection_list_stt[$sec])
						{
							$subsection_obj = $section_temp->find('wc_section_temp', array('section_id'=>$sec));
							$temp_subsec = 1;
						}
						else
						{
							$subsection_obj = $section->find('wc_section', array('id'=>$sec));
							$temp_subsec = 0;
						}
		
						foreach ($subsection_obj as $obj)
						{
							if(isset($obj->section_id))
							{
								$serial_sec = $obj->section_id;
							}
							else
							{
								$serial_sec = $obj->id;
							}
								
							$sections_arr[] = array('id'=>$serial_sec,
									'temp'=>$temp_subsec,
									'section_parent_id'=>$obj->section_parent_id,
									'title'=>$obj->title,
									'article'=>$obj->article,
									'order_number'=>$obj->order_number
							);
						}
					}
				}
			}
		
			//Get only variable sections (Banner Module Control)
		
			if($sections_arr_variable){
				foreach ($sections_arr as $sav){
					foreach ($sections_arr_variable as $sa)
					{
						if($sav['id']==$sa['id']){
							$sections_arr_list[] = $sav;
						}
					}
				}
			}
		
			$sections_arr = array();
				
			if(isset($sections_arr_list)){
				$sections_arr = $sections_arr_list;
			}
		
		}
		
		// Ordering sections by article and number
		$sort_col_number = array();
		$sort_col_article = array();
		foreach ($sections_arr as $key=> $row) {
			if($row['article']=='yes')
				$sort_col_article[$key] = 1;
			else
				$sort_col_article[$key] = 2;
			$sort_col_number[$key] = $row['order_number'];
		}
		array_multisort($sort_col_article, SORT_ASC, $sort_col_number, SORT_ASC, $sections_arr);
		 
		//string with sections tree html
		$html_list = '';
		 
		if(count($sections_arr)>0)
		{
			//sections tree - parents and children as array
			$sections_tree = GlobalFunctions::buildSectionTree($sections_arr);
			//sections tree as list
			$html_list = GlobalFunctions::buildHtmlSectionTree($sections_tree);
			
		}

		//Register html_list in view
		$this->view->data = $html_list;
		//Disabled display section bar in index
		$this->view->displaysectionbar = false;
		 
		$cms_arr = array();
		 
		//Get module_id by module_name
		 
		$module_obj = new Core_Model_Module();
		$module = $module_obj->find('wc_module',array('name'=>'Banners'));
		$module_id = $module[0]->id;
		 
		//Get user module action for banner 
		if($id->user_modules_actions){
			foreach ($id->user_modules_actions as $k => $mod)
			{
				 
				if($mod->module_id == $module_id)
				{
					$cms_arr[$mod->action_id] = array('action'=> $mod->action_name, 'title'=>$mod->action_title);
				}
			}
		}

		//Put sections array in session var
		$id->sections_banner_array = $sections_arr;
		
		//Put user module actions in view
		$this->view->cms_links = $cms_arr;	 		
	}
	
	/**
	 * Loads banners to be ordered
	 */
	public function indexAction()
	{	
		//Disable layout for action	
		$this->_helper->layout->disableLayout ();
		
		//session stores website_id
		$id = New Zend_Session_Namespace('id');

		//get section_id
		$section_id = $this->_getParam('id');
		
		//Section id in view
		$this->view->section_id = $section_id;
		
		//Get module_id by module_name
		$module_obj = new Core_Model_Module();
		$module = $module_obj->find('wc_module',array('name'=>'Banners'));
		$module_id = $module[0]->id;
		
		/** Get banner list **/
		
		//Get module description by module (Banners)
		$module_description_obj = new Core_Model_ModuleDescription();
		$module_description_list = $module_description_obj->find('wc_module_description',array('module_id'=>$module_id));
		
		//Create section module area model
		$section_module_area_obj = new Core_Model_SectionModuleArea();
		
		if($module_description_list){
			//Check if is home link
			if($section_id=='all') //Is home link
			{
				//Get all banners
				$banner_obj = new Banners_Model_Banners();
				$banners_list_obj = $banner_obj->find('banner');
				
				//Convert objClass to normal array
				if($banners_list_obj){
					foreach ($banners_list_obj as $bl){
						$banners_list[] = get_object_vars($bl);
					}
				}
				
				//Banners_list to index view
				if(isset($banners_list)){
					$this->view->banners_list = $banners_list;
				}
			}
			else //Is section tree link
			{
				//Get banners by section
				foreach ($module_description_list as $md){
					$section_module_area_item = $section_module_area_obj->find('wc_section_module_area',array('module_description_id'=>$md->id,'section_id'=>$section_id));
					if($section_module_area_item){
						foreach ($section_module_area_item as $sma){
							$section_module_area_list[] = $sma;
						}
				
					}
				}
				
			//Get module_description_id of section_module_area
			if(isset($section_module_area_list)){
					
				foreach ($section_module_area_list as $smal){
					$module_description_banners = $module_description_obj->find('wc_module_description',array('id'=>$smal->module_description_id));
					if($module_description_banners){
						foreach ($module_description_banners as $mdi){
							$module_descriptions_banners_list[] = $mdi;
						}
							
					}
				}
					
			}
		
			//Get banner data by module_description
			if(isset($module_descriptions_banners_list)){
					
				//Get banners list data by module_area
					$banner_obj = new Banners_Model_Banners();
					foreach ($module_descriptions_banners_list as $mdbl){
						$banner_item = $banner_obj->find('banner',array('id'=>$mdbl->row_id));
						if($banner_item){
							foreach ($banner_item as $bi){
								$banners_list[] = get_object_vars($bi);
							}
					
						}
					}
			}
			
			//Ordering banners by order_number
			if(isset($banners_list)){
				$sort_col_number = array();
				foreach ($banners_list as $key=> $row) {
					$sort_col_number[$key] = $row['order_number'];
				}
				array_multisort($sort_col_number, SORT_ASC, $banners_list);
				
				if(isset($banners_list)){
					$this->view->banners_list = $banners_list;
				}
			}
			
		}

		}	
		//Get available sections for website
		
		$section = new Core_Model_Section();
		$available_sections = $section->personalized_find('wc_section',array(array('section_parent_id','=',''),array('website_id','=',$id->website_id)),'order_number');
		
		//available parent section list according profile
		
		//Check if user profile is admin
		if($id->user_profile == '1')
		{		
			$section_list = $available_sections; 
			if(count($section_list)>0)
			{
				foreach ($section_list as &$sli)
				{
					if(GlobalFunctions::checkEditableSection($sli->id))
					{
						$sli->editable_section = 'yes';
					}
					else
					{
						$sli->editable_section = 'no';
					}
						
					if(GlobalFunctions::checkErasableSection($sli->id))
					{
						$sli->erasable_section = 'yes';
					}
					else
					{
						$sli->erasable_section = 'no';
					}
				}
			}
		}
		else
		{
			$section_aux = array();			
			foreach ($id->user_allowed_sections as $sbc)
			{
				foreach ($available_sections as $sct)
				{
					if($sct->id == $sbc->section_id)
					{						
						$section_aux[] = $sct;
					}
				}
			}			
			$section_list = $section_aux;
			if(count($section_list)>0)
			{
				foreach ($section_list as &$sli)
				{
					if(GlobalFunctions::checkEditableSection($sli->id))
					{
						$sli->editable_section = 'yes';
					}
					else
					{
						$sli->editable_section = 'no';
					}
			
					if(GlobalFunctions::checkErasableSection($sli->id))
					{
						$sli->erasable_section = 'yes';
					}
					else
					{
						$sli->erasable_section = 'no';
					}
				}
			}
			
			/*
			 * Ordering sections by order_number in tree of sections
			 */
			$sort_col = array();
			foreach ($section_list as $row) {				
				$sort_col_number[$row->id] = $row->order_number;
			}
			array_multisort($sort_col_number, SORT_ASC, $section_list);
		}

		//Section list to view
		$this->view->section = $section_list;
	}

    public function newAction(){
    	
    	//Get section_id
    	$section_id = $this->_getParam('id');
    	
    	//Disable layout for action
    	$this->_helper->layout->disableLayout ();
    	
    	//session
    	$id = New Zend_Session_Namespace('id');    	
    	 
    	/* Get sections with fixed areas for new form */
    	
    	//Find template according website 
    	$website = new Core_Model_Website();
    	$website_data = $website->find('wc_website',array('id'=>$id->website_id));
    	$template_id = $website_data[0]->template_id;
    	 
    	//Get fixed areas by template
    	 
    	$area = new Core_Model_Area();
    	$area_data = $area->personalized_find('wc_area',array(array('template_id','=',$template_id),array('type','LIKE','fixed')));
    	
    	//Get sections with fixed areas  
    	
    	$section_list = $id->sections_banner_array;
    	
    	//Delete current section of the section list 

    	if(isset($section_list)){  			
    		foreach ($section_list as $s){
    			if($s['id']!=$section_id){
    				$section_list_option[] = $s;
    			}
    		}		
    	}
    	
    	if(isset($section_list_option)){

    		//create a new banner form with section list field
    		$form = New Banners_Form_Banners(array('area_list'=>$area_data,'section_list_option'=>$section_list_option));
    		$this->view->section_list_option = $section_list_option;
    	}
    	else
    	{
    		//create a new banner form
    		$form = New Banners_Form_Banners(array('area_list'=>$area_data,'section_list_option'=>null));
    		$this->view->section_list_option = null;
    	}


    	$form->setMethod('post');
    	$this->view->section_id = $section_id;
    	$this->view->form = $form;

    	
    }
    
    public function editAction(){
    	 
    	//Get section_id
    	$section_id = $this->_getParam('section_id');

    	//Disable layout for this form
    	$this->_helper->layout->disableLayout ();
    	 
    	//session
    	$id = New Zend_Session_Namespace('id');
    	//Get banner_id
    	$banner_id = $this->_getParam('banner_id');

    	//Find template according website
    	$website = new Core_Model_Website();
    	$website_data = $website->find('wc_website',array('id'=>$id->website_id));
    	$template_id = $website_data[0]->template_id;
    
    	//Get fixed areas by template
    	$area = new Core_Model_Area();
    	$area_data = $area->personalized_find('wc_area',array(array('template_id','=',$template_id),array('type','LIKE','fixed')));
    	
    	//Get request params
    	$request_params = $this->getRequest()->getParams();
    	
    	//Get static sections
    	$section_list = $id->sections_banner_array;
    	 
    	//Delete current section of the section list
    	if(isset($section_list)){
    		foreach ($section_list as $s){
    			if($s['id']!=$section_id){
    				$section_list_option[] = $s;
    			}
    		}
    	}
    	
    	if(isset($section_list_option)){
    	
    		//create a new banner form with section list field
	    	$form = New Banners_Form_Banners(array('area_list'=>$area_data,'section_list_option'=>$section_list_option,'action'=>$request_params['action']));
	    	$form->setMethod('post');
    		$this->view->section_list_option = $section_list_option;
    	}
    	else
    	{
    		//create a new banner form
	    	$form = New Banners_Form_Banners(array('area_list'=>$area_data,'section_list_option'=>null,'action'=>$request_params['action']));
	    	$form->setMethod('post');
    		$this->view->section_list_option = null;
    	}
    	
    	//Get banner data for edit
    	$banner_aux = new Banners_Model_Banners();
    	$banner_data = $banner_aux->find('banner',array('id'=>$banner_id));
    	
    	$arr_data = get_object_vars($banner_data[0]); //make an array of the object data
    	
    	//Content open depend banner type
    	switch($arr_data['banner_type']){
    		case "image":
    			$arr_data['file_img_1'] = $arr_data['content'];
    			
    			$image_preview = New Zend_Form_Element_Image('imageprw_1');
    			$image_preview->setImage('/uploads/banners/'.$arr_data['file_img_1']);
    			$image_preview->setAttrib('style', 'width:150px;');
    			$image_preview->setAttrib('onclick', 'return false;');
    			$form->addElement($image_preview);	
    			unset($arr_data['content']);
    			break;
    		case "flash":
    			$arr_data['file_flash_1'] = $arr_data['content'];
    			unset($arr_data['content']);
    			break;
    		case "html":
    			$arr_data['html']=$arr_data['content'];
    			unset($arr_data['content']);
    			break;
    	}
    	
    	
    	//**Get Actual area of banner**/
    	
    	//Get module_id by module_name
    		
    	$module_obj = new Core_Model_Module();
    	$module = $module_obj->find('wc_module',array('name'=>'Banners'));
    	$module_id = $module[0]->id;
    	
    	//Get module description by module (Banners)
    	$module_description_obj = new Core_Model_ModuleDescription();
    	$module_description = $module_description_obj->find('wc_module_description',array('module_id'=>$module_id,'row_id'=>$banner_id));
    	$module_description_id = $module_description[0]->id;
    	
    	//Get section module area list by module description and section
    	$section_module_area_obj = new Core_Model_SectionModuleArea();
    	$section_module_area = $section_module_area_obj->find('wc_section_module_area',array('module_description_id'=>$module_description_id));  			

    	if(count($section_module_area)>0)
    	{
    		$areas = new Core_Model_Area();
    		$number = $areas->find('wc_area',array('id'=>$section_module_area[0]->area_id));
    		$number_area = $number[0]->area_number;
    	
    	}
    	
    	//Set actual area accord template areas
    	if(count($area_data)>0){
    		foreach($area_data as $ad => $value)
    		{
    			if($value->area_number==$number_area){
    				$actual_area[] = $value;
    			}
    		}
    	}
 	
    	//Set banner area id for populate
    	$arr_data['area'] = $actual_area[0]->id;
    	
    	//Get aditional sections of banner
    	
    	if(isset($section_module_area)){
    		foreach ($section_module_area as $sma){
    			if($sma->section_id!=$section_id){
    				$section_aditional[] = $sma->section_id;
    			}
    		}
    	}
    	
    	//Set array aditional sections for populate
    	if(isset($section_aditional)){
    		$arr_data['sections'] = $section_aditional;
    	}

    	//Get publish date and expire date with website format
    	
    	if($arr_data['publish_date']){
    		$arr_data['publish_date'] = GlobalFunctions::getFormattedDate($arr_data['publish_date']);
    	}
    	
    	if($arr_data['expire_date']){
    		$arr_data['expire_date'] = GlobalFunctions::getFormattedDate($arr_data['expire_date']);
    	}
    	
    	//Populate form with data
    	$form->populate($arr_data);
    	
    	$this->view->banner_type = $arr_data['banner_type'];
    	$this->view->section_id = $section_id;
    	$this->view->form = $form;
    	 
    }
    
    public function saveAction(){
    	
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	
    	//Get section_id
    	$section_id = $this->_getParam('section_id');
    	
		//disable autorendering for this action
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender();
		
		//Create a Banner Form
		$form = New Banners_Form_Banners();
		$form->setMethod('post');
		
		if ($this->getRequest()->isPost()) {
			$formData = $this->getRequest()->getPost();

			//create banner model
			$banner =  new Banners_Model_Banners();
			$banner_obj = $banner->getNewRow('banner');
			 
			//save data

			//Check if id exist 
			if(array_key_exists('id',$formData)){ //Is update banner
				$banner_obj->id = GlobalFunctions::value_cleaner($formData['id']);
				$banner_obj->status = GlobalFunctions::value_cleaner($formData['status']);	
			}
			else //Is new banner
			{
				$banner_obj->status = 'active';					
			}		
			
			$banner_obj->banner_type = GlobalFunctions::value_cleaner($formData['btn_value']);
			$banner_obj->name = GlobalFunctions::value_cleaner($formData['name']);
			$banner_obj->description = GlobalFunctions::value_cleaner($formData['description']);
			
			//Check type of banner
			if($formData['type']=='hits'){
				$banner_obj->hits = GlobalFunctions::value_cleaner($formData['hits']);
				$banner_obj->publish_date = NULL;
				$banner_obj->expire_date = NULL;
			}
			else if($formData['type']=='calendar')
			{
				//Format date
				if($formData['publish_date']){
					$formData['publish_date'] = GlobalFunctions::setFormattedDate($formData['publish_date']);
					$banner_obj->publish_date = $formData['publish_date'];
				}
				
					
				if($formData['expire_date']){
					$formData['expire_date'] = GlobalFunctions::setFormattedDate($formData['expire_date']);
					$banner_obj->expire_date = $formData['expire_date'];
				}
				
				$banner_obj->hits = NULL;
			}

			$banner_obj->link = GlobalFunctions::value_cleaner($formData['link']);
			$banner_obj->type = GlobalFunctions::value_cleaner($formData['type']);
				
			//Check content type of banner for content field
			switch($formData['btn_value']){
				case "image":
					
					//path to upload image
					if(!is_dir(APPLICATION_PATH. '/../public/uploads/banners/'))
					{
						$path = APPLICATION_PATH. '/../public/uploads/banners/';
						mkdir($path);
						chmod($path, 0777);
					}
					
					if(!is_dir(APPLICATION_PATH. '/../public/uploads/banners/'.date('Y')))
					{
						$path = APPLICATION_PATH. '/../public/uploads/banners/'.date('Y');
						mkdir($path);
						chmod($path, 0777);
					}
					
					if(!is_dir(APPLICATION_PATH. '/../public/uploads/banners/'.date('Y').'/'.date('m')))
					{
						$path = APPLICATION_PATH. '/../public/uploads/banners/'.date('Y').'/'.date('m');
						mkdir($path);
						chmod($path, 0777);
					}

					
					//if image file uploaded to create new or update
					if($formData['hdnNameFile_1'])
					{

						if(isset($formData['file_img_1'])){

							//delete old image file
							if($formData['file_img_1']!="")
							{
							
								list($folder,$subfolder,$file) = explode('/',$formData['file_img_1']);
								GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/banners/'.$folder.'/'.$subfolder.'/');
							
							}

						}

						//Save image in object
						$img = GlobalFunctions::uploadFiles($formData['hdnNameFile_1'], APPLICATION_PATH. '/../public/uploads/banners/'.date('Y').'/'.date('m').'/');
						$banner_obj->content = date('Y').'/'.date('m').'/'.$img;
						
													
						//remove images temp files
						GlobalFunctions::removeOldFiles($formData['hdnNameFile_1'], APPLICATION_PATH. '/../public/uploads/tmp/');

					}
					else
					{
						//Same image
						$banner_obj->content = $formData['file_img_1'];
					}

					break;
					
				case "flash":
					
					//path to upload image
					if(!is_dir(APPLICATION_PATH. '/../public/uploads/banners/'))
					{
						$path = APPLICATION_PATH. '/../public/uploads/banners/';
						mkdir($path);
						chmod($path, 0777);
					}
						
					if(!is_dir(APPLICATION_PATH. '/../public/uploads/banners/'.date('Y')))
					{
						$path = APPLICATION_PATH. '/../public/uploads/banners/'.date('Y');
						mkdir($path);
						chmod($path, 0777);
					}
						
					if(!is_dir(APPLICATION_PATH. '/../public/uploads/banners/'.date('Y').'/'.date('m')))
					{
						$path = APPLICATION_PATH. '/../public/uploads/banners/'.date('Y').'/'.date('m');
						mkdir($path);
						chmod($path, 0777);
					}
					
			
					
					//if image file uploaded to create new or update
					if($formData['hdnNameFileFlash_1'])
					{

						if(isset($formData['file_flash_1'])){

							//delete old image file
							if($formData['file_flash_1']!="")
							{
							
								list($folder,$subfolder,$file) = explode('/',$formData['file_flash_1']);
								GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/banners/'.$folder.'/'.$subfolder.'/');
							
							}

						}

						//Save image in object
						$flash = GlobalFunctions::uploadFiles($formData['hdnNameFileFlash_1'], APPLICATION_PATH. '/../public/uploads/banners/'.date('Y').'/'.date('m').'/');
						$banner_obj->content = date('Y').'/'.date('m').'/'.$flash;
						
													
						//remove images temp files
						GlobalFunctions::removeOldFiles($formData['hdnNameFileFlash_1'], APPLICATION_PATH. '/../public/uploads/tmp/');

					}
					else
					{
						//Same image
						$banner_obj->content = $formData['file_flash_1'];
					}
					
					break;
				case "html":
					$banner_obj->content = $formData['html'];
					break;
			}

			// Save data
			$saved_banner = $banner->save('banner',$banner_obj);

			if($saved_banner){
				
				//If exist id is Update Banner
				if(array_key_exists('id',$formData)){
	
					$arr_success = array('section_id'=>$section_id);
					
					//create section module area model
					$section_module_area =  new Core_Model_SectionModuleArea();
					$section_module_area_obj = $section_module_area->getNewRow('wc_section_module_area');
					
					//Save data of aditional sections in module area table
					if(isset($formData['sections'])){
						if($section_id!='all'){
							//Have current section. Add section id to sections array
							array_push($formData['sections'], $section_id);	
						}
	

						//Get module_id by module_name
						$module_obj = new Core_Model_Module();
						$module = $module_obj->find('wc_module',array('name'=>'Banners'));
						$module_id = $module[0]->id;
							
						//Get module description by module (Banners)
						$module_description_obj = new Core_Model_ModuleDescription();
						$module_description_item = $module_description_obj->find('wc_module_description',array('module_id'=>$module_id,'row_id'=>$formData['id']));
						
						//Get section module area list accord module description
						$section_module_area_list = $section_module_area->find('wc_section_module_area',array('module_description_id'=>$module_description_item[0]->id));

						//Delete all section module area list for update
						foreach($section_module_area_list as $smal){
							$section_module_area->delete('wc_section_module_area',array('id'=>$smal->id));
						}
						
						foreach ($formData['sections'] as $section_item){
							
							//Check if exist in section module area						
							$section_module_area_exist = $section_module_area->find('wc_section_module_area',array('module_description_id'=>$module_description_item[0]->id,'section_id'=>$section_item));
							
							if(count($section_module_area_exist)>0){
								
								//Save module description area with update area
								$section_module_area_obj->id= $section_module_area_exist[0]->id;
								$section_module_area_obj->section_id= $section_module_area_exist[0]->section_id;
								$section_module_area_obj->area_id= $formData['area'];
								$section_module_area_obj->module_description_id= $section_module_area_exist[0]->module_description_id;

								// Save data of aditional section
								$section_module_area->save('wc_section_module_area',$section_module_area_obj);
								
							}
							else
							{
								//Save new module description area with new section
								$section_module_area_obj->section_id= $section_item;
								$section_module_area_obj->area_id= $formData['area'];
								$section_module_area_obj->module_description_id= $module_description_item[0]->id;
								
								// Save data of aditional section
								$section_module_area->save('wc_section_module_area',$section_module_area_obj);
							}
							
						}

					}
					else 
					{
						//Not aditional banner
						
						/* Save only update in section module area */
						
						//create section module area model
						$section_module_area =  new Core_Model_SectionModuleArea();
						$section_module_area_obj = $section_module_area->getNewRow('wc_section_module_area');
						
						//Get module_id by module_name
							
						$module_obj = new Core_Model_Module();
						$module = $module_obj->find('wc_module',array('name'=>'Banners'));
						$module_id = $module[0]->id;
						
						//Get module description by module (Banners)
						$module_description_obj = new Core_Model_ModuleDescription();
						$module_description_list = $module_description_obj->find('wc_module_description',array('module_id'=>$module_id,'row_id'=>$formData['id']));
							
						//Get section module area by module description id for update banner
						if($module_description_list){
							$section_module_area_item = $section_module_area->find('wc_section_module_area',array('module_description_id'=>$module_description_list[0]->id,'section_id'=>$section_id));
						}
						
						//Get section module area list accord module description
						$section_module_area_list = $section_module_area->find('wc_section_module_area',array('module_description_id'=>$module_description_list[0]->id));
						
						//Delete all section module area list for update
						foreach($section_module_area_list as $smal){
							$section_module_area->delete('wc_section_module_area',array('id'=>$smal->id));
						}
						
						if(isset($section_module_area_item)){
							//Update section module area table
							
							$section_module_area_obj->section_id= $section_module_area_item[0]->section_id;
							$section_module_area_obj->area_id= $formData['area'];
							$section_module_area_obj->module_description_id= $section_module_area_item[0]->module_description_id;
							
							$section_module_area->save('wc_section_module_area',$section_module_area_obj);
						}			
					}
					
					echo json_encode($arr_success);
					//success message
					$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success updated')));
					
				}
				else
				{ //Is new banner		
					
					
					//Get last saved banner
					$last_banner_id = $saved_banner['id'];
					

					//Save in banner count table
					
					$banner_count = new Banners_Model_BannerCount();
					$banner_count_obj = $banner_count->getNewRow('banner_counts');
					$banner_count_obj->banner_id = $last_banner_id;
					$banner_count_obj->count_hits = '0';
					$saved_banner_count = $banner_count->save('banner_counts', $banner_count_obj);
					
					
					//create module description model
					$module_description =  new Core_Model_ModuleDescription();
					$module_description_obj = $module_description->getNewRow('wc_module_description');
					
					//Get module_id by module_name
						
					$module_obj = new Core_Model_Module();
					$module = $module_obj->find('wc_module',array('name'=>'Banners'));
					$module_id = $module[0]->id;
					
					//Save data in module description table
					$module_description_obj->module_id= $module_id;
					$module_description_obj->row_id= $last_banner_id;
					
					// Save data
					$saved_module_description = $module_description->save('wc_module_description',$module_description_obj);
					
					if($saved_module_description){
							
						//Get last saved module description
						$last_module_description_id = $saved_module_description['id'];
							
						//create section module area model
						$section_module_area =  new Core_Model_SectionModuleArea();
						$section_module_area_obj = $section_module_area->getNewRow('wc_section_module_area');
							
							
						//Save data in section module area table

						//Check if new banner not have section id
						if($section_id == 'all')
						{
									
								//Save data of aditional sections in module area table
								if(isset($formData['sections'])){
									foreach ($formData['sections'] as $s){
											
										$section_module_area_obj = $section_module_area->getNewRow('wc_section_module_area');
											
										//Save data in section module area table
											
										$section_module_area_obj->section_id= $s;
										$section_module_area_obj->area_id= $formData['area'];
										$section_module_area_obj->module_description_id= $last_module_description_id;
											
										// Save data of aditional section
										$saved_diferent_sections = $section_module_area->save('wc_section_module_area',$section_module_area_obj);
									}
								}
								
								if($saved_diferent_sections){
									$arr_success = array('section_id'=>$section_id);
									echo json_encode($arr_success);
									//success message
									$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
									
								}
								else
								{
									$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in saving data')));
								}

						}
						else
						{
							$section_module_area_obj->section_id= $section_id;
							$section_module_area_obj->area_id= $formData['area'];
							$section_module_area_obj->module_description_id= $last_module_description_id;
								
							// Save data
							$saved_section_module_area = $section_module_area->save('wc_section_module_area',$section_module_area_obj);
							
							if($saved_section_module_area){
									
								//Save data of aditional sections in module area table
								if(isset($formData['sections'])){
									foreach ($formData['sections'] as $s){
											
										$section_module_area_obj = $section_module_area->getNewRow('wc_section_module_area');
											
										//Save data in section module area table
											
										$section_module_area_obj->section_id= $s;
										$section_module_area_obj->area_id= $formData['area'];
										$section_module_area_obj->module_description_id= $last_module_description_id;
											
										// Save data of aditional section
										$section_module_area->save('wc_section_module_area',$section_module_area_obj);
									}
								}
									
								$arr_success = array('section_id'=>$section_id);
								echo json_encode($arr_success);
								//success message
								$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
									
							}
							else
							{
								$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in saving data')));
							}
							
						}
						

					}
				}	


			}
		
		}
    	
    }


    /**
     * Saves banner order
     */
    public function saveorderAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    
    	$this->_helper->layout->disableLayout ();
    	//disable autorendering for this action
    	$this->_helper->viewRenderer->setNoRender();
    
    	if ($this->getRequest()->isPost())
    	{
    		//Create banner Model
    		$banner = new Banners_Model_Banners();
    			
    		//retrieved data from post
    		$formData  = $this->_request->getPost();
    			
    		$session_id = New Zend_Session_Namespace('id');
    		
    		if($formData['identifier']=='banners')
    		{ 
    			//Get banners order by form data
    			if(isset($formData['banner_order']))
    				$order_list = GlobalFunctions::value_cleaner($formData['banner_order']);
    
    			$order_arr = explode(',', $order_list);
    			$count = 1;
    
    			if(count($order_arr)>0)
    			{
    				//save banners according order
    				foreach ($order_arr as $order)
    				{
    					if($order)
    					{
	    						$options = explode('_', $order);
	    						$banner_id = $options[0];

    							$banner_data = $banner->find('banner', array('id'=>$banner_id));
    							
    							//Create banner object for update with new order
    							$banner_obj = $banner->getNewRow('banner');
    							$banner_obj->id = $banner_data[0]->id;
    							$banner_obj->name = GlobalFunctions::value_cleaner($banner_data[0]->name);
    							$banner_obj->description = GlobalFunctions::value_cleaner($banner_data[0]->description);
    							$banner_obj->content = $banner_data[0]->content;
    							$banner_obj->banner_type = GlobalFunctions::value_cleaner($banner_data[0]->banner_type);
    							$banner_obj->link = GlobalFunctions::value_cleaner($banner_data[0]->link);
    							$banner_obj->type = GlobalFunctions::value_cleaner($banner_data[0]->type);
    							$banner_obj->publish_date = $banner_data[0]->publish_date;
    							$banner_obj->expire_date = $banner_data[0]->expire_date;
    							$banner_obj->hits = $banner_data[0]->hits;
    							$banner_obj->order_number = GlobalFunctions::value_cleaner($count);
    							$banner_obj->status = GlobalFunctions::value_cleaner($banner_data[0]->status);
 
    							$serial_id = $banner->save('banner',$banner_obj);
    							$count++;
    						
    					}
    				}
    			}
    			if($formData['section_id']){
    				$arr_success = array('serial'=>$formData['section_id']);
    			}
    			else
    			{
    				$arr_success = array('serial'=>'saved');
    			}
    		}

    		echo json_encode($arr_success);
    		$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved order')));
    	}
    }
    
    
    /**
     * Deletes an existent banner
     */
    public function deleteAction()
    {
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action
    	$this->_helper->viewRenderer->setNoRender();
    
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    
    	//banner_id passed in URL
    	$banner_id = $this->_getParam('id');
    	
    	//section_id of banner passed in URL
    	$section_id = $this->_getParam('section_id');
    	 
    	//session
    	$session = new Zend_Session_Namespace('id');

    	/* Delete module area according section of banner */
    	
    	//Get module_id by module_name
    		
    	$module_obj = new Core_Model_Module();
    	$module = $module_obj->find('wc_module',array('name'=>'Banners'));
    	$module_id = $module[0]->id;
    	
    	//Get module description id by module and banner_id
    	$module_description_obj = new Core_Model_ModuleDescription();
    	$module_description = $module_description_obj->find('wc_module_description',array('module_id'=>$module_id,'row_id'=>$banner_id));
    	$module_description_id = $module_description[0]->id;
    	
    	
    	//Delete module area by module description id and section id
		$section_module_area_aux = new Core_Model_SectionModuleArea();
    	$delete_banner= $section_module_area_aux->delete('wc_section_module_area',array('module_description_id'=>$module_description_id,'section_id'=>$section_id));

    	//succes or error messages displayed on screen
    	if($delete_banner)
    	{
    		$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success deleted')));
    		$arr_success = array('serial'=>$section_id);
    		echo json_encode($arr_success);
    	}
    	else
    	{
    		$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in deleting data')));
    	}
    }
    
    /**
     * Search an existent banner according to name or section
     */
    public function linkAction()
    {
    	$this->_helper->layout->disableLayout ();
    
    	$link_search_form = new Banners_Form_Search();
    	$this->view->form = $link_search_form;
    	$this->view->showresults = false;
    	
    	//get section_tree_id
    	$section_tree_id = $this->_getParam('section_tree_id');
    	$this->view->section_tree_id = $section_tree_id;
    	
    	// retrieved data from post
    	$formData = $this->_request->getPost ();
    	
    	if ($formData['search_content']=='1')
    	{
    		//session
    		$id = New Zend_Session_Namespace('id');
    			
    		$this->view->showresults = true;
    		// retrieved data from post
    		$formData = $this->_request->getPost ();
    			
    		$internal_name = mb_strtolower ( $formData ['text'], 'UTF-8' );
    		$serial_sec = $formData ['section'];
    			
    		$banners_list = array();
    		$section = new Core_Model_Section();
    		$section_temp = new Core_Model_SectionTemp();
    		$content = new Core_Model_Content();
    		
    		//**Get banners list with wica content area (variable) and search parameters **/
    		
    		//Get module_id by module_name
    		$module_obj = new Core_Model_Module();
    		$module = $module_obj->find('wc_module',array('name'=>'Banners'));
    		$module_id = $module[0]->id;
    		
    		//Get module description by module (Banners)
    		$module_description_obj = new Core_Model_ModuleDescription();
    		$module_description_list = $module_description_obj->find('wc_module_description',array('module_id'=>$module_id));
    		
    		//Create section module area model
    		$section_module_area_obj = new Core_Model_SectionModuleArea();
    		
    		if($module_description_list){
    			//Check if search all sections 
    			if($formData['section']=='0') 
    			{
	    				//Get section module areas by module description
	    				foreach ($module_description_list as $md){
	    					$section_module_area_item = $section_module_area_obj->find('wc_section_module_area',array('module_description_id'=>$md->id));
	    					if($section_module_area_item){
	    						foreach ($section_module_area_item as $sma){
	    							$section_module_area_list[] = $sma;
	    						}
	    						 
	    					}
	    				}
    				
	    				//Get module_description_id of section_module_area
	    				if(isset($section_module_area_list)){
	    					 
	    					foreach ($section_module_area_list as $smal){
	    						$module_description_banners = $module_description_obj->find('wc_module_description',array('id'=>$smal->module_description_id));
	    						if($module_description_banners){
	    							foreach ($module_description_banners as &$mdi){
	    								$mdi->section_id = $smal->section_id;
	    								$module_descriptions_banners_list[] = $mdi;
	    							}
	    							 
	    						}
	    					}
	    					 
	    				}
	    				
	    				//Get banner data by module_description
	    				if(isset($module_descriptions_banners_list)){
	    					 
	    					//Get banners list data by module_area
	    					$banner_obj = new Banners_Model_Banners();
	    					foreach ($module_descriptions_banners_list as $mdbl){
	    						//Get section data
	    						$section_data = $section->find('wc_section',array('id'=>$mdbl->section_id));
	    						$section_data = get_object_vars($section_data[0]);
	    						
	    						//Check if name search is empty
	    						if($formData['text']==''){
	    							$banner_item = $banner_obj->find('banner',array('id'=>$mdbl->row_id));
	    						}
	    						else
	    						{
	    							$banner_item = $banner_obj->personalized_find('banner',array(array('id','=',$mdbl->row_id),array('name','LIKE','%'.$internal_name.'%')));
	    						}
	    						if($banner_item){
	    							foreach ($banner_item as &$bi){
	    	
    									$bi->section_id = $section_data['id'];
    									$bi->section_name =$section_data['internal_name'];
	    								$banners_list[] = get_object_vars($bi);
	    				
	    							}
	    							 
	    						}
	    					}
	    				}
	    				
    					
    			}
    			else //Specified section in search
    			{
    				//Get section data
    				$section_data = $section->find('wc_section',array('id'=>$formData['section'])); 

    				//Get section module areas by module description
    				foreach ($module_description_list as $md){
    					$section_module_area_item = $section_module_area_obj->find('wc_section_module_area',array('module_description_id'=>$md->id,'section_id'=>$formData['section']));
    					if($section_module_area_item){
    						foreach ($section_module_area_item as $sma){
    							$section_module_area_list[] = $sma;
    						}
    			
    					}
    				}
    				
    				//Get module_description_id of section_module_area
    				if(isset($section_module_area_list)){
    			
    					foreach ($section_module_area_list as $smal){
    						$module_description_banners = $module_description_obj->find('wc_module_description',array('id'=>$smal->module_description_id));
    						if($module_description_banners){
    							foreach ($module_description_banners as $mdi){
    								$module_descriptions_banners_list[] = $mdi;
    							}
    			
    						}
    					}
    			
    				}
    			
    				//Get banner data by module_description
    				if(isset($module_descriptions_banners_list)){
    			
    					//Get banners list data by module_area
    					$banner_obj = new Banners_Model_Banners();
    					foreach ($module_descriptions_banners_list as $mdbl){
    						//Check if name search is empty
    						if($formData['text']==''){
    							$banner_item = $banner_obj->find('banner',array('id'=>$mdbl->row_id));
    						}
    						else
    						{
    							$banner_item = $banner_obj->personalized_find('banner',array(array('id','=',$mdbl->row_id),array('name','LIKE','%'.$internal_name.'%')));
    						}
    						if($banner_item){
    							foreach ($banner_item as $bi){
  								
    								$bi->section_id = $section_data[0]->id;
    								$bi->section_name = $section_data[0]->internal_name;
    								$banners_list[] = get_object_vars($bi);
    								
    							}
    			
    						}
    					}
    				}
    			}
    		}
    		
    		if(count($banners_list)>0)
    		{
	    		if(isset($banners_list)){
	    			$this->view->content_results = $banners_list;
	    		}
    		}
    		
    		//banners will be linked to this section
    		$section_obj = $section->find('wc_section', array('id'=>$section_tree_id));

    		if(count($section_obj)>0){
    			$this->view->section = $section_obj[0];
    		}
    		
    	}
    }
    
    /**
     * Returns the sections list for the autocompleter.
     *
     * @param $_GET['q']
     */
    public function sectionautocompleterAction()
    {
    	$name = $this->_request->getParam ( 'q' );
    
    	$title = mb_strtolower ( $name, 'UTF-8' );
    
    	// stored session
    	$session_id = new Zend_Session_Namespace ( 'id' );
    	$website_id = $session_id->website_id;
    
    	$section = new Core_Model_Section();
    	$section_temp = new Core_Model_SectionTemp();
    
    	$section_array = $section->personalized_find ( 'wc_section', array ( array ( 'title', 'LIKE', $title ), array ( 'website_id', '=', $website_id ) ) );
    	foreach ( $section_array as $k => &$slt ) {
    		$sections_published_arr[] = $slt->id;
    	}
    	//temp
    	$section_array_temp = $section_temp->personalized_find ( 'wc_section_temp', array ( array ( 'title', 'LIKE', $title ), array ( 'website_id', '=', $website_id ) ) );
    
    	if(count($section_array)>0 && count($section_array_temp)>0)
    	{
    		$sections_copied_arr = array();
    		//replacing sections that area eddited on temp
    		foreach ($section_array as $k => &$sbc)
    		{
    			foreach ($section_array_temp as $p => &$sct)
    			{
    				if($sbc->id == $sct->section_id)
    				{
    					$sct->id = $sct->section_id;
    					$sections_list_res[] = $sct;
    					$sections_copied_arr[] = $sct->section_id;
    				}
    			}
    		}
    
    		//adding sections created on temp
    		if(count($sections_copied_arr)>0)
    		{
    			$section_pub_missing = array_diff($sections_published_arr, $sections_copied_arr);
    			if(count($section_pub_missing)>0)
    			{
    				foreach ($section_pub_missing as $serial)
    				{
    					$section_obj = $section->find('wc_section', array('id'=>$serial));
    					$section_obj[0]->temp = 0;
    					$sections_list_res[] = $section_obj[0];
    				}
    			}
    		}
    		$section_array = $sections_list_res;
    	}
    
    	if (is_array($section_array)) {
    		foreach ( $section_array as $c ) {
    			echo $c->title . '|' . $c->id . "\n";
    		}
    	}
    
    	// disable autorendering for this action only:
    	$this->_helper->layout->disableLayout ();
    	$this->_helper->viewRenderer->setNoRender ();
    }
    
    /**
     * Link a banner to a section
     */
    public function linkbannersAction()
    {
    	$this->_helper->layout->disableLayout ();
    	$this->_helper->viewRenderer->setNoRender ();
    
    	// translate library
    	$lang = Zend_Registry::get ( 'Zend_Translate' );
    
    	if ($this->getRequest ()->isPost ())
    	{
    		// retrieved data from post
    		$formData = $this->_request->getPost ();
    		$inserted = array ();
    		$section = new Core_Model_Section();
    		$section_temp = new Core_Model_SectionTemp();
			$saved = 0;
    		
    		foreach ( $formData ['objects'] as $banner_id => $status )
    		{
    			
    		    	//Get module_id by module_name
			    	$module_obj = new Core_Model_Module();
			    	$module = $module_obj->find('wc_module',array('name'=>'Banners'));
			    	$module_id = $module[0]->id;
			    	
			    	//Get module description by module (Banners)
			    	$module_description_obj = new Core_Model_ModuleDescription();
			    	$module_description = $module_description_obj->find('wc_module_description',array('module_id'=>$module_id,'row_id'=>$banner_id));
			    	$module_description_id = $module_description[0]->id;

			    	//Create section module area model
			    	$section_module_area_model = new Core_Model_SectionModuleArea();
			    	//Get area of banner
			    	$section_module_area_banner = $section_module_area_model->find('wc_section_module_area',array('module_description_id'=>$module_description_id));
			    	$area_banner = $section_module_area_banner[0]->area_id;
			    	
			    	//Check if it exist in section destiny id
			    	$section_module_area_exist = $section_module_area_model->find('wc_section_module_area',array('module_description_id'=>$module_description_id,'section_id'=>$formData['section_id']));
			    	
			    	if(count($section_module_area_exist)>0){
			    		//Exist registry in section module area table
			    		$saved++;
			    	}
			    	else
			    	{
			    		//Insert new registry in section module area table
			    		$section_module_area_obj = $section_module_area_model->getNewRow('wc_section_module_area');
			    		$section_module_area_obj->section_id = $formData['section_id'];
			    		$section_module_area_obj->area_id = $area_banner;
			    		$section_module_area_obj->module_description_id = $module_description_id;
			    		$section_module_area_saved = $section_module_area_model->save('wc_section_module_area', $section_module_area_obj);
			    		if($section_module_area_saved){
			    			$saved++;
			    		}
			    	}    	
    		}
    		
    		if ($saved == count ( $formData ['objects'] ))
    		{
    			$this->_helper->flashMessenger->addMessage ( array ( 'success' => $lang->translate ( 'Success saved' )) );
    			$arr_success = array('serial'=>'linked');
    			echo json_encode($arr_success);
    		}
    		else
    		{
    			$this->_helper->flashMessenger->addMessage ( array ('error' => $lang->translate ( 'Errors in saving data' ) ) );
    		}
    	}
    }
    
    
    /**
     * Validate that the entered banner name is not repeated
     */
    public function validatebannernameAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();
    	 
    	if ($this->getRequest()->isPost())
    	{
    		$data = $this->_request->getPost();

    		$bannername = mb_strtolower($data['name'], 'UTF-8');
    		$id = -1;
    		if(isset($data['id']))
    			$id= $data['id'];
    
    		$banner = new Banners_Model_Banners();
    		if(isset($id) && $id>0)
    			$banner_array = $banner->personalized_find('banner', array(array('name', '=', $bannername),array('id', '!=', $id)));
    		else
    			$banner_array = $banner->personalized_find('banner', array(array('name', '=', $bannername)));
    
    		if($banner_array && count($banner_array)>0)
    			echo json_encode(false);
    		else
    			echo json_encode(true);
    
    	}
    }
    
    /**
     * Uploads a banner picture
     */
    public function uploadfileAction()
    {
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action only:
    	$this->_helper->viewRenderer->setNoRender();
    
    	$formData  = $this->_request->getPost();
    
    	$directory = $formData['directory'];
    	$maxSize = $formData['maxSize'];
    
    	$directory = APPLICATION_PATH. '/../'. $directory;
    	if ($_FILES["section_photos"]["size"] <= $maxSize) {//DETERMINING IF THE SIZE OF THE FILE UPLOADED IS VALID
    		$path_parts = pathinfo($_FILES["section_photos"]["name"]);
    		$extensions = array(0 => 'jpg', 1 => 'jpeg', 2 => 'png', 3 => 'gif', 4 => 'JPG', 5 => 'JPEG', 6 => 'PNG', 7 => 'GIF', 8 => 'swf');
    
    		if (in_array($path_parts['extension'], $extensions)) {//DETERMINING IF THE EXTENSION OF THE FILE UPLOADED IS VALID
    			if (is_dir($directory)) {
    				do {
    					$tempName = 'pic_' . time() . '.' . $path_parts['extension'];
    				} while (file_exists($directory . $tempName));
    				move_uploaded_file($_FILES["section_photos"]["tmp_name"], $directory . $tempName);
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
     * Deletes the banner temp picture
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
    
    /**
     * Deletes the banner picture
     */
    public function deletepictureAction()
    {
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action only:
    	$this->_helper->viewRenderer->setNoRender();
    
    	$formData  = $this->_request->getPost();
    	$image_id = $formData['image_id'];
    
    	$section_image = new Core_Model_SectionImage();
    	$section_image_obj = $section_image->find('wc_section_image', array('id'=>$image_id));
    
    	if($section_image_obj)
    	{
    		foreach ($section_image_obj as $image)
    		{
    			list($folder,$subfolder,$file) = explode('/',$image->file_name);
    			if(!GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/')){
    				throw new Zend_Exception("CUSTOM_EXCEPTION:FILE NOT DELETED.");
    			}
    			$delete_image = $section_image->delete('wc_section_image', array('id'=>$image->id));
    		}
    	}
    }
    
    
    

}
