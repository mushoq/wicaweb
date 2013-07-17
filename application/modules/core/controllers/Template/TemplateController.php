<?php
/**
 * Upload Templates
 * This file has the fucntion that allow the user to upload template files
 *
 * @category   WicaWeb
 * @package    Core_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   Santiago Arellano
 * @version    1.0
 * 
 */
class Core_Template_TemplateController extends Zend_Controller_Action
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
				if($mod->module_id == '6')
				{
					$profile_access[$mod->action_id] = array('action'=> $mod->action_name, 'title'=>$mod->action_title);
				}
			}
		}
		$this->view->template_links = $profile_access;
    }
	
    /**
     * Generates a list of all available websites
     */
    public function indexAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');

    	$website = new Core_Model_Website();
    	$website_data = $website->find('wc_website');
    	
    	if($website_data){
    		$templates = '';
    		
	    	foreach ($website_data as $key => $ws){
	    		if($key == (count($website_data)-1))
	    			$templates.= $ws->template_id;
	    		else	
	    			$templates.= $ws->template_id.',';
	    	}
    	}
    	$website_active_templates = explode(',',$templates);
    	
    	$section_module_area_storage = new Core_Model_SectionModuleAreaStorage();
    	$section_module_area_storage_data = $section_module_area_storage->find('wc_section_module_area_storage');
    	
    	$templates_storage=array();
    	
    	foreach($section_module_area_storage_data as $smas){
    		$area = new Core_Model_Area();
    		$area_data = $area->find('wc_area', array('id'=>$smas->area_id));
    		
    		array_push($templates_storage, $area_data[0]->template_id);
    	}
    	$final_templates = array_merge($website_active_templates,$templates_storage);
    	$template = new Core_Model_WebsiteTemplate();
    	$this->view->templates = $template->find('wc_website_template');
		$this->view->active_templates = $final_templates;
    }
    
    /**
     * Action to upload a new file
     */
    public function newAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	 
    	$request = $this->getRequest();
    	 
    	$form = New Core_Form_Template_Template();
    	
    	//after submit the form
    	if ($this->getRequest()->isPost()) {
    		$formData = $this->getRequest()->getPost();
    		if ($form->isValid($formData)) {

    			//save data
    			$template =  new Core_Model_WebsiteTemplate();
    			$template_obj = $template->getNewRow('wc_website_template');
    			
    			$template_obj->name = $formData['name'];
    			
    			//move files from tmp
    			$template_file = GlobalFunctions::uploadFiles ( $formData['hdn_template_file'], APPLICATION_PATH . '/../application/modules/default/views/scripts/partials/' );
    			$template_obj->file_name = $template_file;
    			GlobalFunctions::removeOldFiles ( $formData['hdn_template_file'], APPLICATION_PATH . '/../public/uploads/tmp/' );    			

    			//move files from tmp
    			if($formData['hdn_template_image']){
	    			$template_image = GlobalFunctions::uploadFiles ( $formData['hdn_template_image'], APPLICATION_PATH . '/../public/uploads/template_img/' );
	    			$template_obj->image = $template_image;
	    			GlobalFunctions::removeOldFiles ( $formData['hdn_template_image'], APPLICATION_PATH . '/../public/uploads/tmp/' );
    			}
    			
    			//move css files from tmp
    			if($formData['hdn_css_files']){
    				$css_files = explode(',',$formData['hdn_css_files']);
    				array_pop($css_files);

    				if (! is_dir ( APPLICATION_PATH . '/../public/css/templates/')) {
    					$path = APPLICATION_PATH . '/../public/css/templates/';
    					mkdir ( $path );
    					chmod ( $path, 0777 );
    				}    				
    				
    				if (! is_dir ( APPLICATION_PATH . '/../public/css/templates/'. $formData['name'] .'/')) {
    					$path = APPLICATION_PATH . '/../public/css/templates/'. $formData['name'] .'/';
    					mkdir ( $path );
    					chmod ( $path, 0777 );
    				}

    				$path_css = APPLICATION_PATH . '/../public/css/templates/'. $formData['name'] .'/'; 
    				foreach($css_files as $css){
    					copy(APPLICATION_PATH. '/../public/uploads/tmp/css_'.$formData['template_folder'].'/'.$css, $path_css.$css);
    					GlobalFunctions::removeOldFiles ( $css, APPLICATION_PATH. '/../public/uploads/tmp/css/' );
    					$template_obj->css_files = $formData['hdn_css_files'];
    				}
    			}
    			
    			//insert media atribute on css files
    			if($formData['hdn_media_css']){
    				$template_obj->media_css = $formData['hdn_media_css'];
    			}
    			
    			//move js files from tmp
    			if($formData['hdn_js_files']){
    				$js_files = explode(',',$formData['hdn_js_files']);
    				array_pop($js_files);
    			
    				if (! is_dir ( APPLICATION_PATH . '/../public/js/templates/')) {
    					$path = APPLICATION_PATH . '/../public/js/templates/';
    					mkdir ( $path );
    					chmod ( $path, 0777 );
    				}
    				
    				if (! is_dir ( APPLICATION_PATH . '/../public/js/templates/'. $formData['name'] .'/')) {
    					$path = APPLICATION_PATH . '/../public/js/templates/'. $formData['name'] .'/';
    					mkdir ( $path );
    					chmod ( $path, 0777 );
    				}
    			
    				$path_js = APPLICATION_PATH . '/../public/js/templates/'. $formData['name'] .'/';
    				foreach($js_files as $js){
    					copy(APPLICATION_PATH. '/../public/uploads/tmp/js_'.$formData['template_folder'].'/'.$js, $path_js.$js);
    					GlobalFunctions::removeOldFiles ( $js, APPLICATION_PATH. '/../public/uploads/tmp/js/' );
    					$template_obj->js_files = $formData['hdn_js_files'];
    				}
    			}

    			//move image files from tmp
    			if($formData['hdn_image_files']){
    				$image_files = explode(',',$formData['hdn_image_files']);
    				array_pop($image_files);
    			
    				if (! is_dir ( APPLICATION_PATH . '/../public/images/templates/')) {
    					$path = APPLICATION_PATH . '/../public/images/templates/';
    					mkdir ( $path );
    					chmod ( $path, 0777 );
    				}    				
    				
    				if (! is_dir ( APPLICATION_PATH . '/../public/images/templates/'. $formData['name'] .'/')) {
    					$path = APPLICATION_PATH . '/../public/images/templates/'. $formData['name'] .'/';
    					mkdir ( $path );
    					chmod ( $path, 0777 );
    				}
    			
    				$path_image = APPLICATION_PATH . '/../public/images/templates/'. $formData['name'] .'/';
    				foreach($image_files as $image){
    					copy(APPLICATION_PATH. '/../public/uploads/tmp/images_'.$formData['template_folder'].'/'.$image, $path_image.$image);
    					GlobalFunctions::removeOldFiles ( $image, APPLICATION_PATH. '/../public/uploads/tmp/images/' );
    					$template_obj->images_files = $formData['hdn_image_files'];
    				}
    			}    			
    						
     			$template_id = $template->save('wc_website_template',$template_obj);
    			
    			
    			if($formData['hdn_areas'] && $formData['hdn_template_file']){
	    			$hdn_areas = explode(';',$formData['hdn_areas']);
	    			array_pop($hdn_areas);
	    			//save areas from template file uploaded
	    			foreach($hdn_areas as $key_area => $areas){
	    				
	    				$info = explode(",",$areas);
	    				
	    				$area =  new Core_Model_Area();
	    				$area_obj = $area->getNewRow('wc_area');
	    				$area_obj->template_id = $template_id['id'];
	    				$area_obj->name = $info[0];
	    				
	    				if($area_obj->name == 'wica_area_content')
	    					$area_obj->type = 'variable';
	    				else	
	    					$area_obj->type = 'fixed';
	    				
	    				$area_obj->area_number = $key_area+1;
	    				
	    				$area_obj->width = $info[1];
	    				
	    				$area->save('wc_area', $area_obj);
	    				
	    			}
	    			
    			}
    			
    				if($template_id){
    					//success message
    					$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success uploaded')));
    				}
    				else{
    					//error message
    					$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in uploading')));
    				}

    			$this->_helper->redirector('index','template_template','core');
    		}	
    	}
    	
    	$this->view->form = $form;
    	
    }
    
    /**
     * Action to update template
     */
    public function editAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    
    	$request = $this->getRequest();
    
    	$form = New Core_Form_Template_Template();
    	 
    	$template_id = $this->_getParam ( 'id' );   

    	$template = new Core_Model_WebsiteTemplate();
    	$template_data = $template->find('wc_website_template', 
    			array('id' => $template_id));
    	
    	$arr_data = get_object_vars ( $template_data [0] );
    	
    	$arr_data['hdn_template_file'] = $arr_data['file_name'];
    	$arr_data['hdn_template_image'] = $arr_data['image'];
    	
    	$form->populate ( $arr_data );
    	$this->view->form = $form;
    	$this->view->template_id = $template_id;   
    	$this->view->images_files= $arr_data['images_files']; 	
    	$this->view->css_files= $arr_data['css_files'];
    	$this->view->media_css= $arr_data['media_css'];
    	$this->view->js_files= $arr_data['js_files'];
    	$this->view->template_name= $arr_data['name'];
    	
    	$website = new Core_Model_Website();
    	$website_data = $website->find('wc_website');
    	 
    	if($website_data){
    		$templates = '';
    	
    		foreach ($website_data as $key => $ws){
    			if($key == (count($website_data)-1))
    				$templates.= $ws->template_id;
    			else
    				$templates.= $ws->template_id.',';
    		}
    	}    

    	$templates = explode(',',$templates);
    	
    	//Disable upload template file element
    	if(in_array($template_id, $templates)){
    		$template_file_element = $form->getElement('template_file');
    		$template_file_element->setAttrib ('disabled', 'disabled' );
    	}    	
    	
    	//after submit the form
    	if ($this->getRequest()->isPost()) {
    		$formData = $this->getRequest()->getPost();
    		if ($form->isValid($formData)) {
//     			Zend_Debug::dump($formData);
    			//save data
    			$template =  new Core_Model_WebsiteTemplate();
    			$template_obj = $template->getNewRow('wc_website_template');
    			$template_obj->id = $formData['template_id'];
    			$template_obj->name = $formData['name'];
    			
    			//move files from tmp
    			if($formData['hdn_template_file']){
	    			$template_file = GlobalFunctions::uploadFiles ( $formData['hdn_template_file'], APPLICATION_PATH . '/../application/modules/default/views/scripts/partials/' );
	    			$template_obj->file_name = $template_file;
	    			GlobalFunctions::removeOldFiles ( $formData['hdn_template_file'], APPLICATION_PATH . '/../public/uploads/tmp/' );
	    			//delete old files
	    			if (! GlobalFunctions::removeOldFiles ( $arr_data['file_name'], APPLICATION_PATH . '/../application/modules/default/views/scripts/partials/' )) {
	    				throw new Zend_Exception ( "CUSTOM_EXCEPTION:FILE NOT DELETED." );	  
	    			}  			
    			}else{
    				$template_obj->file_name = $arr_data['file_name'];
    			}
    			
    			//move files from tmp
    			if($formData['hdn_template_image']){
    				$template_image = GlobalFunctions::uploadFiles ( $formData['hdn_template_image'], APPLICATION_PATH . '/../public/uploads/template_img/' );
    				$template_obj->image = $template_image;
    				GlobalFunctions::removeOldFiles ( $formData['hdn_template_image'], APPLICATION_PATH . '/../public/uploads/tmp/' );
    				//delete old files
    				if (! GlobalFunctions::removeOldFiles ( $arr_data['image'], APPLICATION_PATH . '/../public/uploads/template_img/' )) {
    					throw new Zend_Exception ( "CUSTOM_EXCEPTION:FILE NOT DELETED." );   
    				} 				
    			}else{
    				$template_obj->image = $arr_data['image'];
    			}
    			
    			//move css files from tmp
    			if($formData['hdn_css_files']){
    				$css_files = explode(',',$formData['hdn_css_files']);
    				array_pop($css_files);
    			
    				if (! is_dir ( APPLICATION_PATH . '/../public/css/templates/')) {
    					$path = APPLICATION_PATH . '/../public/css/templates/';
    					mkdir ( $path );
    					chmod ( $path, 0777 );
    				}
    			
    				if (! is_dir ( APPLICATION_PATH . '/../public/css/templates/'. $formData['name'] .'/')) {
    					$path = APPLICATION_PATH . '/../public/css/templates/'. $formData['name'] .'/';
    					mkdir ( $path );
    					chmod ( $path, 0777 );
    				}
    			
    				$path_css = APPLICATION_PATH . '/../public/css/templates/'. $formData['name'] .'/';
    				foreach($css_files as $css){
    					if(copy(APPLICATION_PATH. '/../public/uploads/tmp/css_'.$formData['template_folder'].'/'.$css, $path_css.$css))
    						GlobalFunctions::removeOldFiles ( $css, APPLICATION_PATH. '/../public/uploads/tmp/css/' );
    				}
    				$template_obj->css_files = $formData['hdn_css_files'];
    			}else
    				$template_obj->css_files = $arr_data['css_files'];
    			 
    			//insert media atribute on css files
    			if($formData['hdn_media_css']){
    				$template_obj->media_css = $formData['hdn_media_css'];
    			}    			
    			
    			//move js files from tmp
    			if($formData['hdn_js_files']){
    				$js_files = explode(',',$formData['hdn_js_files']);
    				array_pop($js_files);
    				 
    				if (! is_dir ( APPLICATION_PATH . '/../public/js/templates/')) {
    					$path = APPLICATION_PATH . '/../public/js/templates/';
    					mkdir ( $path );
    					chmod ( $path, 0777 );
    				}
    			
    				if (! is_dir ( APPLICATION_PATH . '/../public/js/templates/'. $formData['name'] .'/')) {
    					$path = APPLICATION_PATH . '/../public/js/templates/'. $formData['name'] .'/';
    					mkdir ( $path );
    					chmod ( $path, 0777 );
    				}
    				 
    				$path_js = APPLICATION_PATH . '/../public/js/templates/'. $formData['name'] .'/';
    				foreach($js_files as $js){
    					if(copy(APPLICATION_PATH. '/../public/uploads/tmp/js_'.$formData['template_folder'].'/'.$js, $path_js.$js))
    						GlobalFunctions::removeOldFiles ( $js, APPLICATION_PATH. '/../public/uploads/tmp/js/' );
    				}
    				$template_obj->js_files = $formData['hdn_js_files'];
    			}else
    				$template_obj->js_files = $arr_data['js_files'];
    			
    			//move image files from tmp
    			if($formData['hdn_image_files']){
    				$image_files = explode(',',$formData['hdn_image_files']);
    				array_pop($image_files);
    				 
    				if (! is_dir ( APPLICATION_PATH . '/../public/images/templates/')) {
    					$path = APPLICATION_PATH . '/../public/images/templates/';
    					mkdir ( $path );
    					chmod ( $path, 0777 );
    				}
    			
    				if (! is_dir ( APPLICATION_PATH . '/../public/images/templates/'. $formData['name'] .'/')) {
    					$path = APPLICATION_PATH . '/../public/images/templates/'. $formData['name'] .'/';
    					mkdir ( $path );
    					chmod ( $path, 0777 );
    				}
    				 
    				$path_image = APPLICATION_PATH . '/../public/images/templates/'. $formData['name'] .'/';
    				foreach($image_files as $image){
    					if(copy(APPLICATION_PATH. '/../public/uploads/tmp/images_'.$formData['template_folder'].'/'.$image, $path_image.$image))
    						GlobalFunctions::removeOldFiles ( $image, APPLICATION_PATH. '/../public/uploads/tmp/images/' );
    				}
    				$template_obj->images_files = $formData['hdn_image_files'];
    			}else
    				$template_obj->images_files = $arr_data['images_files'];    			
    			
//     			Zend_Debug::dump($template_obj);die;
    			$template_id = $template->save('wc_website_template',$template_obj);
    			 
    			if($formData['hdn_areas'] && $formData['hdn_template_file']){
    				$hdn_areas = explode(';',$formData['hdn_areas']);
    				array_pop($hdn_areas);
    				
    				if($hdn_areas){
    					//delete old areas
    					$area = new Core_Model_Area();
    					$area->delete('wc_area',
    							array('template_id'=>$formData['template_id']));
    					//save areas from template file uploaded
	    				foreach($hdn_areas as $key_area => $areas){
	    					 
	    					$info = explode(",",$areas);
	    					 
	    					$area =  new Core_Model_Area();
	    					$area_obj = $area->getNewRow('wc_area');
	    					$area_obj->template_id = $template_id['id'];
	    					$area_obj->name = $info[0];
	    					 
	    					if($area_obj->name == 'wica_area_content')
	    						$area_obj->type = 'variable';
	    					else
	    						$area_obj->type = 'fixed';
	    					 
	    					$area_obj->area_number = $key_area+1;
	    					 
	    					$area_obj->width = $info[1];
	    					 
	    					$area->save('wc_area', $area_obj);
	    					 
	    				}
    				}
    			}
    			 
    			if($template_id){
    				//success message
    				$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
    			}
    			else{
    				//error message
    				$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in saving data')));
    			}
    
    			$this->_helper->redirector('index','template_template','core');
    		}
    	}
    	 
    	$this->view->form = $form;
    	 
    }    
    
    /**
     * Action to delete template
     */
    public function deleteAction()
    {
     	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    
    	$template_id = $this->_getParam ( 'id' );   

    	$template = new Core_Model_WebsiteTemplate();
    	$template_data = $template->find('wc_website_template', 
    			array('id' => $template_id));

    	//remove files
    	GlobalFunctions::removeOldFiles ( $template_data [0]->file_name, APPLICATION_PATH . '/../application/modules/default/views/scripts/partials/' );
    	GlobalFunctions::removeOldFiles ( $template_data [0]->image, APPLICATION_PATH . '/../public/uploads/template_img/' );
    	
    	if($template_data[0]->css_files){
    		$css_files = explode(',', $template_data[0]->css_files);
    		array_pop($css_files);
    		
    		foreach($css_files as $cssf){
    			GlobalFunctions::removeOldFiles ( $cssf, APPLICATION_PATH . '/../public/css/templates/'.$template_data [0]->name.'/' );
    		}
    		
    		rmdir(APPLICATION_PATH . '/../public/css/templates/'.$template_data [0]->name);
    	}
    	
    	if($template_data[0]->js_files){
    		$js_files = explode(',', $template_data[0]->js_files);
    		array_pop($js_files);
    	
    		foreach($js_files as $jsf){
    			GlobalFunctions::removeOldFiles ( $jsf, APPLICATION_PATH . '/../public/js/templates/'.$template_data [0]->name.'/' );
    		}
    		
    		rmdir(APPLICATION_PATH . '/../public/js/templates/'.$template_data [0]->name);
    	}

    	if($template_data[0]->images_files){
    		$images_files = explode(',', $template_data[0]->images_files);
    		array_pop($images_files);
    	
    		foreach($images_files as $imagesf){
    			GlobalFunctions::removeOldFiles ( $imagesf, APPLICATION_PATH . '/../public/images/templates/'.$template_data [0]->name.'/' );
    		}
    		
    		rmdir(APPLICATION_PATH . '/../public/images/templates/'.$template_data [0]->name);
    	}    	
    	
    	//remove template areas
    	$area = new Core_Model_Area();
    	$delete_area = $area->delete('wc_area',
    			array('template_id'=>$template_id));
    	
    	//remove template
    	$template = new Core_Model_WebsiteTemplate();
    	$delete_template = $template->delete('wc_website_template',
    			array('id' => $template_id));    	

    	if($delete_area && $delete_template){
    		//success message
    		$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success removed')));
    	}
    	else{
    		//error message
    		$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in removing file')));
    	}
    
    	$this->_helper->redirector('index','template_template','core');
    	
    }

    /**
     * Action to display the content of the
     */
    public function viewAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');

    	$template_id = $this->_getParam ( 'id' );   

    	$template = new Core_Model_WebsiteTemplate();
    	$template_data = $template->find('wc_website_template', 
    			array('id' => $template_id));
    	 
    	$filename = $template_data[0]->file_name;
    	$path = APPLICATION_PATH. '/../application/modules/default/views/scripts/partials/';
    	//read the file content
    	$template_file = file($path.$filename);
    	 
    	$this->view->template_file = $template_file;
    	$this->view->file_name = $filename;
    }    

    /**
     * Render the template as a preview
     */
    public function rendertemplateAction()
    {
    	
    	$this->_helper->layout->disableLayout ();
    	    	
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    
    	$template_id = $this->_request->getPost ( 'id' );
    
    	$template = new Core_Model_WebsiteTemplate();
    	$template_data = $template->find('wc_website_template',
    			array('id' => $template_id));
    
    	$filename = $template_data[0]->file_name;
    	$path = APPLICATION_PATH. '/../application/modules/default/views/scripts/partials/';
    	//read the file content
    	$template_file = file($path.$filename);
    
    	$this->view->template_file = $template_file;
    	$this->view->file_name = $filename;
    }    
    
    /**
     * Uploads a file
     */
    public function uploadfileAction()
    {
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action only:
    	$this->_helper->viewRenderer->setNoRender();
    
    	$formData  = $this->_request->getPost();
    	
    	$directory = $formData['directory'];
    	$maxSize = $formData['maxSize'];
    	$type = $formData['type'];
    	$template_name = '';
    	if(array_key_exists('template_name', $formData))
    		$template_name = $formData['template_name'];
    	
    	$directory = APPLICATION_PATH. '/../'. $directory;
    	
    	if($type != 'template_file' && $type != 'template_image'){
    		$directory.=$type.'_'.$formData['template'];
    	}
    	
    	if (! is_dir ($directory)) {
    		$path = $directory;
    		mkdir ( $path );
    		chmod ( $path, 0777 );		
    	}    	
    	if($_FILES["content_file"]["size"]!=0){
	    	if ($_FILES["content_file"]["size"] <= $maxSize) {//DETERMINING IF THE SIZE OF THE FILE UPLOADED IS VALID
	    		$path_parts = pathinfo($_FILES["content_file"]["name"]);
	    			
	    		if($type == 'template_image' || $type == 'images'){
	    			$extensions = array(0 => 'jpg', 1 => 'jpeg', 2 => 'png', 3 => 'gif', 4 => 'JPG', 5 => 'JPEG', 6 => 'PNG', 7 => 'GIF');
	    		}else
	    			if($type == 'template_file'){
	    				$extensions = array(0 => 'phtml');
	    			}else{
	    				$extensions = array(0 => $type);
	    			}
	    			if (in_array($path_parts['extension'], $extensions)) {//DETERMINING IF THE EXTENSION OF THE FILE UPLOADED IS VALID
	    				if (is_dir($directory)) {
	    					if($type != 'template_file' && $type != 'template_image'){
	    						if(!file_exists($directory . $_FILES["content_file"]["name"]) && !file_exists( APPLICATION_PATH. '/../'. 'public/' . $type.'/templates/'.$template_name.'/'. $_FILES["content_file"]["name"])){
		    						move_uploaded_file($_FILES["content_file"]["tmp_name"], $directory . $_FILES["content_file"]["name"]);
		    						echo $_FILES["content_file"]["name"];
	    						}else{
	    							echo 5; //FILE ALREADY EXIST
	    						}
	    					}else{
		    					do {
		    						$tempName = 'file_' . time() . '.' . $path_parts['extension'];
		    					} while (file_exists($directory . $tempName));
		    					move_uploaded_file($_FILES["content_file"]["tmp_name"], $directory . $tempName);
		    					echo $tempName;
	    					}
	    				} else {//ITS NOT A DIRECTORY
	    					echo 3;
	    				}
	    			} else {//INCORRECT EXTENSION
	    				echo 2;
	    			}
	    		
	    	} else {//INCORRECT SIZE
	    		echo 1;
	    	}
    	}else{//EMPTY FILE
    		echo 4;
    	}
    }
    
    /**
     * Deletes the content file temp
     */
    public function deletefileAction()
    {
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action only:
    	$this->_helper->viewRenderer->setNoRender();
    
    	$formData  = $this->_request->getPost();
    
    	$file = $formData['file'];
    	$type = '';
    	$saved = '';
    	$template = '';
    	$template_id = '';
    	$type_saved = '';
    	
    	if(array_key_exists('type', $formData))
    		$type = $formData['type'];
    	if(array_key_exists('saved', $formData))
    		$saved = $formData['saved'];
    	if(array_key_exists('template', $formData))
    		$template = $formData['template'];
    	if(array_key_exists('template_id', $formData))
    		$template_id = $formData['template_id'];
    	if(array_key_exists('type_saved', $formData))
    		$type_saved = $formData['type_saved'];   	
    	
    	if ($file) {
    		if($type){
    			if($saved=='no' || !$saved){
	    			if (file_exists(APPLICATION_PATH. '/../'. 'public/uploads/tmp/' . $type.'/'.$file)) {
	    				unlink(APPLICATION_PATH. '/../'. 'public/uploads/tmp/'. $type.'/'.$file);
	    			}	    		
    			}else{
    				if (file_exists(APPLICATION_PATH. '/../'. 'public/' . $type_saved.'/templates/'.$template.'/'.$file)) {
    					unlink(APPLICATION_PATH. '/../'. 'public/'. $type_saved.'/templates/'.$template.'/'.$file);
    					//save data
    					$template =  new Core_Model_WebsiteTemplate();
    					$template_data = $template->find('wc_website_template',array('id'=>$template_id));
    					
    					$column_name = $type_saved.'_files';
    					$files = $template_data[0]->$column_name;
    					$files = str_replace($file.',', '', $files);
    					
    					$template =  new Core_Model_WebsiteTemplate();
    					$template_obj = $template->getNewRow('wc_website_template');
    					$template_obj->id = $formData['template_id'];
    					$template_obj->name = $template_data[0]->name;
    					$template_obj->file_name = $template_data[0]->file_name;
    					$template_obj->image = $template_data[0]->image;
    					$template_obj->css_files = $template_data[0]->css_files;
    					$template_obj->media_css = $template_data[0]->media_css;
    					$template_obj->js_files = $template_data[0]->js_files;
    					$template_obj->images_files = $template_data[0]->images_files;
    					
    					$template_obj->$column_name = $files;
    					$template->save('wc_website_template', $template_obj);
    					
    				}   				
    			}	
    		}else 
	    		if (file_exists(APPLICATION_PATH. '/../'. 'public/uploads/tmp/' . $file)) {
	    			unlink(APPLICATION_PATH. '/../'. 'public/uploads/tmp/'. $file);
	    		}
    	}
    }
    
    
    /**
     * check if name already exist
     */
    public function checknameAction(){
    
    	$this->_helper->layout->disableLayout ();
    	$this->_helper->viewRenderer->setNoRender ( TRUE );
    
    	// 		translate library
    	$lang = Zend_Registry::get ( 'Zend_Translate' );
    
    	if ($this->getRequest ()->isPost ())
    	{
    		$template_id = $this->_request->getPost ( 'template_id' );
    		$name = $this->_request->getPost ( 'name' );
    			
    		$template = new Core_Model_WebsiteTemplate();
    			
    		if($template_id)
    		{
    			$data = $template->personalized_find ( 'wc_website_template',
    					array (array('id','!=',$template_id), array('name','==',$name)));
    		}
    		else
    		{
    			$data = $template->personalized_find ( 'wc_website_template',
    					array (array('name','==',$name)));
    		}
    		if($data)
    			echo json_encode ( FALSE );
    		else
    			echo json_encode ( TRUE );
    	}
    
    }

    /**
     * Download function to show save as popup
     */
    public function downloadAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    
    	$url = str_replace('@@@', '/',$this->_getParam ( 'url' ));
    	$file = $this->_getParam ( 'file' );
    
    	//open/save dialog box
    	header('Content-Disposition: attachment; filename="'.$file.'"');
    	//content type
    	header("Content-type: application/text");
    	//read from server and write to buffer
		readfile($url);
    }    
    
}
