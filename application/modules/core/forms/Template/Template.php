<?php
/**
 * Form Template
 * This file has parameters to create a form for the template upload
 *
 * @category   WicaWeb
 * @package    Core_Form
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   Santiago Arellano
 * @version    1.0
 *
 */
class Core_Form_Template_Template extends Zend_Form
{
	/**
	 * Loads all the objects of the form when the form is initialized
	 */
	public function init()
	{
		$this->setAttrib('id', 'frmTemplate');
		$this->setAttrib('enctype', 'multipart/form-data'); //attr to support file upload
		
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
				
		//Name
		$name = New Zend_Form_Element_Text('name');
		$name->setLabel('* '.$lang->translate('Name').':');
		$name->setRequired(true);
		$name->setFilters(array( new Zend_Filter_StringTrim()));
		$name->setAttrib('style','width:100%');
		
		$this->addElement ( $name );
				
		//Template File
		$template_file = new Zend_Form_Element_Button('template_file');
		$template_file->setLabel($lang->translate('Search').'..');
		$template_file->setAttrib('class', 'btn');
		$template_file->setAttrib('label_name',  '* '.$lang->translate ( 'Template file' ) . ':'  );

		$this->addElement($template_file);
		
		// Hidden Name Template File
		$hidden_template_file = new Zend_Form_Element_Hidden ( 'hdn_template_file' );
		$hidden_template_file->setValue ( '' );
		$hidden_template_file->addFilters ( array (
				'StringTrim'
		) );
		$this->addElement ( $hidden_template_file );
		
		// Multiple css file upload
		$hidden_css_file = new Zend_Form_Element_Button ( 'css_files' );
		$hidden_css_file->setLabel($lang->translate('See Files').'..');
		$hidden_css_file->setAttrib('class', 'btn');
		$hidden_css_file->setAttrib('label_name',  $lang->translate ( 'Css files' ) . ':'  );
		
		$this->addElement ( $hidden_css_file );		
		
		// Multiple js file upload
		$hidden_js_file = new Zend_Form_Element_Button ( 'js_files' );
		$hidden_js_file->setLabel($lang->translate('See Files').'..');
		$hidden_js_file->setAttrib('class', 'btn');
		$hidden_js_file->setAttrib('label_name',  $lang->translate ( 'Js files' ) . ':'  );
		
		$this->addElement ( $hidden_js_file );		
		
		// Multiple image file upload
		$hidden_image_file = new Zend_Form_Element_Button ( 'image_files' );
		$hidden_image_file->setLabel($lang->translate('See Files').'..');
		$hidden_image_file->setAttrib('class', 'btn');
		$hidden_image_file->setAttrib('label_name',  $lang->translate ( 'Images' ) . ':'  );
		
		$this->addElement ( $hidden_image_file );		
		
		
		//Template Image
		$template_image = new Zend_Form_Element_Button('template_image');
		$template_image->setLabel($lang->translate('Search').'..');
		$template_image->setAttrib('class', 'btn');
		$template_image->setAttrib('label_name', $lang->translate ( 'Template image' ) . ':'  );
		
		$this->addElement($template_image);
		
		// Hidden Name Template image
		$hidden_template_image = new Zend_Form_Element_Hidden ( 'hdn_template_image' );
		$hidden_template_image->setValue ( '' );
		$hidden_template_image->addFilters ( array (
				'StringTrim'
		) );
		$this->addElement ( $hidden_template_image );
		
		//Submit Button
		$submit = New Zend_Form_Element_Button('submit_btn');
		$submit->setLabel($lang->translate('Save'));
		$submit->setAttrib('class','btn btn-success');
				
		$this->addElement($submit);
	}
}
