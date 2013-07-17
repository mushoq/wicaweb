<?php
/**
 *	The content form displays on view all elements that user could fill.
 *
 * @category   WicaWeb
 * @package    Core_Form
 * @copyright  Copyright (c) WicaWeb - Mushoq 
 * @license    GNP
 * @version    1.0
 * @author	   Santiago Arellano
 */

class Core_Form_Content_Content extends Zend_Form {
	
	/**
	 * Creates elements that will be displayed on screen.
	 *
	 * The creation of some depends on website configuration.
	 */
	public function init() {
		
		$content_type_id = $this->getAttrib ( 'content_type_id' );
		
		$lang = Zend_Registry::get ( 'Zend_Translate' );
		
		// Set the method for the display form to POST
		$this->setMethod ( 'post' );
		$this->setAttrib ( 'id', 'frmContent' );
		$this->setAttrib('enctype', 'multipart/form-data');
		$this->setAction ( '' );
		
		$field = new Core_Model_Field ();
		$fields = $field->find ( 'wc_field', array (
				'content_type_id' => $content_type_id 
		) );
		
		// Title
		$title = new Zend_Form_Element_Text ( 'title' );
		$title->setLabel ( $lang->translate ( 'Title' ) . ':' );
		$title->addFilters ( array (
				'StringTrim' 
		) );
		$this->addElement ( $title );
		
		// Internal Name
		$internal_name = new Zend_Form_Element_Text ( 'internal_name' );
		$internal_name->setLabel ( '* '.$lang->translate ( 'Internal name' ) . ':' );
		$internal_name->addFilters ( array (
				'StringTrim' 
		) );
		$internal_name->setRequired ( true );
		
		$this->addElement ( $internal_name );
		
		// Hidden ID
		$hidden_id = new Zend_Form_Element_Hidden ( 'id' );
		$hidden_id->addFilters ( array (
				'StringTrim' 
		) );
		$this->addElement ( $hidden_id );
		
		// Hidden Content type ID
		$hdn_content_type_id = new Zend_Form_Element_Hidden ( 'content_type_id' );
		$hdn_content_type_id->setValue ( $content_type_id );
		$hdn_content_type_id->addFilters ( array (
				'StringTrim' 
		) );
		$this->addElement ( $hdn_content_type_id );
		
		foreach ( $fields as $fl ) {
			switch ($fl->type) {
				
				case 'textfield' :
					// text field object
					$textfield = new Zend_Form_Element_Text ( str_replace ( ' ', '_', strtolower ( $fl->name ) ) );
					if($fl->required == 'yes')
						$textfield->setLabel ( '* '.$lang->translate ( $fl->name ) . ':' );
					else 
						$textfield->setLabel ($lang->translate ( $fl->name ) . ':' );
					$textfield->addFilters ( array (
							'StringTrim' 
					) );
					
					if(((str_replace ( ' ', '_', strtolower ( $fl->name)) == 'email' || str_replace ( ' ', '_', strtolower ( $fl->name)) == 'link') && $content_type_id==3) || str_replace ( ' ', '_', strtolower ( $fl->name)) == 'number')
					$textfield->setAttrib('class', 'hide');
										
					$this->addElement ( $textfield );
					break;
				
				case 'textarea' :
					// text area object
					$textarea = new Zend_Form_Element_Textarea ( str_replace ( ' ', '_', strtolower ( $fl->name ) ) );
					if($fl->required == 'yes')
						$textarea->setLabel ( '* '.$lang->translate ( $fl->name ) . ':' );
					else	
						$textarea->setLabel ( $lang->translate ( $fl->name ) . ':' );
					$textarea->setAttribs ( array (
							'cols' => 40,
							'rows' => 5 
					) );
					$textarea->addFilters ( array (
							'StringTrim' 
					) );
					
					$this->addElement ( $textarea );
					
					break;
				
				case 'button' :

					$button = new Zend_Form_Element_Button ( str_replace ( ' ', '_', strtolower ( $fl->name ) ) );
					
					if(str_replace ( ' ', '_', strtolower ( $fl->name ) ) == 'save_image' || str_replace ( ' ', '_', strtolower ( $fl->name ) ) == 'save_form' || str_replace ( ' ', '_', strtolower ( $fl->name ) ) == 'save')
					{
						$button->setLabel ( $lang->translate ( 'Save' ) );
						$button->setAttrib('class', 'btn btn-success');
					} else {	
						$button->setLabel ( $lang->translate ( $fl->name ) );
						$button->setAttrib('class', 'btn btn-primary');
					}
					
					$this->addElement ( $button );
						
					break;
				
				case 'checkbox' :
					// checkbox object
					$checkbox = new Zend_Form_Element_Checkbox ( str_replace ( ' ', '_', strtolower ( $fl->name ) ) );
					if($fl->required == 'yes')
						$checkbox->setLabel ( '* '.$lang->translate ( $fl->name ) . ':' );
					else	
						$checkbox->setLabel ( $lang->translate ( $fl->name ) . ':' );
					$this->addElement ( $checkbox );
					break;
				
				case 'select_images' :
					// file object
					// Hidden Content type ID
					$hidden_aux_div = new Zend_Form_Element_Hidden ( 'select_images' );
					if($fl->required == 'yes')
						$hidden_aux_div->setLabel ( '* '.$lang->translate ( $fl->name ) . ':' );
					else
						$hidden_aux_div->setLabel ( $lang->translate ( $fl->name ) . ':' );
					$hidden_aux_div->addFilters ( array (
							'StringTrim'
					) );
					$this->addElement ( $hidden_aux_div );
					
					break;
				
				case 'file' :
					// file object

					$file = new Zend_Form_Element_Button(str_replace ( ' ', '_', strtolower ( $fl->name ) ));
					$file->setLabel($lang->translate('Search').'..');
					$file->setAttrib('class', 'hide btn');
					if($fl->required == 'yes')
						$file->setAttrib('label_name',  '* '.$lang->translate ( $fl->name ) . ':'  );
					else 	
						$file->setAttrib('label_name',  $lang->translate ( $fl->name ) . ':'  );
					$this->addElement($file);
					
					// Hidden Content type ID
					$hidden_element = new Zend_Form_Element_Hidden ( 'hdnNameFile_'.str_replace ( ' ', '_', strtolower ( $fl->name ) ) );
					$hidden_element->setValue ( '' );
					$hidden_element->addFilters ( array (
							'StringTrim'
					) );
					$this->addElement ( $hidden_element );					
									
					
					break;
				
				case 'flash' :
					// flash object
					$flash = new Zend_Form_Element_File ( 'swf_' . str_replace ( ' ', '_', strtolower ( $fl->name ) ) );
					if($fl->required == 'yes')
						$flash->setLabel ( '* '.$lang->translate ( $fl->name ) . ':' );
					else
						$flash->setLabel ( $lang->translate ( $fl->name ) . ':' );
					$flash->setDestination ( APPLICATION_PATH . '/../public/uploads/tmp' );
					$flash->addValidator ( 'Count', false, 1 );
					$flash->addValidator ( 'Extension', false, 'swf' );
					$this->addElement ( $flash );
					
					break;
				
				case 'image' :
					
					// Hidden Content type ID
					$hidden_aux_div = new Zend_Form_Element_Hidden ( 'aux_make_div_image' );
					if($fl->required == 'yes')
						$hidden_aux_div->setLabel ( '* '.$lang->translate ( $fl->name ) . ':' );
					else	
						$hidden_aux_div->setLabel ( $lang->translate ( $fl->name ) . ':' );
					$hidden_aux_div->addFilters ( array (
							'StringTrim'
					) );
					$this->addElement ( $hidden_aux_div );

					$image_preview = new Zend_Form_Element_Image ( 'prw_' . str_replace ( ' ', '_', strtolower ( $fl->name ) ) );
					$image_preview->setAttrib ( 'onclick', 'return false;' );
					$image_preview->setAttrib ( 'class', 'preview_img' );
					$image_preview->removeDecorator ( 'Label' );
					$image_preview->removeDecorator ( 'HtmlTag' );
					$this->addElement ( $image_preview );					
					
					break;
				
				case 'select' :
					// select object
					$select = new Zend_Form_Element_Select ( str_replace ( ' ', '_', strtolower ( $fl->name ) ) );
					
					if(str_replace ( ' ', '_', strtolower ( $fl->name ) )=='internal_section')
						$select->setLabel ( '* '.$lang->translate ( 'Section' ) . ':' );
					else{
						if($fl->required == 'yes')
							$select->setLabel ( '* '.$lang->translate ( $fl->name ) . ':' );
						else	
							$select->setLabel ( $lang->translate ( $fl->name ) . ':' );
					}
					
					if (str_replace ( ' ', '_', strtolower ( $fl->name )) == 'format')
						$select->setMultiOptions ( array (
								'' => $lang->translate ('- Select -'),
								'frame' => $lang->translate ('Frame'),
								'no_frame' => $lang->translate ('No frame')
						) );
					if(str_replace ( ' ', '_', strtolower ( $fl->name)) == 'file_type')
					{
						$select->setMultiOptions ( array (
								'' => $lang->translate ('- Select -'),
								'image' => $lang->translate ('Image'),
								'audio' => $lang->translate ('Audio'),
								'video' => $lang->translate ('Video'),
								'pdf' => 'Pdf',
								'zip' => 'Zip',
								'word' => 'Word',
								'excel' => 'Excel' ,
								'ppt' => 'Power Point',
								'other' => $lang->translate ( 'Other' )
						) );
						$select->setAttrib('class', 'hide');
	
					}	

					if(str_replace ( ' ', '_', strtolower ( $fl->name)) == 'element_type')
						$select->setMultiOptions ( array (
								'' => $lang->translate ('- Select -'),
								'textfield' => $lang->translate ('Text field'),
								'textarea' => $lang->translate ('Text area'),
								'radiobutton' => $lang->translate ('Radio button'),
								'dropdown' => $lang->translate('Dropdown list'),
								'checkbox' => $lang->translate('Checkbox'),
								'comment' => $lang->translate('Comment'),
								'file' => $lang->translate('File')
						) );	
					
					if(str_replace ( ' ', '_', strtolower ( $fl->name)) == 'internal_section' && $content_type_id==3)
					{
						$section = new Core_Model_Section();
						$sections = $section->find('wc_section');
						$sections_arr = array();
						
						//sections list array
						if($sections)
						{
							$sections_arr[''] = $lang->translate ('- Select -');
							foreach ($sections as $sec)
							{
								$section_module_area = new Core_Model_SectionModuleArea();
                                $area = $section_module_area->find('wc_section_module_area',array('section_id'=>$sec->id));
                                $area_type = "";
                                if(count($area)>0)
                                {
	                                $area_sec = $area[0]->area_id;
									$template_areas = new Core_Model_Area();
									$area_tpl = $template_areas->find('wc_area',array('id'=>$area_sec));
									$area_type = $area_tpl[0]->type;
								}

								if($area_type=='variable')
									$sections_arr[$sec->id] = $sec->title;
							}
						}		
						
						$select->setMultiOptions ($sections_arr);						
						
						$select->setAttrib('class', 'hide');
					}
					
					$this->addElement ( $select );
					break;
				
				case 'radio' :
					// radio object
					$hidden_element = new Zend_Form_Element_Hidden ( str_replace ( ' ', '_', strtolower ( $fl->name ) ) );
					if($fl->required == 'yes')
						$hidden_element->setLabel ( '* '.$lang->translate ( $fl->name ) . ':' );
					else	
						$hidden_element->setLabel ( $lang->translate ( $fl->name ) . ':' );
					$this->addElement ( $hidden_element );					
				
					break;
			}
		}
	
	}

}