<?php
/**
 * Form Dictionary
 * This file has parameters to create a form for enter user information
 *
 * @category   WicaWeb
 * @package    Core_Form
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   Esteban
 * @version    1.0
 *
 */
class Core_Form_Dictionary_Dictionary extends Zend_Form
{
	protected $_param;
	
	public function __construct($param = NULL)
	{
		$this->_param = $param;
		parent::__construct();
	}
	
	/**
	 * Loads all the objects of the form when the form is initialized
	 */
	public function init()
	{
		$this->setAttrib('id', 'frmDictionary');
		
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		//translate enums
		$dictionary_status = GlobalFunctions::arrayTranslate(Core_Model_Dictionary::$status_dictionary);
		
		//name
		$name = New Zend_Form_Element_Text('name');
		$name->setLabel($lang->translate('Name').':');
		$name->setRequired(true);
		$name->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$name->setFilters(array( new Zend_Filter_StringTrim()));
		$name->setAttrib('style','width:100%');
		
		//website
		$website_id = New Zend_Form_Element_Select('website_id');
		$website_id->setLabel($lang->translate('Website').':');
		$website_id->setRequired(true);
		$website_id->getDecorator('label')->setOption('requiredPrefix', ' * ');
	
		$options_website = array();
		
		//websites according user profile
		$selected_websites ='';
		
		
		$id = New Zend_Session_Namespace('id');
		
		
		if($id->user_id!=1)
		{
			//Recover the websites list available per user
			$user = new Core_Model_User();
			$logged_user = $user->find('wc_user',array('id'=>$id->user_id));
			$user_profile = $logged_user[0]->profile_id;
			//websites according profile
			$website_profile = new Core_Model_WebsiteProfile('wc_website_profile');
			$selected_websites = $website_profile->find('wc_website_profile',array('profile_id'=>$user_profile));
			$websites_object = new Core_Model_Website();
			//Websites Options according profile
			foreach ($selected_websites as $l){
				$aux=$websites_object->find('wc_website',array('id'=>$l->website_id));
				$options_website[$aux[0]->id] = $aux[0]->name;
			}
						
		}
		else{
			//Websites Options for admin
			$websites = new Core_Model_Website();
			$selected_websites=$websites->find('wc_website');
			foreach ($selected_websites as $l){
				$options_website[$l->id] = $l->name;
			}			
		}
		
		$website_id->setMultiOptions($options_website);
		$website_id->setAttrib('style','width:100%');
		
		//words
		$words = New Zend_Form_Element_Textarea('words');
		$words->setLabel($lang->translate('Words').':');
		$words->setRequired(true);
		$words->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$words->setFilters(array( new Zend_Filter_StringTrim()));
		$words->setAttrib('style','width:100%');
		$words->setAttrib('rows', '10');
		
		//status
		$status = New Zend_Form_Element_Select('status');
		$status->setLabel($lang->translate('Status').':');
		$status->setRequired(true);
		$status->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$options_status = $dictionary_status;
		$status->setMultiOptions($options_status);
		$status->setAttrib('style','width:100%');
		
		//Submit Button
		$submit = New Zend_Form_Element_Button('submit');
		$submit->setLabel($lang->translate('Save'));
		$submit->setAttrib('class','btn btn-success');
		$submit->setIgnore(true);
		
		//Cancel Button
		$cancel = New Zend_Form_Element_Button('cancel');
		$cancel->setLabel($lang->translate('Cancel'));
		$cancel->setAttrib('class', 'btn');
		$cancel->setIgnore(true);
		
		//Hidden Id
		$id = New Zend_Form_Element_Hidden('id');
		$id->removeDecorator('Label');
		$id->removeDecorator('HtmlTag');
		
		
		//add elements to the form
		$this->addElements(array(
				$name,
				$website_id,
				$words
				
		));
		
		if( $this->_param == 'edit')
			$this->addElement($status);

		
		$this->addElements(array(
				$submit,
				$cancel,
				$id
		));
	}
}
