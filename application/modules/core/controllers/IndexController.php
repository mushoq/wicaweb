<?php

/**
 * IndexController controls the login process
 *
 * @category   wicaWeb
 * @package    Core controllers IndexController
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license	   GNP
 * @author	   Santiago Arellano
 * @version    1.0
 */


class Core_IndexController extends Zend_Controller_Action {
	
	public function getForm() {
		return new Core_Form_Login_Login ( array (
				'action' => '/core/index/login',
				'method' => 'post' 
		) );
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
	 * Get the login form
	 */	
	public function indexAction() {
		
		if(GlobalFunctions::checkInstallationFile())
		{
			Zend_Auth::getInstance ()->clearIdentity ();
			$this->view->form = $this->getForm ();
			$this->_helper->_layout->setLayout('login');
		}
		else
		{
			//Redirect to installation
			$this->_helper->redirector ( 'index','index','installer' );
		}
	}

	/**
	 * Login process authenticate valid user and password
	 */	
	public function loginAction() {
		$request = $this->getRequest ();
		$this->_helper->_layout->setLayout('login');
		
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		// Check if we have a POST request
		if (! $request->isPost ()) {
			$this->_helper->redirector ( 'index','index','core' );
		}
		
		// Get our form and validate it
		$form = $this->getForm ();
		if (! $form->isValid ( $request->getPost () )) {
			// Invalid entries
			$this->view->form = $form;
			$form->setDescription ($lang->translate('Supplied credential is invalid.'));
			return $this->render ( 'index' ); // re-render the login form
		}
		
		$user = new Core_Model_User();
		$user_status = $user->find('wc_user',array('username'=>$request->getParam('username')));
		
		//user active is allowed to validate credentials
		$allowed = true;
		
		if(count($user_status)>0)
		{			
			if($user_status[0]->status!='active')
				$allowed = false;
		}
		else
		{
			$allowed = false;
		}
		
		if (! $allowed ) 
		{
			// Invalid entries
			$this->view->form = $form;
			$form->setDescription ($lang->translate('User status inactive.'));
			return $this->render ( 'index' ); // re-render the login form
		}		
		
		// Get our authentication adapter and check credentials
		$adapter = $this->getAuthAdapter ( $request->getPost () );
		$auth = Zend_Auth::getInstance ();
		$result = $auth->authenticate ( $adapter );		
		
		if (! $result->isValid ()) {
			//Invalid credentials for login or inactive user
			foreach ($result->getMessages() as $message) {
		        $errors = $lang->translate($message).' ';
		    }
			$form->setDescription ($errors);
			$this->view->form = $form;
			return $this->render ( 'index' ); // re-render the login form
		}
		
		//We're authenticated! Redirect to the home page
		//Recover the logged user info	
		$data = $adapter->getResultRowObject(null,'password');
		
		//check user module permissions and recover the modules to show the control panel
		$user = new Core_Model_User();
		$logged_user = $user->find('wc_user',array('id'=>$data->id));
		$user_profile = $logged_user[0]->profile_id;
		$user_modules = Core_Model_User::getUserModules($user_profile);
		//allowed module actions
		$profile = new Core_Model_Profile();
		$profile_options = $profile->getModuleActionByProfile($user_profile);
					
		//Set the user id in the session var for further use
		$id = new Zend_Session_Namespace('id');
		//$id->setExpirationSeconds('15');
		$id->user_id = $data->id;
		$id->user_profile = $user_profile;
		$id->user_name_info = utf8_encode($data->name.' '.$data->lastname);
		$id->user_modules = $user_modules;
		$id->user_modules_actions = $profile_options;
				
		$this->_helper->redirector('controlpanel','index','core'); //go to control panel page
	}

	/**
	 * Logout process clear the identify credentials
	 */	
	public function logoutAction() {
		Zend_Auth::getInstance ()->clearIdentity ();

		Zend_Session::namespaceUnset('id');
		Zend_Session::forgetMe();
		Zend_Session::destroy(true);
		
		$this->_helper->redirector ( 'index','index','core' ); // back to login page
	}
	
	/**
	 * Get the control panel of the site
	 */
	public function controlpanelAction() {
		$this->view->page = 'controlpanel';
		
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		//check logged in user
		if (!Zend_Auth::getInstance ()->hasIdentity ()) {
			//translate library
			$lang = Zend_Registry::get('Zend_Translate');			
			throw new Zend_Exception("CUSTOM_EXCEPTION:".$lang->translate('No Access Permissions').'<br/><br/>'.'<a href="/core">'.$lang->translate('Login to the Administration').'</a>');		
		}
				
		$id = New Zend_Session_Namespace('id');
		
		//websites according user profile
		$selected_websites ='';
		
		if($id->user_id!=1)
		{
			//Recover the websites list available per user
			$user = new Core_Model_User();
			$logged_user = $user->find('wc_user', array('id'=>$id->user_id));
			$user_profile = $logged_user[0]->profile_id;
			//websites according profile
			$website_profile = new Core_Model_WebsiteProfile('wc_website_profile');
			$selected_websites = $website_profile->find('wc_website_profile', array('profile_id'=>$user_profile));			
		}
		else
		{
			$user_profile = 1;
		}
		
		//check user module permissions and recover the modules to show the control panel
		$menu_modules = Core_Model_User::getUserModules($user_profile);
		$this->view->menu_modules = $menu_modules;
		
		$website_model = New Core_Model_Website();
		$aux = $website_model->find('wc_website');		
			
		//if there are no websites available. Redirect to create a new one
		if(!count($aux)){
			$new = New Zend_Session_Namespace('new_website');
			$new->website = TRUE;
			$this->_helper->redirector('index','website_website','core'); //go to website config
		}
		
		//create a new form for the select
		$form2 = New Zend_Form();
		$form2->setAttrib('name', 'select_website');
		$form2->setAttrib('id', 'select_website');
		$form2->setMethod('post');
		$form2->setAction('/core/index/selectedwebsite');
		
		//create the select element
		$websites = New Zend_Form_Element_Select('websites');
		$websites->setLabel($lang->translate('Registered Website').':');		
		
		//create the array to populate the select
		$list = array();
		$selected = true;		
		
		foreach ($aux as $w)
		{
			if($selected_websites)
			{
				$selected = false;
				foreach ($selected_websites as $wpr)
				{
					if($w->id == $wpr->website_id)
						$selected = true;
				}
			}
			if($selected){
				$list[$w->id] = $w->name;
			}		
		}
		
		$websites->setMultiOptions($list);
		$websites->setAttrib("onchange", "$('#select_website').submit()");
				
		if(isset($id->website_id))
		{
			$websites->setValue($id->website_id);		
			//get language website abbreviation
			$language = GlobalFunctions::getLanguageAbbreviationOfWebsite($id->website_id);
		}
		else
		{		
			//gets the first element of the websites lists, to set the session for the first time.
			$aux = array_slice(array_keys($list), 0,1);
			$websites->setValue($aux[0]);
			$id->website_id = $aux[0]; //setting the session var
			$id->website_name_info = $list[$aux[0]];
			
			//allowed sections
			$options_arr = array();
			$options_str = '';
			$section_profile = new Core_Model_SectionProfile();
			$sections_published_opt = $section_profile->getPublishedSectionsByProfile($id->user_profile, $id->website_id);	
			if(count($sections_published_opt)>0)
			{
				foreach ($sections_published_opt as $ky => $stp)
				{
					if($stp->section_id)
						$options_arr[] = $stp->section_id;
				}
			}
						
			if(count($options_arr)>0)
				$options_str = implode(',',$options_arr);
						
			$id->user_allowed_sections = $options_str;
			
			//get language website abbreviation
			$language = GlobalFunctions::getLanguageAbbreviationOfWebsite($id->website_id);	
		}
		
		// Translate to website language
		$id->website_language = $language;
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
		}else {
			$translate->setLocale('es');
		}
		
		Zend_Registry::set('Zend_Translate', $translate);
		
		$form2->addElement($websites);
		$this->view->select = $form2;
	}
	
	/**
	 * Sets in the session the serial of the selected website
	 */
	public function selectedwebsiteAction() {
		if ($this->getRequest()->isPost()) {
			$formData = $this->getRequest()->getPost();
			
			$id = New Zend_Session_Namespace('id');
			$id->website_id = $formData['websites'];
			
			//Get the website language abbreviation
					
			$language = GlobalFunctions::getLanguageAbbreviationOfWebsite($id->website_id);
			
			$id->website_language = $language;

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
					
			//allowed sections
			$options_arr = array();
			$options_str = '';
			$section_profile = new Core_Model_SectionProfile();
			$sections_published_opt = $section_profile->getPublishedSectionsByProfile($id->user_profile, $id->website_id);	
			if(count($sections_published_opt)>0)
			{
				foreach ($sections_published_opt as $ky => $stp)
				{
					if($stp->section_id)
						$options_arr[] = $stp->section_id;
				}
			}
			
			if(count($options_arr)>0)
				$options_str = implode(',',$options_arr);
						
			$id->user_allowed_sections = $options_str;
			
			//Recover the websites list
			$website_model = New Core_Model_Website();
			$aux = $website_model->find('wc_website');
			
			foreach ($aux as $w){
				if($w->id == $formData['websites'])
					$id->website_name_info = $w->name;
			}
			
			$this->_helper->viewRenderer->setNoRender();
			$this->_forward('controlpanel');
		}
		 
	}
	

}

