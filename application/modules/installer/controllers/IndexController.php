<?php
/**
 * IndexController controls the Installer
 *
 * @category   wicaWeb
 * @package    Installer controllers IndexController
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license	   GNP
 * @author	   Esteban - Diego
 * @version    1.0
 */


class Installer_IndexController extends Zend_Controller_Action
{
	
	/**
	 * Initialize the controller.
	 */
	
	public function init()
	{
		/* Initialize action controller here */
		//create Languages list
		$languages = array(
				'es'=>'Espa&ntilde;ol',
				'en'=>'English',
		);
		$this->view->language_list = $languages;
		
		//checks if there is a previous installation
		if($this->previewinstallation()){
			$preview_install = true;
		}else {
			$preview_install = false;
		}
		$this->view->preview_install = $preview_install;

	}

	/**
	 * Initialize the installer. Sets default Language
	 */
	
	public function indexAction()
	{


		//recover session var
		$installer_session = New Zend_Session_Namespace('installer_session');
		
		if(isset($installer_session->default_language)){
			//language was changed during step 1
			$default_lang = $installer_session->default_language;
		}
		else{
			//clear session var
			Zend_Session::namespaceUnset('installer_session');
			$default_lang = 'en';
		}
			
		//set the translation to English by default
		Zend_Loader::loadClass('Zend_Translate');
		$translate = new Zend_Translate(
				'array',
				APPLICATION_PATH.'/configs/languages/',
				'en',
				array('scan' => Zend_Translate::LOCALE_FILENAME)
		);
		$locale = new Zend_Locale();
		$locale->setLocale($default_lang);

		// setting the right locale
		if ($translate->isAvailable($locale->getLanguage())) {
			$translate->setLocale($locale);
		} else {
			$translate->setLocale('en');
			$default_lang = 'en';
		}
		Zend_Registry::set('Zend_Translate', $translate);
		
		
		$this->view->default_lang = $default_lang;
		
		
		
	}

	/**
	 * Sets the installer language from the available list
	 **/
	public function step1Action()
	{
		// disable autorendering for this action
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender();
		
		
		if ($this->getRequest()->isPost())
		{
			//retrieved data from post
			$formData  = $this->_request->getPost();

			//Save in session var the language
			$installer_session = New Zend_Session_Namespace('installer_session');
			$installer_session->language = $formData['selLanguage'];

			//success var
			$arr_success = array('success'=>true);
			echo json_encode($arr_success);
		}

	}

	/**
	 * Loads the steps according to the selected language
	 **/
	public function stepsAction()
	{
		// disable autorendering for this action
		$this->_helper->layout->disableLayout ();
		//change language
		$this->changeLanguageAction();
				

	}

	/**
	 * Loads the legend according to the selected language
	 **/
	public function legendAction()
	{
		// disable autorendering for this action
		$this->_helper->layout->disableLayout ();
		//change language
		$this->changeLanguageAction();
	}

	/**
	 * Change the language and gets the corresponding translation for the installer
	 **/
	public function changeLanguageAction(){
		//get sections var
		$installer_session = New Zend_Session_Namespace('installer_session');

		//change the installer language
		Zend_Loader::loadClass('Zend_Translate');
		$translate = new Zend_Translate(
				'array',
				APPLICATION_PATH.'/configs/languages/',
				'en',
				array('scan' => Zend_Translate::LOCALE_FILENAME)
		);
		$locale = new Zend_Locale();
		
		//check if the session language is set, otherwise English as default
		if(isset($installer_session->language))
			$locale->setLocale($installer_session->language);
		else
			$locale->setLocale('en');

		// setting the right locale
		if ($translate->isAvailable($locale->getLanguage())) {
			$translate->setLocale($locale);
		} else {
			$translate->setLocale('en');
		}

		Zend_Registry::set('Zend_Translate', $translate);
	}
	
	/**
	 * Test the Database connection
	 **/
	public function dbtestAction(){
		
		//disable autorendering for this action
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender();
		
		//return var 
		$arr_success['success'] = 'TESTING';
		//get post vals
		if ($this->getRequest()->isPost())
		{
			//retrieved data from post
			$formData  = $this->_request->getPost();

			if(!isset($formData['db_password'])){
				$formData['db_password'] = '';
			}
			//Testing the mysql db connection
			$connection = @mysql_connect($formData['db_host'], $formData['db_user'], $formData['db_password']);
			if(!$connection)
			{
				$arr_success['success'] = false;
			}
			else{
				$arr_success['success'] = true;
			}
		}
		//return
		echo json_encode($arr_success);
	}
	
	/**
	 * Validate pre Install Details for installation 
	 **/
	
	public function preinstalldetailsAction(){
		
		//disable autorendering for this action
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender();
		
		//check the corrrect version of php for wicaWeb
		$validation = array();
		if(phpversion() > '5.1.10'){
			$validation['php']=true;
		}else
		{
			$validation['php']=false;
		}
	
		//check if mysql is installed
		if(function_exists('mysql_connect') || function_exists('mysqli_connect')){
			$validation['mysql']=true;
		}
		else
		{
			$validation['mysql']=false;
		}
	
		//check if application.ini file is writable and readable
		if(file_exists(APPLICATION_PATH.'/configs/application.ini')){
			if(is_writable(APPLICATION_PATH.'/configs/application.ini') || is_writable('../')){
				$validation['writable']=true;
			}
			else
			{
				$validation['writable']=false;
			}
	
		}else{
			$validation['writable']=false;
		}
	
		echo json_encode($validation);
	}
	
	
	/**
	 * Create DB structure
	 **/
	
	public function createdbestructureAction(){
		//disable autorendering for this action
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender();
		
		//return var
		$arr_success['success'] = 'TESTING';
		//get post vals
		if ($this->getRequest()->isPost())
		{
			//retrieved data from post
			$formData  = $this->_request->getPost();
		
			if(!isset($formData['db_password'])){
				$formData['db_password'] = '';
			}
			//Create the mysql db
			// First test the connection
			$link = @mysql_connect($formData['db_host'], $formData['db_user'], $formData['db_password']);
			if(!$link)
			{
				$arr_success['success'] = false;
			}
			
			// Select the DB
			$db_selected = @mysql_select_db($formData['db_name'], $link);
			
			if($db_selected){
				//Create database estructure of external .sql file
				
				# Load the database stuff
				$path1 = APPLICATION_PATH.'/modules/installer/sql_scripts/bdd_installer.sql';
				$path2 = APPLICATION_PATH.'/modules/installer/sql_scripts/basic_data_installer.sql';
				if(file_exists($path1) && file_exists($path2))
				{
					$this->executeSqlScript($link,$path1);
					$this->executeSqlScript($link,$path2);
					$arr_success['success'] = true;
				}
				else
				{
					$arr_success['success'] = false;
				}
			}
			
			if($db_selected==false && $formData['db_new']=='no')
			{
				$arr_success['success'] = false;
			}
			elseif ($db_selected==false && $formData['db_new']=='yes')
			{
				// Create the DB
				$result = @mysql_query("CREATE DATABASE {$formData['db_name']} CHARACTER SET utf8 COLLATE utf8_unicode_ci", $link);
				$db_selected = @mysql_select_db($formData['db_name'], $link);
				if ($result==true)
				{
					//Create database estructure of external .sql file
						
					// Load the database files
					$path1 = APPLICATION_PATH.'/modules/installer/sql_scripts/bdd_installer.sql';
					$path2 = APPLICATION_PATH.'/modules/installer/sql_scripts/basic_data_installer.sql';
					if(file_exists($path1) && file_exists($path2))
					{
						$this->executeSqlScript($link,$path1);//Run script
						$this->executeSqlScript($link,$path2);
						$arr_success['success'] = true;
					}else
					{
						$arr_success['success'] = false;
					}
					
				}else{
					$arr_success['success'] = false;
				}
			}
						
		}
		//return
		echo json_encode($arr_success);
	}
	
	
	/**
	 * Create Admin User in database.
	 **/
	public function createadminuserAction(){
	
		//disable autorendering for this action
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender();
	
		//return var
		$arr_success['success'] = 'TESTING';
		//get post vals
		if ($this->getRequest()->isPost())
		{
			//retrieved data from post
			$formData  = $this->_request->getPost();
			
			// First test the connection
			$link = @mysql_connect($formData['db_host'], $formData['db_user'], $formData['db_password']);
			if($link==false)
			{
				
				$arr_success['success'] = false;
			}else{

				// Select the DB
				$db_selected = mysql_select_db($formData['db_name'], $link);
				
				if($db_selected){
					
					
					//Check wc_user table is empty
					$sql_query = "SELECT COUNT(*) FROM `wc_user`";
					$result_count = mysql_query($sql_query,$link);
					$total=mysql_result($result_count,0);
					
					
					if ($total >0 ){
						//Update admin user in DB
						$sql_query_user = "UPDATE `wc_user` SET name='".GlobalFunctions::value_cleaner($formData['user_name'])."',lastname='".GlobalFunctions::value_cleaner($formData['user_lastname'])."',email='".GlobalFunctions::value_cleaner($formData['user_email'])."',password='".md5($formData['user_password'])."' WHERE id=1";
    						
					} else {
						//Create admin user in DB
						$sql_query_user = "INSERT INTO `wc_user` (`id`,`profile_id`, `name`, `lastname`, `email`, `username`, `password`,`status`) VALUES
    				(1, 1, '".GlobalFunctions::value_cleaner($formData['user_name'])."','".GlobalFunctions::value_cleaner($formData['user_lastname'])."','".GlobalFunctions::value_cleaner($formData['user_email'])."','admin','".md5($formData['user_password'])."','active')";
					}

					$result = mysql_query($sql_query_user,$link);
					if($result){
						//put user and pass in session var
						$installer_session = New Zend_Session_Namespace('installer_session');
						$installer_session->user_name = 'admin';
						$installer_session->user_pass = $formData['user_password'];
						$installer_session->user_name_lastname = $formData['user_name'].' '.$formData['user_lastname'];
						$installer_session->user_email = $formData['user_email'];

						$arr_success['success'] = true;

					}else{
						$arr_success['success'] = false;
					}
				}
			}
			
		}
		//return
		echo json_encode($arr_success);
	}
	

	/**
	 * Write Application.ini with installation parameters.
	 **/
	public function writeparametersAction(){
	
		//disable autorendering for this action
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender();
	
		//return var
		$arr_success['success'] = 'TESTING';
		//get post vals
		if ($this->getRequest()->isPost())
		{
			//retrieved data from post
			$formData  = $this->_request->getPost();
			
			//Check permissions of Application.ini file
			if(file_exists(APPLICATION_PATH.'/configs/application.ini')){
				if(is_writable(APPLICATION_PATH.'/configs/application.ini') || is_writable('../')){
					
					//Application.ini file in string
					$content_file = @file_get_contents(APPLICATION_PATH.'/configs/application.ini');

					//Search and update db parameters with current installation parameters
					$content_file = preg_replace("/.*resources.db.adapter = \".*\"/", "resources.db.adapter = \"pdo_mysql\"", $content_file, -1, $count1);
					$content_file = preg_replace("/.*resources.db.params.host = \".*\"/", "resources.db.params.host = \"".$formData['db_host']."\"", $content_file, -1, $count2);
					$content_file = preg_replace("/.*resources.db.params.username = \".*\"/", "resources.db.params.username = \"".$formData['db_user']."\"", $content_file, -1, $count3);
					$content_file = preg_replace("/.*resources.db.params.password = \".*\"/", "resources.db.params.password = \"".$formData['db_password']."\"", $content_file, -1, $count4);
					$content_file = preg_replace("/.*resources.db.params.dbname = \".*\"/", "resources.db.params.dbname = \"".$formData['db_name']."\"", $content_file, -1, $count5);
					$content_file = preg_replace("/.*resources.db.isDefaultTableAdapter = .*/", "resources.db.isDefaultTableAdapter = true", $content_file, -1, $count6);
					
					//Check successfully search and replace
					if($count1 && $count2 && $count3 && $count4 && $count5 && $count6 == 1){
						//Rewrite application.ini with installation parameters																										
						if(@file_put_contents(APPLICATION_PATH.'/configs/application.ini', $content_file)){
							$arr_success['success']=true;
						}else{
							$arr_success['success']=false;
						}
					}else{
						$arr_success['success']=false;
					} 
				}
				else
				{
					$arr_success['success']=false;
				}
			
			}else{
				$arr_success['success']=false;
			}
	
		}
		//return
		echo json_encode($arr_success);
	}
		
	
	
	
	/**
	 * Execute a script .sql type
	 *
	 * @param MySQLconnect $con
	 * @param .sql file $_fileName
	 */
	public function executeSqlScript($con, $_fileName) {
		$sql = file_get_contents($_fileName); // Read file
		// Returns non empty tokens
		$tokens = preg_split("/(--.*\s+|\s+|\/\*.*\*\/)/", $sql, null, PREG_SPLIT_NO_EMPTY);
		$length = count($tokens);
	
		$query = '';
		$inSentence = false;
		$curDelimiter = ";";
		// Read string
		for($i = 0; $i < $length; $i++) {
			$lower = strtolower($tokens[$i]);
			$isStarter = in_array($lower, array( // Check the begin of query
					'select', 'update', 'delete', 'insert',
					'delimiter', 'create', 'alter', 'drop',
					'call', 'set', 'use'
			));
	
			if($inSentence) { 
				if($tokens[$i] == $curDelimiter || substr(trim($tokens[$i]), -1*(strlen($curDelimiter))) == $curDelimiter) {
					
					$query .= str_replace($curDelimiter, '', $tokens[$i]); // Delete delimiter
					$result=mysql_query($query,$con);
					$query = ""; // Prepare next query
					$tokens[$i] = '';
					$inSentence = false;
				}
			}
			else if($isStarter) { 
				
				if($lower == 'delimiter' && isset($tokens[$i+1]))
					$curDelimiter = $tokens[$i+1];
				else
					$inSentence = true; 
				$query = "";
			}
			$query .= "{$tokens[$i]} "; 
		}
	}
	
	/**
	 * Set the new language value
	 **/
	public function setlanguageAction(){
		// disable autorendering for this action
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender();
		
		if ($this->getRequest()->isPost())
		{
			//retrieved data from post
			$formData  = $this->_request->getPost();
			
			//Save in session var the language
			$installer_session = New Zend_Session_Namespace('installer_session');
			$installer_session->default_language = $formData['selLanguage'];
		}
		
		//success var
		$arr_success = array('success'=>true);
		echo json_encode($arr_success);
	}
	
	
	
	/**
	 * Save website data 
	 **/
	public function savewebsiteAction(){

		//translate library
		$lang = Zend_Registry::get('Zend_Translate');

		//disable autorendering for this action
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender();
		
		//Create a Website Form
		$form = New Installer_Form_Website();
		$form->setMethod('post');
		
		//after submit the form    	
    	if ($this->getRequest()->isPost()) {
    		$formData = $this->getRequest()->getPost();

	    		//Recover the uploaded values
	    		$uploadedData = $form->getValues();
	    		
	    		
	    		//MOVE AND RENAME FILES
	    		if($uploadedData['logo']){
	    			$logo = GlobalFunctions::uploadFiles($uploadedData['logo'], APPLICATION_PATH. '/../public/uploads/website/');
	    		}
	    		else{
	    			$logo = NULL;
	    		}
	    		
	    		if($uploadedData['icon']){
	    			$icon = GlobalFunctions::uploadFiles($uploadedData['icon'], APPLICATION_PATH. '/../public/uploads/website/');
	    		}
	    		else{
	    			$icon = NULL;
	    		}
	    		 
	    		// END RENAME
    		
	    		//check for defualt websites
	    		$website =  new Core_Model_Website();
	    		$website_obj = $website->getNewRow('wc_website');
    			
    			//save data
				$website_obj->language_id = $formData['language_id'];
    			$website_obj->template_id = $formData['template_id'];
    			$website_obj->name = GlobalFunctions::value_cleaner($formData['name']);
    			$website_obj->description = GlobalFunctions::value_cleaner($formData['description']);
    			$website_obj->keywords = GlobalFunctions::value_cleaner($formData['keywords']);
    			$website_obj->website_url = GlobalFunctions::value_cleaner($formData['website_url']);
    			$website_obj->default_page = 'yes';
    			$website_obj->logo = $logo;
    			$website_obj->icon = $icon;
    			$website_obj->info_email = $formData['info_email'];
    			$website_obj->copyright = GlobalFunctions::value_cleaner($formData['copyright']);
    			$website_obj->publication_approve = 'no';
    			$website_obj->prints = 'no';
    			$website_obj->friendly_url = 'no';
    			$website_obj->tiny_url = 'no';
    			$website_obj->log = 'no';
    			$website_obj->sitemap_level = '1';
    			$website_obj->dictionary = 'no';
    			$website_obj->private_section = 'no';
    			$website_obj->section_expiration = 'no';
    			$website_obj->section_author = 'no';
    			$website_obj->section_feature = 'no';
    			$website_obj->section_highlight = 'no';
				$website_obj->section_comments_type ='none';
    			$website_obj->section_comments = 'none';
    			$website_obj->section_images_number = '0';
    			$website_obj->section_storage = 'no';
    			$website_obj->section_rss = 'no';
    			$website_obj->analytics = null;
    			$website_obj->max_height = 1000;
    			$website_obj->max_width = 1000;
    			$website_obj->time_zone = 'GMT-12';
    			$website_obj->date_format = 'dd/mm/yyyy';
    			$website_obj->hour_format = '24H';
    			$website_obj->number_format = '1';

    			
    			
    			//Put template_id in session var
    			$installer_session = New Zend_Session_Namespace('installer_session');
    			$installer_session->template_id = $formData['template_id'];    			

    			// Save data							
				$saved_website = $website->save('wc_website',$website_obj);

				if($saved_website){
                    
                    /*Register product external files*/
                    for($i_ext=1; $i_ext<=4; $i_ext++)
                    {
                        $external_file =  new Core_Model_Externalfiles();
                        $stored_data = $external_file->find('wc_external_files', array('id'=>$i_ext));
                        if($stored_data[0]->id){
                            $external_file_obj = $external_file->getNewRow('wc_external_files');
                            $external_file_obj->id = $stored_data[0]->id;
                            $external_file_obj->website_id = $saved_website['id'];
                            $external_file_obj->name = GlobalFunctions::value_cleaner($stored_data[0]->name);
                            $external_file_obj->path = GlobalFunctions::value_cleaner($stored_data[0]->path);
                            $external_file_obj->type = $stored_data[0]->type;
                            $external_file_obj->order_number = $stored_data[0]->order_number;
                            $external_file->save('wc_external_files',$external_file_obj);
                        }
                    }
                    /*End register product external files*/

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
				GlobalFunctions::removeOldFiles($uploadedData['icon'], APPLICATION_PATH. '/../public/uploads/tmp/');
				GlobalFunctions::removeOldFiles($uploadedData['offline_image'], APPLICATION_PATH. '/../public/uploads/tmp/');
				GlobalFunctions::removeOldFiles($uploadedData['coming_soon_image'], APPLICATION_PATH. '/../public/uploads/tmp/');

				if($saved_website && count($save_status)==3)
				{
					//if the website info and the states are correctly saved. Autologin to controlpanel
					$login_user = $this->autoLogin();
					if($login_user){
					

						//add the session website id to the new website created
						$id = New Zend_Session_Namespace('id');
						$id->website_id = $saved_website['id'];
						$id->website_name_info = utf8_encode($website_obj->name);
						
						$create_section = $this->createFirstSection($saved_website['id']);

						if(!$create_section){
							//if the website info and the states are not correctly saved. Shows the success message and redirects to the website list
							$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Errors in saving data')));
						}
						else{
						
							//Lock the installer
							$path = APPLICATION_PATH . '/../public/installer.lock';
							file_put_contents($path, "Installer lock file. DO NOT DELETE THIS FILE");
							

							$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Website sucesfully created. Welcome to WicaWeb')));
							$this->_helper->redirector('controlpanel','index','core'); //go to control panel page
						
						}
						
						
					}else{
					
						$this->_helper->redirector('login','index','core'); //go to index page
					}

					
				}
				else{
					$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in saving data')));
					$this->_helper->redirector('index','index','core');
				}								
				
			}
			
	}

	
	/**
	 * Check preview installation
	 * @return boolean
	 **/
	
	public function previewinstallation(){

		// Check if run the installer again
		if(file_exists(APPLICATION_PATH . '/../public/installer.lock'))
		{
			return true;
		}else
			return false;

	}	
	
	/**
	 * Function that auto logs the user to the system
	 * @return boolean
	 **/
	
	public function autoLogin(){
		//Recovery session var
		$installer_session = New Zend_Session_Namespace('installer_session');
		$user_name=$installer_session->user_name;
		$user_pass=$installer_session->user_pass;
		
		// Get our authentication adapter and check credentials
		$adapter = $this->getAuthAdapter(array('username'=>$user_name,'password'=>$user_pass));
		$auth = Zend_Auth::getInstance ();
		$result = $auth->authenticate ( $adapter );
		
		if (! $result->isValid ()) {
			return false;
		}else {
			$data = $adapter->getResultRowObject(null,'password');
			
			
			//check user module permissions and recover the modules to show the control panel
			$user = new Core_Model_User();
			$logged_user = $user->find('wc_user',array('id'=>$data->id));
			$user_profile = $logged_user[0]->profile_id;
			$user_modules = Core_Model_User::getUserModules($user_profile);
			//allowed module actions
			$profile = new Core_Model_Profile();
			$profile_options = $profile->getModuleActionByProfile($user_profile);
			//allowed sections
			$section_profile = new Core_Model_SectionProfile();
			$sections_opt = $section_profile->find('wc_section_profile', array('profile_id'=>$user_profile));
			
			//Set the user id in the session var for further use
			$id = New Zend_Session_Namespace('id');
			$id->user_id = $data->id;
			$id->user_profile = $user_profile;
			$id->user_name_info = utf8_encode($data->name.' '.$data->lastname);
			$id->user_modules = $user_modules;
			$id->user_modules_actions = $profile_options;
			$id->user_allowed_sections = $sections_opt;
			

			return true;
		}
			
	}
	
	/**
	 * Build an adapter to authenticate
	 * @param array $params
	 * @return adapter
	 */
	public function getAuthAdapter(array $params)
	{
		$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	
		$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
	
		$authAdapter
		->setTableName('wc_user')
		->setIdentityColumn('username')
		->setCredentialColumn('password')
		->setIdentity($params['username'])
		->setCredential(md5($params['password']));
	
		return $authAdapter;
	}
	
	
	/**
	 * Function that auto logs the user to the system, through ajax
	 * 
	 */
	public function autologinajaxAction()
	{
		//disable autorendering for this action
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender();

		//return var
		$arr_success['success']= false;
		
		if($this->autoLogin()){
			//Lock the installer
			$path = APPLICATION_PATH . '/../public/installer.lock';
			file_put_contents($path, "installer lock file.  DO NOT DELETE THIS FILE");
			$arr_success['success']= true;
			$new = New Zend_Session_Namespace('new_website'); 
			$new->website = TRUE; //First website created
			
			$id = New Zend_Session_Namespace('id');
			$id->website_id = ''; //Erase werbsite_id of session var
			$id->website_name_info = ''; //Erase website_name_info of session var
			
		}
		
		echo json_encode($arr_success);
		
	}
	
	/**
	 * Build an adapter to authenticate
	 * @param array $params
	 * @return adapter
	 */	
	
	public static function createFirstSection($website_id){

		//create section
		$section = new Core_Model_Section();
		 
		//searchs for stored session data
		$installer_session = New Zend_Session_Namespace('installer_session');
		$template_id = $installer_session->template_id;
		$area = new Core_Model_Area();
		$aux = $area->personalized_find('wc_area',array(array('name','LIKE','wica_area_content'),array('template_id','=',$template_id)));
		$area_id = $aux[0]->id;

		//searchs for stored session data
		$id = New Zend_Session_Namespace('id');
	
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
		
		//Find area_content by template_id
		$id = New Zend_Session_Namespace('id');
		
		
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
	 * Create a Website Form for Step 6
	 */	
	
	public function step6Action(){
		
		//disable autorendering for this action
		$this->_helper->layout->disableLayout ();
		//change language
		$this->changeLanguageAction();
		
		//get all templates
		$template_model = new Core_Model_WebsiteTemplate();
		$aux = $template_model->find('wc_website_template');
		$this->view->template_list = $aux;
		
		
		//create a new express website form
		$form = New Installer_Form_Website();
		$form->setMethod('post');
		
		$installer_session = New Zend_Session_Namespace('installer_session');

		$form->info_email->setValue($installer_session->user_email);
		$form->copyright->setValue($installer_session->user_name_lastname);
		
		$this->view->form = $form;
	}
	
}