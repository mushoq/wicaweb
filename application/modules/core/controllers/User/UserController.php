<?php
/**
 * User Controller
 * This file has the fucntions create and edit the information of a user 
 * 
 * @category   WicaWeb
 * @package    Core_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   Esteban 
 * @version    1.0
 * 
 */
class Core_User_UserController extends Zend_Controller_Action
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
				if($mod->module_id == '3')
				{
					$profile_access[$mod->action_id] = array('action'=> $mod->action_name, 'title'=>$mod->action_title);
				}
			}			
		}

		$this->view->user_links = $profile_access;
    }
	
    /**
     * Generates a list of all users
     */
    public function indexAction()
    {
    	$user= new Core_Model_User();
    	$this->view->users = $user->find('wc_user');
    }
    
    /**
     * Creates a New User
     */
    public function newAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	
    	//check if available profiles
    	$profile_obj = new Core_Model_Profile();
    	$profiles_list = $profile_obj->personalized_find('wc_profile', array(array('id','!=','1'),array('status','=','active')));
    	if(!$profiles_list)
    	{    	
    		$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Required profiles for user')));
    		$this->_helper->redirector('index','profile_profile','core');
    	}
    	
    	$request = $this->getRequest();
    	
    	$form = New Core_Form_User_User();
    	$form->setMethod('post');
    	
		//after submit the form    	
    	if ($this->getRequest()->isPost()) 
    	{
    		$formData = $this->getRequest()->getPost();
    		if ($form->isValid($formData)) 
    		{
    			    			
    			//save data
    			$user =  new Core_Model_User();
    			$user_obj = $user->getNewRow('wc_user');
    			
    			$user_obj->name = GlobalFunctions::value_cleaner($formData['name']);
    			$user_obj->lastname = GlobalFunctions::value_cleaner($formData['lastname']);
    			$user_obj->identification = GlobalFunctions::value_cleaner($formData['identification']);
    			$user_obj->email = GlobalFunctions::value_cleaner($formData['email']);
    			$user_obj->phone = $formData['phone'];
    			$user_obj->username = GlobalFunctions::value_cleaner($formData['username']);
    			$user_obj->password = GlobalFunctions::value_cleaner(md5($formData['password']));
    			$user_obj->profile_id = GlobalFunctions::value_cleaner($formData['profile']);
    			$user_obj->creation_date = date('Y-m-d h%i%s');
    			$user_obj->status = 'active';
    			
    			// Save data							
				$saved_user = $user->save('wc_user',$user_obj);
				
				if($saved_user)
				{										
					//success message
					$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
					$this->_helper->redirector('index','user_user','core');
				}
				else{
					//Adding Error Messages
					$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in saving data')));
					$this->_helper->redirector('new','user_user','core');
				}
    		}
    		else
    		{
    			//Adding Error Messages
    			$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Invalid Form')));
    			$this->_helper->redirector('new','user_user','core');
    		}
    		 
    	}
    	
    	$this->view->form = $form;
    }
    
    /**
     * Edit User Info.
     * Load a Form with all existing data
     */
    public function editAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	
    	$user = new Core_Model_User;
    	$request_params = $this->getRequest()->getParams();
    	
    	$form = New Core_Form_User_User($request_params['action']);
    	$form->setMethod('post');
    	
    	//check if available profiles
    	$profile_obj = new Core_Model_Profile();
    	$profiles_list = $profile_obj->personalized_find('wc_profile', array(array('id','!=','1'),array('status','=','active')));
    	if(!$profiles_list)
    	{
    		$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Required profiles for user')));
    		$this->_helper->redirector('index','profile_profile','core');
    	}
    	
    	//user_id
    	$id = $this->_getParam('id');
    	
    	if(!$id)
    	{
    		$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Selected invalid user')));
    		$this->_helper->redirector('index','user_user','core');
    	}    	
    	
    	$data = $user->find('wc_user', array('id'=>$id)); //get the selected user data
    	
    	if(!$data)
    	{
    		$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Selected invalid user')));
    		$this->_helper->redirector('index','user_user','core');
    	}
    	
    	$arr_data = get_object_vars($data[0]); //make an array of the object data
    	//add profile
    	$arr_data['profile'] = $data[0]->profile_id;
 	    	    	    	    	
		//populate the form
		$form->populate($arr_data);
    	$this->view->form = $form;
    	
    	if ($this->getRequest()->isPost())
    	{
    		$formData  = $this->_request->getPost();
    		if ($form->isValid($formData))
    		{
    			
    			//set Data
    			$user =  new Core_Model_User();
    			$user_obj = $user->getNewRow('wc_user');
    			$user_obj->id = $formData['id'];    			
    			$user_obj->name = GlobalFunctions::value_cleaner($formData['name']);
    			$user_obj->lastname = GlobalFunctions::value_cleaner($formData['lastname']);
    			$user_obj->identification = GlobalFunctions::value_cleaner($formData['identification']);
    			$user_obj->email = GlobalFunctions::value_cleaner($formData['email']);
    			$user_obj->phone = $formData['phone'];
    			$user_obj->username = GlobalFunctions::value_cleaner($formData['username']);
    			if($formData['password_checkbox']== '1')
    				$user_obj->password = GlobalFunctions::value_cleaner(md5($formData['password']));
    			else
    				$user_obj->password = $arr_data['password'];
    			
    			$user_obj->profile_id = GlobalFunctions::value_cleaner($formData['profile']);
    			$user_obj->creation_date = $arr_data['creation_date'];
    			$user_obj->last_update_date = date('Y-m-d h%i%s');    			
    			$user_obj->status = $formData['status'];
    			    			
    			// Save data							
				$saved_user = $user->save('wc_user',$user_obj);
				
	    		if($saved_user)
	    		{   																														
					//success message
					$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
					$this->_helper->redirector('index','user_user','core');
				}
				else{
					//Adding Error Messages
					$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in saving data')));
					$this->_helper->redirector('new','user_user','core');
				}
    		}
    		else
    		{
    			//Adding Error Messages
    			$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Invalid Form')));
    			$this->_helper->redirector('edit','user_user','core',array('id'=>$id));
    		}
    	}
    
    }
    
    /**
     * Validate that the entered username is not repeated
     */
    public function validateusernameAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();
    	if ($this->getRequest()->isPost())
    	{
    		$data = $this->_request->getPost();
    		//Zend_Debug::dump($data);
    		$username = mb_strtolower($data['username'], 'UTF-8');
    		$id = -1;
    		if(isset($data['id']))
    			$id= $data['id'];
    		
    		$user =  new Core_Model_User();
    		if(isset($id) && $id>0)
    			$user_array = $user->personalized_find('wc_user', array(array('username', '=', $username),array('id', '!=', $id)));
    		else
    			$user_array = $user->personalized_find('wc_user', array(array('username', '=', $username)));
    		
    		if($user_array && count($user_array)>0)
    			echo json_encode(false);
    		else
    			echo json_encode(true);
    	}
    }
}
