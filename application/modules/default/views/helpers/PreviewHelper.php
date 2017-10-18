<?php
/**
 * Load object preview
 *
 * @category   wicaWeb
 * @package    Core_view_helpers
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license	   GNP
 * @version    1.1
 * @author      Jose Luis Santiago Arellano
 */

class Zend_View_Helper_PreviewHelper extends Zend_View_Helper_Abstract {
	
	function previewHelper($content, $area_id=null) 
	{
		$lang = Zend_Registry::get ( 'Zend_Translate' );
		if ( $content) 
		{
				
				
				
                    //Proceed according object type
                    switch (str_replace ( ' ', '_', strtolower ($content['data_content_type']->name ) )) 
                    {

                            case 'text' :
                                                            return '<div align="'.$content['content_by_section_data']->align.'">' .$content['data_content_field'][0]->value  . '</div>';
                                                            break;

                            case 'flash' :
                                                            $return='<div class="front_image_container" align="'.$content['content_by_section_data']->align.'">
                                                                                    <object type="application/x-shockwave-flash" data="/uploads/content/' . $content['data_content_field'][2]->value . '" width="320" height="240">
                                                                                            <param name="movie" value="/uploads/content/' . $content['data_content_field'][2]->value . '" />
                                                                                            <param name="quality" value="high" />';

                                                            if($content['data_content_field'][1]->value == 'transparent')
                                                                    $return.='<param name="wmode" value="transparent" />';
                                                            else
                                                                    $return.='<param name="wmode" value="opaque" />';

                                                                                            $return.='<img src="/uploads/content/' . $content['data_content_field'][3]->value . '" width="320" alt="Imagen en reemplazo de flash" />
                                                                                    </object>
                                                                            </div>';

                                                            return $return;												
                                                            break;	

                            case 'carousel':
                                                            $return = '<div align="'.$content['content_by_section_data']->align.'" class="carruselInterna">
                                                                        <div class="cycle-slideshow" data-cycle-fx="fade" data-cycle-pause-on-hover="false" data-cycle-timeout="4000" data-cycle-speed="500">
                                                                            <div class="cycle-prev"></div>
                                                                            <div class="cycle-next"></div>';					

                                                            $array_images = explode ( ',', $content['data_content_field'][0]->value );
                                                            if(count($array_images)>1)
                                                                    array_pop ( $array_images );
                                                            if($array_images){
                                                                    //recover render values
                                                                    $session_render_vals = new Zend_Session_Namespace('render_vals_front');
                                                                    $content_width = GlobalFunctions::getImageWithForRender($session_render_vals->area_width, $session_render_vals->section_cols, $content['content_by_section_data']->column_number);

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
                                                           $return = '<div class="front_image_container item" align="'.$content['content_by_section_data']->align.'">';
                                                            //recover render values
                                                            $session_render_vals = new Zend_Session_Namespace('render_vals_front');
                                                            $content_width = GlobalFunctions::getImageWithForRender($session_render_vals->area_width, $session_render_vals->section_cols, $content['content_by_section_data']->column_number);
                                                            //getWebsiste id
                                                            $id = new Zend_Session_Namespace('ids');
                                                            //get watermark info
                                                            $watermark_data =  GlobalFunctions::getWatermark($id->website_id);

                                                            $path = APPLICATION_PATH.'/../public/uploads/content/';
                                                            //get the image size
                                                            $image_data = getimagesize($path.$content['data_content_field'][4]->value);
                                                            $widthImg = $image_data[0].'px';
                                                            $heightImg = $image_data[1];
                                                            //echo $content['data_content_field'][7]->value;
                                                            if($content['data_content_field'][7]->value =='yes' && $content['data_content_field'][8]->value =='no'){
                                                                // RESIZE
                                                                $img = imageRender::cache_image($content['data_content_field'][4]->value, array('width' =>$content_width,'watermark' =>0, 'watermark_pos'=>0));

                                                            }elseif($content['data_content_field'][8]->value =='yes' && $content['data_content_field'][7]->value =='no'){
                                                                // WATERMARK - NO RESIZE
                                                                $img = imageRender::cache_image($content['data_content_field'][4]->value, array('width' =>$widthImg,'watermark' =>$watermark_data['file'], 'watermark_pos'=>$content['data_content_field'][9]->value));

                                                            }elseif($content['data_content_field'][8]->value =='yes' && $content['data_content_field'][7]->value =='yes'){
                                                                // WATERMARK AND RESIZE
                                                                $img = imageRender::cache_image($content['data_content_field'][4]->value, array('width' =>$content_width,'watermark' =>$watermark_data['file'], 'watermark_pos'=>$content['data_content_field'][9]->value));
                                                            }else{
                                                                // NO RESIZE - NO WATERMARK
                                                                $img = '/uploads/content/'.$content['data_content_field'][4]->value;
                                                            }

                                                            if($content['data_content_field'][3]->value){

                                                                $return .= $content['data_content_field'][3]->value? '<a target="' . $content['data_content_field'][2]->value . '" href="' .  $content['data_content_field'][3]->value  . '"><img' : '<a class="wicabox" title="'.$content['data_content_field'][0]->value.'" rel="wicabox'.$section_id.'" id="content'.$content->id.'" href="'. $hrefImg .'" ><img';					
                                                            }elseif($content['data_content_field'][10]->value =='yes'){

                                                                $hrefImg = ($content['data_content_field'][8]->value =='yes')?imageRender::cache_image($content['data_content_field'][4]->value, array('watermark' =>$watermark_data['file'], 'watermark_pos'=>$content['data_content_field'][9]->value)):'/uploads/content/'.$content['data_content_field'][4]->value;
                                                                $return .= '<a class="wicabox" rel="imagesGroup" href="'.$hrefImg.'"><img';					

                                                            }else{
                                                                 $return .= '<img';					
                                                            }

                                                            $return .= ' src="'.$img.'" ';




                                                            $return.= ' class="img-responsive';
                                                            if($content['data_content_field'][5]->value == 'frame'){
                                                                    $return.= ' image-frame';
                                                            }
                                                            $return.= '"';
                                                            $return .='/>';
                                                            if($content['data_content_field'][3]->value || $content['data_content_field'][10]->value == 'yes'){
                                                                $return .= '</a>';
                                                            }
                                                            if($content['data_content_field'][0]->value != '')
                                                                    $return .= '<p>'.str_replace("\\", "", $content['data_content_field'][0]->value).'</p>';

                                                            $return .= '</div>';
                                                            return  $return;
                                                            break;

                            case 'flash_video':
                                                            if($content['data_content_field'][0]->value){

                                                                    $url = $content['data_content_field'][0]->value;
                                                                    parse_str( parse_url( $url, PHP_URL_QUERY ), $my_array_of_vars );


                                                                    if(!$my_array_of_vars){
                                                                            $my_array_of_vars = explode('/',$content['data_content_field'][0]->value);
                                                                            $return = '<div  align="'.$content['content_by_section_data']->align.'"><div class="video-container"><embed src="http://www.youtube.com/v/'.$my_array_of_vars[3].'&rel=1" pluginspage="http://adobe.com/go/getflashplayer" type="application/x-shockwave-flash" quality="high" width="450" height="376" bgcolor="#ffffff" loop="false"></embed></div></div>';
                                                                    }else{
                                                                            $return = '<div  align="'.$content['content_by_section_data']->align.'"><div class="video-container"><embed src="http://www.youtube.com/v/'.$my_array_of_vars['v'].'&rel=1" pluginspage="http://adobe.com/go/getflashplayer" type="application/x-shockwave-flash" quality="high" width="450" height="376" bgcolor="#ffffff" loop="false"></embed></div></div>';
                                                                    }

                                                                    return $return;
                                                            }else
                                                                    return '<div align="'.$content['content_by_section_data']->align.'" class="map-container">' . $content['data_content_field'][1]->value . '</div>';
                                                            break;

                            case 'link' :					
                                                            switch ($content['data_content_field'][0]->value) {
                                                                    case 'internal_link' :
                                                                            return '<div align="'.$content['content_by_section_data']->align.'"><a href="/section/' . $content['data_content_field'][1]->value . '/'.strtolower(GlobalFunctions::formatFilename($content['data_content_field'][6]->value)).'" >'.$content['data_content_field'][6]->value.'</a></div>';
                                                                            break;
                                                                    case 'external_link' :
                                                                            return '<div align="'.$content['content_by_section_data']->align.'"><a href="http://' . str_replace ('http://', '', $content['data_content_field'][2]->value) . '" >'.$content['title'].'</a></div>';
                                                                            break;
                                                                    case 'e_mail' :
                                                                            return '<div align="'.$content['content_by_section_data']->align.'"><a href="http://' . str_replace ('http://', '', $content['data_content_field'][3]->value) . '" >'.$content['title'].'</a></div>';
                                                                            break;
                                                                    case 'file' :
                                                                            return '<div align="'.$content['content_by_section_data']->align.'" id="este"><a href="/uploads/content/' . $content['data_content_field'][4]->value . '" target="_blank" ><img src="/images/' . $content['data_content_field'][5]->value . '.png" /> '.$content['data_content_field'][6]->value.' ('.GlobalFunctions::fileWeight($content['data_content_field'][4]->value) .')</a></div>';
                                                                            break;
                                                            }
                                                            break;

                            case 'form' :
                                                            $form_field = new Core_Model_FormField();
                                                            $forms_fields = $form_field->find('wc_form_field', array( 'content_id' => $content['content_id']), array ( 'weight' => 'ASC'));

                                                            $return='<div class="prv_form" align="'.$content['content_by_section_data']->align.'">
                                                                            <form id="content_form_'.$content['content_id'].'" content_id="'.$content['content_id'].'" name="content_form_'.$content['content_id'].'">';

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
                                                                                                                            $return .= '<label class="radio-inline"><input id="'.$ao.'" class="radio-inline" type="radio" value="'.$ao.'" name="'.strtolower ( $content['data_content_type']->name ).$content_id.'">'.$ao.'</label>';
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

                                                            if($content['data_content_field'][2]->value=='yes')
                                                            {
                                                                    $return .= '<div id="recaptcha" class="form-group text-center">
                                                                                    <label id="mushoq_error_'.$content['content_id'].'" class="error_captcha hide" for="form_field_captcha_'.$content['content_id'].'">'.$lang->translate('Please check the reCAPTCHA verification ').'</label>
                                                                                    <div class="g-recaptcha text-center center-block" style="width: 304px; height: 78px;margin: 0 auto" data-sitekey="6LfGUiUTAAAAAPZNmgcZjc27DHEQwr9Pzu47I-qu"></div>
                                                                                </div>';
                                                            }

                                                            $front_ids = New Zend_Session_Namespace('ids');

                                                            $return .= '<div class="form-group center">								
                                                                                            <input type="button" id="btn_sub_form_'.$content['content_id'].'" name="btn_sub_form_'.$content['content_id'].'" class="btn btn-primary" value="'.$lang->translate('Send').'"/>
                                                                                            <input type="hidden" id="website_id" name="website_id" value="'.$front_ids->website_id.'" />
                                                                                            <input type="hidden" id="form_id" name="form_id" value="'.$content['content_id'].'" />		
                                                                                    </div>';

                                                            $return .= '</form></div>';
                                                            return $return;
                                                            break;
                    }	
				
		}else {
			$front_ids = New Zend_Session_Namespace('ids');
			$return = '<div class="row-fluid">
							
							<div id="section_login_container" class="col-md-4 col-md-offset-4">
								<form id="form_login_'.$area_id.'" name="form_login_'.$area_id.'" action="">
									
                                                                        <div class="form-group">
                                                                                <label class="required" for="public_user_'.$area_id.'">* '.$lang->translate("User").'</label>
                                                                                <input type="text" class="form-control" id="public_user_'.$area_id.'" name="public_user_'.$area_id.'" />
                                                                        </div>
									
									<div class="form-group">
										<label class="required" for="public_password_'.$area_id.'">* '.$lang->translate("Password").'</label>
                                                                                <input type="password" class="form-control" id="public_password_'.$area_id.'" name="public_password_'.$area_id.'" />
                                                                        </div>
									<div id="error_login_'.$area_id.'" name="error_login_'.$area_id.'" class="error_validation hide">'.$lang->translate("The username or password are wrong").'</div>
									<div id="error_login_inactive_'.$area_id.'" name="error_login_inactive_'.$area_id.'" class="error_validation hide">'.$lang->translate("This user is inactive").'</div>
									<input type="button" class="btn btn-primary" id="btnLogin_'.$area_id.'" area="'.$area_id.'" name="btnLogin_'.$area_id.'" value="'.$lang->translate("Login").'"/><br/>
									<a class="pointer" href="#form_register_'.$area_id.'" area="'.$area_id.'" id="register_'.$area_id.'">'.$lang->translate("Register").'</a><br/>
									<a class="pointer" href="#form_forgot_'.$area_id.'" area="'.$area_id.'" id="forgot_'.$area_id.'">'.$lang->translate("Forgot your password").'?</a>
								</form>	
							</div>
							
							<div class="hide">
								<form class="form-login margin-fancy " id="form_register_'.$area_id.'" name="form_register_'.$area_id.'" action="">

                                                                        <h3 class="text-center">'.$lang->translate("Register Form").'</h3>

												      							      		
							      			<div class="form-group col-md-6">
                                                                                    <label for="public_user_name_'.$area_id.'">* '.$lang->translate("Name").'</label>
                                                                                    <input type="text" class="form-control" id="public_user_name_'.$area_id.'" name="public_user_name_'.$area_id.'" />
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label for="public_user_last_name_'.$area_id.'">* '.$lang->translate("Lastname").'</label>
                                                                                    <input type="text" class="form-control" id="public_user_last_name_'.$area_id.'" name="public_user_last_name_'.$area_id.'" />
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label for="public_user_identification_'.$area_id.'">* '.$lang->translate("Identification").'</label>
                                                                                    <input type="text" class="form-control" id="public_user_identification_'.$area_id.'" name="public_user_identification_'.$area_id.'" />
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label for="public_user_email_'.$area_id.'">* '.$lang->translate("Email").'</label>
                                                                                    <input type="text" class="form-control email" id="public_user_email_'.$area_id.'" name="public_user_email_'.$area_id.'" />
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label for="public_user_phone_'.$area_id.'">'.$lang->translate("Phone").'</label>
                                                                                    <input type="text" class="form-control" id="public_user_phone_'.$area_id.'" name="public_user_phone_'.$area_id.'" />
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label for="public_user_username_'.$area_id.'">* '.$lang->translate("Username").'</label>
                                                                                    <input type="text" class="form-control" id="public_user_username_'.$area_id.'" name="public_user_username_'.$area_id.'" />
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label for="public_user_password_'.$area_id.'">* '.$lang->translate("Password").'</label>
                                                                                    <input type="password" class="form-control" id="public_user_password_'.$area_id.'" name="public_user_password_'.$area_id.'" />
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label for="public_user_cpassword_'.$area_id.'">* '.$lang->translate("Confirm Password").'</label>
                                                                                    <input type="password" class="form-control" id="public_user_cpassword_'.$area_id.'" name="public_user_cpassword_'.$area_id.'" />
                                                                                </div>
                                                                                
									<div class="text-center col-md-12">
										<input class="btn btn-primary" type="button" id="btn_register_user_'.$area_id.'" name="btn_register_user_'.$area_id.'" value="'.$lang->translate("Send").'" />
										<input type="hidden" id="website_id" name="website_id" value="'.$front_ids->website_id.'" />
										<input type="hidden" id="area" name="area" value="'.$area_id.'" />
										<input type="hidden" id="error_email_'.$area_id.'" name="error_email_'.$area_id.'" value="'. $lang->translate('The email address already has been registered').'" />
										<input type="hidden" id="error_username_'.$area_id.'" name="error_username_'.$area_id.'" value="'. $lang->translate('The username already has been registered').'" />
									</div>																																				
								</form>
							</div>
							
							<div class="hide">
								<form class="col-md-12 form-login" id="form_forgot_'.$area_id.'" name="form_forgot_'.$area_id.'" action="">
									<div class="text-center">
										<h3>'.$lang->translate("Password Reset").'</h3>
									</div><br/>
									
							      		
                                                                        <div class="form-group">
                                                                                <label for="public_for_user_email_'.$area_id.'">'.$lang->translate("Email").'</label>
                                                                                <input type="text" class="form-control" id="public_for_user_email_'.$area_id.'" name="public_for_user_email_'.$area_id.'" />
                                                                        </div>
							      			
									<div class="form-group text-center">
										<input class="btn btn-primary" type="button" id="btn_send_password_user_'.$area_id.'" name="btn_send_password_user_'.$area_id.'" value="'.$lang->translate("Send").'" />
									</div>										
								</form>
							</div>								
						</div>';
			
			return $return;			
		}
	}
}
