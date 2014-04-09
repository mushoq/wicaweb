<?php
/**  	
 * Adds a form with a field and dropdown to search banners
 *
 * @category   WicaWeb_Form
 * @package    Core_Form
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   Diego Perez
 * @version    1.0
 *
 */
class Banners_Form_Search extends Zend_Form
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
		$text->setLabel($lang->translate('Banner name').':');
		$text->setRequired(true);
		$text->setAttrib('style', 'width:100%');
		
		//template
		$section = New Zend_Form_Element_Select('section');
		$section->setLabel('* '.$lang->translate('Section'));
				
		/**Get values from wc_section with wica_content_area (variable) for options**/

		$section_model = new Core_Model_Section();
		
		//Find template according website
		$website = new Core_Model_Website();
		$website_data = $website->find('wc_website',array('id'=>$session_id->website_id));
		$template_id = $website_data[0]->template_id;
		
		//Find variable area content by template
		$area = new Core_Model_Area();
		$area_data = $area->personalized_find('wc_area',array(array('template_id','=',$template_id),array('type','LIKE','variable')));
		$area_content_id = $area_data[0]->id;
		
		//Find sections with variable area content
		$section_area = new Core_Model_SectionModuleArea();
		$section_area_list = $section_area->find('wc_section_module_area',array('area_id'=>$area_content_id));
		
		
		//Get data of existent sections that contents variable content
		if($section_area_list){
			foreach ($section_area_list as $sal){
				$section_area_item = $section_model->find('wc_section',array('id'=>$sal->section_id));
				if($section_area_item){
					foreach ($section_area_item as $sei){
						$options[] = $sei;
					}
		
				}
			}
		}
	
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
