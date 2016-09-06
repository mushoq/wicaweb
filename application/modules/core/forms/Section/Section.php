<?php
/**
*        The section form displays on view all elements that user could fill.
*
* @category WicaWeb
* @package Core_Form
* @copyright Copyright (c) WicaWeb - Mushoq
* @license GNP
* @version 1.0
* @author         David Rosales
*/

class Core_Form_Section_Section extends Zend_Form{
        
        /**
         *        Creates elements that will be displayed on screen.
         *        The creation of some depends on website configuration.
         */
        public function init()
        {
                // Set the method for the display form to POST
                $this->setMethod('post');
                $this->setAttrib('class', 'form-horizontal');
                $this->setAttrib('id', 'frmSection');
                $this->setAction('');
                                                
                $publication_options = GlobalFunctions::arrayTranslate(Core_Model_Section::$status_publication);
                $type_options = GlobalFunctions::arrayTranslate(Core_Model_Section::$section_type);
                $confirm_options = GlobalFunctions::arrayTranslate(Core_Model_Section::$confirm);        
                $target_type = GlobalFunctions::arrayTranslate(Core_Model_Section::$target_type);        
                
                //translate
                $lang = Zend_Registry::get('Zend_Translate');
                
                //session
                $session = New Zend_Session_Namespace('id');
                                                
                //id
                $id = new Zend_Form_Element_Hidden('id');
                $id->removeDecorator('Label');
                $id->removeDecorator('HtmlTag');
                
                //section_parent_id
                $section_parent_id = new Zend_Form_Element_Hidden('section_parent_id');
                $section_parent_id->removeDecorator('Label');
                $section_parent_id->removeDecorator('HtmlTag');
                
                //subsection of
                $subsection = new Zend_Form_Element_Button('subsection_of');
                $subsection->setAttrib('class', 'btn btn-default');
                        
                //template
                $template = new Zend_Form_Element_Select('section_template_id');
                $template->setLabel('* '.$lang->translate('Template').':'); 
                $template->setAttrib('class', 'form-control');
                //Get values from wc_section_template
                $section_template_model = new Core_Model_SectionTemplate();        
                $options_both = $section_template_model->find('wc_section_template', array('type'=>'both'), array('name'=>'ASC'));
                if(count($options_both)>0)
                {                
                        foreach ($options_both as $t){
                                $options_template[$t->id] = $t->name;
                        }
                }
                $options_section = $section_template_model->find('wc_section_template', array('type'=>'section'), array('name'=>'ASC'));
                if(count($options_section)>0)
                {
                        foreach ($options_section as $t){
                                $options_template[$t->id] = $t->name;
                        }
                }
                $template->setMultiOptions($options_template);
                
                //internal name
                $internal_name = new Zend_Form_Element_Text('internal_name');
                $internal_name->setLabel('* '.$lang->translate('Internal name').':');
                $internal_name->setAttrib('class', 'form-control');

                //title
                $title = new Zend_Form_Element_Text('title');
                $title->setLabel('* '.$lang->translate('Title').':');
                $title->setAttrib('class', 'form-control');

                //subtitle
                $subtitle = new Zend_Form_Element_Text('subtitle');
                $subtitle->setLabel($lang->translate('Subtitle').':'); 
                $subtitle->setAttrib('class', 'form-control');
                
                //title_browser
                $title_browser = new Zend_Form_Element_Text('title_browser');
                $title_browser->setLabel($lang->translate('Browser title').':');
                $title_browser->setAttrib('class', 'form-control');
                
                //synopsis
                $synopsis = new Zend_Form_Element_Textarea('synopsis');
                $synopsis->setLabel($lang->translate('Synopsis').':');
                $synopsis->setAttribs(array('cols' => 40, 'rows' => 5));
                
                
                //keywords
                $keywords = new Zend_Form_Element_Textarea('keywords');
                $keywords->setLabel($lang->translate('Keywords').':');
                $keywords->setAttribs(array('cols' => 40, 'rows' => 5));
                $keywords->setAttrib('class', 'form-control');

                //link - yes / no -
                $link = new Zend_Form_Element_Hidden('link');
                $default_link_arr = array_keys($confirm_options);
                $link->setValue($default_link_arr[1]);
                $link->removeDecorator('Label');
                $link->removeDecorator('HtmlTag');
                $link->setAttrib('class', 'form-control');
                                
                //external link text
                $external_link = new Zend_Form_Element_Text('external_link');
                $external_link->setLabel('* '.$lang->translate('Url external link').':');
                $external_link->setAttrib('class', 'form-control');

                //target - self / none -                
                $target = new Zend_Form_Element_Hidden('target');
                $target->removeDecorator('Label');
                $target->removeDecorator('HtmlTag');
                
                //menu
                $homepage = new Zend_Form_Element_Hidden('homepage');
                $default_homepage_arr = array_keys($confirm_options);
                $homepage->setValue($default_homepage_arr[1]);
                $homepage->removeDecorator('Label');
                $homepage->removeDecorator('HtmlTag');
                
                /**
                 * searching for actual website to retrieve config options for sections
                 */                                
                $website = new Core_Model_Website();
                $website_fn = $website->find('wc_website',array('id'=>$session->website_id));
                $website_db = $website_fn[0];
                
                //website template areas
                if($website_db->template_id)
                {                        
                        $area = new Zend_Form_Element_Hidden('section_area');                        
                        $area->removeDecorator('Label');
                        $area->removeDecorator('HtmlTag');
                        
                        $area_id = new Zend_Form_Element_Hidden('section_area_id');
                        $area_id->removeDecorator('Label');
                        $area_id->removeDecorator('HtmlTag');
                }
                
                //menu
                $menu = new Zend_Form_Element_Hidden('menu');
                $menu->removeDecorator('Label');
                $menu->removeDecorator('HtmlTag');        

                //menu2
                $menu2 = new Zend_Form_Element_Hidden('menu2');
                $menu2->removeDecorator('Label');
                $menu2->removeDecorator('HtmlTag');                
                
                //parent show menu
                $parent_show_menu = new Zend_Form_Element_Hidden('parent_show_menu');
                $parent_show_menu->removeDecorator('Label');
                $parent_show_menu->removeDecorator('HtmlTag');

                //parent show menu2
                $parent_show_menu2 = new Zend_Form_Element_Hidden('parent_show_menu2');
                $parent_show_menu2->removeDecorator('Label');
                $parent_show_menu2->removeDecorator('HtmlTag');                
                
                //die( $parent_show_menu);
                //publish dates
                if($website_db->section_expiration =='yes')
                {
                        //publish date
                        $publish_date = new Zend_Form_Element_Text('publish_date');
                        $publish_date->setLabel($lang->translate('Publish date').':');
                        //expire date
                        $expire_date = new Zend_Form_Element_Text('expire_date');
                        $expire_date->setLabel($lang->translate('Expire date').':');
                        //start time
                        $start_time = new Zend_Form_Element_Text('hora_inicio');
                        $start_time->setLabel('Hora inicio:')
                                    ->setAttrib('class', 'date-calendar')
                                    ->setAttrib('placeholder', 'hh:mm:ss');
                        //end time
                        $end_time = new Zend_Form_Element_Text('hora_fin');
                        $end_time->setLabel('Hora fin:')
                                    ->setAttrib('class', 'date-calendar')
                                    ->setAttrib('placeholder', 'hh:mm:ss');
                }
                else
                {
                        $publish_date = new Zend_Form_Element_Hidden('publish_date');                        
                        $publish_date->removeDecorator('Label');
                        $publish_date->removeDecorator('HtmlTag');
                        
                        $expire_date = new Zend_Form_Element_Hidden('expire_date');
                        $expire_date->removeDecorator('Label');
                        $expire_date->removeDecorator('HtmlTag');
                        
                        $start_time = new Zend_Form_Element_Hidden('hora_inicio');
                        $start_time->removeDecorator('Label');
                        $start_time->removeDecorator('HtmlTag');
                        
                        $end_time = new Zend_Form_Element_Hidden('hora_fin');
                        $end_time->removeDecorator('Label');
                        $end_time->removeDecorator('HtmlTag');
                }
                
                //show_publish_date - yes / no -
                $show_publish_date = new Zend_Form_Element_Hidden('show_publish_date');
                $default_publish_arr = array_keys($confirm_options);
                $show_publish_date->setValue($default_publish_arr[1]);
                $show_publish_date->removeDecorator('Label');
                $show_publish_date->removeDecorator('HtmlTag');
                                
                //author
                if($website_db->section_author =='yes')
                {
                        $author = new Zend_Form_Element_Text('author');
                        $author->setLabel($lang->translate('Author').':');
                }
                else
                {
                        $author = new Zend_Form_Element_Hidden('author');                
                        $author->removeDecorator('Label');
                        $author->removeDecorator('HtmlTag');
                }        

                //type        
                $type = new Zend_Form_Element_Hidden('type');
                $type->setRequired(true);
                $default_type_arr = array_keys($type_options);
                $type->setValue($default_type_arr[0]);
                $type->removeDecorator('Label');
                $type->removeDecorator('HtmlTag');

                //feature - yes / no -
                $feature = new Zend_Form_Element_Hidden('feature');
                $feature->setRequired(true);
                $default_feature_arr = array_keys($confirm_options);
                $feature->setValue($default_feature_arr[1]);                        
                $feature->removeDecorator('Label');
                $feature->removeDecorator('HtmlTag');                        

                //highlight - yes / no -                                
                $highlight = new Zend_Form_Element_Hidden('highlight');
                $highlight->setRequired(true);
                $default_highlight_arr = array_keys($confirm_options);
                $highlight->setValue($default_highlight_arr[1]);
                $highlight->removeDecorator('Label');
                $highlight->removeDecorator('HtmlTag');

                //comments - yes / no -                
                $comments = new Zend_Form_Element_Hidden('comments');
                $default_comments_arr = array_keys($confirm_options);
                $comments->setValue($default_comments_arr[1]);
                $comments->removeDecorator('Label');
                $comments->removeDecorator('HtmlTag');

                //RSS - yes / no -                                        
                $rss_available = new Zend_Form_Element_Hidden('rss_available');
                $default_rss_available_arr = array_keys($confirm_options);
                $rss_available->setValue($default_rss_available_arr[1]);
                $rss_available->removeDecorator('Label');
                $rss_available->removeDecorator('HtmlTag');
                        
                //publication_status                                        
                $approved = new Zend_Form_Element_Hidden('approved');                        
                $default_approve_arr = array_keys($confirm_options);
                //Save section data according publication approve option                
                if($website_db->publication_approve =='yes')
                {
                        if($session->user_profile == '1')
                        {
                                $approved->setValue($default_approve_arr[0]);
                        }
                        else
                        {
                                $approved->setValue($default_approve_arr[1]);
                        }                        
                }
                else
                {
                        $approved->setValue($default_approve_arr[0]);
                }
                $approved->removeDecorator('Label');
                $approved->removeDecorator('HtmlTag');
                
                $publication_status = new Zend_Form_Element_Hidden('publication_status');
                $default_publication_arr = array_keys($publication_options);
                if($website_db->publication_approve =='yes')
                {
                        if($session->user_profile == '1')
                        {
                                $publication_status->setValue($default_publication_arr[0]);
                        }
                        else
                        {
                                $publication_status->setValue($default_publication_arr[1]);
                        }                        
                }
                else
                {
                        $publication_status->setValue($default_publication_arr[0]);
                }                                                
                $publication_status->removeDecorator('Label');
                $publication_status->removeDecorator('HtmlTag');        

                //temp
                $section_temp = new Zend_Form_Element_Hidden('section_temp');
                $section_temp->removeDecorator('Label');
                $section_temp->removeDecorator('HtmlTag');
                
                //section images number                                
                $section_images = new Zend_Form_Element_Hidden('section_images');
                $section_images->setValue($website_db->section_images_number);        
                $section_images->removeDecorator('Label');
                $section_images->removeDecorator('HtmlTag');
                      
                $this->addElements(array(
                                $id,
                                $section_parent_id,                                
                                $subsection,
                                $template,
                                $area,
                                $area_id,
                                $internal_name,
                                $title,
                                $subtitle,
                                $title_browser,
                                $synopsis,
                                $keywords,
                                $link,
                                $external_link,
                                $target,                                
                                $type,
                                $publish_date,
                                $expire_date,
                                $show_publish_date,
                                $author,
                                $feature,
                                $highlight,
                                $comments,
                                $rss_available,
                                $publication_status,
                                $approved,
                                $section_temp,
                                $section_images,
                                $menu,
                                $parent_show_menu,
                                $menu2,
                                $parent_show_menu2,
                                $homepage,
                                $start_time,
                                $end_time
                ));
                                
                if($website_db->section_images_number !=0)
                {                                                
                        for ($i=1; $i<=$website_db->section_images_number ;$i++)
                        {                                                        
                                $image = new Zend_Form_Element_Button('img_'.$i);                                
                                $image->setLabel($lang->translate('Search'));
                                $image->setAttrib('class', 'btn btn-default');
                                $this->addElement($image);                                                        
                                
                                //image name
                                $name = new Zend_Form_Element_Text('name_img_'.$i);
                                $name->setLabel($lang->translate('Name').':');
                                $name->setAttrib('class', 'form-control');
                                $name->addFilters(array('StringTrim'));
                                $this->addElement($name);                

                                $img_file = new Zend_Form_Element_Hidden('file_img_'.$i);                                
                                $img_file->removeDecorator('Label');
                                $img_file->removeDecorator('HtmlTag');                        
                                $this->addElement($img_file);
                                
                                $img_id = new Zend_Form_Element_Hidden('id_img_'.$i);                                
                                $img_id->removeDecorator('Label');
                                $img_id->removeDecorator('HtmlTag');
                                $this->addElement($img_id);
                                
                                $image_preview = New Zend_Form_Element_Image('imageprw_'.$i);                                
                                $image_preview->removeDecorator('Label');
                                $image_preview->removeDecorator('HtmlTag');
                                $image_preview->setAttrib ('class', 'preview_img hide');                                
                                $image_preview->setAttrib('onclick', 'return false;');
                                $this->addElement($image_preview);
                         }
                 }
                
                //submit button
                $submit_button = new Zend_Form_Element_Button('submit_button');
                $submit_button->setLabel($lang->translate('Save'));
                $submit_button->setAttrib('class', 'btn btn-success');
                $this->addElement($submit_button);                
                
                //cencel button
                $cancel_button = new Zend_Form_Element_Button('cancel_button');
                $cancel_button->setLabel($lang->translate('Cancel'));
                $cancel_button->setAttrib('class', 'btn btn-default');
                $this->addElement($cancel_button);
        }
}