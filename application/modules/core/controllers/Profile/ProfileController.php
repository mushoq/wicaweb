<?php
/** 
 *	Profiles colects allowed actions 
 *
 * @category   WicaWeb
 * @package    Core_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   David Rosales
 */

class Core_Profile_ProfileController extends Zend_Controller_Action {
	
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
				if($mod->module_id == '4')
				{
					$profile_access[$mod->action_id] = array('action'=> $mod->action_name, 'title'=>$mod->action_title);
				}
			}
		}
	
		$this->view->profile_links = $profile_access;
	}
	
	/**
	 * Loads existent profile options in DB
	 */
	public function indexAction() 
	{
		$profile = new Core_Model_Profile();
				
		//profiles list
		$this->view->profiles = $profile->personalized_find('wc_profile',array(array('id','!=','1')));	
		//available profile status	
		$this->view->status = GlobalFunctions::arrayTranslate(Core_Model_Profile::$status_profile);
	}

	/**
	 * Creates a profile
	 */
	public function newAction()
	{
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		// websites
		$website = new Core_Model_Website();
		$this->view->websites = $website->find ( 'wc_website' );
		
		//modules
		$module = new Core_Model_Module();
		$this->view->modules = $module->find('wc_module');
		
		if ($this->getRequest()->isPost())
		{
			//retrieved data from post
			$formData  = $this->_request->getPost();
			//profile
			$profile = new Core_Model_Profile();
			$profile_obj = $profile->getNewRow('wc_profile');
			$profile_obj->name = GlobalFunctions::value_cleaner($formData['profile']);
			$profile_obj->status = 'active';
			$profile_id = $profile->save('wc_profile',$profile_obj);
				
			//website profile
			$website_array = $formData['website'];
				
			foreach ($website_array as $wid => $val)
			{
				$website_profile = new Core_Model_WebsiteProfile();
				$website_profile_obj = $website_profile->getNewRow('wc_website_profile');
				$website_profile_obj->profile_id = $profile_id['id'];
				$website_profile_obj->website_id = $wid;
				$website_profile_id = $website_profile->save('wc_website_profile',$website_profile_obj);
			}
				
			//module action profile
			$module_array = $formData['module'];
				
			foreach ($module_array as $mid => $val)
			{
				foreach ($val as $id => $vl)
				{
					if(is_array($vl))
					{
						$module_action_profile = new Core_Model_ModuleActionProfile();
						$module_action_profile_obj = $module_action_profile->getNewRow('wc_module_action_profile');
						$module_action_profile_obj->profile_id = $profile_id['id'];
						$module_action_profile_obj->module_action_id = $id;
						$module_action_profile_id = $module_action_profile->save('wc_module_action_profile',$module_action_profile_obj);
					}
				}
			}
				
			//section profile
			$section_str = $formData['section_sel'];
				
			$sections_arr = explode('|', $section_str);
				
			foreach ($sections_arr as $ele)
			{
				if($ele)
				{
					$website_section = explode(',', $ele);
		
					if(isset($website_section[0])&&isset($website_section[1]))
					{
						$section_profile = new Core_Model_SectionProfile();
						$section_profile_obj = $section_profile->getNewRow('wc_section_profile');
						$section_profile_obj->profile_id = $profile_id['id'];
						$section_profile_obj->section_id = $website_section[1];
						$section_profile_id = $section_profile->save('wc_section_profile',$section_profile_obj);
					}
				}
			}
		
			if($profile_id && $module_action_profile_id)
			{
				$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
			}
			else
			{
				$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in saving data')));
			}
			$this->_helper->redirector('index','profile_profile','core');
		}
	}
	
	/**
	 * Loads existent profile options in DB
	 */
	public function editAction()
	{
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');					
		
		//profile_id passed in URL
		$id = $this->_getParam('id');
	
		$profile = new Core_Model_Profile();
		$profile_arr = $profile->find('wc_profile', array('id'=>$id));
		
		if(!$profile_arr || $id==1)
		{
			$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Profile access denied')));
			$this->_helper->redirector('index','profile_profile','core');
		}
		
		$this->view->profile_id = $id;		
		
		//profile status		
		$this->view->status = GlobalFunctions::arrayTranslate(Core_Model_Profile::$status_profile);
		
		//profile name and status
		$profile_obj = $profile_arr[0];
		$this->view->profile_name = $profile_obj->name;		
		$this->view->profile_status = $profile_obj->status;
		
		// websites
		$website = new Core_Model_Website();
		$this->view->websites = $website->find ( 'wc_website' );		
		//websites profile
		$website_profile = new Core_Model_WebsiteProfile();		
		$this->view->websites_profile = $website_profile->find( 'wc_website_profile', array('profile_id'=>$id) ); 
		
		//modules
		$module = new Core_Model_Module();
		$this->view->modules = $module->find('wc_module');
		//modules profile
		$module_profile = new Core_Model_ModuleActionProfile();
		$this->view->modules_profile = $module_profile->find( 'wc_module_action_profile', array('profile_id'=>$id) );
			
		//section action profile
		$section_profile = new Core_Model_SectionProfile();
		$sections_profile_arr = $section_profile->getPublishedSectionsByProfile($id);		
		
		$section_sel = '';
		$options = '';
		
		if($sections_profile_arr)
		{	
			foreach ($sections_profile_arr as $sec)
			{
				$options[] = $sec['website_id'].','.$sec['section_id'];				
			}
			if(is_array($options))
			{
				$aux_section_sel = implode('|', $options);
				$section_sel = $aux_section_sel.'|';
			}
		}			
		
		$this->view->sections_profile = $section_sel;
			
		if ($this->getRequest()->isPost())
		{
			//retrieved data from post
			$formData  = $this->_request->getPost();
		
			//profile
			$profile_id = $formData['profile_id'];			
			$profile = new Core_Model_Profile();	
			$profile_obj = $profile->getNewRow('wc_profile');							
			//$stored_profile_data = $profile->find('wc_profile', array('id'=>$profile_id));
			$profile_obj->id = $profile_id;						
			$profile_obj->name = GlobalFunctions::value_cleaner($formData['profile']);
			$profile_obj->status = $formData['status'];
			$profile_id = $profile->save('wc_profile',$profile_obj);
		
			//website profile
			//delete old websites profile
			$website_profile = new Core_Model_WebsiteProfile();			
			$website_profile_arr = $website_profile->find('wc_website_profile',array('profile_id'=>$profile_id['id']));
			foreach ($website_profile_arr as $k => $wpr)
			{				
				$delete_old_wpr = $website_profile->delete('wc_website_profile', array('id'=>$wpr->id));
			}			
			//insert new websites profile
			$website_array = $formData['website'];		
			foreach ($website_array as $wid => $val)
			{
				$website_profile = new Core_Model_WebsiteProfile();
				$website_profile_obj = $website_profile->getNewRow('wc_website_profile');
				$website_profile_obj->profile_id = $profile_id['id'];
				$website_profile_obj->website_id = $wid;
				$website_profile_id = $website_profile->save('wc_website_profile',$website_profile_obj);
			}
		
			//module action profile
			//delete old module action profile
			$module_action_profile = new Core_Model_ModuleActionProfile();
			$module_profile_arr = $module_action_profile->find('wc_module_action_profile',array('profile_id'=>$profile_id['id']));
			foreach ($module_profile_arr as $mpr)
			{
				$delete_old_mpr = $module_action_profile->delete('wc_module_action_profile', array('id'=>$mpr->id));
			}			
			//insert new module actions profile
			$module_array = $formData['module'];	
	
			foreach ($module_array as $mid => $val)
			{
				foreach ($val as $id => $vl)
				{
					if(is_array($vl))
					{
						$module_action_profile = new Core_Model_ModuleActionProfile();
						$module_action_profile_obj = $module_action_profile->getNewRow('wc_module_action_profile');
						$module_action_profile_obj->profile_id = $profile_id['id'];
						$module_action_profile_obj->module_action_id = $id;
						$module_action_profile_id = $module_action_profile->save('wc_module_action_profile',$module_action_profile_obj);
					}
				}
			}
		
			//section profile
			//delete old module action profile
			$section_profile = new Core_Model_SectionProfile();
			$section_profile_arr = $section_profile->find('wc_section_profile',array('profile_id'=>$profile_id['id']));
			foreach ($section_profile_arr as $spr)
			{
				$delete_old_spr = $section_profile->delete('wc_section_profile', array('id'=>$spr->id));
			}
			//insert new section profile			
			$section_str = $formData['section_sel'];		
			$sections_arr = explode('|', $section_str);	
  			
			foreach ($sections_arr as $ele)
			{
				if($ele)
				{
					$website_section = explode(',', $ele);
		
					if(isset($website_section[0])&&isset($website_section[1]))
					{
						$section_profile = new Core_Model_SectionProfile();
						$section_profile_obj = $section_profile->getNewRow('wc_section_profile');
						$section_profile_obj->profile_id = $profile_id['id'];
						$section_profile_obj->section_id = $website_section[1];
						$section_profile_id = $section_profile->save('wc_section_profile',$section_profile_obj);
					}
				}
			}
		
			if($profile_id && $module_action_profile_id)
			{
				$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
			}
			else
			{
				$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in saving data')));
			}
			$this->_helper->redirector('index','profile_profile','core');
		}			
		
	}
}