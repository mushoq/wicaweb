<?php
/**
 * Form Website
 * This file has parameters to create a form for the website configurations
 *
 * @category   WicaWeb
 * @package    Core_Form
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   Esteban
 * @version    1.0
 *
 */
class Core_Form_Website_Website extends Zend_Form
{
	/**
	 * Loads all the objects of the form when the form is initialized
	 */
	public function init()
	{
		$this->setAttrib('id', 'frmWebsite');
		$this->setAttrib('class', 'well');
		$this->setAttrib('enctype', 'multipart/form-data'); //attr to support file upload
		
		
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
		$language->setAttrib('style', 'width:100%');
		
		//template
		$template = New Zend_Form_Element_Hidden('template_id');
// 		$template = New Zend_Form_Element_Select('template_id');
// 		$template->setLabel($lang->translate('Template').':');
// 		$template->setRequired(true);
// 		$template->getDecorator('label')->setOption('requiredPrefix', ' * ');
		
// 		//Get values from wc_website_template
// 		$template_model = new Core_Model_WebsiteTemplate();
// 		$aux= $template_model->find('wc_website_template');
// 		//create the array for populate the select
// 		$options_language = array();
// 		foreach ($aux as $t){
// 			$options_template[$t->id] = $t->name;
// 		}
// 		$template->setMultiOptions($options_template);
// 		$template->setAttrib('style', 'width:100%');
		
		//name
		$name = New Zend_Form_Element_Text('name');
		$name->setLabel($lang->translate('Website Name').':');
		$name->setRequired(true);
		$name->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$name->setFilters(array( new Zend_Filter_StringTrim()));
		$name->setAttrib('style', 'width:100%');		

		//description
		$description = New Zend_Form_Element_Textarea('description');
		$description->setLabel($lang->translate('Description').':');
		$description->setRequired(true);
		$description->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$description->setAttribs(array('cols' => 40, 'rows' => 5));
		$description->setAttrib('style', 'width:100%');
		
		//keywords
		$keywords = New Zend_Form_Element_Textarea('keywords');
		$keywords->setLabel($lang->translate('Keywords').':');
		$keywords->setRequired(true);
		$keywords->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$keywords->setAttribs(array('cols' => 40, 'rows' => 5));
		$keywords->setAttrib('style', 'width:100%');
		
		//website Url
		$website_url = New Zend_Form_Element_Text('website_url');
		$website_url->setLabel($lang->translate('Website Url').':');
		$website_url->setRequired(true);
		$website_url->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$website_url->setFilters(array( new Zend_Filter_StringTrim()));
		$website_url->setAttrib('style', 'width:100%');
		
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
		$logo->setAttrib('style', 'width:100%');
		
		//Icono
		$icon = New Zend_Form_Element_File('icon');
		$icon->setLabel($lang->translate('Icon').':');
		$icon->setDestination(APPLICATION_PATH. '/../public/uploads/tmp');
		$icon->addValidator('Count', false, 1);
		$icon->addValidator('Extension', false, 'jpg,png,gif,jpeg,ico');
		$icon->setAttrib('style', 'width:100%');
		
		//Info Email
		$info_email = New Zend_Form_Element_Text('info_email');
		$info_email->setLabel($lang->translate('Info Email').':');
		$info_email->setRequired(true);
		$info_email->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$info_email->addValidator(new Zend_Validate_EmailAddress());
		$info_email->setFilters(array( new Zend_Filter_StringTrim()));
		$info_email->setAttrib('style', 'width:100%');
		
		//Time zone
		$time_zone = New Zend_Form_Element_Select('time_zone');
		$time_zone->setLabel($lang->translate('Time Zone').':');
		$time_zone->setRequired(true);
		$time_zone->getDecorator('label')->setOption('requiredPrefix', ' * ');
		
		$options_time_zone = array();
		for($i=-12; $i<15; $i++){
			$options_time_zone['GMT'.$i] = 'GMT'.$i;

		} 
		$time_zone->setMultiOptions($options_time_zone);
		
		//Date Format
		$date_format = New Zend_Form_Element_Select('date_format');
		$date_format->setLabel($lang->translate('Date Format').':');
		$date_format->setRequired(true);
		$date_format->getDecorator('label')->setOption('requiredPrefix', ' * ');
		//TODO: Change for date_format values
		
		$options_date_format = array('dd/mm/yyyy' => 'dd/mm/yyyy',
									'mm/dd/yyyy' => 'mm/dd/yyyy',
									'mm/dd/yy' => 'mm/dd/yy',
									'm/d/y' => 'm/d/y',
									'yyyy/mm/dd' => 'yyyy/mm/dd',
									'yy/mm/dd' => 'yy/mm/dd',
		);
		$date_format->setMultiOptions($options_date_format);
		
		//Hour Format
		$hour_format = New Zend_Form_Element_Hidden('hour_format');
		$default_hourformat_arr = array_keys($hour_format_options);
		$hour_format->setValue($default_hourformat_arr[1]);
		$hour_format->removeDecorator('Label');
		$hour_format->removeDecorator('HtmlTag');
		 
		
		//Number Format
		$number_format = New Zend_Form_Element_Select('number_format');
		$number_format->setLabel($lang->translate('Number Format').':');
		$number_format->setRequired(true);
		$number_format->getDecorator('label')->setOption('requiredPrefix', ' * ');
		//TODO: Change for number_format values
		$options_number_format = array('1' => '123.X','2' => '123.XX','3' => '123.XXX','4' => '123.XXXX');
		$number_format->setMultiOptions($options_number_format);
		
		//Copyright
		$copyright = New Zend_Form_Element_Text('copyright');
		$copyright->setLabel($lang->translate('Copyright').':');
		$copyright->setRequired(true);
		$copyright->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$copyright->setFilters(array( new Zend_Filter_StringTrim()));
		$copyright->setAttrib('style', 'width:100%');
		
		
		//Publication Aprove
		$publication_approve = New Zend_Form_Element_Hidden('publication_approve');
		$default_publicationapp_arr = array_keys($confirm);
		$publication_approve->setValue($default_publicationapp_arr[1]);
		$publication_approve->removeDecorator('Label');
		$publication_approve->removeDecorator('HtmlTag');
		
		
		//Prints
		$prints = New Zend_Form_Element_Hidden('prints');
		$default_prints_arr = array_keys($confirm);
		$prints->setValue($default_prints_arr[1]);
		$prints->removeDecorator('Label');
		$prints->removeDecorator('HtmlTag');
		
		//Friendly Url
		$friendly_url = New Zend_Form_Element_Hidden('friendly_url');
		$default_friendlyurl_arr = array_keys($confirm);
		$friendly_url->setValue($default_friendlyurl_arr[1]);
		$friendly_url->removeDecorator('Label');
		$friendly_url->removeDecorator('HtmlTag');
		
		//Tiny Url
		$tiny_url = New Zend_Form_Element_Hidden('tiny_url');
		$default_tinyurl_arr = array_keys($confirm);
		$tiny_url->setValue($default_tinyurl_arr[1]);
		$tiny_url->removeDecorator('Label');
		$tiny_url->removeDecorator('HtmlTag');
		
		//Log
		$log = New Zend_Form_Element_Hidden('log');
		$default_log_arr = array_keys($confirm);
		$log->setValue($default_log_arr[1]);
		$log->removeDecorator('Label');
		$log->removeDecorator('HtmlTag');
		
		//Sitemap level
		$sitemap_level = New Zend_Form_Element_Hidden('sitemap_level');
		$sitemap_level->setLabel($lang->translate('Sitemap Levels').':');
		$sitemap_level->setRequired(true);
		$sitemap_level->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$sitemap_level->setFilters(array( new Zend_Filter_StringTrim()));
		$sitemap_level->setAttrib('style', 'width:100%');
		
		//Dictionary level
		$dictionary = New Zend_Form_Element_Hidden('dictionary');
		$default_dictionary_arr = array_keys($confirm);
		$dictionary->setValue($default_dictionary_arr[1]);
		$dictionary->removeDecorator('Label');
		$dictionary->removeDecorator('HtmlTag');
		
		//Private Sections level
		$private_section = New Zend_Form_Element_Hidden('private_section');
		$default_privatesection_arr = array_keys($confirm);
		$private_section->setValue($default_privatesection_arr[1]);
		$private_section->removeDecorator('Label');
		$private_section->removeDecorator('HtmlTag');
		
		//Section Expiration 
		$section_expiration = New Zend_Form_Element_Hidden('section_expiration');
		$default_sectionexpiration_arr = array_keys($confirm);
		$section_expiration->setValue($default_sectionexpiration_arr[1]);
		$section_expiration->removeDecorator('Label');
		$section_expiration->removeDecorator('HtmlTag');
		
		//Section Author
		$section_author = New Zend_Form_Element_Hidden('section_author');
		$default_sectionauthor_arr = array_keys($confirm);
		$section_author->setValue($default_sectionauthor_arr[1]);
		$section_author->removeDecorator('Label');
		$section_author->removeDecorator('HtmlTag');
		
		//Section Feature
		$section_feature = New Zend_Form_Element_Hidden('section_feature');
		$default_sectionfeature_arr = array_keys($confirm);
		$section_feature->setValue($default_sectionfeature_arr[1]);
		$section_feature->removeDecorator('Label');
		$section_feature->removeDecorator('HtmlTag');
		
		//Section Highlight
		$section_highlight = New Zend_Form_Element_Hidden('section_highlight');
		$default_sectionhighlight_arr = array_keys($confirm);
		$section_highlight->setValue($default_sectionhighlight_arr[1]);
		$section_highlight->removeDecorator('Label');
		$section_highlight->removeDecorator('HtmlTag');
		
		//Section Comments Management
		$section_comments_management = New Zend_Form_Element_Hidden('section_comments_management');
		$default_sectioncomments_arr = array_keys($confirm);
		$section_comments_management->setValue($default_sectioncomments_arr[1]);
		$section_comments_management->removeDecorator('Label');
		$section_comments_management->removeDecorator('HtmlTag');
		
		//Section Comments 
		$section_comments = New Zend_Form_Element_Select('$section_comments');
		$section_comments->setLabel($lang->translate('Comments Place').':');
		$section_comments->setRequired(true);
		$section_comments->setAttrib('style', 'width:100%');
		$section_comments->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$section_comments->setMultiOptions($comments_enum);
		
		//Section Comments Type
		$section_comments_type = New Zend_Form_Element_Hidden('section_comments_type');
		$section_comments_type->removeDecorator('Label');
		$section_comments_type->removeDecorator('HtmlTag');
		
		
		//Section Images number
		$section_images_number = New Zend_Form_Element_Hidden('section_images_number'); 
		$section_images_number->setLabel($lang->translate('Section Images Number').':');
		$section_images_number->getDecorator('label')->setOption('requiredPrefix', ' * ');
		$section_images_number->setFilters(array( new Zend_Filter_StringTrim()));
		$section_images_number->setAttrib('style', 'width:100%');
		
		//Section storage
		$section_storage = New Zend_Form_Element_Hidden('section_storage');
		$default_sectionstorage_arr = array_keys($confirm);
		$section_storage->removeDecorator('Label');
		$section_storage->removeDecorator('HtmlTag');
				
		//Section rss
		$section_rss = New Zend_Form_Element_Hidden('section_rss');
		$default_sectionrss_arr = array_keys($confirm);
		$section_rss->setValue($default_sectionrss_arr[1]);
		$section_rss->removeDecorator('Label');
		$section_rss->removeDecorator('HtmlTag');
		
		//Watermark
		$watermark = New Zend_Form_Element_File('watermark');
		$watermark->setLabel($lang->translate('Watermark').':');
		$watermark->setDestination(APPLICATION_PATH. '/../public/uploads/tmp');
		$watermark->addValidator('Count', false, 1);
		$watermark->addValidator('Extension', false, 'jpg,png,gif,jpeg');
		$watermark->setAttrib('style', 'width:100%');
		
		//Watermark Pos
		$watermark_pos = New Zend_Form_Element_Hidden('watermark_pos');
		$watermark_pos->setValue('C');
		$watermark_pos->removeDecorator('Label');
		$watermark_pos->removeDecorator('HtmlTag');
		
		
		//CONFIG SMTP
		$note_smtp_config = new Core_Form_FormLabel('note_smtp_config');
		$note_smtp_config->setValue($lang->translate('SMTP Configuration'));
		$note_smtp_config->setAttrib('class', 'label');
		
		//Hostname
		$smtp_hostname = New Zend_Form_Element_Text('smtp_hostname');
		$smtp_hostname->setLabel($lang->translate('Hostname').':');
		$smtp_hostname->setFilters(array( new Zend_Filter_StringTrim()));
		$smtp_hostname->setAttrib('style', 'width:100%');
		
		//Port
		$smtp_port = New Zend_Form_Element_Text('smtp_port');
		$smtp_port->setLabel($lang->translate('Port').':');
		$smtp_port->setFilters(array( new Zend_Filter_StringTrim()));
		$smtp_port->setAttrib('style', 'width:100%');
		
		//Username
		$smtp_username = New Zend_Form_Element_Text('smtp_username');
		$smtp_username->setLabel($lang->translate('Username').':');
		$smtp_username->setFilters(array( new Zend_Filter_StringTrim()));
		$smtp_username->setAttrib('style', 'width:100%');
		
		//Password
		$smtp_password = New Zend_Form_Element_Text('smtp_password');
		$smtp_password->setLabel($lang->translate('Password').':');
		$smtp_password->setFilters(array( new Zend_Filter_StringTrim()));
		$smtp_password->setAttrib('style', 'width:100%');
		
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
		$website_status->setAttrib('style', 'width:100%');
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
		$offline_text->setAttrib('style', 'width:100%');
		
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
		$coming_soon_text->setAttrib('style', 'width:100%');
		
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

		//google analytics
		$expiration_time = New Zend_Form_Element_Text('section_expiration_time');
		$expiration_time->setLabel($lang->translate('Expiration Time').':');
		$expiration_time->setAttrib('style', 'width:100%');		
		
		//google analytics
		$analytics = New Zend_Form_Element_Text('analytics');
		$analytics->setLabel($lang->translate('Google Analytics ID').':');
		$analytics->setAttrib('style', 'width:100%');

		//Loaded image max height
		$max_height = New Zend_Form_Element_Text('max_height');
		$max_height->setLabel($lang->translate('Loaded image max height').':');
		$max_height->setAttrib('style', 'width:100%');
				
		//Loaded image max width
		$max_width = New Zend_Form_Element_Text('max_width');
		$max_width->setLabel($lang->translate('Loaded image max width').':');
		$max_width->setAttrib('style', 'width:100%');		
		
		//add elements to the form
		$this->addElements(array(
				$max_height,
				$max_width,
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
				$time_zone,
				$date_format,
				$hour_format,
				$number_format,
				$copyright,
				$publication_approve,
				$prints,
				$friendly_url,
				$tiny_url,
				$log,
				$sitemap_level,
				$dictionary,
				$private_section,
				$section_expiration,
				$section_author,
				$section_feature,
				$section_highlight,
				$section_comments_management,
				$section_comments,
				$section_comments_type,
				$section_images_number,
				$section_storage,
				$section_rss,
				$watermark,
				$watermark_pos,
				$img_watermark,
				$note_smtp_config,
				$smtp_hostname,
				$smtp_port,
				$smtp_username,
				$smtp_password,
				$note_website_status,
				$website_status,
				$offline_text,
				$offline_image,
				$img_offline,
				$coming_soon_text,
				$coming_soon_image,
				$img_coming_soon,
				$analytics,
				$expiration_time,
				$submit,
				$cancel,
				$id
		));

	}
}
