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

class Zend_View_Helper_PreviewHelperStorage extends Zend_View_Helper_Abstract {
	
	function previewHelperStorage($section_id, $content_id, $area_id=null) 
	{
		//translate library
		$lang = Zend_Registry::get ( 'Zend_Translate' );
		
		$content = new Core_Model_Content ();
		
		$data_content = $content->find ( 'wc_content_storage', array ('id' => $content_id) );
		
		if ( $data_content && $section_id) 
		{
				
				$content_type = new Core_Model_ContentType ();
				$data_content_type = $content_type->find ( 'wc_content_type', array ('id' => $data_content [0]->content_type_id) );
					
				$content_field = new Core_Model_ContentFieldStorage();
				$data_content_field = $content_field->find ( 'wc_content_field_storage', array ('content_id' => $data_content [0]->id) );
				
				$content_by_section = new Core_Model_ContentBySectionStorage();
				$content_by_section_data = $content_by_section->find('wc_content_by_section_storage', array('section_id'=> $section_id, 'content_id'=> $content_id));
				
				//Proceed according object type
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
									$return = '<div align="'.$content_by_section_data[0]->align.'">
										    <div id="myCarousel_'.$content_id.'" class="carousel slide">										    
											    <div class="carousel-inner">';					
						
									$array_images = explode ( ',', $data_content_field [0]->value );
									if(count($array_images)>1)
										array_pop ( $array_images );
									if($array_images){
										//recover render values
										$session_render_vals = new Zend_Session_Namespace('render_vals_front');
										$content_width = GlobalFunctions::getImageWithForRender($session_render_vals->area_width, $session_render_vals->section_cols, $content_by_section_data[0]->column_number);
										
										//getWebsiste id
										$id = new Zend_Session_Namespace('ids');
										//get watermark info
										$watermark_data =  GlobalFunctions::getWatermark($id->website_id);
										foreach ( $array_images as $ai ) {
											$return .= '<div class="item"><img src="'. imageRender::cache_image($ai, array('width' =>$content_width,'watermark' =>$watermark_data['file'], 'watermark_pos'=>$watermark_data['pos'])) .'" style="'.GlobalFunctions::checkImageSize($ai,$content_width).'"/></div>';
										
										}
									}
						
									$return .= '	</div>
												    <!-- Carousel nav -->
												    <a id="carousel_left_'.$content_id.'" class="carousel-control left hide" href="#myCarousel_'.$content_id.'" data-slide="prev">&lsaquo;</a>
												    <a id="carousel_right_'.$content_id.'" class="carousel-control right hide" href="#myCarousel_'.$content_id.'" data-slide="next">&rsaquo;</a>
											   		 </div>
												</div>';
									
									return $return;
									break;
													
					case 'image':					
									$return = '<div class="front_image_container" align="'.$content_by_section_data[0]->align.'">';
																
									$return .= $data_content_field [3]->value? '<a target="' . $data_content_field [2]->value . '" href="http://' . str_replace ('http://', '', $data_content_field [3]->value) . '"><img' : '<img';
														
									//recover render values
									$session_render_vals = new Zend_Session_Namespace('render_vals_front');
									$content_width = GlobalFunctions::getImageWithForRender($session_render_vals->area_width, $session_render_vals->section_cols, $content_by_section_data[0]->column_number);
										
									//getWebsiste id
									$id = new Zend_Session_Namespace('ids');
									//get watermark info
									$watermark_data =  GlobalFunctions::getWatermark($id->website_id);
																
									$return .= ' src="'. imageRender::cache_image($data_content_field [4]->value, array('width' =>$content_width,'watermark' =>$watermark_data['file'], 'watermark_pos'=>$watermark_data['pos'])) .'" ';
									$return .= ' style="'.GlobalFunctions::checkImageSize($data_content_field [4]->value,$content_width);
									
									if($data_content_field [5]->value == 'frame'){
										$return.= ' border: thin solid; color: black !important;';
									}
									
									$return .= '"';
									$return .='/>';
									
									$return .= $data_content_field [3]->value? '</a>' : '';
									
									if($data_content_field [4]->value != '')
										$return .= '<p>'.$data_content_field [0]->value.'</p>';
									
									$return .= '</div>';
									return  $return;
									break;
					
					case 'flash_video':
									if($data_content_field [0]->value){
										
										$url = $data_content_field [0]->value;
										parse_str( parse_url( $url, PHP_URL_QUERY ), $my_array_of_vars );
										
										
										if(!$my_array_of_vars){
											$my_array_of_vars = explode('/',$data_content_field [0]->value);
											$return = '<div  align="'.$content_by_section_data[0]->align.'"><embed src="http://www.youtube.com/v/'.$my_array_of_vars[3].'&rel=1" pluginspage="http://adobe.com/go/getflashplayer" type="application/x-shockwave-flash" quality="high" width="450" height="376" bgcolor="#ffffff" loop="false"></embed></div>';
										}else{
											$return = '<div  align="'.$content_by_section_data[0]->align.'"><embed src="http://www.youtube.com/v/'.$my_array_of_vars['v'].'&rel=1" pluginspage="http://adobe.com/go/getflashplayer" type="application/x-shockwave-flash" quality="high" width="450" height="376" bgcolor="#ffffff" loop="false"></embed></div>';
										}
										
										return $return;
									}
									else
										return '<div align="'.$content_by_section_data[0]->align.'">' . $data_content_field [1]->value . '</div>';
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
											return '<div align="'.$content_by_section_data[0]->align.'" id="este"><a href="/uploads/content/' . $data_content_field [4]->value . '" ><img src="/images/' . $data_content_field [5]->value . '.png" />'.$data_content[0]->title.'</a></div>';
											break;
									}
									break;
					
					case 'form' :
									$form_field = new Core_Model_FormFieldStorage();
									$forms_fields = $form_field->find('wc_form_field_storage', array( 'content_id' => $data_content [0]->id), array ( 'weight' => 'ASC'));
									
									$return='<div class="prv_form" align="'.$content_by_section_data[0]->align.'">
											<form id="content_form_'.$data_content [0]->id.'" content_id="'.$data_content [0]->id.'" name="content_form_'.$data_content [0]->id.'">';
						
									foreach($forms_fields as $ff)
									{
										switch($ff->type)
										{
											case 'textfield':
															$return .=	'<div class="line row-fluid">
																			<div class="col-md-3 form_label">
																				<label>'.$ff->name.'</label>
																			</div>
																			<div class="col-md-3 form_field">
																				<input type="text" id="form_field_textfield_'.$data_content [0]->id.'" name="'.$ff->name.'" valid="'.$ff->required.'"/>
																			</div>
																		</div>';
															break;
											case 'textarea':	
															$return .=	'<div class="line row-fluid">
																			<div class="col-md-3 form_label">
																				<label>'.$ff->name.'</label>
																			</div>
																			<div class="col-md-3 form_field">
																				<textarea id="form_field_textarea_'.$data_content [0]->id.'" name="'.$ff->name.'" valid="'.$ff->required.'" ></textarea>
																			</div>
																		</div>';					
															break;
															
											case 'radiobutton':
															$return .=	'<div class="line row-fluid">
																			<div class="col-md-3 form_label">
																				<label>'.$ff->name.'</label>
																			</div>';
															
															$array_options = explode ( ',', $ff->options );
															
															if(count($array_options)>1)
																array_pop ( $array_options );
															
															if($array_options)
															{
																foreach ( $array_options as $ao ) 
																{
																	$return .= '	<input id="'.$ao.'" type="radio" value="'.$ao.'" name="'.strtolower ( $data_content_type [0]->name ).$content_id.'">'.$ao;
																}
															}					
															$return.= '</div>
																		</div>';
															break;
												
											case 'dropdown':
															$return .=	'<div class="line row-fluid">
																			<div class="col-md-3 form_label">
																				<label>'.$ff->name.'</label>
																			</div>
																			<div class="col-md-3 form_field">
																				<select id="form_field_dropdown_'.$data_content [0]->id.'" name="'.$ff->name.'" valid="'.$ff->required.'">';
															
																$array_options = explode ( ',', $ff->options );
																if(count($array_options)>1)
																	array_pop ( $array_options );
																if($array_options){
																	foreach ( $array_options as $ao ) {
																		$return .= '<option id="'.$ao.'" type="radio" value="'.$ao.'" name="'.$ao.'">'.$ao.' </option>';
																	}
																}
															
															$return.= '</select>
																			</div>
																	</div>';											
															break;	
				
											case 'checkbox':
															$return .=	'<div class="line row-fluid">
																			<div class="col-md-3 form_label">
																				<label>'.$ff->name.'</label>
																			</div>
																			<div class="col-md-3 form_field">
																				<input type="checkbox" id="form_field_checkbox_'.$data_content [0]->id.'" name="'.$ff->name.'" valid="'.$ff->required.'" />
																			</div>
																		</div>';						
															break;	
				
											case 'comment':	
															$return .=	'<div class="line row-fluid">
																			<div class="col-md-3 form_label">
																				&ensp;
																			</div>
																			<div class="col-md-5 form_field">
																				<label class="comment">'.$ff->description.'</label>
																			</div>
																		</div>';
															break;	
														
											case 'file':	
															$return .=	'<div class="line row-fluid">
																			<div class="col-md-3 form_label">
																				<label>'.$ff->name.'</label>
																			</div>
																			<div class="col-md-3 form_field">
																				<input type="file" id="form_field_file_'.$data_content [0]->id.'" name="'.$ff->name.'" valid="'.$ff->required.'" />
																			</div>
																		</div>';
															break;								
										}
									}
									
									if($data_content_field[2]->value=='yes')
									{
										//create captcha object on form content
										$captcha = new Zend_Captcha_Image();
										$captcha->setName('foo');
										$captcha->setImgDir(APPLICATION_PATH . '/../public/images/captcha/');
										$captcha->setImgUrl($this->view->baseUrl('/images/captcha/'));
										$captcha->setFont(APPLICATION_PATH . '/../public/fonts/VeraIt.ttf');
										$captcha->setWordlen(5);
										$captcha->setFontSize(28);
										$captcha->setLineNoiseLevel(15);
										$captcha->setWidth(200);
										$captcha->setHeight(64);
										$id_captcha = $captcha->generate();
										//$this->view->captcha = $captcha;
									
										$captchaSession = new Zend_Session_Namespace('Zend_Form_Captcha_'.$id_captcha);
										//get captcha iterator
										$captchaIterator = $captchaSession->getIterator();
									
										//validate if session code is set
										
										$_SESSION['captcha_session_'.$data_content [0]->id] =  $captchaIterator['word'];
										
										$return .= '<div class="line row-fluid">
														<div class="col-md-3 form_label">
															<label>'.$lang->translate('Insert the security code').'</label>
														</div>
														<div class="col-md-3 form_field">
															'.$captcha->render().'
															<input type ="text" id ="form_field_captcha_'.$data_content [0]->id.'" name = "form_field_captcha_'.$data_content [0]->id.'" valid="yes" class="line-top"/>
																<label id="captcha_error_'.$data_content [0]->id.'" class="error_validation hide" for="form_field_captcha_'.$data_content [0]->id.'">'.$lang->translate('Invalid code').'</label>
														</div>
													</div>';
									}
	
									$front_ids = New Zend_Session_Namespace('ids');
	
									$return .= '<div class="row-fluid line center">				
													<input type="hidden" id="website_id" name="website_id" value="'.$front_ids->website_id.'" />
													<input type="hidden" id="form_id" name="form_id" value="'.$data_content [0]->id.'" />		
												</div>';
															
									$return .= '</form></div>';
									return $return;
									break;
				}	
				
		}else {
			$front_ids = New Zend_Session_Namespace('ids');
			$return = '<div class="row-fluid">
							
							<div id="section_login_container" class="row_fluid">
								<form id="form_login_'.$area_id.'" name="form_login_'.$area_id.'" action="">
									<div class="row-fluid">
										<div class="row-fluid">
											<label class="required">* '.$lang->translate("User").'</label>
										</div>
										<div class="row-fluid">
											<input type="text" id="public_user_'.$area_id.'" name="public_user_'.$area_id.'" />
										</div>
									</div>
									<div class="row-fluid">
										<div class="row-fluid">
											<label class="required">* '.$lang->translate("Password").'</label>
										</div>
										<div class="row-fluid">
											<input type="password" id="public_password_'.$area_id.'" name="public_password_'.$area_id.'" />
										</div>
									</div>
									<label  id="error_login_'.$area_id.'" name="error_login_'.$area_id.'" class="error_validation hide">'.$lang->translate("The username or password are wrong").'</label>
									<label  id="error_login_inactive_'.$area_id.'" name="error_login_inactive_'.$area_id.'" class="error_validation hide">'.$lang->translate("This user is inactive").'</label>
									<input type="button" id="btnLogin_'.$area_id.'" area="'.$area_id.'" name="btnLogin_'.$area_id.'" value="'.$lang->translate("Login").'"/><br/>
									<a class="pointer" href="#form_register_'.$area_id.'" area="'.$area_id.'" id="register_'.$area_id.'">'.$lang->translate("Register").'</a><br/>
									<a class="pointer" href="#form_forgot_'.$area_id.'" area="'.$area_id.'" id="forgot_'.$area_id.'">'.$lang->translate("Forgot your password").'?</a>
								</form>	
							</div>
							
							<div class="hide">
								<form class="col-md-8 form-horizontal margin-fancy" id="form_register_'.$area_id.'" name="form_register_'.$area_id.'" action="">
									<div class="row-fluid center">
										<h3>'.$lang->translate("Register Form").'</h3>
									</div><br/>
									<div class="row-fluid">
							      		<div class="col-md-6">				      							      		
							      			<div class="control-group">
												<label class="control-label">* '.$lang->translate("Name").'</label>
												<div class="controls">
													<input type="text" id="public_user_name_'.$area_id.'" name="public_user_name_'.$area_id.'" />
												</div>
											</div>
							      		</div>
							      		<div class="col-md-6">				      							      		
							      			<div class="control-group">
												<label class="control-label">* '.$lang->translate("Lastname").'</label>
												<div class="controls">
													<input type="text" id="public_user_last_name_'.$area_id.'" name="public_user_last_name_'.$area_id.'" />
												</div>
											</div>
							      		</div>								      												
									</div>
									<div class="row-fluid">
							      		<div class="col-md-6">				      							      		
							      			<div class="control-group">
												<label class="control-label">* '.$lang->translate("Identification").'</label>
												<div class="controls">
													<input type="text" id="public_user_identification_'.$area_id.'" name="public_user_identification_'.$area_id.'" />
												</div>
											</div>
							      		</div>
							      		<div class="col-md-6">				      							      		
							      			<div class="control-group">
												<label class="control-label">* '.$lang->translate("Email").'</label>
												<div class="controls">
													<input type="text" id="public_user_email_'.$area_id.'" name="public_user_email_'.$area_id.'" />
												</div>
											</div>
							      		</div>								      												
									</div>		
									<div class="row-fluid">
							      		<div class="col-md-6">				      							      		
							      			<div class="control-group">
												<label class="control-label">'.$lang->translate("Phone").'</label>
												<div class="controls">
													<input type="text" id="public_user_phone_'.$area_id.'" name="public_user_phone_'.$area_id.'" />
												</div>
											</div>
							      		</div>
							      		<div class="col-md-6">				      							      		
							      			<div class="control-group">
												<label class="control-label">* '.$lang->translate("Username").'</label>
												<div class="controls">
													<input type="text" id="public_user_username_'.$area_id.'" name="public_user_username_'.$area_id.'" />
												</div>
											</div>
							      		</div>								      												
									</div>	
									<div class="row-fluid">
							      		<div class="col-md-6">				      							      		
							      			<div class="control-group">
												<label class="control-label">* '.$lang->translate("Password").'</label>
												<div class="controls">
													<input type="password" id="public_user_password_'.$area_id.'" name="public_user_password_'.$area_id.'" />
												</div>
											</div>
							      		</div>	
							      		<div class="col-md-6">				      							      		
							      			<div class="control-group">
												<label class="control-label">* '.$lang->translate("Confirm Password").'</label>
												<div class="controls">
													<input type="password" id="public_user_cpassword_'.$area_id.'" name="public_user_cpassword_'.$area_id.'" />
												</div>
											</div>
							      		</div>								      								      												
									</div>
									<div class="row-fluid center">
										<input class="btn " type="button" id="btn_register_user_'.$area_id.'" name="btn_register_user_'.$area_id.'" value="'.$lang->translate("Send").'" />
										<input type="hidden" id="website_id" name="website_id" value="'.$front_ids->website_id.'" />
										<input type="hidden" id="area" name="area" value="'.$area_id.'" />
										<input type="hidden" id="error_email_'.$area_id.'" name="error_email_'.$area_id.'" value="'. $lang->translate('The email address already has been registered').'" />
										<input type="hidden" id="error_username_'.$area_id.'" name="error_username_'.$area_id.'" value="'. $lang->translate('The username already has been registered').'" />
									</div>																																				
								</form>
							</div>
							
							<div class="hide">
								<form class="col-md-8 form-horizontal margin-fancy" id="form_forgot_'.$area_id.'" name="form_forgot_'.$area_id.'" action="">
									<div class="row-fluid center">
										<h3>'.$lang->translate("Password Reset").'</h3>
									</div><br/>
									<div class="row-fluid">
										<div class="col-md-3">
							      				&ensp;
							      		</div>
							      		<div class="col-md-6">
							      			<div class="control-group">
												<label class="control-label">'.$lang->translate("Email").'</label>
												<div class="controls">
													<input type="text" id="public_for_user_email_'.$area_id.'" name="public_for_user_email_'.$area_id.'" />
												</div>
											</div>
							      		</div>
									</div>	
									<div class="row-fluid center">
										<input class="btn " type="button" id="btn_send_password_user_'.$area_id.'" name="btn_send_password_user_'.$area_id.'" value="'.$lang->translate("Send").'" />
										
									</div>										
								</form>
							</div>								
						</div>';
			
			return $return;			
		}
	}
}
