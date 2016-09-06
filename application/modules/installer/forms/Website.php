<?php
/**
 * Form Website
 * This file has parameters to create a form for the website configurations
 *
 * @category   WicaWeb
 * @package    Core_Form
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   Diego
 * @version    1.0
 *
 */
class Installer_Form_Website extends Zend_Form
{
	/**
	 * Loads all the objects of the form when the form is initialized
	 */
	public function init()
	{
		$this->setAttrib('id', 'frmWebsite');
		$this->setAttrib('class', 'well');
		$this->setAttrib('enctype', 'multipart/form-data'); //attr to support file upload
		$this->setAction('/installer/index/savewebsite');
		
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		//translate enums
		$confirm = GlobalFunctions::arrayTranslate(Core_Model_Website::$confirm);
		$comments_enum = GlobalFunctions::arrayTranslate(Core_Model_Website::$comments_enum);
		$comments_type = GlobalFunctions::arrayTranslate(Core_Model_Website::$comments_type);
		$hour_format_options = GlobalFunctions::arrayTranslate(Core_Model_Website::$hour_format);
		
		$website_type = GlobalFunctions::arrayTranslate(Core_Model_WebsiteState::$type);
		$website_type_status = GlobalFunctions::arrayTranslate(Core_Model_WebsiteState::$status);
		
		//language
		$language = New Zend_Form_Element_Select('language_id');
		$language->setLabel($lang->translate('Language').':');
		$language->setRequired(true);
		$language->getDecorator('label')->setOption('requiredPrefix', ' * ');
		//Get values from wc_website_language
		$language_model = new Core_Model_WebsiteLanguage(); 
		$aux= $language_model->find('wc_website_language');
		//create the array for populate the select
		$options_language = array();
		foreach ($aux as $l){ 
			$options_language[$l->id] = $l->name;
		}
		$language->setMultiOptions($options_language);
		$language->setAttrib('class', 'form-control');
		
		//template
		$template = New Zend_Form_Element_Hidden('template_id');
		
		//name
		$name = New Zend_Form_Element_Text('name');
		$name->setLabel($lang->translate('Website Name').':');
		$name->setRequired(true);
		$name->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$name->setFilters(array( new Zend_Filter_StringTrim()));
		$name->setAttrib('class', 'form-control');		

		//description
		$description = New Zend_Form_Element_Textarea('description');
		$description->setLabel($lang->translate('Description').':');
		$description->setRequired(true);
		$description->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$description->setAttribs(array('cols' => 40, 'rows' => 5));
		$description->setAttrib('class', 'form-control');	
		
		//keywords
		$keywords = New Zend_Form_Element_Textarea('keywords');
		$keywords->setLabel($lang->translate('Keywords').':');
		$keywords->setRequired(true);
		$keywords->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$keywords->setAttribs(array('cols' => 40, 'rows' => 5));
		$keywords->setAttrib('class', 'form-control');
		
		//website Url
		$website_url = New Zend_Form_Element_Text('website_url');
		$website_url->setLabel($lang->translate('Website Url').':');
		$website_url->setRequired(true);
		$website_url->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$website_url->setFilters(array( new Zend_Filter_StringTrim()));
		$website_url->setAttrib('class', 'form-control');
		
		//default page
		$default_page = New Zend_Form_Element_Hidden('default_page');
		$default_page->setLabel($lang->translate('Default Page').':');
		$default_page->setFilters(array( new Zend_Filter_StringTrim()));
		$default_page->removeDecorator('Label');
		$default_page->removeDecorator('HtmlTag');
		
		
		//Logo
		$logo = New Zend_Form_Element_File('logo');
		$logo->setLabel($lang->translate('Logo').':');
		$logo->setDestination(APPLICATION_PATH. '/../public/uploads/tmp');
		$logo->addValidator('Count', false, 1);
		$logo->addValidator('Extension', false, 'jpg,png,gif,jpeg');
		//$logo->setAttrib('class', 'form-control');
		
		//Icono
		$icon = New Zend_Form_Element_File('icon');
		$icon->setLabel($lang->translate('Icon').':');
		$icon->setDestination(APPLICATION_PATH. '/../public/uploads/tmp');
		$icon->addValidator('Count', false, 1);
		$icon->addValidator('Extension', false, 'jpg,png,gif,jpeg,ico');
		//$icon->setAttrib('class', 'form-control');
		
		//Info Email
		$info_email = New Zend_Form_Element_Text('info_email');
		$info_email->setLabel($lang->translate('Info Email').':');
		$info_email->setRequired(true);
		$info_email->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$info_email->addValidator(new Zend_Validate_EmailAddress());
		$info_email->setFilters(array( new Zend_Filter_StringTrim()));
		$info_email->setAttrib('class', 'form-control');
		
		
		//Copyright
		$copyright = New Zend_Form_Element_Text('copyright');
		$copyright->setLabel($lang->translate('Copyright').':');
		$copyright->setRequired(true);
		$copyright->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$copyright->setFilters(array( new Zend_Filter_StringTrim()));
		$copyright->setAttrib('class', 'form-control');
		
		
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
		
		//fields to show the uploaded images
		$img_logo = New Zend_Form_Element_Image('img_logo');
		$img_logo->setAttrib('onclick', 'return false;');
		$img_logo->setAttrib('style', 'width: 200px;');
		$img_logo->setAttrib('class', 'preview_img hide');
		
		$img_icon = New Zend_Form_Element_Image('img_icon');
		$img_icon->setAttrib('onclick', 'return false;');
		$img_icon->setAttrib('style', 'width: 16px;');
		$img_icon->setAttrib('class', 'preview_icon hide');
		
		$img_watermark = New Zend_Form_Element_Image('img_watermark');
		$img_watermark->setAttrib('onclick', 'return false;');
		$img_watermark->setAttrib('style', 'width: 200px;');
		$img_watermark->setAttrib('class', 'preview_img hide');
		
		//Website status
		$note_website_status = new Core_Form_FormLabel('note_website_status');
		$note_website_status->setValue($lang->translate('Website Status'));
		$note_website_status->setAttrib('class', 'label');
		
		//status
		$website_status = New Zend_Form_Element_Select('website_status');
		$website_status->setLabel($lang->translate('Status').':');
		$website_status->setRequired(true);
		$website_status->setAttrib('class', 'form-control');
		$website_status->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$website_status_type = $website_type;
		$website_status->setMultiOptions($website_status_type);
		
		//offline text
		$offline_text = New Zend_Form_Element_Textarea('offline_text');
		$offline_text->setLabel($lang->translate('Offline Text').':');
		$offline_text->setRequired(true);
		$offline_text->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$offline_text->setAttribs(array('cols' => 40, 'rows' => 5));
		$offline_text->setFilters(array( new Zend_Filter_StringTrim()));
		$offline_text->setAttrib('class', 'form-control');
		
		//Offline_img
		$img_offline = New Zend_Form_Element_Image('img_offline');
		$img_offline->setAttrib('onclick', 'return false;');
		$img_offline->setAttrib('style', 'width: 100px;');
		$img_offline->setAttrib('class', 'preview_img hide');
		
		$offline_image = New Zend_Form_Element_File('offline_image');
		$offline_image->setLabel($lang->translate('Offline Image').':');
		$offline_image->setDestination(APPLICATION_PATH. '/../public/uploads/tmp');
		$offline_image->addValidator('Count', false, 1);
		$offline_image->addValidator('Extension', false, 'jpg,png,gif,jpeg');
		$offline_image->setAttrib('style', 'width: 100%;');
		
		//coming soon text
		$coming_soon_text = New Zend_Form_Element_Textarea('coming_soon_text');
		$coming_soon_text->setLabel($lang->translate('Coming Soon Text').':');
		$coming_soon_text->setRequired(true);
		$coming_soon_text->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$coming_soon_text->setAttribs(array('cols' => 40, 'rows' => 5));
		$coming_soon_text->setFilters(array( new Zend_Filter_StringTrim()));
		$coming_soon_text->setAttrib('class', 'form-control');
		
		//coming soon img
		$img_coming_soon = New Zend_Form_Element_Image('img_coming_soon');
		$img_coming_soon->setAttrib('onclick', 'return false;');
		$img_coming_soon->setAttrib('style', 'width: 100px;');
		$img_coming_soon->setAttrib('class', 'preview_img hide');
		
		$coming_soon_image = New Zend_Form_Element_File('coming_soon_image');
		$coming_soon_image->setLabel($lang->translate('Coming Soon Image').':');
		$coming_soon_image->setDestination(APPLICATION_PATH. '/../public/uploads/tmp');
		$coming_soon_image->addValidator('Count', false, 1);
		$coming_soon_image->addValidator('Extension', false, 'jpg,png,gif,jpeg');
		$coming_soon_image->setAttrib('style', 'width: 100%;');
		
		
		//add elements to the form
		$this->addElements(array(
				$language,
				$template,
				$name,
				$description,
				$keywords,
				$website_url,
				$default_page,
				$logo,
				$img_logo,
				$icon,
				$img_icon,
				$info_email,
				$copyright,
				$img_watermark,
				$note_website_status,
				$website_status,
				$offline_text,
				$offline_image,
				$img_offline,
				$coming_soon_text,
				$coming_soon_image,
				$img_coming_soon,
				$submit,
				$cancel,
				$id
		));

	}
}
