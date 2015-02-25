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
					return '<div id="alignment_cont_'.$content_id.'" align="'.(count($content_by_section_data)>0? $content_by_section_data[0]->align : 'left').'">'. $data_content_field [0]->value  . '</div>';
					break;
				
				case 'flash' :
					$return = '<div id="alignment_cont_'.$content_id.'" align="'.(count($content_by_section_data)>0? $content_by_section_data[0]->align : 'left').'">
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
				
				case 'carousel' :
					$return = '<div id="alignment_cont_'.$content_id.'" align="'.(count($content_by_section_data)>0? $content_by_section_data[0]->align : 'left').'">
						    <div id="myCarousel_'.$content_id.'" class="carousel slide">
							    <div class="carousel-inner">';					
					
					$array_images = explode ( ',', $data_content_field [0]->value );
					if(count($array_images)>1)
						array_pop ( $array_images );
					
					if($array_images){
						//recover render values
						$session_render_vals = new Zend_Session_Namespace('render_vals');
						$content_width = GlobalFunctions::getImageWithForRender($session_render_vals->area_width, $session_render_vals->section_cols, count($content_by_section_data[0])>0? $content_by_section_data[0]->column_number : 1);
						
						//getWebsiste id
						$id = new Zend_Session_Namespace('id');
						//get watermark info
						$watermark_data =  GlobalFunctions::getWatermark($id->website_id);
						
						foreach ( $array_images as $ai ) {
							$return .= '<div class="item"><img src="'. imageRender::cache_image($ai, array('width' =>$content_width,'watermark' =>$watermark_data['file'], 'watermark_pos'=>$watermark_data['pos'])) .'" style="'.GlobalFunctions::checkImageSize($ai,$content_width).'"/></div>';
						
						}
					}
					
					$return .= '</div>							   
							    <a id="carousel_left_'.$content_id.'" class="carousel-control left hide" href="#myCarousel_'.$content_id.'" data-slide="prev">&lsaquo;</a>
							    <a id="carousel_right_'.$content_id.'" class="carousel-control right hide" href="#myCarousel_'.$content_id.'" data-slide="next">&rsaquo;</a>
						    </div>
						</div>';
					
					return $return;
					break;
				
				case 'image' :
					
					$return = '<div id="alignment_cont_'.$content_id.'" align="'.(count($content_by_section_data)>0? $content_by_section_data[0]->align : 'left').'" class="overflow_hidden"><img';

					//recover render values
					$session_render_vals = new Zend_Session_Namespace('render_vals');
					$content_width = GlobalFunctions::getImageWithForRender($session_render_vals->area_width, $session_render_vals->section_cols, count($content_by_section_data[0])>0? $content_by_section_data[0]->column_number : 1);
					
					if( $data_content_field[7]->value == 'no'){
						$return .= ' src="/uploads/content/'.$data_content_field[4]->value.'" style="max-width:none;"';
					}else{
					//getWebsiste id
					$id = new Zend_Session_Namespace('id');
					//get watermark info
					$watermark_data =  GlobalFunctions::getWatermark($id->website_id);
                                        
                                        if($data_content_field[8]->value =='yes'){
                                            $return .= ' src="'. imageRender::cache_image($data_content_field [4]->value, array('width' =>$content_width,'watermark' =>$watermark_data['file'], 'watermark_pos'=>$data_content_field[9]->value)) .'" ';
                                        }else{
                                            $return .= ' src="'. imageRender::cache_image($data_content_field [4]->value, array('width' =>$content_width,'watermark' =>0, 'watermark_pos'=>0)) .'" ';
                                        }
                                        $return .= ' style="'.GlobalFunctions::checkImageSize($data_content_field [4]->value,$content_width);
					}
					
					if($data_content_field [5]->value == 'frame'){
						$return.= ' border: thin solid; color: black !important;';
					}
					
					$return .= '"';
					$return .='/>';
					if($data_content_field [4]->value != '')
						$return .= '<p>'.$data_content_field [0]->value.'</p>';
					
					$return .= '</div>';
					
					return  $return;
					break;
				
				case 'flash_video' :
					if($data_content_field [0]->value){
						
						$url = $data_content_field [0]->value;
						parse_str( parse_url( $url, PHP_URL_QUERY ), $my_array_of_vars );
						
						
						if(!$my_array_of_vars){
							$my_array_of_vars = explode('/',$data_content_field [0]->value);
							$return = '<div id="alignment_cont_'.$content_id.'" align="'.(count($content_by_section_data)>0? $content_by_section_data[0]->align : 'left').'"><embed src="http://www.youtube.com/v/'.$my_array_of_vars[3].'&rel=1" pluginspage="http://adobe.com/go/getflashplayer" type="application/x-shockwave-flash" quality="high" width="450" height="376" bgcolor="#ffffff" loop="false"></embed></div>';
						}else{
							$return = '<div id="alignment_cont_'.$content_id.'" align="'.(count($content_by_section_data)>0? $content_by_section_data[0]->align : 'left').'"><embed src="http://www.youtube.com/v/'.$my_array_of_vars['v'].'&rel=1" pluginspage="http://adobe.com/go/getflashplayer" type="application/x-shockwave-flash" quality="high" width="450" height="376" bgcolor="#ffffff" loop="false"></embed></div>';
						}
						
						return $return;
					}
					else
						return '<div id="alignment_cont_'.$content_id.'" align="'.(count($content_by_section_data)>0? $content_by_section_data[0]->align : 'left').'">' . $data_content_field [1]->value . '</div>';
					break;
				
				case 'link' :					
					switch ($data_content_field [0]->value) {
						case 'internal_link' :
							return '<div id="alignment_cont_'.$content_id.'" align="'.(count($content_by_section_data)>0? $content_by_section_data[0]->align : 'left').'"><a href="/core/section_section/index?id=' . $data_content_field [1]->value . '" >'.$data_content[0]->title.'</a></div>';
							break;
						case 'external_link' :
							return '<div id="alignment_cont_'.$content_id.'" align="'.(count($content_by_section_data)>0? $content_by_section_data[0]->align : 'left').'"><a href="http://' . str_replace ('http://', '', $data_content_field [2]->value) . '" >'.$data_content[0]->title.'</a></div>';
							break;
						case 'e_mail' :
							return '<div id="alignment_cont_'.$content_id.'" align="'.(count($content_by_section_data)>0? $content_by_section_data[0]->align : 'left').'"><a href="http://' . str_replace ('http://', '', $data_content_field [3]->value) . '" >'.$data_content[0]->title.'</a></div>';
							break;
						case 'file' :
							return '<div id="alignment_cont_'.$content_id.'" align="'.(count($content_by_section_data)>0? $content_by_section_data[0]->align : 'left').'" id="este"><a href="/uploads/content/' . $data_content_field [4]->value . '" ><img src="/images/' . $data_content_field [5]->value . '.png" />'.$data_content[0]->title.'</a></div>';
							break;
					}
					break;
				
				case 'form' :
					$form_field = new Core_Model_FormField();
					$forms_fields = $form_field->find('wc_form_field', 
						array(
								'content_id' => $content_id),
						array (
								'weight' => 'ASC'
						));
					
					$return='<h3 class="prv_form">'.$lang->translate('Form').': '.$data_content [0]->title.'</h3>
							<div class="row-fluid">
								<div class="span9" style="float:'.(count($content_by_section_data)>0? $content_by_section_data[0]->align : 'left').';">';
					
					foreach($forms_fields as $ff){
						switch($ff->type){
							case 'textfield':
								
								$return .= '<div class="line row-fluid">
												<div class="span3 form_label">
													<label>'.$ff->name.'</label>
												</div>
												<div class="span3 form_field">
													<input type="text" id="'.$ff->name.'" required="'.$ff->required.'" />
												</div>
											</div>';
								break;
								
							case 'emailfield':
								
								$return .= '<div class="line row-fluid">
												<div class="span3 form_label">
													<label>'.$ff->name.'</label>
												</div>
												<div class="span3 form_field">
													<input type="text" id="'.$ff->name.'" required="'.$ff->required.'" />
												</div>
											</div>';
								break;
                                                            
                                                        case 'datepicker':
                                                                $return .=	'<div class="form-group">
                                                                                    <label for="form_field_textfield_'.$ff->id.'">'.$ff->name.'</label>
                                                                                    <input type="text" id="form_field_textfield_'.$ff->id.'" class="form-control date-calendar hasDatepicker" readonly="readonly" name="'.$ff->name.'" valid="'.$ff->required.'"/><img class="ui-datepicker-trigger" src="/images/calendar.gif" alt="..." title="...">
                                                                                 </div>';
                                                                break;
							case 'textarea':
								
								$return .= '<div class="line row-fluid">
												<div class="span3 form_label">
													<label>'.$ff->name.'</label>
												</div>
												<div class="span3 form_field">
													<textarea id="'.$ff->name.'" required="'.$ff->required.'" ></textarea>
												</div>
											</div>';								
								break;
							case 'radiobutton':
								$return.= '<div class="line row-fluid">
												<div class="span3 form_label">
													<label>'.$ff->name.'</label>
												</div>';
								
									$array_options = explode ( ',', $ff->options );
									if(count($array_options)>1)
										array_pop ( $array_options );
									if($array_options){
										foreach ( $array_options as $ao ) {
// 											$return .= ''.$ao.' <input id="'.$ao.'" type="radio" value="'.$ao.'" name="'.$ao.'">';
											
											$return .= '<div class="span1 form_label">
															<label>'.$ao.'</label>
														</div>
														<div class="span1 form_field">
															<input id="'.$ao.'" type="radio" value="'.$ao.'" name="'.$ao.'">
														</div>';
										}
									}
								
								$return.= '</div>';
								break;
								
							case 'dropdown':
								$return.= '<div class="line row-fluid">
												<div class="span3 form_label">
													<label>'.$ff->name.'</label>
												</div>
												<div class="span3 form_field">
													<select id="'.$ff->name.'" required="'.$ff->required.'">';
								
									$array_options = explode ( ',', $ff->options );
									if(count($array_options)>1)
										array_pop ( $array_options );
									if($array_options){
										foreach ( $array_options as $ao ) {
											$return .= '<option id="'.$ao.'" type="radio" value="'.$ao.'" name="'.$ao.'">'.$ao.' </option>';
										}
									}
								
								$return.= '</select></div></div>';
								
								break;	

							case 'checkbox':
								
								$return .= '<div class="line row-fluid">
												<div class="span3 form_label">
													<label>'.$ff->name.'</label>
												</div>
												<div class="span3 form_field">
													<input type="checkbox" id="'.$ff->name.'" required="'.$ff->required.'" />
												</div>
											</div>';								
								break;	

							case 'comment':
								
								$return .= '<div class="line row-fluid">
												<div class="span3 form_label">
													&ensp;
												</div>
												<div class="span5 form_field">
													<label class="comment">'.$ff->description.'</label>
												</div>
											</div>';								
								break;	

							case 'file':
								
								$return .= '<div class="line row-fluid">
												<div class="span3 form_label">
													<label>'.$ff->name.'</label>
												</div>
												<div class="span3 form_field">
													<input type="file" id="'.$ff->name.'" required="'.$ff->required.'" /> 
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
						if (!isset($_SESSION['codigo'])){
							$_SESSION['codigo'] =  $captchaIterator['word'];
						}
						$return .= '<div class="line row-fluid">
										<div class="span3 form_label">
											<label>'.$lang->translate('Insert the security code').'</label>
										</div>
										<div class="span3 form_field">
											'.$captcha->render().'
											<input type = "text" id = "foo" name = "foo" class="line-top"></input> 
										</div>
									</div>';
					}
					
					$return .= '<div class="line">
									<div class="span2 form_field">
										<input type="button" id="btn_sub_form" class="btn" value="'.$lang->translate('Save').'"/> 
									</div>
								</div>';
					
					$return .= '</div>
							</div>';
					
					return $return;
					break;
			}
	
		}
	}
}