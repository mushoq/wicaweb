<?php
/**
 *	Functinallity on banners 
 *
 * @category   WicaWeb
 * @package    Banners_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Diego Perez
 */

class Banners_BannersController extends Zend_Controller_Action
{
	public function init(){

		//Create Zend layout
		$layout = new Zend_Layout();
		// Set a layout scripts path
		$layout->setLayoutPath(APPLICATION_PATH.'/modules/core/layouts/scripts/');
		// choose a different layout script:
		$layout->setLayout('core');
		
                $lang = Zend_Registry::get('Zend_Translate');
		//session
		$id = New Zend_Session_Namespace('id');
                
                //check logged in user
		if (!Zend_Auth::getInstance ()->hasIdentity ()) {
			//translate library
			$lang = Zend_Registry::get('Zend_Translate');			
			throw new Zend_Exception("CUSTOM_EXCEPTION:".$lang->translate('No Access Permissions').'<br/><br/>'.'<a href="/core">'.$lang->translate('Login to the Administration').'</a>');		
		}
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
                
		
		$banner_obj = new Banners_Model_Banners();
		
		
                //Check if is home link
                if($section_id=='all') //Is home link
                {
                        //Get all banners
                        
                        $banners_list = $banner_obj->find('wc_banner', array('status'=>'active'));
                        
                        

                        //Banners_list to index view
                        if(isset($banners_list)){
                                $this->view->banners_list = $banners_list;
                        }
                }
                else //Is section tree link
                {
                    //Get banners by section
                    $banners_list = $banner_obj->getbannersbysection($section_id);
                    
                
                    //Ordering banners by order_number
                    if(isset($banners_list)){
                        $sort_col_number = array();
                        foreach ($banners_list as $key=> $row) {
                                $sort_col_number[$key] = $row->order_number;
                        }
                        array_multisort($sort_col_number, SORT_ASC, $banners_list);

                        if(isset($banners_list)){
                                $this->view->banners_list = $banners_list;
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
    	$area_data = $area->find('wc_area_banner');
    	
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
    	$area_data = $area->find('wc_area_banner');
    	
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
    	$banner_data = $banner_aux->find('wc_banner',array('id'=>$banner_id));
    	
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
    	
    	$bannerbysection = $banner_aux->find('wc_banner_by_section', array('banner_id'=>$banner_id,'section_id'=>$section_id));
        $number = $banner_aux->find('wc_area_banner',array('id'=>$bannerbysection[0]->area_banner_id));
        $number_area = $number[0]->area_number;
    	
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
			$banner_obj = $banner->getNewRow('wc_banner');
			 
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
			$saved_banner = $banner->save('wc_banner',$banner_obj);

			if($saved_banner){
				
				//If exist id is Update Banner
				if(array_key_exists('id',$formData)){
                                        $banner_id = $saved_banner['id'];
                                        
                                        //Save in banner_by_section
                                        $banner_by_sections = $banner->find('wc_banner_by_section', array('banner_id'=>$banner_id));
                                        foreach($banner_by_sections as $banner_by_section){
                                            $banner_by_section->area_banner_id = $formData['area'];
                                            $banner->save('wc_banner_by_section', $banner_by_section);
                                        }
                                        
                                        if(isset($formData['sections'])){
                                            foreach($formData['sections'] as $another_section_id){
                                                $banner_by_sections = $banner->find('wc_banner_by_section', array('banner_id'=>$banner_id, 'section_id'=>$another_section_id));
                                                if(count($banner_by_sections) == 0){
                                                    $order_number = 1;
                                                    $lastorder = $banner->find('wc_banner_by_section', array('section_id'=>$another_section_id), array('order_number'=>'DESC'));
                                                    if($lastorder){
                                                        $order_number = $lastorder[0]->order_number + 1;
                                                    }
                                                    $banner_by_section = $banner->getNewRow('wc_banner_by_section');
                                                    $banner_by_section->banner_id = $banner_id;
                                                    $banner_by_section->section_id = $another_section_id;
                                                    $banner_by_section->area_banner_id = $formData['area'];
                                                    $banner_by_section->order_number = $order_number;
                                                    $banner->save('wc_banner_by_section',$banner_by_section);
                                                }
                                                
                                            }
                                        }
                                        
					$arr_success = array('section_id'=>$section_id);
					
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
					$banner_count_obj = $banner_count->getNewRow('wc_banner_counts');
					$banner_count_obj->banner_id = $last_banner_id;
					$banner_count_obj->count_hits = '0';
                                        $banner_count_obj->count_views = '0';
					$saved_banner_count = $banner_count->save('wc_banner_counts', $banner_count_obj);
					$order_number = 1;
                                        $lastorder = $banner->find('wc_banner_by_section', array('section_id'=>$section_id), array('order_number'=>'DESC'));
                                        if($lastorder){
                                            $order_number = $lastorder[0]->order_number + 1;
                                        }
                                        $banner_by_section = $banner->getNewRow('wc_banner_by_section');
                                        $banner_by_section->banner_id = $last_banner_id;
                                        $banner_by_section->section_id = $section_id;
                                        $banner_by_section->area_banner_id = $formData['area'];
                                        $banner_by_section->order_number = $order_number;
                                        $banner->save('wc_banner_by_section',$banner_by_section);
                                        
                                        if(isset($formData['sections'])){
                                            foreach($formData['sections'] as $another_section_id){
                                                $order_number = 1;
                                                $lastorder = $banner->find('wc_banner_by_section', array('section_id'=>$another_section_id), array('order_number'=>'DESC'));
                                                if($lastorder){
                                                    $order_number = $lastorder[0]->order_number + 1;
                                                }
                                                $banner_by_section = $banner->getNewRow('wc_banner_by_section');
                                                $banner_by_section->banner_id = $last_banner_id;
                                                $banner_by_section->section_id = $another_section_id;
                                                $banner_by_section->area_banner_id = $formData['area'];
                                                $banner_by_section->order_number = $order_number;
                                                $banner->save('wc_banner_by_section',$banner_by_section);
                                            }
                                        }
                                        
                                        
					$arr_success = array('section_id'=>$section_id);
                                        
                                        echo json_encode($arr_success);
                                        //success message
                                        $this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
					
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

    							$banner->updateorder($banner_id, $formData['section_id'], $count);
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
    	
    	$obj = new Banners_Model_Banners();
        
        $deleted = null;
        
        if($section_id == 'all'){
            $obj->delete('wc_banner_counts', array('banner_id'=>$banner_id));
            $obj->delete('wc_banner_by_section', array('banner_id'=>$banner_id));
            $deleted = $obj->delete('wc_banner', array('id'=>$banner_id));
        }else{
            $deleted = $obj->delete('wc_banner_by_section', array('banner_id'=>$banner_id, 'section_id'=>$section_id));
        }
    	if($deleted)
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
    		$banner_obj = new Banners_Model_Banners();
    		
    		
    		if($formData['text']==''){
                    if($formData['section']==0)
                        $banner_item = $banner_obj->getbannersforvinculation($formData['section_tree_id'], '!=');
                    else
                        $banner_item = $banner_obj->getbannersforvinculation($formData['section'], '=');
                }
                else
                {
                    if($formData['section']==0)
                        $banner_item = $banner_obj->getbannersforvinculation($formData['section_tree_id'], '!=');
                    else
                        $banner_item = $banner_obj->getbannersforvinculation($formData['section'], '=');
                }
                if($banner_item){
                        foreach ($banner_item as &$bi){

                                $bi->section_id = $section_data['id'];
                                $bi->section_name =$section_data['internal_name'];
                                $banners_list[] = get_object_vars($bi);

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
    			$banner_array = $banner->personalized_find('wc_banner', array(array('name', '=', $bannername),array('id', '!=', $id)));
    		else
    			$banner_array = $banner->personalized_find('wc_banner', array(array('name', '=', $bannername)));
    
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
