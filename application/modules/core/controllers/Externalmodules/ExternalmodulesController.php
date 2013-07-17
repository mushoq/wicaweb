<?php
/**
 * Upload External Files Controller
 * This file has the fucntion that allow the user to upload external files
 *
 * @category   WicaWeb
 * @package    Core_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   Diego Perez 
 * @version    1.0
 * 
 */
class Core_Externalmodules_ExternalmodulesController extends Zend_Controller_Action
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
				if($mod->module_id == '8')
				{
					$profile_access[$mod->action_id] = array('action'=> $mod->action_name, 'title'=>$mod->action_title);
				}
			}
		}
	
		$this->view->external_modules_links = $profile_access;
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
    	
    	$external_modules = new Core_Model_Module();
    	$external_modules_list = $external_modules->getExternalModules();
    	
    	$this->view->external_modules_list = $external_modules_list;
    	
    }
    
    /**
     * Action to upload a new file
     */
    public function newAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	 
    	$request = $this->getRequest();
    	 
    	$form = New Core_Form_Externalmodules_Externalmodules();
    	$form->setMethod('post');
    	
    	//after submit the form
    	if ($this->getRequest()->isPost()) {
    		$formData = $this->getRequest()->getPost();
    		if ($form->isValid($formData)) {
    			//Recover the uploaded values
    			$uploadedData = $form->getValues(); 
    			$filename = $uploadedData['file'];
    			$filebase = pathinfo($filename,PATHINFO_FILENAME);
    			
    			//Descompress .zip package installer
    			$zip_path = APPLICATION_PATH. '/../public/uploads/tmp/'.$filename;
    			$zip = new ZipArchive;
    			if ($zip->open($zip_path) === TRUE) {
    				$zip->extractTo(APPLICATION_PATH.'/../public/uploads/tmp/');
    				chmod(APPLICATION_PATH.'/../public/uploads/tmp/'.$filebase,0777);
    				$zip->close();
    				//Delete zip package of temp
    				unlink($zip_path);
   				
    				//Check if destination folder "modules" exist and have permissions
    				if(is_dir(APPLICATION_PATH. '/modules/'))
    				{
    					//Check "external module" folder in the installation folder 
    					if(is_dir(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/'.$filebase.'/')){
    						
    						//Copy all folder of external module to modules folder
    						$this->full_copy(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/'.$filebase.'/', APPLICATION_PATH. '/modules/'.$filebase.'/');
    						chmod(APPLICATION_PATH. '/modules/'.$filebase.'/',0777);

    						//Check if external module folder is installed correctly
    						if(is_dir(APPLICATION_PATH. '/modules/'.$filebase.'/')){
    							
    							//Move and rename image file
    							if($uploadedData['image']){
    								$image = GlobalFunctions::uploadFiles($uploadedData['image'], APPLICATION_PATH. '/../public/images/controlPanel/');
    								GlobalFunctions::removeOldFiles($uploadedData['image'], APPLICATION_PATH. '/../public/uploads/tmp/');
    							}
    							else{
    								$image = NULL;
    							}
    			
    							//Check partial phtml for render
    							if(is_dir(APPLICATION_PATH. '/modules/'.$filebase.'/views/scripts/partials/')){
    								if($this->searchPartialFile(APPLICATION_PATH. '/modules/'.$filebase.'/views/scripts/partials/')){
    									$partial_file = $this->searchPartialFile(APPLICATION_PATH. '/modules/'.$filebase.'/views/scripts/partials/');
    								}
    								else
    								{
    									$partial_file = null;
    									//error message
    									$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Partial .phtml file not found. Errors in the installation')));
    									$this->_helper->redirector('index','externalmodules_externalmodules','core');
    								}
    							
    							}
    							
    							//Save module info in wc_module table
    							
    							$module_aux = new Core_Model_Module();
    							$module_obj = $module_aux->getNewRow('wc_module');
    							$module_obj->name = GlobalFunctions::value_cleaner($formData['name']);
    							$module_obj->description = GlobalFunctions::value_cleaner($formData['description']);
    							$module_obj->status = 'active';
    							$module_obj->action = $formData['action'];
    							$module_obj->image = $image;
    							$module_obj->partial = $partial_file;
    							$module_saved = $module_aux->save('wc_module', $module_obj);
    							
    							if($module_saved)
    							{
    								//Save module action permission
    								$module_action_aux = new Core_Model_ModuleAction();
    								$module_action_obj = $module_action_aux->getNewRow('wc_module_action');
    								$module_action_obj->module_id = $module_saved['id'];
    								$module_action_obj->title = 'Create';
    								$module_action_obj->action = 'new';
    								$module_action_saved = $module_action_aux->save('wc_module_action', $module_action_obj);
    								
    								if($module_action_saved){
    									//Save module action profile for admin profile
    									$module_action_profile_aux = new Core_Model_ModuleActionProfile();
    									$module_action_profile_obj = $module_action_profile_aux->getNewRow('wc_module_action_profile');
    									$module_action_profile_obj->profile_id = '1';
    									$module_action_profile_obj->module_action_id = $module_action_saved['id'];
    									$module_action_profile_saved = $module_action_profile_aux->save('wc_module_action_profile', $module_action_profile_obj);
    								}
    								
    								$module_action_obj = $module_action_aux->getNewRow('wc_module_action');
    								$module_action_obj->module_id = $module_saved['id'];
    								$module_action_obj->title = 'Update';
    								$module_action_obj->action = 'edit';
    								$module_action_saved = $module_action_aux->save('wc_module_action', $module_action_obj);
    								
    								if($module_action_saved){
    									//Save module action profile for admin profile
    									$module_action_profile_aux = new Core_Model_ModuleActionProfile();
    									$module_action_profile_obj = $module_action_profile_aux->getNewRow('wc_module_action_profile');
    									$module_action_profile_obj->profile_id = '1';
    									$module_action_profile_obj->module_action_id = $module_action_saved['id'];
    									$module_action_profile_saved = $module_action_profile_aux->save('wc_module_action_profile', $module_action_profile_obj);
    								}
    								
    								$module_action_obj = $module_action_aux->getNewRow('wc_module_action');
    								$module_action_obj->module_id = $module_saved['id'];
    								$module_action_obj->title = 'Delete';
    								$module_action_obj->action = 'delete';
    								$module_action_saved = $module_action_aux->save('wc_module_action', $module_action_obj);
    								
    								if($module_action_saved){
    									//Save module action profile for admin profile
    									$module_action_profile_aux = new Core_Model_ModuleActionProfile();
    									$module_action_profile_obj = $module_action_profile_aux->getNewRow('wc_module_action_profile');
    									$module_action_profile_obj->profile_id = '1';
    									$module_action_profile_obj->module_action_id = $module_action_saved['id'];
    									$module_action_profile_saved = $module_action_profile_aux->save('wc_module_action_profile', $module_action_profile_obj);
    									
    								}
    								
    							}
    						}
    						else 
    						{
    							
    						}
    						
    					}
    					else
    					{
    						//error message
    						$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('External module folder not exist in installation folder. Errors in the installation')));
    						$this->_helper->redirector('index','externalmodules_externalmodules','core');
    					}
    					
    				}
    				else
    				{
    					//error message
    					$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Modules folder not exist o dont have permissions. Errors in the installation')));
    					$this->_helper->redirector('index','externalmodules_externalmodules','core');
    				}
    				
    				//Check if destination folder "public" exist and have permissions
    				if(is_dir(APPLICATION_PATH. '/../public/'))
    				{  						
    					//Check "public" folder in the installation folder
    					if(is_dir(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/')){
    						//Copy css folder to public/css/modules folder		
    						$this->full_copy(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/css/'.$filebase.'/', APPLICATION_PATH.'/../public/css/modules/'.$filebase.'/');
    						chmod(APPLICATION_PATH. '/../public/css/modules/'.$filebase.'/',0777); 						
    						
    						//Copy js folder to public/js/modules folder
    						$this->full_copy(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/js/'.$filebase.'/', APPLICATION_PATH. '/../public/js/modules/'.$filebase.'/');
    						chmod(APPLICATION_PATH. '/../public/js/modules/'.$filebase.'/',0777);
    						
    						//Delete folder installation of temp
    						$this->removeDirectory(APPLICATION_PATH.'/../public/uploads/tmp/'.$filebase);
    						
    						//check user module permissions and recover the modules to show the control panel
    						$id = new Zend_Session_Namespace('id');
							$user = new Core_Model_User();
							$user_profile = $id->user_profile;
							$user_modules = Core_Model_User::getUserModules($user_profile);
							//allowed module actions
							$profile = new Core_Model_Profile();
							$profile_options = $profile->getModuleActionByProfile($user_profile);
							
							//Register new external module in the session var
							$id->user_modules = $user_modules;
							$id->user_modules_actions = $profile_options;
    						
    					}
    					else
    					{
    						//error message
    						$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('External public folder not exist in installation folder. Errors in the installation')));
    						$this->_helper->redirector('index','externalmodules_externalmodules','core');
    					}
    					
    					/**UPLOAD EXTERNAL FILES**/
    					
    				    if(is_dir(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/images/'))
	    				{
	    					$directory = APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/images/';
	    				
	    					//get all css files.
	    					$image_files = glob($directory.'*');
	    					
	    					if(!is_dir(APPLICATION_PATH. '/../public/images/'))
	    					{
	    						$path = APPLICATION_PATH. '/../public/images/';
	    						mkdir($path);
	    						chmod($path, 0777);
	    					}else{
	    						$path = APPLICATION_PATH. '/../public/images/';
	    						chmod($path, 0777);
	    					}
	    				
	    					$current_image_files = glob($path.'*');
	    					$image_already_exist = '';
	    					foreach($image_files as $image)
	    					{
	    						if(in_array(str_replace(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/images/', APPLICATION_PATH. '/../public/images/', $image), $current_image_files)){
	    							$image_already_exist = str_replace(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/images/', '', $image);
	    						}
	    					}
	    					if($image_already_exist == ''){
	    						$this->full_copy(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/images/', APPLICATION_PATH.'/../public/images/');
	    					}else{
	    						$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('The image').' '.$image_already_exist.' '.$lang->translate('already exist into images folder')));
	    						$this->_helper->redirector('index','externalmodules_externalmodules','core');
	    					}
	    				
	    				}
    					
    					if(is_dir(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/css/external/'))
    					{    					
	    					$directory = APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/css/external/';
	    					
	    					//get all css files.
	    					$css_files = glob($directory . "*.css");
	    					
	    					if(!is_dir(APPLICATION_PATH. '/../public/css/external/'))
	    					{
	    						$path = APPLICATION_PATH. '/../public/css/external/';
	    						mkdir($path);
	    						chmod($path, 0777);
	    					}else{
	    						$path = APPLICATION_PATH. '/../public/css/external/';
	    						chmod($path, 0777);
	    					}
	    					
	    					$current_css_files = glob($path . "*.css");
	    					
	    					//print each file name
	    					foreach($css_files as $css)
	    					{
	    						if(!in_array(str_replace(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/css/external/', APPLICATION_PATH. '/../public/css/external/', $css), $current_css_files)){
	    							//website id
	    							$id = New Zend_Session_Namespace('id');
	    							$website_id = $id->website_id;
	    								
	    							//save data
	    							$external_file =  new Core_Model_Externalfiles();
	    							$external_file_obj = $external_file->getNewRow('wc_external_files');
	    							$external_file_obj->name = str_replace(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/css/external/', '', $css);
	    							copy($css, $path.$external_file_obj->name);
	    							$external_file_obj->type = 'css';
	    							$external_file_obj->path = $path.$external_file_obj->name;
	    					
	    							$website = new Core_Model_Website();
	    							$website_list = $website->find('wc_website');
	    							foreach($website_list as $w){
	    								//order files
	    								$next = $external_file->getNextOrderNumber('css',$w->id);
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
	    						}else{
	    							$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('The file').' '.str_replace(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/css/external/', '', $css).' '.$lang->translate('already exist on css external files')));
	    							$this->_helper->redirector('index','externalmodules_externalmodules','core');
	    						}
	    					}
    					}
    					
    					if(is_dir(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/js/external/'))
    					{
	    					$directory = APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/js/external/';
	    					
	    					//get all js files.
	    					$js_files = glob($directory . "*.js");
	    					
	    					if(!is_dir(APPLICATION_PATH. '/../public/js/external/'))
	    					{
	    						$path = APPLICATION_PATH. '/../public/js/external/';
	    						mkdir($path);
	    						chmod($path, 0777);
	    					}else{
	    						$path = APPLICATION_PATH. '/../public/js/external/';
	    						chmod($path, 0777);
	    					}
	    					
	    					$current_js_files = glob($path . "*.js");
	    					
	    					//print each file name
	    					foreach($js_files as $js)
	    					{
	    						if(!in_array(str_replace(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/js/external/', APPLICATION_PATH. '/../public/js/external/', $js), $current_js_files)){
	    							//website id
	    							$id = New Zend_Session_Namespace('id');
	    							$website_id = $id->website_id;
	    							 
	    							//save data
	    							$external_file =  new Core_Model_Externalfiles();
	    							$external_file_obj = $external_file->getNewRow('wc_external_files');
	    							$external_file_obj->name = str_replace(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/js/external/', '', $js);
	    							copy($js, $path.$external_file_obj->name);
	    							$external_file_obj->type = 'js';
	    							$external_file_obj->path = $path.$external_file_obj->name;
	    								
	    							$website = new Core_Model_Website();
	    							$website_list = $website->find('wc_website');
	    							foreach($website_list as $w){
	    								//order files
	    								$next = $external_file->getNextOrderNumber('js',$w->id);
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
	    						}else{
	    							$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('The file').' '.str_replace(APPLICATION_PATH. '/../public/uploads/tmp/'.$filebase.'/public/js/external/', '', $js).' '.$lang->translate('already exist on js external files')));
	    							$this->_helper->redirector('index','externalmodules_externalmodules','core');
	    						}
	    					}
    					}
    					/**END UPLOAD EXTERNAL FILES**/    					
    						
    				}
    				else
    				{
    					//error message
    					$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Public folder not exist or dont have permissions. Errors in the installation')));
    					$this->_helper->redirector('index','externalmodules_externalmodules','core');
    				}
    				
    			} 
    			else 
    			{
    				//error message
    				$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in the installation')));
    				$this->_helper->redirector('index','externalmodules_externalmodules','core');
    			}
    			//success message
    			$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Installation succesfully')));
    			$this->_helper->redirector('index','externalmodules_externalmodules','core');
    		}	
    	}
    	
    	$this->view->form = $form;
    	
    }

    /**
     * Function to copy a folder with contains
     */
    public function full_copy( $source, $target ) {
    	if ( is_dir( $source ) ) {
    		@mkdir( $target );
    		chmod($target,0777);
    		$d = dir( $source );
    		while ( FALSE !== ( $entry = $d->read() ) ) {
    			if ( $entry == '.' || $entry == '..' ) {
    				continue;
    			}
    			$Entry = $source . '/' . $entry;
    			if ( is_dir( $Entry ) ) {
    				$this->full_copy( $Entry, $target . '/' . $entry );
    				continue;
    			}
    			copy( $Entry, $target . '/' . $entry );
    		}
    
    		$d->close();
    	}else {
    		copy( $source, $target );
    	}
    }
    
    /**
     * Remove a non empty directory
     */
    public function removeDirectory($path)
    {
    	$path = rtrim( strval( $path ), '/' ) ;
    
    	$d = dir( $path );
    
    	if( ! $d )
    		return false;
    
    	while ( false !== ($current = $d->read()) )
    	{
    		if( $current === '.' || $current === '..')
    			continue;
    
    		$file = $d->path . '/' . $current;
    
    		if( is_dir($file) )
    			$this->removeDirectory($file);
    
    		if( is_file($file) )
    			unlink($file);
    	}
    
    	rmdir( $d->path );
    	$d->close();
    	return true;
    }
    
    /**
     * Checks if external module name already exist on db
     */
    public function checknameAction()
    {
    	$this->_helper->layout->disableLayout ();
    	$this->_helper->viewRenderer->setNoRender ( TRUE );
    
    	//translate library
    	$lang = Zend_Registry::get ( 'Zend_Translate' );
    
    	if ($this->getRequest ()->isPost ())
    	{
    		$name = $this->_request->getPost ( 'name' );
    
    		$module = new Core_Model_Module();
    
    		$name_param = mb_strtolower($name, 'UTF-8');
    		 
			$data = $module->personalized_find ( 'wc_module', array (array('name','==',$name_param)));
    		
    		if($data)
    			echo json_encode ( FALSE );
    		else
    			echo json_encode ( TRUE );
    	}
    }
    
    /**
     * Check partial file in external module
     */
    public function searchPartialFile($dir) 
    {
    	if (is_dir($dir)) {
    		if ($dh = opendir($dir)) {
    			while (($file = readdir($dh)) !== false) {
    				if(stristr($file,'.phtml')){
    					$partial = $file;
    				}
    					
    			}
    			closedir($dh);
    		}
    	}
    	if(isset($partial)){
    		return $partial;
    	}
    	else
    		return false;
    	
    }
    

}


