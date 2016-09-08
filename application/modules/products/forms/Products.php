<?php
/**
 *
 * @category   WicaWeb
 * @package    Products_Form
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   David Rosales
 * @version    1.0
 *
 */
class Products_Form_Products extends Zend_Form
{
	/**
	 * Loads all the objects of the form when the form is initialized
	 */
	public function init()
	{

		$this->setAttrib('id', 'frmProducts');
		$this->setAttrib('class', 'form-horizontal well');
		$this->setAttrib('enctype', 'multipart/form-data'); //attr to support file upload
		$this->setAction('/products/index/saveproducts');
		
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		//translate enums
		$available_opt = GlobalFunctions::arrayTranslate(Products_Model_Products::$available);
		$feature_opt = GlobalFunctions::arrayTranslate(Products_Model_Products::$feature);
                $highlight_opt = GlobalFunctions::arrayTranslate(Products_Model_Products::$highlight);
		$status_opt = GlobalFunctions::arrayTranslate(Products_Model_Products::$status);
		
		//translate
		$lang = Zend_Registry::get('Zend_Translate');
		
		//session
		$session = New Zend_Session_Namespace('id');
		
		//id
		$id = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('Label');
		$id->removeDecorator('HtmlTag');
		
		$section_id = new Zend_Form_Element_Hidden('section_id');
		$section_id->removeDecorator('Label');
		$section_id->removeDecorator('HtmlTag');
		
                $website_id = new Zend_Form_Element_Hidden('website_id');
		$website_id->removeDecorator('Label');
		$website_id->removeDecorator('HtmlTag');
                
		//name
		$name_pro = new Zend_Form_Element_Text('name');
		$name_pro->setLabel('* '.$lang->translate('Name').':');
                $name_pro->setAttrib('class', 'form-control');
		
		//description
		$description = new Zend_Form_Element_Textarea('description');
		$description->setLabel($lang->translate('Description').':');
		$description->setAttribs(array('cols' => 40, 'rows' => 5));
		$description->setAttrib('class', 'form-control');
		
		//image
		/*$name = new Zend_Form_Element_Text('product_name_img');
		$name->setLabel($lang->translate('Name').':');
		$name->addFilters(array('StringTrim'));
		$this->addElement($name);*/
		
		$img_file = new Zend_Form_Element_Hidden('product_file_img');
		$img_file->removeDecorator('Label');
		$img_file->removeDecorator('HtmlTag');
		$this->addElement($img_file);
		
		/*$img_id = new Zend_Form_Element_Hidden('product_id_img');
		$img_id->removeDecorator('Label');
		$img_id->removeDecorator('HtmlTag');
		$this->addElement($img_id);*/
		
		$image_preview = New Zend_Form_Element_Image('product_imageprw');
		$image_preview->removeDecorator('Label');
		$image_preview->removeDecorator('HtmlTag');
		$image_preview->setAttrib ('class', 'preview_img hide');
		$image_preview->setAttrib('onclick', 'return false;');
		$this->addElement($image_preview);
		
		$image = new Zend_Form_Element_Button('product_img');
		$image->setLabel($lang->translate('Search'));
		$image->setAttrib('class', 'btn btn-default');
		$this->addElement($image);
                
                
                $ficha_file = new Zend_Form_Element_Hidden('product_file_ficha');
		$ficha_file->removeDecorator('Label');
		$ficha_file->removeDecorator('HtmlTag');
		$this->addElement($ficha_file);
		
		/*$img_id = new Zend_Form_Element_Hidden('product_id_img');
		$img_id->removeDecorator('Label');
		$img_id->removeDecorator('HtmlTag');
		$this->addElement($img_id);*/
		
		$ficha = new Zend_Form_Element_Button('product_ficha');
		$ficha->setLabel($lang->translate('Search'));
		$ficha->setAttrib('class', 'btn btn-default');
		$this->addElement($ficha);

				
		//available - yes / no -
		$available = new Zend_Form_Element_Hidden('available');
		$default_available_arr = array_keys($available_opt);
		$available->setValue($default_available_arr[0]);
		$available->removeDecorator('Label');
		$available->removeDecorator('HtmlTag');
		
		//status - published / nonpublished
		$status = new Zend_Form_Element_Hidden('status');
		$default_status_arr = array_keys($status_opt);
		$status->setValue($default_status_arr[0]);
		$status->removeDecorator('Label');
		$status->removeDecorator('HtmlTag');
		
		//feature - yes / no -
		$feature = new Zend_Form_Element_Hidden('feature');
		$default_feature_arr = array_keys($feature_opt);
		$feature->setValue($default_feature_arr[1]);
		$feature->removeDecorator('Label');
		$feature->removeDecorator('HtmlTag');
                
                //highlight - yes / no -
		$highlight = new Zend_Form_Element_Hidden('highlight');
		$default_highlight_arr = array_keys($highlight_opt);
		$highlight->setValue($default_highlight_arr[1]);
		$highlight->removeDecorator('Label');
		$highlight->removeDecorator('HtmlTag');
		
		$this->addElements(array(
                    $id,
                    $section_id,
                    $website_id,
                    $name_pro,
                    $description,
                    $available,
                    $status,
                    $feature,
                    $highlight
		));
		
		//submit button
		$add_feature = new Zend_Form_Element_Button('add_feature');
		$add_feature->setLabel($lang->translate('Add feature'));
		$add_feature->setAttrib('class', 'btn btn-warning');
		$this->addElement($add_feature);
		
		//submit button
		$submit_button = new Zend_Form_Element_Button('submit_button');
		$submit_button->setLabel($lang->translate('Save'));
		$submit_button->setAttrib('class', 'btn btn-success');
		$this->addElement($submit_button);
		
		//cancel button
		$cancel_button = new Zend_Form_Element_Button('cancel_button');
		$cancel_button->setLabel($lang->translate('Cancel'));
		$cancel_button->setAttrib('class', 'btn');
		$this->addElement($cancel_button);
		
		
	}
}
