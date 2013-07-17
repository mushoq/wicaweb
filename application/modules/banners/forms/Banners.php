<?php
/**
 * Form Website
 * This file has parameters to create a form for the website configurations
 *
 * @category   WicaWeb
 * @package    Core_Form
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   Diego Perez
 * @version    1.0
 *
 */
class Banners_Form_Banners extends Zend_Form
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

		$this->setAttrib('id', 'frmBanners');
		$this->setAttrib('class', 'form-horizontal well');
		$this->setAttrib('enctype', 'multipart/form-data'); //attr to support file upload
		$this->setAction('/banners/index/savebanner');
		
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		//translate enums
		$confirm = GlobalFunctions::arrayTranslate(Core_Model_Website::$confirm);
		$comments_enum = GlobalFunctions::arrayTranslate(Core_Model_Website::$comments_enum);
		$comments_type = GlobalFunctions::arrayTranslate(Core_Model_Website::$comments_type);
		$hour_format_options = GlobalFunctions::arrayTranslate(Core_Model_Website::$hour_format);
		
		$banner_type = GlobalFunctions::arrayTranslate(Banners_Model_Banners::$publication_type);
		$banner_status = GlobalFunctions::arrayTranslate(Core_Model_WebsiteState::$status);
		
		//area
		$area = New Zend_Form_Element_Select('area');
		$area->setLabel($lang->translate('Area').':');
		$area->setRequired(true);
		$area->getDecorator('label')->setOption('requiredPrefix', ' * ');

		//create the array for populate the select
		$options_area = array();
		
		//Get area_list param
		$option_area_list = $this->_param['area_list'];
		
		if($option_area_list){
			foreach ($option_area_list as $l){
				$options_area[$l->id] = $l->name;
			}
		}

		$area->setMultiOptions($options_area);
		$area->setAttrib('style', 'width:78%');
		
		//banner
		$banner = New Zend_Form_Element_Hidden('banner_id');
		
		//name
		$name = New Zend_Form_Element_Text('name');
		$name->setLabel($lang->translate('Name').':');
		$name->setRequired(true);
		$name->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$name->setFilters(array( new Zend_Filter_StringTrim()));
		$name->setAttrib('style', 'width:75%');		

		//description
		$description = New Zend_Form_Element_Textarea('description');
		$description->setLabel($lang->translate('Description').':');
		$description->setRequired(true);
		$description->setAttribs(array('cols' => 40, 'rows' => 5));
		$description->setAttrib('style', 'width:75%');
				
		
		//html Banner

		$html = New Zend_Form_Element_Textarea('html');
		$html->setLabel($lang->translate('Banner HTML').':');
		$html->setRequired(true);
		$html->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$html->setAttribs(array('cols' => 40, 'rows' => 5));
		$html->setAttrib('style', 'width:75%');
		
		
		//Link
		$link = New Zend_Form_Element_Text('link');
		$link->setLabel($lang->translate('Link').':');
		$link->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$link->setAttrib('style', 'width:75%');
		
		//status
		$status = New Zend_Form_Element_Select('status');
		$status->setLabel($lang->translate('Status').':');
		$status->setAttrib('style', 'width:75%');
		$status->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$status->setMultiOptions($banner_status);
		
		//publish date
		$publish_date = new Zend_Form_Element_Text('publish_date');
		$publish_date->setLabel($lang->translate('Publish date').':');
		$publish_date->getDecorator('label')->setOption('requiredPrefix', ' * ');
		
		//expire date
		$expire_date = new Zend_Form_Element_Text('expire_date');
		$expire_date->setLabel($lang->translate('Expire date').':');
		$expire_date->getDecorator('label')->setOption('requiredPrefix', ' * ');
		
		//Publication type Calendar/Hits
		$type = New Zend_Form_Element_Hidden('type');
		$type->removeDecorator('Label');
		$type->removeDecorator('HtmlTag');
		
		//Hits
		$hits = New Zend_Form_Element_Text('hits');
		$hits->setLabel($lang->translate('Hits').':');
		$hits->getDecorator('label')->setOption('requiredPrefix', ' * ');

		//Sections
		
		//create the array for populate the select
		$options_section = array();
		
		//Get sections_list param
		
		$option_section_list = $this->_param['section_list_option'];	
		
		if($option_section_list){
			foreach ($option_section_list as $l){
				$options_section[$l['id']] = $l['title'];
			}
		}
		
		$sections = new Zend_Form_Element_Multiselect('sections');
		$sections->setAttrib('style', 'width:78%');
		$sections->setLabel($lang->translate('Assign to other section').':');
		$sections->setMultiOptions($options_section);
		

		//Submit Button
		$submit = New Zend_Form_Element_Button('submit');
		$submit->setLabel($lang->translate('Finish'));
		$submit->setAttrib('class', 'btn btn-success');
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

		//Hideen Banner type (Image,Flash or html code)
		$banner_type = New Zend_Form_Element_Hidden('banner_type');
		$banner_type->removeDecorator('Label');
		$banner_type->removeDecorator('HtmlTag');
		
		//add elements to the form
		$this->addElements(array(
				$area,
				$name,
				$description,
				$html,
				$link,
				$publish_date,
				$expire_date,
				$hits,
				$type,
				$sections,
				$submit,
				$cancel,
				$id
		));
		
		//Edit fields
		
		if(isset($this->_param['action'])){
			if( $this->_param['action'] == 'edit')
				$this->addElement($status);
				$this->addElement($banner_type);
		}

		
		//Image Banner

		for ($i=1; $i<=1 ;$i++)
		{
				
				$image = new Zend_Form_Element_Button('img_'.$i);
				$image->setLabel($lang->translate('Search'));
				$image->setAttrib('class', 'btn');
				$this->addElement($image);

				
				$img_file = new Zend_Form_Element_Hidden('file_img_'.$i);
				$img_file->removeDecorator('Label');
				$img_file->removeDecorator('HtmlTag');
				$this->addElement($img_file);

				
				$image_preview = New Zend_Form_Element_Image('imageprw_'.$i);
				$image_preview->removeDecorator('Label');
				$image_preview->removeDecorator('HtmlTag');
				$image_preview->setAttrib ('class', 'preview_img hide');
				$image_preview->setAttrib('onclick', 'return false;');
				$this->addElement($image_preview);
		}
		
		//Flash Banner
		
		for ($i=1; $i<=1 ;$i++)
		{
		
				$flash = new Zend_Form_Element_Button('flash_'.$i);
				$flash->setLabel($lang->translate('Search'));
				$flash->setAttrib('class', 'btn');
				$this->addElement($flash);
				
				
				$flash_file = new Zend_Form_Element_Hidden('file_flash_'.$i);
				$flash_file->removeDecorator('Label');
				$flash_file->removeDecorator('HtmlTag');
				$this->addElement($flash_file);
				

			
		}		
		
	}
}
