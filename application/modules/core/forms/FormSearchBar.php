<?php
/**
 * FormSearchBar	
 * Adds a form with a field to search sections or content
 *
 * @category   WicaWeb
 * @package    Core_Form
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   Esteban
 * @version    1.0
 *
 */
class Core_Form_FormSearchBar extends Zend_Form
{  
	/**
	 * Loads all the objects of the form when the form is initialized
	 */
	public function init()
	{
		$this->setAttrib('id', 'frmSearchBar');
 		$this->setAttrib('class', 'form-inline');
		$this->setMethod('post');
	
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
				
		//select section or content to search
		$options = array('section'=>'Section','content'=>'Content');
		$searchSelection = New Zend_Form_Element_Radio('select_search_form');
		$searchSelection->setMultiOptions($options);
		$searchSelection->setSeparator('  ');		
		$searchSelection->setRequired(true);
		$searchSelection->setValue('section');
		
		//search field
		$searchField = New Zend_Form_Element_Text('nameField');
		$searchField->setRequired(true);
		$searchField->setAttrib('class', 'form-control');					
		
		//Submit Button
		$submit = New Zend_Form_Element_Button('submit_search_form');
		$submit->setLabel($lang->translate('Search'));
		$submit->setAttrib('class', 'btn btn-primary');
		$submit->setIgnore(true);

		//add elements to the form
		$this->addElements(array(
				$searchSelection,
				$searchField,
				$submit
				));
		
	}
    
}
