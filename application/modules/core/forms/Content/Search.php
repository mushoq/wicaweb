<?php
/**  	
 * Adds a form with a field and dropdown to search content
 *
 * @category   WicaWeb_Form
 * @package    Core_Form
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   David Rosales
 * @version    1.0
 *
 */
class Core_Form_Content_Search extends Zend_Form
{  
	/**
	 * Loads all the objects of the form when the form is initialized
	 */
	public function init()
	{
		$this->setAttrib('id', 'frmLink');
		$this->setAttrib('class', 'well');
		$this->setMethod('post');
		
		//website
		$session_id = new Zend_Session_Namespace ( 'id' );
		$website_id = $session_id->website_id;
		
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		//search field
		$text = New Zend_Form_Element_Text('text');
		$text->setLabel($lang->translate('Content internal name').':');
		$text->setRequired(true);
                $text->setAttrib('class', 'form-control');
		
		//template
		$section = New Zend_Form_Element_Select('section');
		$section->setLabel('* '.$lang->translate('Section'));
                $section->setAttrib('class', 'form-control');
				
		//Get values from wc_section
		$section_model = new Core_Model_Section();		
		$options= $section_model->find('wc_section',array('website_id'=>$website_id, 'article'=>'no'));
		//create the array for populate the select
		$options_array = array();
		$options_array[] = '-Todas-';
		foreach ($options as $sec){
			$options_array[$sec->id] = $sec->internal_name;
		}	
		$section->setMultiOptions($options_array);
		$section->setAttrib('style', 'width:100%');
		
		//Submit Button
		$submit = New Zend_Form_Element_Button('submit_link_search_form');
		$submit->setLabel($lang->translate('Search'));
		$submit->setIgnore(true);
		$submit->setAttrib('class', 'btn btn-primary');
		
		//add elements to the form
		$this->addElements(array(
				$text,
				$section,				
				$submit
				));
		
	}
    
}
