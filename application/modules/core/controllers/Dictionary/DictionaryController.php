<?php
/**
 * User Controller
 * This file has the fucntions create and edit the information of a user 
 * 
 * @category   WicaWeb
 * @package    Core_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   Diego P�rez 
 * @version    1.0
 * 
 */
class Core_Dictionary_DictionaryController extends Zend_Controller_Action
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
				if($mod->module_id == '7')
				{
					$profile_access[$mod->action_id] = array('action'=> $mod->action_name, 'title'=>$mod->action_title);
				}
			}			
		}

		$this->view->dictionary_links = $profile_access;
    }
	
    /**
     * Generates a list of all dictionaries
     */
    public function indexAction()
    {
    	$id = New Zend_Session_Namespace('id');
		
    	$dictionary_obj = new Core_Model_Dictionary();
		
		if($id->user_id!=1)
		{
			//Recover the websites list available per user
			$user = new Core_Model_User();
			$logged_user = $user->find('wc_user',array('id'=>$id->user_id));
			$user_profile = $logged_user[0]->profile_id;
			//websites according profile
			$website_profile = new Core_Model_WebsiteProfile('wc_website_profile');
			$selected_websites = $website_profile->find('wc_website_profile',array('profile_id'=>$user_profile));
			
			//Get dictionary list by websites according profile
			foreach ($selected_websites as $l){
				$dictionary_item = $dictionary_obj->find('wc_dictionary',array('website_id'=>$l->website_id));
				if($dictionary_item){
					foreach ($dictionary_item as $di){
						$dictionary[] = $di;
					}
					
				}
			}
		}
		else{
			//Dictionaries for admin profile
			$dictionary = $dictionary_obj->find('wc_dictionary');
		}

    	$this->view->dictionary = $dictionary;
    }
    
    /**
     * Creates a New Dictionary
     */
    public function newAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	
    	$request = $this->getRequest();
    	
    	$form = New Core_Form_Dictionary_Dictionary();
    	$form->setMethod('post');
    		
    	
		//after submit the form    	
    	if ($this->getRequest()->isPost()) 
    	{
    		$formData = $this->getRequest()->getPost();
    		
    		if ($form->isValid($formData)) 
    		{
    			    			
    			//save data in wc_dictionary
    			$dictionary =  new Core_Model_Dictionary();
    			$dictionary_obj = $dictionary->getNewRow('wc_dictionary');
    			
    			$dictionary_obj->name = GlobalFunctions::value_cleaner($formData['name']);
    			$dictionary_obj->website_id = GlobalFunctions::value_cleaner($formData['website_id']);
    			$dictionary_obj->status = 'active';
    			
	    			
    			// Save data							
				$saved_dictionary = $dictionary->save('wc_dictionary',$dictionary_obj);
				
				if($saved_dictionary)
				{	

					//save data in wc_words
					$word = new Core_Model_Word();
					$word_obj = $word->getNewRow('wc_word');
					 
					
					$words_array_list_lowercase = mb_strtolower($formData['words'],'UTF-8');
					$words_array_list = explode(',', $words_array_list_lowercase);			
					$words_array = array_unique($words_array_list);		
					

					foreach ($words_array as $w){
						
						//Get last id saved dictionary		
						$word_obj->dictionary_id = $saved_dictionary['id'];
						$word_obj->expression =GlobalFunctions::value_cleaner($w);
						$saved_word = $word->save('wc_word',$word_obj);
					
						if($saved_word)
						{
							//Success
						}
						else{
							break;
						}
					
					}
					
					
					//success message
					$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
					$this->_helper->redirector('index','dictionary_dictionary','core');
				}
				else{
					//Adding Error Messages
					$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in saving data')));
					$this->_helper->redirector('new','dictionary_dictionary','core');
				}
    		}
    		else
    		{
    			//Adding Error Messages
    			$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Invalid Form')));
    			$this->_helper->redirector('new','dictionary_dictionary','core');
    		}
    		 
    	}
    	
    	$this->view->form = $form;
    }
    
    /**
     * Edit Dictionary Info.
     * Load a Form with all existing data
     */
    public function editAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	
    	$dictionary = new Core_Model_Dictionary;
    	$request_params = $this->getRequest()->getParams();
    	
    	$form = New Core_Form_Dictionary_Dictionary($request_params['action']);
    	$form->setMethod('post');
    	
    	//dictionary_id
    	$id = $this->_getParam('id');
    
    	
    	if(!$id)
    	{
    		$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Selected invalid dictionary')));
    		$this->_helper->redirector('index','dictionary_dictionary','core');
    	}    	
    	
    	$data = $dictionary->find('wc_dictionary', array('id'=>$id)); //get the selected user data
    	
    	if(!$data)
    	{
    		$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Selected invalid dictionary')));
    		$this->_helper->redirector('index','dictionary_dictionary','core');
    	}
    	
    	$arr_data = get_object_vars($data[0]); //make an array of the object data

    	//Get words of selected dictionary 
    	$word_obj = new Core_Model_Word();
    	$word_aux = $word_obj->find('wc_word',array('dictionary_id'=>$id));
    	
 
    	$word_list = array();
    	
    	foreach ($word_aux as $word){
    		$word_list[] = $word->expression;
    	}
    	
    	$words=implode(',',$word_list);
    	
    	$arr_data['words']=$words;
    	
    	//Get words array for preview view
    	$words_preview = explode(',',$words);
    	sort($words_preview);
    	 	
		//populate the form
		$form->populate($arr_data);
    	$this->view->form = $form;
    	$this->view->words_preview = $words_preview;
    	
    	
    	if ($this->getRequest()->isPost())
    	{
    		$formData  = $this->_request->getPost();
    		if ($form->isValid($formData))
    		{
    			
    			
    			//set Data
    			$dictionary =  new Core_Model_Dictionary();
    			$dictionary_obj = $dictionary->getNewRow('wc_dictionary');
    			$dictionary_obj->id = $formData['id'];    			
    			$dictionary_obj->name = GlobalFunctions::value_cleaner($formData['name']);
    			$dictionary_obj->website_id = $formData['website_id'];
    			$dictionary_obj->status = $formData['status'];
  			
    			// Save data							
				$saved_dictionary = $dictionary->save('wc_dictionary',$dictionary_obj);
				
	    		if($saved_dictionary)
	    		{   					

	    			//save data in wc_words
	    			$word = new Core_Model_Word();
	    			$word_obj = $word->getNewRow('wc_word');
	    			
	    			//delete last words of the dictionary
	    			$word->delete('wc_word',array('dictionary_id'=>$formData['id']));
	    			
	    			$words_array_list_lowercase = mb_strtolower($formData['words'],'UTF-8');
	    			$words_array_list = explode(',', $words_array_list_lowercase);
	    			$words_array_unique = array_unique($words_array_list);
	    			
	    			foreach ($words_array_unique as $w){
	    				
	    				$word_obj->dictionary_id = GlobalFunctions::value_cleaner($formData['id']);
	    				$word_obj->expression =GlobalFunctions::value_cleaner($w);
	    				$saved_word = $word->save('wc_word',$word_obj);
	    					
	    				if($saved_word)
	    				{
	    					//Success
	    				}
	    				else{
	    					break;
	    				}
	    					
	    			}
	    			
	    			
					//success message
					$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
					$this->_helper->redirector('index','dictionary_dictionary','core');
				}
				else{
					//Adding Error Messages
					$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in saving data')));
					$this->_helper->redirector('new','dictionary_dictionary','core');
				}
    		}
    		else
    		{
    			//Adding Error Messages
    			$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Invalid Form')));
    			$this->_helper->redirector('edit','dictionary_dictionary','core',array('id'=>$id));
    		}
    	}
    
    }
    
    /**
     * Validate that the entered dictionay name is not repeated
     */
    public function validatedictionarynameAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();   	
    	
    	if ($this->getRequest()->isPost())
    	{
    		$data = $this->_request->getPost();
    		
    		$dictionaryname = mb_strtolower($data['name'], 'UTF-8');
    		$id = -1;
    		if(isset($data['id']))
    			$id= $data['id'];
    
    		$dictionary = new Core_Model_Dictionary();
    		if(isset($id) && $id>0)
    			$dictionary_array = $dictionary->personalized_find('wc_dictionary', array(array('name', '=', $dictionaryname),array('id', '!=', $id)));
    		else
    			$dictionary_array = $dictionary->personalized_find('wc_dictionary', array(array('name', '=', $dictionaryname)));
    		
    		if($dictionary_array && count($dictionary_array)>0)
    			echo json_encode(false);
    		else
    			echo json_encode(true);
    		
    	}
    }
    
}
