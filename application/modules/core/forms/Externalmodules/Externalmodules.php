<?php
/**
 * Form External Files
 * This file has parameters to create a form for the externalFiles upload
 *
 * @category   WicaWeb
 * @package    Core_Form
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   Diego Perez
 * @version    1.0
 *
 */
class Core_Form_Externalmodules_Externalmodules extends Zend_Form
{
	/**
	 * Loads all the objects of the form when the form is initialized
	 */
	public function init()
	{
		$this->setAttrib('id', 'frmExternalModules');
		$this->setAttrib('enctype', 'multipart/form-data'); //attr to support file upload
		
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');	
						
		//File
		$file = New Zend_Form_Element_File('file');
		$file->setLabel($lang->translate('Filename').':');
		$file->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$file->setDestination(APPLICATION_PATH. '/../public/uploads/tmp');
		$file->addValidator('Count', false, 1);
		$file->addValidator('Extension', false, 'zip');
		$file->setAttrib('style','width:100%');
		
		//name
		$name = New Zend_Form_Element_Text('name');
		$name->setLabel($lang->translate('Name').':');
		$name->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$name->setAttrib('style', 'width:47%');
		
		//description
		$description = New Zend_Form_Element_Textarea('description');
		$description->setLabel($lang->translate('Description').':');
		$description->setRequired(true);
		$description->setAttribs(array('cols' => 40, 'rows' => 5));
		$description->setAttrib('style', 'width:47%');
		
		//action
		$action = New Zend_Form_Element_Text('action');
		$action->setLabel($lang->translate('Action').':');
		$action->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$action->setAttrib('style', 'width:47%');
		
		//Module Image
		$image = New Zend_Form_Element_File('image');
		$image->setLabel($lang->translate('Image').':');
		$image->setDestination(APPLICATION_PATH. '/../public/uploads/tmp');
		$image->addValidator('Count', false, 1);
		$image->addValidator('Extension', false, 'jpg,png,gif,jpeg');
		$image->setAttrib('style', 'width:100%');
		
		$preview_img = New Zend_Form_Element_Image('preview_img');
		$preview_img->setAttrib('onclick', 'return false;');
		$preview_img->setAttrib('style', 'width: 128px;');
		$preview_img->setAttrib('class', 'preview_img hide');
		
		//Submit Button
		$submit = New Zend_Form_Element_Submit('submit');
		$submit->setLabel($lang->translate('Install'));
		$submit->setAttrib('class','btn btn-success');
		$submit->setIgnore(true);
		
		//Cancel Button
		$cancel = New Zend_Form_Element_Button('cancel');
		$cancel->setLabel($lang->translate('Cancel'));
		$cancel->setAttrib('class', 'btn');
		$cancel->setIgnore(true);
				
		//add elements to the form
		$this->addElements(array(
				$file,
				$description,
				$action,
				$image,
				$preview_img,
				$name
		));
		
		$this->addElement($submit);
		$this->addElement($cancel);

	}
}
