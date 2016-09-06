<?php
/**
 * Form External Files
 * This file has parameters to create a form for the externalFiles upload
 *
 * @category   WicaWeb
 * @package    Core_Form
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   Esteban
 * @version    1.0
 *
 */
class Core_Form_Externalfiles_Externalfiles extends Zend_Form
{
	/**
	 * Loads all the objects of the form when the form is initialized
	 */
	public function init()
	{
		$this->setAttrib('id', 'frmExternalFiles');
		$this->setAttrib('enctype', 'multipart/form-data'); //attr to support file upload
		
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		//translate enums
		$confirm = GlobalFunctions::arrayTranslate(Core_Model_Externalfiles::$confirm);
				
		//name
		$name = New Zend_Form_Element_Text('name');
		$name->setLabel($lang->translate('Internal name').':');
		$name->setRequired(true);
		$name->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$name->setFilters(array( new Zend_Filter_StringTrim()));
		$name->setAttrib('class','form-control');
				
		//File
		$file = New Zend_Form_Element_File('file');
		$file->setLabel($lang->translate('Filename').':');
		$file->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$file->setDestination(APPLICATION_PATH. '/../public/uploads/tmp');
		$file->addValidator('Count', false, 1);
		$file->addValidator('Extension', false, 'css,js');
		$file->setAttrib('style','width:100%');
		
			
		//add to all websites
		$add_to_all = New Zend_Form_Element_Hidden('add_to_all');
		$add_to_all->removeDecorator('Label');
		$add_to_all->removeDecorator('HtmlTag');
		
		
		//Submit Button
		$submit = New Zend_Form_Element_Submit('submit');
		$submit->setLabel($lang->translate('Save'));
		$submit->setAttrib('class','btn btn-success');
		$submit->setIgnore(true);
		
		//Cancel Button
		$cancel = New Zend_Form_Element_Button('cancel');
		$cancel->setLabel($lang->translate('Cancel'));
		$cancel->setAttrib('class', 'btn btn-default');
		$cancel->setIgnore(true);
				
		//add elements to the form
		$this->addElements(array(
				$name,
				$file
		));
		
		$website = new Core_Model_Website();
		$website_list = $website->find('wc_website');

		if(count($website_list)>1){
			$this->addElement($add_to_all);
		}
		$this->addElement($submit);
		$this->addElement($cancel);

	}
}
