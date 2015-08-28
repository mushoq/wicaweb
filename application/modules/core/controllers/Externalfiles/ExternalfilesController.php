<?php
/**
 * Upload External Files Controller
 * This file has the fucntion that allow the user to upload external files
 *
 * @category   WicaWeb
 * @package    Core_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   Esteban 
 * @version    1.0
 * 
 */
class Core_Externalfiles_ExternalfilesController extends Zend_Controller_Action
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
				if($mod->module_id == '5')
				{
					$profile_access[$mod->action_id] = array('action'=> $mod->action_name, 'title'=>$mod->action_title);
				}
			}
		}
	
		$this->view->external_files_links = $profile_access;
    }
	
    /**
     * Generates a list of all available websites
     */
    public function indexAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	
    	//website id
    	$id = New Zend_Session_Namespace('id');
    	$website_id = $id->website_id;
    	
    	$external_files = new Core_Model_Externalfiles();
    	$this->view->external_files_js = $external_files->personalized_find('wc_external_files',array(array('website_id','=',$website_id),array('type','=','js')),'order_number');
    	$this->view->external_files_css = $external_files->personalized_find('wc_external_files',array(array('website_id','=',$website_id),array('type','=','css')),'order_number');
    	$this->view->files = count($this->view->external_files_css) + count($this->view->external_files_js);

    	if ($this->getRequest()->isPost())
    	{
    		//retrieved data from post
    		$formData  = $this->_request->getPost();

    		if($formData['js_order']){
    			$js_list = GlobalFunctions::value_cleaner($formData['js_order']);
    			$js_arr = explode(',', $js_list);
    		}
    		
    		if($formData['css_order']){
    			$css_list = GlobalFunctions::value_cleaner($formData['css_order']);
    			$css_arr = explode(',', $css_list);
    		}
    		    		
    		$count_js = 1;
    		$count_css = 1;
    		
    		//save js files according order
    		if(count($js_arr)>0){
	    		foreach ($js_arr as $external_file_id)
	    		{
	    			$external_file =  new Core_Model_Externalfiles();
	    			$stored_data = $external_file->find('wc_external_files', array('id'=>$external_file_id));
	    			$external_file_obj = $external_file->getNewRow('wc_external_files');
	    			$external_file_obj->id = $stored_data[0]->id;
	    			$external_file_obj->website_id = $stored_data[0]->website_id;
	    			$external_file_obj->name = GlobalFunctions::value_cleaner($stored_data[0]->name);
	    			$external_file_obj->path = GlobalFunctions::value_cleaner($stored_data[0]->path);
	    			$external_file_obj->type = $stored_data[0]->type;
	    			$external_file_obj->order_number = $count_js;
	    			$saved_js = $external_file->save('wc_external_files',$external_file_obj);
	    				
	    			$count_js++;
	    		}
    		}
    		
    		//save files according order
    		if(count($css_arr)>0){
    			foreach ($css_arr as $external_file_id)
	    		{
	    			$external_file =  new Core_Model_Externalfiles();
	    			$stored_data = $external_file->find('wc_external_files', array('id'=>$external_file_id));
	    			$external_file_obj = $external_file->getNewRow('wc_external_files');
	    			$external_file_obj->id = $stored_data[0]->id;
	    			$external_file_obj->website_id = $stored_data[0]->website_id;
	    			$external_file_obj->name = GlobalFunctions::value_cleaner($stored_data[0]->name);
	    			$external_file_obj->path = GlobalFunctions::value_cleaner($stored_data[0]->path);
	    			$external_file_obj->type = $stored_data[0]->type;
	    			$external_file_obj->order_number = $count_css;
	    			$saved_css = $external_file->save('wc_external_files',$external_file_obj);
	    		
	    			$count_css++;
	    		}
    		}

    		$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved order')));
    		$this->_helper->redirector('index','externalfiles_externalfiles','core');
    	}
    }
    
    /**
     * Action to upload a new file
     */
    public function newAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	 
    	$request = $this->getRequest();
    	 
    	$form = New Core_Form_Externalfiles_Externalfiles();
    	$form->setMethod('post');
    	
    	//after submit the form
    	if ($this->getRequest()->isPost()) {
    		$formData = $this->getRequest()->getPost();
    		if ($form->isValid($formData)) {
    			//Recover the uploaded values
    			$uploadedData = $form->getValues();

    			//MOVE AND RENAME UPLOADED FILES
    			//get extension of uploaded file
    			$filename = $uploadedData['file'];
    			$extension = pathinfo($filename, PATHINFO_EXTENSION);
    			//path to upload image
    			switch($extension){
    				case 'js':
    					if(!is_dir(APPLICATION_PATH. '/../public/js/external/'))
    					{
    						$path = APPLICATION_PATH. '/../public/js/external/';
    						mkdir($path);
    						chmod($path, 0777);
    					}else{
    						$path = APPLICATION_PATH. '/../public/js/external/';
    						chmod($path, 0777);
    					}
    					break;
    					
    				case 'css':
    					if(!is_dir(APPLICATION_PATH. '/../public/css/external/'))
    					{
    						$path = APPLICATION_PATH. '/../public/css/external/';
    						mkdir($path);
    						chmod($path, 0777);
    					}else{
    						$path = APPLICATION_PATH. '/../public/css/external/';
    						chmod($path, 0777);
    					}
    					break;
    			}
    			
    			//website id
    			$id = New Zend_Session_Namespace('id');
    			$website_id = $id->website_id;
    			
    			//save data
    			$external_file =  new Core_Model_Externalfiles();
    			$external_file_obj = $external_file->getNewRow('wc_external_files');
    			$external_file_obj->name = GlobalFunctions::value_cleaner($formData['name']);   			
    			switch($extension){
    				case 'js':
    					$uploaded_file = GlobalFunctions::uploadFiles($filename, APPLICATION_PATH. '/../public/js/external/');
    					$external_file_obj->type = 'js';
    					break;
    				
    				case 'css':
    					$uploaded_file = GlobalFunctions::uploadFiles($filename, APPLICATION_PATH. '/../public/css/external/');
    					$external_file_obj->type = 'css';
    					break;
    			}
    			$external_file_obj->path = $uploaded_file;
    			
    			if($formData['add_to_all']=='yes'){
    				$website = new Core_Model_Website();
    				$website_list = $website->find('wc_website');
    				foreach($website_list as $w){
    					//order files
    					$next = $external_file->getNextOrderNumber($extension,$w->id);
    					$external_file_obj->order_number = $next;
    					$external_file_obj->website_id = $w->id;
    					$saved_external_file = $external_file->save('wc_external_files',$external_file_obj);
    					if($saved_external_file){
    						$valid = TRUE;
    					}
    					else{
    						$valid = FALSE;
    					}
    				}
    				
    				if($valid){
    					//success message
    					$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
    				}
    				else{
    					//error message
    					$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in saving data')));
    				}
    			}
    			else{
    				//Order files
    				$next = $external_file->getNextOrderNumber($extension,$website_id);
    				$external_file_obj->order_number = $next;
    				$external_file_obj->website_id = $website_id;
    				// Save data
	    			$saved_external_file = $external_file->save('wc_external_files',$external_file_obj);
	    			if($saved_external_file){
	    				//success message
	    				$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success uploaded')));
	    			}
	    			else{
	    				//error message
	    				$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in uploading')));
	    			}
    			}
    			$this->_helper->redirector('index','externalfiles_externalfiles','core');
    		}	
    	}
    	
    	$this->view->form = $form;
    	
    }
    
    /**
     * Action to upload a new file
     */
    public function removeAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	
    	$this->_helper->viewRenderer->setNoRender();
    	
    	$external_file =  new Core_Model_Externalfiles();
    	$id = $this->_getParam('id');
    	$data = $external_file->find('wc_external_files', array('id'=>$id)); //get the selected website data
    	$arr_data = get_object_vars($data[0]); //make an array of the object data

    	$filename = $arr_data['path'];

    	$website = new Core_Model_Website();
    	//website id
    	$stored_id = New Zend_Session_Namespace('id');
    	$aux_website_id = $stored_id->website_id;
    	$website_list = $website->personalized_find('wc_website', array(array('id','!=',$aux_website_id)));
		
    	if($website_list){
	    	foreach($website_list as $w){
	    		$website_id = $w->id;
	    		$data = $external_file->personalized_find('wc_external_files', array(
	    																				array('website_id','=',$website_id),
	    																				array('path','=',$filename)
	    																		)); //get the selected website data
	
	    		$remove_file = TRUE;
	    		if($data && count($data)){
	    			$remove_file = FALSE;
	    		}
	    		else{
	    			$remove_file = TRUE;
	    		}
	    	}
    	}
	    else{
	    	$remove_file = TRUE;
	    }
    	

    	if($remove_file===TRUE){
    		echo "Borrar archivo definitivamente <br/>";
    		    		
    		$extension = pathinfo($filename, PATHINFO_EXTENSION);
    		$path = '';
    		switch($extension){
    			case 'js':
    				$path = APPLICATION_PATH. '/../public/js/external/';
    				break;
    		
    			case 'css':
    				$path = APPLICATION_PATH. '/../public/css/external/';
    				break;
    		}

    		$delete = GlobalFunctions::removeOldFiles($filename, $path);

    		$delete_row = $external_file->delete('wc_external_files',array('id'=>$id)); // delete the external link row reference.
    	}
    	else{
    		$delete = 1;
    		$delete_row = $external_file->delete('wc_external_files',array('id'=>$id)); // delete the external link row reference.
    	}
    	
    	if($delete && $delete_row){
    		//success message
    		$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success removed')));
    	}
    	else{
    		//error message
    		$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in removing file')));
    	}
    	$this->_helper->redirector('index','externalfiles_externalfiles','core');
    	
    }
    
    /**
     * Action to display the content of the 
     */
    public function viewAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	//new external file
    	$external_file =  new Core_Model_Externalfiles();
    	$id = $this->_getParam('id');
    	$data = $external_file->find('wc_external_files', array('id'=>$id)); //get the selected file data
    	$arr_data = get_object_vars($data[0]); //make an array of the object data
    	
    	$filename = $arr_data['path'];
    	$extension = pathinfo($filename, PATHINFO_EXTENSION);
    	$path = '';
    	//get the extension and corresponding path
    	switch($extension){
    		case 'js':
    			$path = APPLICATION_PATH. '/../public/js/external/';
    			break;
    	
    		case 'css':
    			$path = APPLICATION_PATH. '/../public/css/external/';
    			break;
    	}
    	//read the file content
    	$file_content = file($path.$filename);
    	
    	$this->view->file_content = $file_content;
    	$this->view->file_name = $filename;
        $this->view->id = $id;
    }
    public function editfileAction(){
        $this->_helper->layout->disableLayout ();
    	// disable autorendering for this action
    	$this->_helper->viewRenderer->setNoRender();
    
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	//new external file
    	$external_file =  new Core_Model_Externalfiles();
    	$id = $this->_getParam('id');
    	$data = $external_file->find('wc_external_files', array('id'=>$id)); //get the selected file data
    	$arr_data = get_object_vars($data[0]); //make an array of the object data
    	
    	$filename = $arr_data['path'];
    	$extension = pathinfo($filename, PATHINFO_EXTENSION);
    	$path = '';
    	//get the extension and corresponding path
    	switch($extension){
    		case 'js':
    			$path = APPLICATION_PATH. '/../public/js/external/';
    			break;
    	
    		case 'css':
    			$path = APPLICATION_PATH. '/../public/css/external/';
    			break;
    	}
        
        $file = fopen($path.$filename, "w+");
        echo fwrite($file, $_POST['code']);
        fclose($file);
        $this->_redirect("/core/externalfiles_externalfiles");
    }
}
