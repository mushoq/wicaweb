<?php
/**
 * Website Controller
 * This file has the fucntion that allow to set and edit the configurations of a website
 *
 * @category   WicaWeb
 * @package    Core_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   Esteban 
 * @version    1.0
 * 
 */
class Core_Website_WebsiteController extends Zend_Controller_Action
{
	/**
	 * 
	 * Initialize the controller.
	 */
    public function init()
    {
    	//session
    	$id = New Zend_Session_Namespace('id');
    	
       	$profile_access = array();		
       	
		if($id->user_modules_actions){
			foreach ($id->user_modules_actions as $k => $mod)
			{
				if($mod->module_id == '1')
				{
					$profile_access[$mod->action_id] = array('action'=> $mod->action_name, 'title'=>$mod->action_title);
				}
			}			
		}

		$this->view->website_links = $profile_access;
    }
	
    /**
     * Generates a list of all available websites
     */
    public function indexAction()
    {
    	$website = new Core_Model_Website();
    	$this->view->websites = $website->find('wc_website');
    }
    
    /**
     * Creates a New Website Configuration.
     */
    public function newAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	
    	$request = $this->getRequest();
    	
    	$form = New Core_Form_Website_Website();
    	$form->setMethod('post');
    	
    	//check for defualt websites
    	$website =  new Core_Model_Website();
    	$websites_helper = $website->personalized_find('wc_website',array(array('default_page','=','yes')));
    	if($websites_helper && count($websites_helper)>0){
    		$default_page = 'no';
    	}
    	else{
    		$default_page = 'yes';
    	}
    	
    	//get all templates
    	$template_model = new Core_Model_WebsiteTemplate();
    	$aux = $template_model->find('wc_website_template');
    	$this->view->template_list = $aux;
    	    	
    	
		//after submit the form    	
    	if ($this->getRequest()->isPost()) {
    		$formData = $this->getRequest()->getPost();
    		if ($form->isValid($formData)) {
    			//Recover the uploaded values
    			$uploadedData = $form->getValues();

    			//MOVE AND RENAME FILES
    			if($uploadedData['logo']){
    				$logo = GlobalFunctions::uploadFiles($uploadedData['logo'], APPLICATION_PATH. '/../public/uploads/website/');
    			}
    			else{
    				$logo = NULL;
    			}
                        
                        if($uploadedData['auspiciante']){
    				$auspiciante = GlobalFunctions::uploadFiles($uploadedData['auspiciante'], APPLICATION_PATH. '/../public/uploads/website/');
    			}
    			else{
    				$auspiciante = NULL;
    			}
    			 
    			if($uploadedData['icon']){
    				$icon = GlobalFunctions::uploadFiles($uploadedData['icon'], APPLICATION_PATH. '/../public/uploads/website/');
    			}
    			else{
    				$icon = NULL;
    			}
    			 
    			if($uploadedData['watermark']){
    				$watermark = GlobalFunctions::uploadFiles($uploadedData['watermark'], APPLICATION_PATH. '/../public/uploads/website/');
    			}
    			else{
    				$watermark = NULL;
    			}
    			
    			// END RENAME
    			
    			//save data
    			$website =  new Core_Model_Website();
    			$website_obj = $website->getNewRow('wc_website');
    			
    			$website_obj->language_id = $formData['language_id'];
    			$website_obj->template_id = $formData['template_id'];
    			$website_obj->name = GlobalFunctions::value_cleaner($formData['name']);
    			$website_obj->description = GlobalFunctions::value_cleaner($formData['description']);
    			$website_obj->keywords = GlobalFunctions::value_cleaner($formData['keywords']);
    			$website_obj->website_url = GlobalFunctions::value_cleaner($formData['website_url']);
                        
    			$website_obj->default_page = $default_page;
    			$website_obj->logo = $logo;
                       
    			$website_obj->icon = $icon;
    			$website_obj->info_email = $formData['info_email'];
    			$website_obj->time_zone = $formData['time_zone'];
    			$website_obj->date_format = $formData['date_format'];
    			$website_obj->hour_format = $formData['hour_format'];
    			$website_obj->number_format = $formData['number_format'];
    			$website_obj->copyright = GlobalFunctions::value_cleaner($formData['copyright']);
    			$website_obj->publication_approve = $formData['publication_approve'];
    			$website_obj->prints = $formData['prints'];
    			$website_obj->friendly_url = $formData['friendly_url'];
    			$website_obj->tiny_url = $formData['tiny_url'];
    			$website_obj->log = $formData['log'];
    			$website_obj->sitemap_level = $formData['sitemap_level'];
    			$website_obj->dictionary = $formData['dictionary'];
    			$website_obj->private_section = $formData['private_section'];
    			$website_obj->section_expiration = $formData['section_expiration'];
    			$website_obj->section_expiration_time = $formData['section_expiration_time'];
    			$website_obj->section_author = $formData['section_author'];
    			$website_obj->section_feature = $formData['section_feature'];
    			$website_obj->section_highlight = $formData['section_highlight'];
    			if(isset($formData['section_comments_type']) && $formData['section_comments_type'] != 'none' && $formData['section_comments_type'] != ''){
    				$website_obj->section_comments_type = $formData['section_comments_type'];
    			}	
    			else{
    				$website_obj->section_comments_type ='none';
    			}
    			
    			$website_obj->section_comments = $formData['section_comments'];
    			$website_obj->section_images_number = $formData['section_images_number'];
    			if($formData['section_storage'])
    				$website_obj->section_storage = $formData['section_storage'];
    			else
    				$website_obj->section_storage = 'no';
    			$website_obj->section_rss = $formData['section_rss'];
    			$website_obj->watermark = $watermark;
    			$website_obj->smtp_hostname = GlobalFunctions::value_cleaner($formData['smtp_hostname']);
    			$website_obj->smtp_port = $formData['smtp_port'];
    			$website_obj->smtp_username = GlobalFunctions::value_cleaner($formData['smtp_username']);
    			$website_obj->smtp_password = GlobalFunctions::value_cleaner($formData['smtp_password']);
    			$website_obj->analytics = GlobalFunctions::value_cleaner($formData['analytics']);
    			$website_obj->watermark_pos = GlobalFunctions::value_cleaner($formData['watermark_pos']);
    			
    			if($formData['max_height'])
    				$website_obj->max_height = GlobalFunctions::value_cleaner($formData['max_height']);
    			else 
    				$website_obj->max_height = 1000;
    			
    			if($formData['max_width'])
    				$website_obj->max_width = GlobalFunctions::value_cleaner($formData['max_width']);    			
    			else
    				$website_obj->max_width = 1000;

    			// Save data							
				$saved_website = $website->save('wc_website',$website_obj);

				if($saved_website){
					//Website states
					$website_state =  new Core_Model_WebsiteState('wc_website_state');
					
					//MOVE AND RENAME IMAGE FILES
					if($uploadedData['offline_image']){
						$offline_image = GlobalFunctions::uploadFiles($uploadedData['offline_image'], APPLICATION_PATH. '/../public/uploads/website/');
					}
					else{
						$offline_image = NULL;
					}
					
					if($uploadedData['coming_soon_image']){
						$coming_soon_image = GlobalFunctions::uploadFiles($uploadedData['coming_soon_image'], APPLICATION_PATH. '/../public/uploads/website/');
					}
					else{
						$coming_soon_image = NULL;
					}
									
					//Save the 3 states of a website
					for($i=1; $i<4; $i++){
						$website_state_obj = $website_state->getNewRow('wc_website_state');
						$website_state_obj->website_id = $saved_website['id'];
	
						switch($i){
							case 1://online
								$website_state_obj->type = 'online';
								$website_state_obj->display_text = NULL;
								break;
							case 2://offline
								$website_state_obj->type = 'offline';
								$website_state_obj->display_text = GlobalFunctions::value_cleaner($formData['offline_text']);
								$website_state_obj->image = $offline_image;
								break;
							case 3://coming soon
								$website_state_obj->type = 'comingsoon';
								$website_state_obj->display_text = GlobalFunctions::value_cleaner($formData['coming_soon_text']);
								$website_state_obj->image = $coming_soon_image;
								break;	
						}
						//setting the current state of the website
						switch($formData['website_status']){
							case 'online':
								if($i==1)
									$website_state_obj->status = 'active';
								else
									$website_state_obj->status = 'inactive';
								break;
							case 'offline':
								if($i==2)
									$website_state_obj->status = 'active';
								else
									$website_state_obj->status = 'inactive';
								break;
							case 'comingsoon':
								if($i==3)
									$website_state_obj->status = 'active';
								else
									$website_state_obj->status = 'inactive';
								break;
						}
						$save_status[] = $website_state->save('wc_website_state', $website_state_obj);
					}
				}
				
				//Removing all temporal files
				GlobalFunctions::removeOldFiles($uploadedData['logo'], APPLICATION_PATH. '/../public/uploads/tmp/');
                                GlobalFunctions::removeOldFiles($uploadedData['auspiciante'], APPLICATION_PATH. '/../public/uploads/tmp/');
				GlobalFunctions::removeOldFiles($uploadedData['icon'], APPLICATION_PATH. '/../public/uploads/tmp/');
				GlobalFunctions::removeOldFiles($uploadedData['watermark'], APPLICATION_PATH. '/../public/uploads/tmp/');
				GlobalFunctions::removeOldFiles($uploadedData['offline_image'], APPLICATION_PATH. '/../public/uploads/tmp/');
				GlobalFunctions::removeOldFiles($uploadedData['coming_soon_image'], APPLICATION_PATH. '/../public/uploads/tmp/');
				
				if($saved_website && count($save_status)==3)
				{
					//add the session website id to the new website created
					$new = New Zend_Session_Namespace('new_website');
					if($new->website === TRUE){
						$id = New Zend_Session_Namespace('id');
						$id->website_id = $saved_website['id'];
						$id->website_name_info = utf8_encode($website_obj->name);
						

						Zend_Session::namespaceUnset('new_website');

							
					}
					
					$id = New Zend_Session_Namespace('id');
					$id->template_id = $formData['template_id'];
					
					$create_section = Core_Website_WebsiteController::createFirstSection($saved_website['id']);
					if(!$create_section){
						//if the website info and the states are correctly saved. Shows the success message and redirects to the website list
						$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Errors in saving data')));
					}
					else{ 
						//if the website info and the states are correctly saved. Shows the success message and redirects to the website list
						$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
					}
					$this->_helper->redirector('index','website_website','core');
				}
				else{
					$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in saving data')));
					$this->_helper->redirector('new','website_website','core');
				}
    		}
    		else
    		{
    			//Adding Error Messages
    			$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Invalid Form')));
    			$this->_helper->redirector('new','website_website','core');
    		}
    		 
    	}
    	
    	$this->view->form = $form;
    }
    
    /**
     * Edit Website Configuration.
     * Load a Form with all existing data
     */
    public function editAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	
    	$website = new Core_Model_Website();
    
    	$form = New Core_Form_Website_Website();
    	
    	$id = $this->_getParam('id');
    	
    	if(!$id){
    		$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Website access denied')));
    		$this->_helper->redirector('index','website_website','core');
    	}

    	$data = $website->find('wc_website', array('id'=>$id)); //get the selected website data
    	
    	if(!$data)
    	{
    		$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Website access denied')));
    		$this->_helper->redirector('index','website_website','core');
    	}
    	
    	$website_state_model = new Core_Model_WebsiteState(); 
    	$website_state = $website_state_model->find('wc_website_state',array('website_id'=>$id)); //get the states data		
    
    	$arr_data = get_object_vars($data[0]); //make an array of the object data

    	if($arr_data['section_comments'] && $arr_data['section_comments_type']!= 'none')
    		$arr_data['section_comments_management'] = 'yes';
    	else
    		$arr_data['section_comments_management'] = 'no';

    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	
    	//load the current images
    	if($arr_data['logo']){
			$form->img_logo->setImage('/uploads/website/'.$arr_data['logo']);
			$form->img_logo->setLabel($lang->translate('Current Image Logo').':');
    	}
        
        if($arr_data['auspiciante']){
			$form->img_auspiciante->setImage('/uploads/website/'.$arr_data['auspiciante']);
			$form->img_auspiciante->setLabel($lang->translate('Current Image Auspiciante').':');
    	}
        
        
		
    	if($arr_data['icon']){
			$form->img_icon->setImage('/uploads/website/'.$arr_data['icon']);
			$form->img_icon->setLabel($lang->translate('Current Image Icon').':');
    	}
    	
		if($arr_data['watermark']){
			$form->img_watermark->setImage('/uploads/website/'.$arr_data['watermark']);
			$form->img_watermark->setLabel($lang->translate('Current Image Watermark').':');
		}
		
		//website_state data
		if($website_state){
			foreach($website_state as $wb){
				if($wb->type=='online'){
					$id_online = $wb->id;
				}
				//offline state data
				if($wb->type=='offline'){
					$id_offline = $wb->id;
					$arr_data['offline_text'] = $wb->display_text;
					$arr_data['offline_image'] = $wb->image;
					//load current state image
					if($wb->image){
						$form->img_offline->setImage('/uploads/website/'.$wb->image);
						$form->img_offline->setLabel($lang->translate('Current Image Offline').':');
					}
					
				}
				//coming soon state data
				if($wb->type=='comingsoon'){
					$id_coming_soon = $wb->id;
					$arr_data['coming_soon_text'] = $wb->display_text;
					$arr_data['coming_soon_image'] = $wb->image;
					//load current state image
					if($wb->image){
						$form->img_coming_soon->setImage('/uploads/website/'.$wb->image);
						$form->img_coming_soon->setLabel($lang->translate('Current Image Comming Soon').':');
					}
				}
				//set the current website state
				if($wb->status=='active')
					$arr_data['website_status'] = $wb->type;
			}
		}
                        $arr_data['competition_time'] = substr($arr_data['competition_date'], -8);
                        $arr_data['competition_date'] = GlobalFunctions::getFormattedDate($arr_data['competition_date']);
			
                
                
		//populate the form
		$form->populate($arr_data);
    	$this->view->form = $form;
    	$this->view->section_img_num = $arr_data['section_images_number'];
    	$this->view->sitemap_level = $arr_data['sitemap_level'];
    	
    	//get all templates
    	$template_model = new Core_Model_WebsiteTemplate();
    	$aux = $template_model->find('wc_website_template');
    	$this->view->template_list = $aux;
    	$this->view->selected_template = $arr_data['template_id'];
    	
    	//watermark info
    	$this->view->watermark = $arr_data['watermark'];
   
    	if ($this->getRequest()->isPost())
    	{
    		$formData  = $this->_request->getPost();
    		//recover uploaded values
    		$uploadedData = $form->getValues();
    		
    		if ($form->isValid($formData))
    		{
    			//MOVE AND RENAME FILES
    			if($uploadedData['logo']){
    				$logo = GlobalFunctions::uploadFiles($uploadedData['logo'], APPLICATION_PATH. '/../public/uploads/website/');
    				if($arr_data['logo']){
    					//delete old file if the image is replaced
    					if(!GlobalFunctions::removeOldFiles($arr_data['logo'], APPLICATION_PATH. '/../public/uploads/website/')){
    						throw new Zend_Exception("CUSTOM_EXCEPTION:FILE NOT DELETED.");
    					}
    				}
    			}
    			else{ //if there is no uploaded image it sets the current image or null in case that any image was uploaded before
    				if($arr_data['logo'] && $formData['deleted_logo']==''){
    					$logo = $arr_data['logo'];
    				}
    				else{
    					$logo = NULL;
    				}
    			}
                        
                        if($uploadedData['auspiciante']){
    				$auspiciante = GlobalFunctions::uploadFiles($uploadedData['auspiciante'], APPLICATION_PATH. '/../public/uploads/website/');
    				if($arr_data['auspiciante']){
    					//delete old file if the image is replaced
    					if(!GlobalFunctions::removeOldFiles($arr_data['auspiciante'], APPLICATION_PATH. '/../public/uploads/website/')){
    						throw new Zend_Exception("CUSTOM_EXCEPTION:FILE NOT DELETED.");
    					}
    				}
    			}
    			else{ //if there is no uploaded image it sets the current image or null in case that any image was uploaded before
    				if($arr_data['auspiciante']){
    					$auspiciante = $arr_data['auspiciante'];
    				}
    				else{
    					$auspiciante = NULL;
    				}
    			}
    			
    			if($uploadedData['icon']){
    				$icon = GlobalFunctions::uploadFiles($uploadedData['icon'], APPLICATION_PATH. '/../public/uploads/website/');
    				if($arr_data['icon']){
    					//delete old file if the image is replaced
    					if(!GlobalFunctions::removeOldFiles($arr_data['icon'], APPLICATION_PATH. '/../public/uploads/website/')){
    						throw new Zend_Exception("CUSTOM_EXCEPTION:FILE NOT DELETED.");
    					}
    				}
    			}
    			else{//if there is no uploaded image it sets the current image or null in case that any image was uploaded before
    				if($arr_data['icon'] && $formData['deleted_icon']==''){ 
    					$icon = $arr_data['icon'];
    				}
    				else{
    					$icon = NULL;
    				}
    			}
    			
    			if($uploadedData['watermark']){
    				$watermark = GlobalFunctions::uploadFiles($uploadedData['watermark'], APPLICATION_PATH. '/../public/uploads/website/');
    				if($arr_data['watermark']){
    					//delete old file if the image is replaced
    					if(!GlobalFunctions::removeOldFiles($arr_data['watermark'], APPLICATION_PATH. '/../public/uploads/website/')){
    						throw new Zend_Exception("CUSTOM_EXCEPTION:FILE NOT DELETED.");
    					}
    				}
    			}
    			else{ //if there is no uploaded image it sets the current image or null in case that any image was uploaded before
    				if($arr_data['watermark']){
    					$watermark = $arr_data['watermark'];
    				}
    				else{
    					$watermark = NULL;
    				}
    				
    			}
    			// END RENAME
    			
    			//set Data
    			$website =  new Core_Model_Website();
    			$website_obj = $website->getNewRow('wc_website');
    			$website_obj->id = $formData['id'];
    			$website_obj->language_id = $formData['language_id'];
    			$website_obj->template_id = $formData['template_id'];
    			$website_obj->name = GlobalFunctions::value_cleaner($formData['name']);
    			$website_obj->description = GlobalFunctions::value_cleaner($formData['description']);
    			$website_obj->keywords = GlobalFunctions::value_cleaner($formData['keywords']);
    			$website_obj->website_url = GlobalFunctions::value_cleaner($formData['website_url']);
                        
                        
    			$website_obj->default_page = GlobalFunctions::value_cleaner($arr_data['default_page']);
    			
    			if($logo)
    				$website_obj->logo = $logo;
    			else
    				$website_obj->logo = NULL;
    			
                        
                        
    			if($icon)
    				$website_obj->icon = $icon;
    			else
    				$website_obj->icon = NULL;
    			
    			$website_obj->info_email = $formData['info_email'];
    			$website_obj->time_zone = $formData['time_zone'];
    			$website_obj->date_format = $formData['date_format'];
    			$website_obj->hour_format = $formData['hour_format'];
    			$website_obj->number_format = $formData['number_format'];
    			$website_obj->copyright = GlobalFunctions::value_cleaner($formData['copyright']);
    			$website_obj->publication_approve = $formData['publication_approve'];
    			$website_obj->prints = $formData['prints'];
    			$website_obj->friendly_url = $formData['friendly_url'];
    			$website_obj->tiny_url = $formData['tiny_url'];
    			$website_obj->log = $formData['log'];
    			$website_obj->sitemap_level = $formData['sitemap_level'];
    			$website_obj->dictionary = $formData['dictionary'];
    			$website_obj->private_section = $formData['private_section'];
    			$website_obj->section_expiration = $formData['section_expiration'];
    			$website_obj->section_expiration_time = $formData['section_expiration_time'];
    			$website_obj->section_author = $formData['section_author'];
    			$website_obj->section_feature = $formData['section_feature'];
    			$website_obj->section_highlight = $formData['section_highlight'];
    			$website_obj->section_comments_type = $formData['section_comments_type'];
    			
    			if(isset($formData['section_comments_type']) && $formData['section_comments_type'] != 'none' && $formData['section_comments_type'] != '')
    				$website_obj->section_comments_type = $formData['section_comments_type'];
    			else
    				$website_obj->section_comments_type ='none';
    			
    			$website_obj->section_comments = $formData['section_comments'];
    			$website_obj->section_images_number = $formData['section_images_number'];
    			
    			if($formData['section_storage'])
    				$website_obj->section_storage = $formData['section_storage'];
    			else
    				$website_obj->section_storage = 'no';  
    			  			
    			$website_obj->section_rss = $formData['section_rss'];

    			if($watermark)
    				$website_obj->watermark = $watermark;
    			else
    				$website_obj->watermark = NULL;
    			
    			$website_obj->smtp_hostname = GlobalFunctions::value_cleaner($formData['smtp_hostname']);
    			$website_obj->smtp_port = $formData['smtp_port'];
    			$website_obj->smtp_username = GlobalFunctions::value_cleaner($formData['smtp_username']);
    			$website_obj->smtp_password = GlobalFunctions::value_cleaner($formData['smtp_password']);
    			$website_obj->analytics = GlobalFunctions::value_cleaner($formData['analytics']);
    			$website_obj->watermark_pos = GlobalFunctions::value_cleaner($formData['watermark_pos']);
    			
    			if($formData['max_height'])
    				$website_obj->max_height = GlobalFunctions::value_cleaner($formData['max_height']);
    			else 
    				$website_obj->max_height = 1000;
    			
    			if($formData['max_width'])
    				$website_obj->max_width = GlobalFunctions::value_cleaner($formData['max_width']);    			
    			else
    				$website_obj->max_width = 1000;
    			
    			// Save data							
				$saved_website = $website->save('wc_website',$website_obj);
				
				if($saved_website){//after the website is saved, set the states of it
					//Website states
					$website_state =  new Core_Model_WebsiteState('wc_website_state');
					
					//MOVE AND RENAME IMAGE FILES
					if($uploadedData['offline_image']){
						$offline_image = GlobalFunctions::uploadFiles($uploadedData['offline_image'], APPLICATION_PATH. '/../public/uploads/website/');
						if($arr_data['offline_image']){
							//delete old file if the image is replaced
							if(!GlobalFunctions::removeOldFiles($arr_data['offline_image'], APPLICATION_PATH. '/../public/uploads/website/')){
								throw new Zend_Exception("CUSTOM_EXCEPTION:FILE NOT DELETED.");
							}
						}
					}
					else{
						if($arr_data['offline_image']){
							$offline_image = $arr_data['offline_image'];
						}
						else{
							$offline_image = NULL;
						}
					}
					
					if($uploadedData['coming_soon_image']){
						$coming_soon_image = GlobalFunctions::uploadFiles($uploadedData['coming_soon_image'], APPLICATION_PATH. '/../public/uploads/website/');
						if($arr_data['coming_soon_image']){
							//delete old file if the image is replaced
							if(!GlobalFunctions::removeOldFiles($arr_data['coming_soon_image'], APPLICATION_PATH. '/../public/uploads/website/')){
								throw new Zend_Exception("CUSTOM_EXCEPTION:FILE NOT DELETED.");
							}
						}
					}
					else{
						if($arr_data['coming_soon_image']){
							$coming_soon_image = $arr_data['coming_soon_image'];
						}
						else{
							$coming_soon_image = NULL;
						}
					}
									
					//Save the 3 states of a website
					for($i=1; $i<4; $i++){
						$website_state_obj = $website_state->getNewRow('wc_website_state');
						$website_state_obj->website_id = $saved_website['id'];
	
						switch($i){
							case 1: //online
								$website_state_obj->id = $id_online;
								$website_state_obj->type = 'online';
								$website_state_obj->display_text = NULL;
								break;
							case 2: //offline
								$website_state_obj->id = $id_offline;
								$website_state_obj->type = 'offline';
								$website_state_obj->display_text = GlobalFunctions::value_cleaner($formData['offline_text']);
								$website_state_obj->image = $offline_image;
								break;
							case 3:// coming soon
								$website_state_obj->id = $id_coming_soon;
								$website_state_obj->type = 'comingsoon';
								$website_state_obj->display_text = GlobalFunctions::value_cleaner($formData['coming_soon_text']);
								$website_state_obj->image = $coming_soon_image;
								break;	
						}
						//setting the current state of the website
						switch($formData['website_status']){
							case 'online':
								if($i==1)
									$website_state_obj->status = 'active';
								else
									$website_state_obj->status = 'inactive';
								break;
							case 'offline':
								if($i==2)
									$website_state_obj->status = 'active';
								else
									$website_state_obj->status = 'inactive';
								break;
							case 'comingsoon':
								if($i==3)
									$website_state_obj->status = 'active';
								else
									$website_state_obj->status = 'inactive';
								break;
						}
						$save_status[] = $website_state->save('wc_website_state', $website_state_obj);
					}
				}
				
				//Removing all temporal files
				GlobalFunctions::removeOldFiles($uploadedData['logo'], APPLICATION_PATH. '/../public/uploads/tmp/');
                                GlobalFunctions::removeOldFiles($uploadedData['auspiciante'], APPLICATION_PATH. '/../public/uploads/tmp/');
				GlobalFunctions::removeOldFiles($uploadedData['icon'], APPLICATION_PATH. '/../public/uploads/tmp/');
				GlobalFunctions::removeOldFiles($uploadedData['watermark'], APPLICATION_PATH. '/../public/uploads/tmp/');
				GlobalFunctions::removeOldFiles($uploadedData['offline_image'], APPLICATION_PATH. '/../public/uploads/tmp/');
				GlobalFunctions::removeOldFiles($uploadedData['coming_soon_image'], APPLICATION_PATH. '/../public/uploads/tmp/');
				
				//if the website info and the website state info was correctly saved, then return success message and return to website list				
				if($saved_website && count($save_status)==3)
				{
					$id = New Zend_Session_Namespace('id');
					if($saved_website['id']==$id->website_id)
						$id->website_name_info = utf8_encode($website_obj->name);
					
					
					//Get the website language abbreviation
						
					$language = GlobalFunctions::getLanguageAbbreviationOfWebsite($id->website_id);
					
					// Translate to website language
					Zend_Loader::loadClass('Zend_Translate');
					$translate = new Zend_Translate(
							'array',
							APPLICATION_PATH.'/configs/languages/',
							'es',
							array('scan' => Zend_Translate::LOCALE_FILENAME)
					);
						
					$locale = new Zend_Locale();
					$locale->setLocale($language);
						
					// setting the right locale
					if ($translate->isAvailable($locale->getLanguage())) {
						$translate->setLocale($locale);
					} else {
						$translate->setLocale('es');
					}
					Zend_Registry::set('Zend_Translate', $translate);

					
					$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
					$this->_helper->redirector('index','website_website','core');
				}
				else{
					$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in saving data')));
					$this->_helper->redirector('edit','website_website','core');
				}
    			
    		}
    		else
    		{
    			//Adding Error Messages
    			$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Invalid Form')));
    			$this->_helper->redirector('edit','website_website','core',array('id'=>$id));
    		}
    	}
    
    }
    
    /**
     * Marks a Website as the default website
     * and marks as not default to all other sites 
     */
    public function defaultpageAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	
    	$website = new Core_Model_Website();
    	$id = $this->_getParam('id');
   		
    	$stored_website_data = $website->find('wc_website', array('id'=>$id));
    	$website_obj = $website->getNewRow('wc_website');
    	$website_obj->id = $stored_website_data[0]->id;
    	$website_obj->language_id = $stored_website_data[0]->language_id;
    	$website_obj->template_id = $stored_website_data[0]->template_id;
    	$website_obj->name = GlobalFunctions::value_cleaner($stored_website_data[0]->name);
    	$website_obj->description = GlobalFunctions::value_cleaner($stored_website_data[0]->description);
    	$website_obj->keywords = GlobalFunctions::value_cleaner($stored_website_data[0]->keywords);
    	$website_obj->website_url = GlobalFunctions::value_cleaner($stored_website_data[0]->website_url);
        
    	$website_obj->logo = GlobalFunctions::value_cleaner($stored_website_data[0]->logo);
        
    	$website_obj->icon = GlobalFunctions::value_cleaner($stored_website_data[0]->icon);
    	$website_obj->info_email = GlobalFunctions::value_cleaner($stored_website_data[0]->info_email);
    	$website_obj->time_zone = $stored_website_data[0]->time_zone;
    	$website_obj->date_format = $stored_website_data[0]->date_format;
    	$website_obj->hour_format = $stored_website_data[0]->hour_format;
    	$website_obj->number_format = $stored_website_data[0]->number_format;
    	$website_obj->copyright = GlobalFunctions::value_cleaner($stored_website_data[0]->copyright);
    	$website_obj->publication_approve = $stored_website_data[0]->publication_approve;
    	$website_obj->prints = $stored_website_data[0]->prints;
    	$website_obj->friendly_url = $stored_website_data[0]->friendly_url;
    	$website_obj->tiny_url = $stored_website_data[0]->tiny_url;
    	$website_obj->log = $stored_website_data[0]->log;
    	$website_obj->sitemap_level = $stored_website_data[0]->sitemap_level;
    	$website_obj->dictionary = $stored_website_data[0]->dictionary;
    	$website_obj->private_section = $stored_website_data[0]->private_section;
    	$website_obj->section_expiration = $stored_website_data[0]->section_expiration;
    	$website_obj->section_expiration_time = $stored_website_data[0]->section_expiration_time;
    	$website_obj->section_author = $stored_website_data[0]->section_author;
    	$website_obj->section_feature = $stored_website_data[0]->section_feature;
    	$website_obj->section_highlight = $stored_website_data[0]->section_highlight;
    	$website_obj->section_comments_type = $stored_website_data[0]->section_comments_type;
    	$website_obj->section_comments_type =$stored_website_data[0]->section_comments_type;
    	$website_obj->section_comments = $stored_website_data[0]->section_comments;
    	$website_obj->section_images_number = $stored_website_data[0]->section_images_number;
    	
    	if($stored_website_data[0]->section_storage)
    		$website_obj->section_storage = $stored_website_data[0]->section_storage;
    	else
    		$website_obj->section_storage = 'no';    	
    	
    	$website_obj->section_rss = $stored_website_data[0]->section_rss;
    	$website_obj->watermark = GlobalFunctions::value_cleaner($stored_website_data[0]->watermark);
    	$website_obj->smtp_hostname = GlobalFunctions::value_cleaner($stored_website_data[0]->smtp_hostname);
    	$website_obj->smtp_port = $stored_website_data[0]->smtp_port;
    	$website_obj->smtp_username = GlobalFunctions::value_cleaner($stored_website_data[0]->smtp_username);
    	$website_obj->smtp_password = GlobalFunctions::value_cleaner($stored_website_data[0]->smtp_password);
    	$website_obj->analytics = GlobalFunctions::value_cleaner($stored_website_data[0]->analytics);
    	$website_obj->max_height = GlobalFunctions::value_cleaner($stored_website_data[0]->max_height);
    	$website_obj->max_width = GlobalFunctions::value_cleaner($stored_website_data[0]->max_width);
    	$website_obj->watermark_pos = GlobalFunctions::value_cleaner($stored_website_data[0]->watermark_pos);
    	
    	$website_obj->default_page = 'yes';
    	$saved_website = $website->save('wc_website',$website_obj);
    	
    	if($saved_website){
    		$deactivate_website_obj_arr = $website->personalized_find('wc_website',array(array('id','!=',$id)));
    		if($deactivate_website_obj_arr && count($deactivate_website_obj_arr)>0){
    			foreach($deactivate_website_obj_arr as $k=>$deactivate){

    				$website_deactivate_obj = $website->getNewRow('wc_website');
    				$website_deactivate_obj->id = $deactivate->id;
    				$website_deactivate_obj->language_id = $deactivate->language_id;
    				$website_deactivate_obj->template_id = $deactivate->template_id;
    				
    				$website_deactivate_obj->name = GlobalFunctions::value_cleaner($deactivate->name);
					$website_deactivate_obj->description = GlobalFunctions::value_cleaner($deactivate->description);
					$website_deactivate_obj->keywords = GlobalFunctions::value_cleaner($deactivate->keywords);
					$website_deactivate_obj->website_url = GlobalFunctions::value_cleaner($deactivate->website_url);
                                        
					$website_deactivate_obj->logo = GlobalFunctions::value_cleaner($deactivate->logo);
                                        
					$website_deactivate_obj->icon = GlobalFunctions::value_cleaner($deactivate->icon);
					$website_deactivate_obj->info_email = GlobalFunctions::value_cleaner($deactivate->info_email);
					$website_deactivate_obj->time_zone = $deactivate->time_zone;
					$website_deactivate_obj->date_format = $deactivate->date_format;
					$website_deactivate_obj->hour_format = $deactivate->hour_format;
					$website_deactivate_obj->number_format = $deactivate->number_format;
					$website_deactivate_obj->copyright = GlobalFunctions::value_cleaner($deactivate->copyright);
					$website_deactivate_obj->publication_approve = $deactivate->publication_approve;
					$website_deactivate_obj->prints = $deactivate->prints;
					$website_deactivate_obj->friendly_url = $deactivate->friendly_url;
					$website_deactivate_obj->tiny_url = $deactivate->tiny_url;
					$website_deactivate_obj->log = $deactivate->log;
					$website_deactivate_obj->sitemap_level = $deactivate->sitemap_level;
					$website_deactivate_obj->dictionary = $deactivate->dictionary;
					$website_deactivate_obj->private_section = $deactivate->private_section;
					$website_deactivate_obj->section_expiration = $deactivate->section_expiration;
					$website_deactivate_obj->section_expiration_time = $deactivate->section_expiration_time;
					$website_deactivate_obj->section_author = $deactivate->section_author;
					$website_deactivate_obj->section_feature = $deactivate->section_feature;
					$website_deactivate_obj->section_highlight = $deactivate->section_highlight;
					$website_deactivate_obj->section_comments_type = $deactivate->section_comments_type;
					$website_deactivate_obj->section_comments_type =$deactivate->section_comments_type;
					$website_deactivate_obj->section_comments = $deactivate->section_comments;
					$website_deactivate_obj->section_images_number = $deactivate->section_images_number;
					
					if($deactivate->section_storage)
						$website_deactivate_obj->section_storage = $deactivate->section_storage;
					else
						$website_deactivate_obj->section_storage = 'no';	
									
					$website_deactivate_obj->section_rss = $deactivate->section_rss;
					$website_deactivate_obj->watermark = GlobalFunctions::value_cleaner($deactivate->watermark);
					$website_deactivate_obj->smtp_hostname = GlobalFunctions::value_cleaner($deactivate->smtp_hostname);
					$website_deactivate_obj->smtp_port = $deactivate->smtp_port;
					$website_deactivate_obj->smtp_username = GlobalFunctions::value_cleaner($deactivate->smtp_username);
					$website_deactivate_obj->smtp_password = GlobalFunctions::value_cleaner($deactivate->smtp_password);
					$website_deactivate_obj->analytics = GlobalFunctions::value_cleaner($deactivate->analytics);
					$website_deactivate_obj->max_height = GlobalFunctions::value_cleaner($deactivate->max_height);
					$website_deactivate_obj->max_width = GlobalFunctions::value_cleaner($deactivate->max_width);
					$website_deactivate_obj->watermark_pos = GlobalFunctions::value_cleaner($deactivate->watermark_pos);
					
    				$website_deactivate_obj->default_page = 'no';
    				$saved_deactivate_website = $website->save('wc_website',$website_deactivate_obj);    				
    				
    				if(!$saved_deactivate_website){
    					throw new Zend_Exception("CUSTOM_EXCEPTION: WEBSITE NOT SET DEFAULT: OFF");
    				}
    				
    			}
    		}
    	}
    	$this->_helper->viewRenderer->setNoRender();
    	//adding message
    	$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
    	$this->_helper->redirector ( 'index','website_website','core' ); // back to login page
    }
    
    public static function createFirstSection($website_id){
    	
    	//create section
    	$section = new Core_Model_Section();
    	
    	//searchs for stored session data
    	$id = New Zend_Session_Namespace('id');
    	$template_id = $id->template_id;
    	$area = new Core_Model_Area();
    	$aux = $area->personalized_find('wc_area',array(array('name','LIKE','wica_area_content'),array('template_id','=',$template_id)));
    	$area_id = $aux[0]->id;

    	
    	//save section
    	$section_obj = $section->getNewRow('wc_section');
    	$section_obj->section_parent_id = NULL;
    	$section_obj->website_id = $website_id;
    	
    	$section_obj->section_template_id = 1;
    	$section_obj->internal_name =  GlobalFunctions::value_cleaner('HOME'.'_'.$website_id);
    	$section_obj->title = GlobalFunctions::value_cleaner('HOME');
    	$section_obj->subtitle = GlobalFunctions::value_cleaner('');
    	$section_obj->title_browser = GlobalFunctions::value_cleaner('HOME');
    	$section_obj->synopsis = GlobalFunctions::value_cleaner('HOME');
    	$section_obj->keywords = GlobalFunctions::value_cleaner('');
    	$section_obj->type = GlobalFunctions::value_cleaner('public');
    	
    	$section_obj->created_by_id = $id->user_id;
    	$section_obj->updated_by_id = NULL;
    	$section_obj->creation_date = date('Y-m-d h%i%s');
    	$section_obj->last_update_date = NULL;
    	$section_obj->order_number = NULL;
    	
    	$section_obj->approved = GlobalFunctions::value_cleaner('yes');
    	$section_obj->author = GlobalFunctions::value_cleaner('');
    	$section_obj->publication_status = GlobalFunctions::value_cleaner('published');
    	$section_obj->feature = GlobalFunctions::value_cleaner('no');
    	$section_obj->highlight = GlobalFunctions::value_cleaner('no');
    	$section_obj->publish_date = NULL;
    	$section_obj->expire_date = NULL;
    	$section_obj->show_publish_date = GlobalFunctions::value_cleaner('no');
    	$section_obj->rss_available = GlobalFunctions::value_cleaner('no');
    	$section_obj->external_link = GlobalFunctions::value_cleaner(NULL);
    	$section_obj->target = GlobalFunctions::value_cleaner('self');
    	$section_obj->comments = GlobalFunctions::value_cleaner('no');
    	$section_obj->external_comment_script = NULL;
    	$section_obj->display_menu = GlobalFunctions::value_cleaner('yes');
    	$section_obj->homepage = 'yes';
    	$section_obj->article = 'no';
    	
    	//Save section data
    	$section_id = $section->save('wc_section',$section_obj);
    	
    	//module area
    	$section_areas = new Core_Model_SectionModuleArea();
    	//section area
    	$section_module_area = $section_areas->getNewRow('wc_section_module_area');
    	$section_module_area->section_id = $section_id['id'];
    	$section_module_area->area_id = $area_id;
    	//save section area
    	$section_area_id = $section_areas->save('wc_section_module_area',$section_module_area);
    	
    	//succes or error messages displayed on screen
    	if($section_id && $section_area_id)
    	{
    		return true;
    	}
    	else{
    		return false;
    	}
    	
    }
    
    /**
     * Checks if website name already exist on db
     */
    public function checknameAction()
    {
    	$this->_helper->layout->disableLayout ();
    	$this->_helper->viewRenderer->setNoRender ( TRUE );
    
    	//translate library
    	$lang = Zend_Registry::get ( 'Zend_Translate' );
    
    	if ($this->getRequest ()->isPost ())
    	{
    		$website_id = $this->_request->getPost ( 'website_id' );
    		$name = $this->_request->getPost ( 'name' );
    
    		$website = new Core_Model_Website();
    
    		$name_param = mb_strtolower($name, 'UTF-8');
    			
    		if($website_id)
    		{
    			$data = $website->personalized_find ( 'wc_website', array (array('id','!=',$website_id), array('name','==',$name_param)));
    		}
    		else
    		{
    			$data = $website->personalized_find ( 'wc_website', array (array('name','==',$name_param)));
    		}
    		if($data)
    			echo json_encode ( FALSE );
    		else
    			echo json_encode ( TRUE );
    	}
    }
    
    /**
     * Generate a backup (zip) that contains the content folder and 
     * a script of the current database.
     */
    public function backupAction()
    {
        $source='../public/uploads/content/';
        $date = str_replace(' ', '_', date("Y-m-d H:i:s"));
        $destination = 'wicawebBCK_'.$date.'.zip';
        //retrieve parameters from de application.ini
        $config = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $db = $config->getOption('resources');
        $host=$db['db']['params']['host'];
        $dbUsername=$db['db']['params']['username'];
        $dbPassword=$db['db']['params']['password'];
        $dbName=$db['db']['params']['dbname'];        
        $filedb = 'wicawebBCK' . $date . '.sql';        
        $command = sprintf("
            mysqldump -u %s --password=%s -d %s --skip-no-data > %s",
            escapeshellcmd($dbUsername),
            escapeshellcmd($dbPassword),
            escapeshellcmd($dbName),            
            escapeshellcmd($filedb)
        );
        //generate sql script
        exec($command);
        //create de zip file
        $zip = new ZipArchive();
        $zip->open($destination, ZIPARCHIVE::CREATE);
        $source = str_replace('\\', '/', realpath($source));
        //recursive add (to zip file) of content folder
        if (is_dir($source) === true)
        {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file)
            {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                    continue;

                $file = realpath($file);

                if (is_dir($file) === true)
                {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                }
                else if (is_file($file) === true)
                {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        }
        else if (is_file($source) === true)
        {
            $zip->addFromString(basename($source), file_get_contents($source));
        }
        //add sql script to zip file
        $zip->addFile($filedb);
        $zip->close();
         //force download
         header("Content-disposition: attachment; filename=$destination");
         readfile($destination);
         //remove files 
         unlink($filedb);
         unlink($destination);
        }
}
