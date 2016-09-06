<?php
/**
 * Load object preview
 *
 * @category   wicaWeb
 * @package    Core_view_helpers
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license	   GNP
 * @author	   Santiago Arellano
 * @version    1.0
 */

class Zend_View_Helper_PreviewHelper extends Zend_View_Helper_Abstract {
	function previewHelper($content_id) {
		
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		$content = new Core_Model_Content();
		$content_temp = new Core_Model_ContentTemp();
		
		$data_content = $content_temp->find ( 'wc_content_temp', array ('content_id' => $content_id) );
		$is_temp = true;
		if(count($data_content)<1)
		{
			$data_content = $content->find ( 'wc_content', array ('id' => $content_id) );
			$is_temp = false;
		}
		
		//session
		$session_id = New Zend_Session_Namespace('id');
		
                
		if ( $data_content) 
		{
			$content_type = new Core_Model_ContentType ();
			$content_by_section = new Core_Model_ContentBySection();
			$content_field = new Core_Model_ContentField ();
			$content_by_section_temp = new Core_Model_ContentBySectionTemp();
			$content_field_temp = new Core_Model_ContentFieldTemp();
			$content_by_section_data = array();
			
			$data_content_type = $content_type->find ( 'wc_content_type', array ('id' => $data_content[0]->content_type_id) );
			
			if($is_temp)
			{
				$section_temp = new Core_Model_SectionTemp();
				
				$section_temp_obj = $section_temp->find('wc_section_temp', array('section_id'=>$session_id->section_id));
				$content_temp_obj = $content_temp->find('wc_content_temp', array('content_id'=>$content_id));
				if(count($content_temp_obj)>0)
				{
					if(count($section_temp_obj)>0)
						$content_by_section_data = $content_by_section->find('wc_content_by_section_temp', array('section_temp_id'=> $section_temp_obj[0]->id, 'content_temp_id'=>$content_temp_obj[0]->id));
					$data_content_field = $content_field_temp->find ( 'wc_content_field_temp', array ('content_temp_id' => $data_content[0]->id) );
				}
				else
				{
					$content_by_section_data = $content_by_section->find('wc_content_by_section', array('section_id'=> $session_id->section_id, 'content_id'=> $content_id));
					$data_content_field = $content_field->find ( 'wc_content_field', array ('content_id' => $data_content[0]->content_id) );
				}				
			}
			else
			{	
				$content_by_section_data = $content_by_section->find('wc_content_by_section', array('section_id'=> $session_id->section_id, 'content_id'=> $content_id));
				$data_content_field = $content_field->find ( 'wc_content_field', array ('content_id' => $content_id) );
			}
                        
                     
	
			//Proceed according to object type			
			switch (str_replace ( ' ', '_', strtolower ( $data_content_type [0]->name ) )) 
			{

                            case 'text' :
                                                            return '<div align="'.$content_by_section_data[0]->align.'">' . $data_content_field [0]->value  . '</div>';
                                                            break;

                            case 'flash' :
                                                            $return='<div class="front_image_container" align="'.$content_by_section_data[0]->align.'">
                                                                                    <object type="application/x-shockwave-flash" data="/uploads/content/' . $data_content_field [2]->value . '" width="320" height="240">
                                                                                            <param name="movie" value="/uploads/content/' . $data_content_field [2]->value . '" />
                                                                                            <param name="quality" value="high" />';

                                                            if($data_content_field [1]->value == 'transparent')
                                                                    $return.='<param name="wmode" value="transparent" />';
                                                            else
                                                                    $return.='<param name="wmode" value="opaque" />';

                                                                                            $return.='<img src="/uploads/content/' . $data_content_field [3]->value . '" width="320" alt="Imagen en reemplazo de flash" />
                                                                                    </object>
                                                                            </div>';

                                                            return $return;												
                                                            break;	

                            case 'carousel':
                                                            $return = '<div align="'.$content_by_section_data[0]->align.'" class="carruselInterna">
                                                                        <div class="cycle-slideshow" data-cycle-fx="fade" data-cycle-pause-on-hover="false" data-cycle-timeout="4000" data-cycle-speed="500">
                                                                            <div class="cycle-prev"></div>
                                                                            <div class="cycle-next"></div>';					

                                                            $array_images = explode ( ',', $data_content_field [0]->value );
                                                            if(count($array_images)>1)
                                                                    array_pop ( $array_images );
                                                            if($array_images){
                                                                    //recover render values
                                                                    $session_render_vals = new Zend_Session_Namespace('render_vals_front');
                                                                    $content_width = GlobalFunctions::getImageWithForRender(1000, 1, $content_by_section_data[0]->column_number);

                                                                    //getWebsiste id
                                                                    $id = new Zend_Session_Namespace('ids');
                                                                    //get watermark info
                                                                    $watermark_data =  GlobalFunctions::getWatermark($id->website_id);

                                                                    foreach ( $array_images as $ai ) {
                                                                            $return .= '<img src="'. imageRender::cache_image($ai, array('width' =>$content_width)) .'" style="'.GlobalFunctions::checkImageSize($ai,$content_width).'" class="img-responsive"/>';
                                                                    }
                                                            }

                                                            $return .= '</div></div>';

                                                            return $return;
                                                            break;

                            case 'image':		           
                                                           $return = '<div class="front_image_container item" align="'.$content_by_section_data[0]->align.'">';
                                                            //recover render values
                                                            $session_render_vals = new Zend_Session_Namespace('render_vals_front');
                                                            $content_width = GlobalFunctions::getImageWithForRender(1000, 1, $content_by_section_data[0]->column_number);
                                                            //getWebsiste id
                                                            $id = new Zend_Session_Namespace('ids');
                                                            //get watermark info
                                                            $watermark_data =  GlobalFunctions::getWatermark($id->website_id);

                                                            $path = APPLICATION_PATH.'/../public/uploads/content/';
                                                            //get the image size
                                                            $image_data = getimagesize($path.$data_content_field[4]->value);
                                                            $widthImg = $image_data[0].'px';
                                                            $heightImg = $image_data[1];

                                                            if($data_content_field [3]->value){

                                                                $hrefImg = ($data_content_field[8]->value =='yes')?imageRender::cache_image($data_content_field [4]->value, array('width' =>$content_width,'watermark' =>$watermark_data['file'], 'watermark_pos'=>$data_content_field[9]->value)):imageRender::cache_image($data_content_field [4]->value, array('width' =>$content_width,'watermark' =>0, 'watermark_pos'=>0));
                                                                $return .= $data_content_field [3]->value? '<a target="' . $data_content_field [2]->value . '" href="' .  $data_content_field [3]->value  . '"><img' : '<a class="wicabox" title="'.$data_content_field [0]->value.'" rel="wicabox'.$section_id.'" id="content'.$data_content [0]->id.'" href="'. $hrefImg .'" ><img';					
                                                            }elseif($widthImg > $content_width){

                                                                $hrefImg = ($data_content_field[8]->value =='yes')?imageRender::cache_image($data_content_field [4]->value, array('width' =>$widthImg,'watermark' =>$watermark_data['file'], 'watermark_pos'=>$data_content_field[9]->value)):'/uploads/content/'.$data_content_field[4]->value;
                                                                $return .= '<a class="wicabox" rel="imagesGroup" href="'.$hrefImg.'"><img';					

                                                            }else{
                                                                 $return .= '<img';					
                                                            }

                                                            if( $data_content_field[7]->value == 'no'){
                                                                if($data_content_field[8]->value =='yes'){
                                                                    $return .= ' src="'. imageRender::cache_image($data_content_field [4]->value, array('width' =>$widthImg,'watermark' =>$watermark_data['file'], 'watermark_pos'=>$data_content_field[9]->value)) .'" ';
                                                                }else{
                                                                $return .= ' src="/uploads/content/'.$data_content_field[4]->value.'" ';
                                                                }
                                                            }else{
                                                                if($data_content_field[8]->value =='yes'){
                                                                    $return .= ' src="'. imageRender::cache_image($data_content_field [4]->value, array('width' =>$content_width,'watermark' =>$watermark_data['file'], 'watermark_pos'=>$data_content_field[9]->value)) .'" ';
                                }                               else{
                                                                    $return .= ' src="'. imageRender::cache_image($data_content_field [4]->value, array('width' =>$content_width,'watermark' =>0, 'watermark_pos'=>0)) .'" ';
                                                                }                               
                                                            }




                                                            $return.= ' class="img-responsive';
                                                            if($data_content_field [5]->value == 'frame'){
                                                                    $return.= ' image-frame';
                                                            }
                                                            $return.= '"';
                                                            $return .='/>';
                                                            if($data_content_field [3]->value || $widthImg > $content_width){
                                                                $return .= '</a>';
                                                            }
                                                            if($data_content_field [0]->value != '')
                                                                    $return .= '<p>'.str_replace("\\", "", $data_content_field [0]->value).'</p>';

                                                            $return .= '</div>';
                                                            return  $return;
                                                            break;

                            case 'flash_video':
                                                            if($data_content_field [0]->value){

                                                                    $url = $data_content_field [0]->value;
                                                                    parse_str( parse_url( $url, PHP_URL_QUERY ), $my_array_of_vars );


                                                                    if(!$my_array_of_vars){
                                                                            $my_array_of_vars = explode('/',$data_content_field [0]->value);
                                                                            $return = '<div  align="'.$content_by_section_data[0]->align.'"><div class="video-container"><embed src="http://www.youtube.com/v/'.$my_array_of_vars[3].'&rel=1" pluginspage="http://adobe.com/go/getflashplayer" type="application/x-shockwave-flash" quality="high" width="450" height="376" bgcolor="#ffffff" loop="false"></embed></div></div>';
                                                                    }else{
                                                                            $return = '<div  align="'.$content_by_section_data[0]->align.'"><div class="video-container"><embed src="http://www.youtube.com/v/'.$my_array_of_vars['v'].'&rel=1" pluginspage="http://adobe.com/go/getflashplayer" type="application/x-shockwave-flash" quality="high" width="450" height="376" bgcolor="#ffffff" loop="false"></embed></div></div>';
                                                                    }

                                                                    return $return;
                                                            }else
                                                                    return '<div align="'.$content_by_section_data[0]->align.'" class="map-container">' . $data_content_field [1]->value . '</div>';
                                                            break;

                            case 'link' :					
                                                            switch ($data_content_field [0]->value) {
                                                                    case 'internal_link' :
                                                                            return '<div align="'.$content_by_section_data[0]->align.'"><a href="/default/index/index?id=' . $data_content_field [1]->value . '" >'.$data_content[0]->title.'</a></div>';
                                                                            break;
                                                                    case 'external_link' :
                                                                            return '<div align="'.$content_by_section_data[0]->align.'"><a href="http://' . str_replace ('http://', '', $data_content_field [2]->value) . '" >'.$data_content[0]->title.'</a></div>';
                                                                            break;
                                                                    case 'e_mail' :
                                                                            return '<div align="'.$content_by_section_data[0]->align.'"><a href="http://' . str_replace ('http://', '', $data_content_field [3]->value) . '" >'.$data_content[0]->title.'</a></div>';
                                                                            break;
                                                                    case 'file' :
                                                                            return '<div align="'.$content_by_section_data[0]->align.'" id="este"><a href="/uploads/content/' . $data_content_field [4]->value . '" target="_blank" ><img src="/images/' . $data_content_field [5]->value . '.png" /> '.$data_content_field [6]->value.' ('.GlobalFunctions::fileWeight($data_content_field [4]->value) .')</a></div>';
                                                                            break;
                                                            }
                                                            break;

                            case 'form' :
                                                            $form_field = new Core_Model_FormField();
                                                            $forms_fields = $form_field->find('wc_form_field', array( 'content_id' => $data_content [0]->id), array ( 'weight' => 'ASC'));

                                                            $return='<div class="prv_form" align="'.$content_by_section_data[0]->align.'">
                                                                            <form id="content_form_'.$data_content [0]->id.'" content_id="'.$data_content [0]->id.'" name="content_form_'.$data_content [0]->id.'">';

                                                            foreach($forms_fields as $ff)
                                                            {
                                                                    switch($ff->type)
                                                                    {
                                                                            case 'textfield':
                                                                                                            $return .=	'<div class="form-group">
                                                                                                                                <label for="form_field_textfield_'.$ff->id.'">'.$ff->name.'</label>
                                                                                                                                <input type="text" id="form_field_textfield_'.$ff->id.'" class="form-control" name="'.$ff->name.'" valid="'.$ff->required.'"/>
                                                                                                                             </div>';
                                                                                                            break;

                                                                            case 'emailfield':
                                                                                                            $return .=	'<div class="form-group">
                                                                                                                                <label for="form_field_textfield_'.$ff->id.'">'.$ff->name.'</label>
                                                                                                                                <input type="text" id="form_field_textfield_'.$ff->id.'" class="form-control email" name="'.$ff->name.'" valid="'.$ff->required.'"/>
                                                                                                                             </div>';
                                                                                                            break;

                                                                            case 'datepicker':
                                                                                                            $return .=	'<div class="form-group has-feedback">
                                                                                                                                <label for="form_field_textfield_'.$ff->id.'">'.$ff->name.'</label>
                                                                                                                                <input type="text" id="form_field_textfield_'.$ff->id.'" class="form-control date-calendar wicaDatepicker" readonly="readonly" name="'.$ff->name.'" valid="'.$ff->required.'"/>
                                                                                                                                <span class="glyphicon glyphicon-calendar form-control-feedback ui-datepicker-trigger" aria-hidden="true"></span>
                                                                                                                             </div>';
                                                                                                            break;

                                                                            case 'textarea':	
                                                                                                            $return .=	'<div class="form-group">
                                                                                                                                <label for="form_field_textarea_'.$ff->id.'">'.$ff->name.'</label>
                                                                                                                                <textarea id="form_field_textarea_'.$ff->id.'" class="form-control" name="'.$ff->name.'" valid="'.$ff->required.'" ></textarea>
                                                                                                                            </div>';					
                                                                                                            break;

                                                                            case 'radiobutton':
                                                                                                            $return .=	'<div class="form-group">

                                                                                                                                                    <label>'.$ff->name.'</label>
                                                                                                                                            ';

                                                                                                            $array_options = explode ( ',', $ff->options );

                                                                                                            if(count($array_options)>1)
                                                                                                                    array_pop ( $array_options );

                                                                                                                $return.= '<div>';
                                                                                                            if($array_options)
                                                                                                            {
                                                                                                                    foreach ( $array_options as $ao ) 
                                                                                                                    {
                                                                                                                            $return .= '<label class="radio-inline"><input id="'.$ao.'" class="radio-inline" type="radio" value="'.$ao.'" name="'.strtolower ( $data_content_type [0]->name ).$content_id.'">'.$ao.'</label>';
                                                                                                                    }
                                                                                                            }
                                                                                                                $return.= '</div>';
                                                                                                            $return.= '</div>';
                                                                                                            break;

                                                                            case 'dropdown':
                                                                                                            $return .=	'<div class="form-group">

                                                                                                                                                    <label for="form_field_dropdown_'.$ff->id.'">'.$ff->name.'</label>


                                                                                                                                                    <select class="form-control" id="form_field_dropdown_'.$ff->id.'" name="'.$ff->name.'" valid="'.$ff->required.'">';

                                                                                                                    $array_options = explode ( ',', $ff->options );
                                                                                                                    if(count($array_options)>1)
                                                                                                                            array_pop ( $array_options );
                                                                                                                    if($array_options){
                                                                                                                            foreach ( $array_options as $ao ) {
                                                                                                                                    $return .= '<option id="'.$ao.'" type="radio" value="'.$ao.'" name="'.$ao.'">'.$ao.' </option>';
                                                                                                                            }
                                                                                                                    }

                                                                                                            $return.= '</select>

                                                                                                                            </div>';											
                                                                                                            break;	



                                                                            case 'checkbox':
                                                                                                            $return .=	'<div class="checkbox">
                                                                                                                                <label>
                                                                                                                                    <input type="checkbox" id="form_field_checkbox_'.$ff->id.'" name="'.$ff->name.'" valid="'.$ff->required.'" />
                                                                                                                                    '.$ff->name.'
                                                                                                                                </label>
                                                                                                                            </div>';						
                                                                                                            break;	

                                                                            case 'comment':	
                                                                                                            $return .=	'<div class="form-group">
                                                                                                                                                    <label class="comment">'.$ff->description.'</label>
                                                                                                                                    </div>';
                                                                                                            break;	

                                                                            case 'file':	
                                                                                                            $return .= '<div class="form-group">

                                                                                                                                                    <label for="form_field_file_'.$ff->id.'">'.$ff->name.'</label>
                                                                                                                                                    <input id="fileLabel_'.$ff->id.'" type="text" value="" disabled="disabled">
                                                                                                                                                    <button id="form_field_file_'.$ff->id.'" class="btn btn-warning" type="button" name="form_field_file_'.$ff->id.'">Examinar</button>

                                                                                                                                                    <input type="hidden" id="hdnNameFile_'.$ff->id.'" name="hdnNameFile_'.$ff->id.'" />

                                                                                                                                    </div>';
                                                                                                            break;
                                                                    }
                                                            }

                                                            if($data_content_field[2]->value=='yes')
                                                            {
                                                                    $return .= '<div id="recaptcha" class="form-group text-center">
                                                                                    <label id="captcha_error_'.$data_content [0]->id.'" class="error_validation hidden" for="form_field_captcha_'.$data_content [0]->id.'">'.$lang->translate('Please check the reCAPTCHA verification ').'</label>
                                                                                    <div class="g-recaptcha text-center center-block" style="width: 304px; height: 78px;margin: 0 auto" data-sitekey="6LfGUiUTAAAAAPZNmgcZjc27DHEQwr9Pzu47I-qu"></div>
                                                                                </div>';
                                                            }

                                                            $front_ids = New Zend_Session_Namespace('ids');

                                                            $return .= '<div class="form-group center">								
                                                                                            <input type="button" id="btn_sub_form_'.$data_content [0]->id.'" name="btn_sub_form_'.$data_content [0]->id.'" class="btn btn-primary" value="'.$lang->translate('Send').'"/>
                                                                                            <input type="hidden" id="website_id" name="website_id" value="'.$front_ids->website_id.'" />
                                                                                            <input type="hidden" id="form_id" name="form_id" value="'.$data_content [0]->id.'" />		
                                                                                    </div>';

                                                            $return .= '</form></div>';
                                                            return $return;
                                                            break;
                    }
	
		}
	}
}