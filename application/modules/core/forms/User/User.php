<?php
/**
 * Form User
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
class Core_Form_User_User extends Zend_Form
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
		$this->setAttrib('id', 'frmUser');
		
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		//translate enums
		$user_status = GlobalFunctions::arrayTranslate(Core_Model_User::$status_user);
		
		//name
		$name = New Zend_Form_Element_Text('name');
		$name->setLabel($lang->translate('Name').':');
		$name->setRequired(true);
		$name->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$name->setFilters(array( new Zend_Filter_StringTrim()));
		$name->setAttrib('class','form-control');
		
		//lastname
		$lastname = New Zend_Form_Element_Text('lastname');
		$lastname->setLabel($lang->translate('Lastname').':');
		$lastname->setRequired(true);
		$lastname->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$lastname->setFilters(array( new Zend_Filter_StringTrim()));
		$lastname->setAttrib('class','form-control');
		
		//identification
		$identification = New Zend_Form_Element_Text('identification');
		$identification->setLabel($lang->translate('ID').':');
		$identification->setFilters(array( new Zend_Filter_StringTrim()));
		$identification->setAttrib('class','form-control');
				
		//email
		$email = New Zend_Form_Element_Text('email');
		$email->setLabel($lang->translate('Email').':');
		$email->addValidator(new Zend_Validate_EmailAddress());
		$email->setFilters(array( new Zend_Filter_StringTrim()));
		$email->setAttrib('class','form-control');
		
		//phone
		$phone = New Zend_Form_Element_Text('phone');
		$phone->setLabel($lang->translate('Phone').':');
		$phone->setFilters(array( new Zend_Filter_StringTrim()));
		$phone->setAttrib('class','form-control');
			
		//username
		$username = New Zend_Form_Element_Text('username');
		$username->setLabel($lang->translate('Username').':');
		$username->setRequired(true);
		$username->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$username->setFilters(array( new Zend_Filter_StringTrim()));
		$username->setAttrib('class','form-control');
		
		//password
		$password = New Zend_Form_Element_Password('password');
		$password->setLabel($lang->translate('Password').':');
                $password->setAttrib('class','form-control');
				
		$password->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$password->setFilters(array( new Zend_Filter_StringTrim()));
                
                //old_password
		$old_password = New Zend_Form_Element_Password('old_password');
		$old_password->setLabel($lang->translate('Old Password').':');
				
		$old_password->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$old_password->setFilters(array( new Zend_Filter_StringTrim()));
		$old_password->setAttrib('class','form-control');
                
                //new_password
		$new_password = New Zend_Form_Element_Password('new_password');
		$new_password->setLabel($lang->translate('New Password').':');
				
		$new_password->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$new_password->setFilters(array( new Zend_Filter_StringTrim()));
		$new_password->setAttrib('class','form-control');
		
		//confirm password
		$confirm_password = New Zend_Form_Element_Password('confirm_password');
		$confirm_password->setLabel($lang->translate('Confirm Password').':');
		
		if( $this->_param != 'edit')
			$confirm_password->setRequired(true);
		
		$confirm_password->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$confirm_password->setFilters(array( new Zend_Filter_StringTrim()));
		$confirm_password->setAttrib('class','form-control');
				
		//profile
		$profile = New Zend_Form_Element_Select('profile');
		$profile->setLabel($lang->translate('Profile').':');
		$profile->setRequired(true);
		$profile->getDecorator('label')->setOption('requiredPrefix', ' * ');
		
		$profile_obj = new Core_Model_Profile();
		$profiles_list = $profile_obj->personalized_find('wc_profile', array(array('id','!=','1'),array('status','=','active')));
		$options_profile = array();
		foreach ($profiles_list as $prf){
			$options_profile[$prf->id] = $prf->name;
		}		
		$profile->setMultiOptions($options_profile);
		$profile->setAttrib('class','form-control');
		
		//status
		$status = New Zend_Form_Element_Select('status');
		$status->setLabel($lang->translate('Status').':');
		$status->setRequired(true);
		$status->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$options_status = $user_status;
		$status->setMultiOptions($options_status);
		$status->setAttrib('class','form-control');
		
		//Submit Button
		$submit = New Zend_Form_Element_Button('submit');
		$submit->setLabel($lang->translate('Save'));
		$submit->setAttrib('class','btn btn-success');
		$submit->setIgnore(true);
		
		//Cancel Button
		$cancel = New Zend_Form_Element_Button('cancel');
		$cancel->setLabel($lang->translate('Cancel'));
		$cancel->setAttrib('class', 'btn btn-default');
		$cancel->setIgnore(true);
		
		//Hidden Id
		$id = New Zend_Form_Element_Hidden('id');
		$id->removeDecorator('Label');
		$id->removeDecorator('HtmlTag');
		
		//password checkbox
		$password_checkbox = New Zend_Form_Element_Checkbox('password_checkbox');
		$password_checkbox->setLabel($lang->translate('Change Password').':');
		
		
		
		//add elements to the form
		$this->addElements(array(
				$name,
				$lastname,
				$identification,
				$email,
				$phone,
				$username
				
		));
		
		if( $this->_param == 'edit')
			$this->addElement($password_checkbox);
		
		$this->addElements(array(
				$password,
                                $old_password,
                                $new_password,
				$confirm_password,
				$profile,
				
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
