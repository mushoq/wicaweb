<?php
/**
 *	The content is considered as object that may be inserted into a content.
 *
 * @category   WicaWeb
 * @package    Core_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Santiago Arellano
 */

class Core_Content_ContentController extends Zend_Controller_Action {
		
	/**
	 * Loads existent objects in DB
	 */
	public function indexAction() 
	{		
		$this->_helper->layout->disableLayout ();
		
		$content_type = new Core_Model_ContentType ();
		$this->view->content_type = $content_type->find ( 'wc_content_type' );
		
		//searchs for stored session
		$session_id = new Zend_Session_Namespace ( 'id' );
		
		//will contain section object data as array
		$section_arr = array ();
		
		if ($session_id->section_id) 
		{
			$section = new Core_Model_Section();
			$section_temp = new Core_Model_SectionTemp();
			
			$section_obj = $section->find ( 'wc_section', array ('id' => $session_id->section_id) );
			$section_obj_temp = $section_temp->find('wc_section_temp', array('section_id'=> $session_id->section_id));
			if(count($section_obj_temp)>0)
				$section_arr = get_object_vars ( $section_obj_temp [0] );
			else
				$section_arr = get_object_vars ( $section_obj [0] );
		}
		
		$this->view->section_data = $section_arr;
	}
	
	/**
	 * Creates a content according fields in DB
	 */
	public function newAction() 
	{		
		//disable layout
		$this->_helper->layout->disableLayout ();
		
		//translate library
		$lang = Zend_Registry::get ( 'Zend_Translate' );
		
		//searchs for stored session section_id
		$session_id = new Zend_Session_Namespace ( 'id' );
						
		$content_type_id = $this->_request->getPost ( 'content_id' );
		if (! $content_type_id) 
		{
			$this->_helper->flashMessenger->addMessage ( array (
					'error' => $lang->translate ( 'Content access denied' )) );
			$this->_redirect ( 'core/content_content/' );
		}
		
		$content_type = new Core_Model_ContentType();
		$content_type_data = $content_type->find ( 'wc_content_type', array ('id' => $content_type_id) );
		$content_type_name = $content_type_data[0]->name;		
		$this->view->content_type = $content_type_name;
		
		$content_form = new Core_Form_Content_Content ( array ('content_type_id' => $content_type_id) );
		
		if (! $content_form) 
		{
			$this->_helper->flashMessenger->addMessage ( array ('error' => $lang->translate ( 'Content access denied') ) );
			$this->_redirect ( 'core/content_content/' );
		}
				
		//website
		$website = new Core_Model_Website();
		$website_fn = $website->find('wc_website',array('id'=>$session_id->website_id));
		$website_db = $website_fn[0];
		
		if($website_db->publication_approve =='yes')
		{
			if($session_id->user_profile == '1')
			{
				if($session_id->section_temp)
				{
					$publication_approved = 'no';
				}
				else
					$publication_approved = 'yes';
			}
			else
			{
				$publication_approved = 'no';
			}
		}
		else
		{
			if($session_id->section_temp)
			{
				$publication_approved = 'no';
			}
			else
			{
				$publication_approved = 'yes';
			}
		}
		
		$this->view->approved_frm = $publication_approved;
		
		if ($session_id->section_id) 
		{
			// parent section info
			$section = new Core_Model_Section();
			$section_temp = new Core_Model_SectionTemp();
						
			$section_obj = $section_temp->find('wc_section_temp', array('section_id'=> $session_id->section_id));
			if(count($section_obj)>0)
				$section_arr = get_object_vars ( $section_obj [0] );
			else
			{
				$section_obj = $section->find ( 'wc_section', array ('id' => $session_id->section_id) );
				$section_arr = get_object_vars ( $section_obj [0] );
			}
			$this->view->section_data = $section_arr;
			
			//set hidden section parent id
			$section_id = new Zend_Form_Element_Hidden ( 'section_id' );
			$section_id->setValue ( $session_id->section_id );
			$section_id->removeDecorator ( 'Label' );
			$section_id->removeDecorator ( 'HtmlTag' );
			$content_form->addElement ( $section_id );			
			$this->view->article = 	$section_obj[0]->article;
			$this->view->section_id = $session_id->section_id;

			//section temp variable
			$this->view->section_temp = intval($session_id->section_temp);
			
			//get max height and max width image
			$website = new Core_Model_Website();
			$website_data = $website->find('wc_website',array('id'=>$section_obj[0]->website_id));
			$this->view->max_height = $website_data[0]->max_height;
			$this->view->max_width = $website_data[0]->max_width;			
		}
		else
		{
			$this->view->article = 	'';
			$this->view->section_id = null;	
			$this->view->max_height = 1000;
			$this->view->max_width = 1000;
		}
		
		$this->view->form = $content_form;
	}
	
	/**
	 * Saves a new content or updates a content
	 */
	public function saveAction(){
		// translate library
		$lang = Zend_Registry::get ( 'Zend_Translate' );		
		
		$this->_helper->layout->disableLayout ();
		// disable autorendering for this action
		$this->_helper->viewRenderer->setNoRender();		
		
		$id = '';
		$content_temp_id = '';
		$flag = 0;
		
		if ($this->getRequest ()->isPost ()) 
		{			
			$formData = $this->_request->getPost ();
                        if($formData ['content_type_id']==2){
                            $position_post=strpos($formData['watermark_position'], ',');
                            $formData['watermark_position']= substr($formData['watermark_position'],0,$position_post);
                       }  
		
			$field = new Core_Model_Field ();
			$get_fields = $field->find ( 'wc_field', array ('content_type_id' => $formData ['content_type_id']) );
			if (! $get_fields ) {
				$this->_helper->flashMessenger->addMessage ( array ('error' => $lang->translate ( 'Content access denied' )) );
				$this->_redirect ( 'core/content_content/' );
			}

			// searchs for stored session section_id
			$session_id = new Zend_Session_Namespace ( 'id' );
			$contents_list_order = array();
			$contents_order_res = array();
			$content_by_section_data = array();
			
			$content_by_section = new Core_Model_ContentBySection ();
			if(isset($formData['section_id']))
				$content_by_section_data = $content_by_section->find('wc_content_by_section', array('section_id' => $formData['section_id']), array('weight' => 'ASC'));			
			
			if(count($content_by_section_data)>0)
			{				
				foreach ($content_by_section_data as $cbs)
				{
					$contents_order_res[] = $cbs->weight;
					$contents_list_order[] = array('content_id'=>$cbs->content_id);
				}
			}
			
			$section = new Core_Model_Section();
			$section_temp = new Core_Model_SectionTemp();
			$content = new Core_Model_Content ();
			$content_temp = new Core_Model_ContentTemp();			
			$content_by_section_temp = new Core_Model_ContentBySectionTemp();
			$content_field = new Core_Model_ContentField ();
			
			if(isset($formData['section_id']))
			{
				$section_obj_temp = $section_temp->find('wc_section_temp', array('section_id' => $formData['section_id']));
				$content_by_section_data_temp = array();
				
				if(count($section_obj_temp)>0)			
					$content_by_section_data_temp = $content_by_section_temp->find('wc_content_by_section_temp', array('section_temp_id'=> $section_obj_temp[0]->id));
												
				if(count($content_by_section_data)>0 && count($content_by_section_data_temp)>0)
				{
					$contents_copied_arr = array();
					//replacing sections that area eddited on temp
					foreach ($content_by_section_data as $ser)
					{
						foreach ($content_by_section_data_temp as $stp)
						{	
							$content_obj_temp = $content_temp->find('wc_content_temp', array('id'=>$stp->content_temp_id));
							$section_obj_temp = $section_temp->find('wc_section_temp', array('id'=>$stp->section_temp_id));
							if($ser->section_id == $section_obj_temp[0]->section_id && $ser->content_id == $content_obj_temp[0]->content_id)
							{							
								$contents_order_res[] = $stp->weight;
								$contents_copied_arr[] = $content_obj_temp[0]->content_id;
							}
						}
					}
						
					//adding sections created on temp
					if(count($contents_copied_arr)>0)
					{
						$contents_pub_missing = array_diff($contents_list_order, $contents_copied_arr);						
						if(count($contents_pub_missing)>0)
						{
							foreach ($contents_pub_missing as $serial)
							{							
								$content_section_data = $content_by_section->find('wc_content_by_section', array('section_id' => $formData['section_id'], 'content_id'=>$serial));
								if(count($content_section_data)>0)
									$contents_order_res[] = $content_section_data[0]->weight;
							}
						}
					}
				}
			}
			
			$last_weight = 0;			
			if(count($contents_order_res)>0)
			{
				foreach ($contents_order_res as $wgt)
				{
					$last_weight+= $wgt;
				}
			}
			$last_weight+=1;
			
			//UPDATE
			if($formData['id'])
			{ 
				$data = $content_temp->find ( 'wc_content_temp', array ( 'content_id' => $formData['id']) );
				$is_temp = true;
				if(!$data)
				{
					$data = $content->find ( 'wc_content', array ( 'id' => $formData['id']) );
					$is_temp = false;
				}
				
				if(!$is_temp)
				{
					if($formData['approved']=='no')
					{
						$content_temp = new Core_Model_ContentTemp();
						$content_obj_temp = $content_temp->getNewRow ( 'wc_content_temp' );
						$content_obj_temp->content_id = $formData['id'];
						$content_obj_temp->content_type_id = $formData ['content_type_id'];
						$content_obj_temp->website_id = $session_id->website_id;
						$content_obj_temp->internal_name = GlobalFunctions::value_cleaner ( $formData ['internal_name'] );
						$content_obj_temp->title = GlobalFunctions::value_cleaner ( $formData ['title'] );
						$content_obj_temp->created_by = $session_id->user_id;
						$content_obj_temp->creation_date = date ( 'Y-m-d h%i%s' );
						$content_obj_temp->approved = $formData ['approved'];
						$content_obj_temp->status = 'active';
						// Save data
						$content_temp_id = $content_temp->save ( 'wc_content_temp', $content_obj_temp );
							
						if($formData ['section_id'])
						{
							//then used in temp
							$stored_section_data = $section->find('wc_section', array('id'=>$formData ['section_id']));
							//new section temp
							$section_tmp = $section_temp->getNewRow('wc_section_temp');
							$section_tmp->section_id = $stored_section_data[0]->id;
							$section_tmp->website_id = $stored_section_data[0]->website_id;								
							$section_tmp->section_parent_id = $stored_section_data[0]->section_parent_id;
							$section_tmp->section_template_id = $stored_section_data[0]->section_template_id;
							$section_tmp->internal_name =  GlobalFunctions::value_cleaner($stored_section_data[0]->internal_name);
							$section_tmp->title = GlobalFunctions::value_cleaner($stored_section_data[0]->title);
							$section_tmp->subtitle = GlobalFunctions::value_cleaner($stored_section_data[0]->subtitle);
							$section_tmp->title_browser = GlobalFunctions::value_cleaner($stored_section_data[0]->title_browser);
							$section_tmp->synopsis = $stored_section_data[0]->synopsis;
							$section_tmp->keywords = GlobalFunctions::value_cleaner($stored_section_data[0]->keywords);
							$section_tmp->type = GlobalFunctions::value_cleaner($stored_section_data[0]->type);
							if(count($stored_section_data)>0)
							{
								$section_tmp->created_by_id = $stored_section_data[0]->created_by_id;
								$section_tmp->updated_by_id = $session_id->user_id;
								$section_tmp->creation_date = $stored_section_data[0]->creation_date;
								$section_tmp->last_update_date = date('Y-m-d h%i%s');
								$section_tmp->order_number = $stored_section_data[0]->order_number;
							}
							else
							{
								$section_tmp->created_by_id = $session_id->user_id;
								$section_tmp->updated_by_id = NULL;
								$section_tmp->creation_date = date('Y-m-d h%i%s');
								$section_tmp->last_update_date = NULL;
								$section_tmp->order_number = NULL;
							}
							$section_tmp->approved = 'no';
							$section_tmp->publication_status = 'nonpublished';
							$section_tmp->author = GlobalFunctions::value_cleaner($stored_section_data[0]->author);
							$section_tmp->feature = GlobalFunctions::value_cleaner($stored_section_data[0]->feature);
							$section_tmp->highlight = GlobalFunctions::value_cleaner($stored_section_data[0]->highlight);
							$section_tmp->publish_date = GlobalFunctions::setFormattedDate($stored_section_data[0]->publish_date);
							$section_tmp->expire_date = GlobalFunctions::setFormattedDate($stored_section_data[0]->expire_date);
							$section_tmp->show_publish_date = GlobalFunctions::value_cleaner($stored_section_data[0]->show_publish_date);
							$section_tmp->rss_available = GlobalFunctions::value_cleaner($stored_section_data[0]->rss_available);
							$section_tmp->external_link = GlobalFunctions::value_cleaner($stored_section_data[0]->external_link);
							$section_tmp->target = GlobalFunctions::value_cleaner($stored_section_data[0]->target);
							$section_tmp->comments = GlobalFunctions::value_cleaner($stored_section_data[0]->comments);
							$section_tmp->external_comment_script = NULL;
							$section_tmp->display_menu = GlobalFunctions::value_cleaner($stored_section_data[0]->display_menu);
							$section_tmp->homepage = $stored_section_data[0]->homepage;
							$section_tmp->article = $stored_section_data[0]->article;
							$section_tmp_id = $section_temp->save('wc_section_temp',$section_tmp);
			
							$content_by_section = new Core_Model_ContentBySection ();
							$content_section_obj = $content_by_section->find( 'wc_content_by_section', array('section_id'=>$formData ['section_id'], 'content_id'=>$formData['id']) );
							if(count($content_section_obj)>0)
							{
								$content_by_section_temp = new Core_Model_ContentBySectionTemp();
								$content_section_obj_temp = $content_by_section_temp->getNewRow ( 'wc_content_by_section_temp' );
								$content_section_obj_temp->content_by_section_id = $content_section_obj[0]->id;
								$content_section_obj_temp->section_temp_id = $section_tmp_id['id'];
								$content_section_obj_temp->content_temp_id = $content_temp_id ['id'];
								$content_section_obj_temp->weight = $last_weight;
								$content_section_obj_temp->column_number = 1;
								$content_section_obj_temp->align = 'left';
								$id_content_section_temp = $content_by_section_temp->save('wc_content_by_section_temp', $content_section_obj_temp );
							}
							
							$section_areas = new Core_Model_SectionModuleArea();
							$section_areas_temp = new Core_Model_SectionModuleAreaTemp();

							//module area
							$stored_area = $section_areas->find('wc_section_module_area', array('section_id'=>$formData['section_id']));							
							if(count($stored_area)>0)
							{
								$section_module_area = $section_areas_temp->getNewRow('wc_section_module_area_temp');
								$section_module_area->section_module_area_id = $stored_area[0]->id;
								$section_module_area->section_temp_id = $section_tmp_id['id'];
								$section_module_area->area_id = $stored_area[0]->area_id;
								//save section area
								$section_area_id = $section_areas->save('wc_section_module_area_temp',$section_module_area);
							}
							$session_id->section_temp = 1;
						}
						
						$data_content_field = $content_field->find ( 'wc_content_field', array ('content_id' => $formData['id']) );						
					}
					else 
					{							
						$data_content_field = $content_field->find ( 'wc_content_field', array ('content_id' => $formData['id']) );
						
						if (!$data || !$data_content_field) 
						{
							$this->_helper->flashMessenger->addMessage ( array ('error' => $lang->translate ( 'Content access denied' )) );
						}
		
						$arr_data = get_object_vars ( $data [0] );
						foreach ( $data_content_field as $df ) 
						{				
							$field = new Core_Model_Field ();				
							$data_field = $field->find ( 'wc_field', array ('id' => $df->field_id) );				
							$arr_data [str_replace ( ' ', '_', strtolower ( $data_field [0]->name ) )] = $df->value;
						}
									
						$content_obj = $data [0];
						$content_obj->internal_name = GlobalFunctions::value_cleaner ( $formData ['internal_name'] );
						$content_obj->title = GlobalFunctions::value_cleaner ( $formData ['title'] );
						$content_obj->updated_by = $session_id->user_id;
						$content_obj->last_update_date = date ( 'Y-m-d h%i%s' );
						$id = $content_temp->save ( 'wc_content', $content_obj );
					}
				}
				else
				{					
					$content_field_temp = new Core_Model_ContentFieldTemp();					
					$data_content_field = $content_field_temp->find ( 'wc_content_field_temp', array ('content_id' => $formData['id']) );
					
					$arr_data = get_object_vars ( $data [0] );
					foreach ( $data_content_field as $df )
					{
						$field = new Core_Model_Field();
						$data_field = $field->find ( 'wc_field', array ('id' => $df->field_id) );
						$arr_data [str_replace ( ' ', '_', strtolower ( $data_field [0]->name ) )] = $df->value;
					}
						
					$content_obj = $data [0];
					$content_obj->internal_name = GlobalFunctions::value_cleaner ( $formData ['internal_name'] );
					$content_obj->title = GlobalFunctions::value_cleaner ( $formData ['title'] );
					$content_obj->updated_by = $session_id->user_id;
					$content_obj->last_update_date = date ( 'Y-m-d h%i%s' );
					$id = $content_temp->save ( 'wc_content_temp', $content_obj );
				}
			}
			else
                            
			{
                            //multiple images upload
                             $imagesArray = array('1'=>1);
                             $multipleId = array();
                             $current_section = new Core_Model_Section();
                             $current_section_obj = $current_section->find('wc_section', array('id'=>$formData['section_id']));
                            if($formData['content_type_id']==2){
                                $uploads_dir = APPLICATION_PATH . "/../public/uploads/tmp/";
                                $imagesArray = array();
                                if(count($_FILES["Filedata"]["error"]) < 2) {
                                    // Single file
                                    //$tmp_name = $_FILES["Filedata"]["tmp_name"];
                                    $name = $_FILES["Filedata"]["name"];
                                    array_push($imagesArray, $name);
                                    
                                } else {
                                    if($_FILES["Filedata"]["error"])	
                                    foreach ($_FILES["Filedata"]["error"] as $key => $error) {
                                            if ($error == UPLOAD_ERR_OK) {
                                                    //$tmp_name = $_FILES["Filedata"]["tmp_name"][$key];
                                                    $name = $_FILES["Filedata"]["name"][$key];
                                                    //$namenew.= $name.',';
                                                    array_push($imagesArray, $name);

                                                    }
                                            }
                                    }
                            }
                                    
                                 //begin foreach
                               foreach ($imagesArray as $key=>$value){                            
				//INSERT
				//content
				$content = new Core_Model_Content ();
				$content_obj = $content->getNewRow ( 'wc_content' );
				$content_obj->content_type_id = $formData ['content_type_id'];
				$content_obj->website_id = $session_id->website_id;
                                if (count($imagesArray)>1){
				   $content_obj->internal_name = GlobalFunctions::value_cleaner 
                                           (str_replace ( ' ', '_', strtolower ( $current_section_obj[0]->internal_name ) ).
                                           '_'.$formData ['internal_name'].'_'.$key );
                                } else {
                                   $content_obj->internal_name = GlobalFunctions::value_cleaner ( $formData ['internal_name']);    
                                }
                                $content_obj->title = GlobalFunctions::value_cleaner ( $formData ['title'] );
				$content_obj->created_by = $session_id->user_id;
				$content_obj->creation_date = date ( 'Y-m-d h%i%s' );
				$content_obj->approved = $formData ['approved'];
				$content_obj->status = 'active';				
				// Save data
				$id = $content->save ( 'wc_content', $content_obj );
                                if (count($imagesArray)>1){
                                    array_push($multipleId, $id);   
                                }
				$is_temp = false;				
				
				//content_by_section
				if(key_exists('section_id', $formData))
					if ($formData ['section_id']) 
					{
						$content_by_section = new Core_Model_ContentBySection ();
						$content_section_obj = $content_by_section->getNewRow ( 'wc_content_by_section' );
						$content_section_obj->section_id = $formData ['section_id'];
						$content_section_obj->content_id = $id ['id'];
						$content_section_obj->weight = $last_weight;
						$content_section_obj->column_number = 1;
						$content_section_obj->align = 'left';
						$id_content_section = $content_by_section->save ( 'wc_content_by_section', $content_section_obj );
					}

				if($formData['approved']=='no')
				{
					$is_temp = true;
					$content_temp = new Core_Model_ContentTemp();
					$content_obj_temp = $content_temp->getNewRow ( 'wc_content_temp' );
					$content_obj_temp->content_id = $id['id'];
					$content_obj_temp->content_type_id = $formData ['content_type_id'];
					$content_obj_temp->website_id = $session_id->website_id;
					$content_obj_temp->internal_name = GlobalFunctions::value_cleaner ( $formData ['internal_name'] );
					$content_obj_temp->title = GlobalFunctions::value_cleaner ( $formData ['title'] );
					$content_obj_temp->created_by = $session_id->user_id;
					$content_obj_temp->creation_date = date ( 'Y-m-d h%i%s' );
					$content_obj_temp->approved = $formData ['approved'];
					$content_obj_temp->status = 'active';
					// Save data
					$content_temp_id = $content_temp->save ( 'wc_content_temp', $content_obj_temp );
					
					if($formData ['section_id'])
					{
						if($session_id->section_temp)
						{
							if($session_id->section_id == $formData['section_id'])
							{								
								$section_obj_temp = $section_temp->find('wc_section_temp', array('section_id'=>$formData['section_id']));
								if(count($section_obj_temp)>0)
								{
									$content_by_section_temp = new Core_Model_ContentBySectionTemp();
									$content_section_obj_temp = $content_by_section_temp->getNewRow ( 'wc_content_by_section_temp' );
									$content_section_obj_temp->content_by_section_id = $id_content_section['id'];
									$content_section_obj_temp->section_temp_id = $section_obj_temp[0]->id;
									$content_section_obj_temp->content_temp_id = $content_temp_id ['id'];
									$content_section_obj_temp->weight = $last_weight;
									$content_section_obj_temp->column_number = 1;
									$content_section_obj_temp->align = 'left';
									$id_content_section_temp = $content_by_section_temp->save('wc_content_by_section_temp', $content_section_obj_temp );
								}
							}
						}
						else
						{
							//then used in temp
							$stored_section_data = $section->find('wc_section', array('id'=>$formData ['section_id']));
							//new section temp
							$section_tmp = $section_temp->getNewRow('wc_section_temp');
							$section_tmp->section_id = $stored_section_data[0]->id;
							$section_tmp->website_id = $stored_section_data[0]->website_id;
							$section_tmp->section_parent_id = $stored_section_data[0]->section_parent_id;
							$section_tmp->section_template_id = $stored_section_data[0]->section_template_id;
							$section_tmp->internal_name =  GlobalFunctions::value_cleaner($stored_section_data[0]->internal_name);
							$section_tmp->title = GlobalFunctions::value_cleaner($stored_section_data[0]->title);
							$section_tmp->subtitle = GlobalFunctions::value_cleaner($stored_section_data[0]->subtitle);
							$section_tmp->title_browser = GlobalFunctions::value_cleaner($stored_section_data[0]->title_browser);
							$section_tmp->synopsis = $stored_section_data[0]->synopsis;
							$section_tmp->keywords = GlobalFunctions::value_cleaner($stored_section_data[0]->keywords);
							$section_tmp->type = GlobalFunctions::value_cleaner($stored_section_data[0]->type);
							if(count($stored_section_data)>0)
							{
								$section_tmp->created_by_id = $stored_section_data[0]->created_by_id;
								$section_tmp->updated_by_id = $session_id->user_id;
								$section_tmp->creation_date = $stored_section_data[0]->creation_date;
								$section_tmp->last_update_date = date('Y-m-d h%i%s');
								$section_tmp->order_number = $stored_section_data[0]->order_number;
							}
							else
							{
								$section_tmp->created_by_id = $session_id->user_id;
								$section_tmp->updated_by_id = NULL;
								$section_tmp->creation_date = date('Y-m-d h%i%s');
								$section_tmp->last_update_date = NULL;
								$section_tmp->order_number = NULL;
							}
							$section_tmp->approved = 'no';
							$section_tmp->publication_status = 'nonpublished';
							$section_tmp->author = GlobalFunctions::value_cleaner($stored_section_data[0]->author);
							$section_tmp->feature = GlobalFunctions::value_cleaner($stored_section_data[0]->feature);
							$section_tmp->highlight = GlobalFunctions::value_cleaner($stored_section_data[0]->highlight);
							$section_tmp->publish_date = GlobalFunctions::setFormattedDate($stored_section_data[0]->publish_date);
							$section_tmp->expire_date = GlobalFunctions::setFormattedDate($stored_section_data[0]->expire_date);
							$section_tmp->show_publish_date = GlobalFunctions::value_cleaner($stored_section_data[0]->show_publish_date);
							$section_tmp->rss_available = GlobalFunctions::value_cleaner($stored_section_data[0]->rss_available);
							$section_tmp->external_link = GlobalFunctions::value_cleaner($stored_section_data[0]->external_link);
							$section_tmp->target = GlobalFunctions::value_cleaner($stored_section_data[0]->target);
							$section_tmp->comments = GlobalFunctions::value_cleaner($stored_section_data[0]->comments);
							$section_tmp->external_comment_script = NULL;
							$section_tmp->display_menu = GlobalFunctions::value_cleaner($stored_section_data[0]->display_menu);
							$section_tmp->homepage = $stored_section_data[0]->homepage;
							$section_tmp->article = $stored_section_data[0]->article;
							$section_tmp_id = $section_temp->save('wc_section_temp',$section_tmp);
								
							$content_by_section = new Core_Model_ContentBySection ();
							$content_section_obj = $content_by_section->find( 'wc_content_by_section', array('section_id'=>$formData ['section_id'], 'content_id'=>$formData['id']) );
							if(count($content_section_obj)>0)
							{
								$content_by_section_temp = new Core_Model_ContentBySectionTemp();
								$content_section_obj_temp = $content_by_section_temp->getNewRow ( 'wc_content_by_section_temp' );
								$content_section_obj_temp->content_by_section_id = $content_section_obj[0]->id;
								$content_section_obj_temp->section_temp_id = $section_tmp_id['id'];
								$content_section_obj_temp->content_temp_id = $content_temp_id ['id'];
								$content_section_obj_temp->weight = $last_weight;
								$content_section_obj_temp->column_number = 1;
								$content_section_obj_temp->align = 'left';
								$id_content_section_temp = $content_by_section_temp->save('wc_content_by_section_temp', $content_section_obj_temp );
							}
								
							$section_areas = new Core_Model_SectionModuleArea();
							$section_areas_temp = new Core_Model_SectionModuleAreaTemp();
						
							//module area
							$stored_area = $section_areas->find('wc_section_module_area', array('section_id'=>$formData['section_id']));
							if(count($stored_area)>0)
							{
								$section_module_area = $section_areas_temp->getNewRow('wc_section_module_area_temp');
								$section_module_area->section_module_area_id = $stored_area[0]->id;
								$section_module_area->section_temp_id = $section_tmp_id['id'];
								$section_module_area->area_id = $stored_area[0]->area_id;
								//save section area
								$section_area_id = $section_areas->save('wc_section_module_area_temp',$section_module_area);
							}
							$session_id->section_temp = 1;							
							
							$data_content_field = $content_field->find ( 'wc_content_field', array ('content_id' => $formData['id']) );
						}
					}
				}
                             }
                                //end foreach
			}
                        
                        //foreach ($imagesArray as $key){ 
			
			if (! is_dir ( APPLICATION_PATH . '/../public/uploads/content/' )) {
				$path = APPLICATION_PATH . '/../public/uploads/content/';
                                if (!mkdir($path, 0777, true)) {
                                    die('Failed to create folders...');
                                }
//				mkdir ( $path );
//				chmod ( $path, 0777 );
			}
	
			if (! is_dir ( APPLICATION_PATH . '/../public/uploads/content/' . date ( 'Y' ) )) {
				$path = APPLICATION_PATH . '/../public/uploads/content/' . date ( 'Y' );
				if (!mkdir($path, 0777, true)) {
                                    die('Failed to create folders...');
                                }
			}
	
			if (! is_dir ( APPLICATION_PATH . '/../public/uploads/content/' . date ( 'Y' ) . '/' . date ( 'm' ) )) {
				$path = APPLICATION_PATH . '/../public/uploads/content/' . date ( 'Y' ) . '/' . date ( 'm' );
				if (!mkdir($path, 0777, true)) {
                                    die('Failed to create folders...');
                                }
			}
			if($formData['id']){
				$array_foreach = $data_content_field;
			}else{
				$array_foreach = $get_fields;
			}
                  if(!isset($imagesArray)){
                      $imagesArray = NULL;
                  }      
                 //for multiple images FIELDS 2,3,4,5,6,7,8,9, 36, 37
                if(count($imagesArray)>1){
                    
                    if(!isset($formData['watermarkimg']))
                        $formData['watermarkimg'] = 'no';
                    
                    if(!isset($formData['resizeimg']))
                        $formData['resizeimg'] = 'no';
                    
                    $content_field = new Core_Model_ContentField ();                              
                    $picture_foot = $formData['picture_foot']; //2
                    $description = $formData['description']; //3
                    $target = $formData['target']; //4
                    $link = $formData['link'];//5
                    //$image_path;//6                    
                    $format = $formData['format']; //7
                    $save_image;//8
                    $resizeimg = $formData['resizeimg']; //9  
                    $watermark = $formData['watermarkimg']; //36 
                    $watermarkposition = $formData['watermark_position']; //37 
                    $zoom = $formData['zoom'];//38
                        if($_FILES["Filedata"]["error"])	
                        foreach ($_FILES["Filedata"]["error"] as $key => $error) {
                                if ($error == UPLOAD_ERR_OK) {
                                        $tmp_name = $_FILES["Filedata"]["tmp_name"][$key];
                                        $name = $_FILES["Filedata"]["name"][$key];
                                        $ext = substr(strrchr($name, '.'), 1);
                                        switch(strtolower($ext)) {
                                                case 'jpg':
                                                case 'jpeg':
                                                case 'png':
                                                case 'gif':
                                                case 'png':
                                                case 'doc':
                                                case 'txt':
                                                        move_uploaded_file($tmp_name, "$uploads_dir/$name");
                                                        $image = GlobalFunctions::uploadFiles ( $name, APPLICATION_PATH . '/../public/uploads/content/' . date ( 'Y' ) . '/' . date ( 'm' ) . '/' );
                                                        $image_path = date ( 'Y' ) . '/' . date ( 'm' ) . '/' . $image;
                                                        
                                                        $content_field_obj = $content_field->getNewRow ( 'wc_content_field' );
                                                        $content_field_obj->field_id=2;
                                                        $content_field_obj->content_id=$multipleId[$key]['id'];
                                                        $content_field_obj->value=$picture_foot;
                                                        $saved_content_field = $content_field->save ( 'wc_content_field', $content_field_obj );
                                                        $content_field_obj2 = $content_field->getNewRow ( 'wc_content_field' );
                                                        $content_field_obj2->field_id=3;
                                                        $content_field_obj2->content_id=$multipleId[$key]['id'];
                                                        $content_field_obj2->value=$description;
                                                        $saved_content_field2 = $content_field->save ( 'wc_content_field', $content_field_obj2 );
                                                        $content_field_obj3 = $content_field->getNewRow ( 'wc_content_field' );
                                                        $content_field_obj3->field_id=4;
                                                        $content_field_obj3->content_id=$multipleId[$key]['id'];
                                                        $content_field_obj3->value=$target;
                                                        $saved_content_field3 = $content_field->save ( 'wc_content_field', $content_field_obj3 );
                                                        $content_field_obj4 = $content_field->getNewRow ( 'wc_content_field' );
                                                        $content_field_obj4->field_id=5;
                                                        $content_field_obj4->content_id=$multipleId[$key] ['id'];
                                                        $content_field_obj4->value=$link;
                                                        $saved_content_field4 = $content_field->save ( 'wc_content_field', $content_field_obj4 );
                                                        $content_field_obj8 = $content_field->getNewRow ( 'wc_content_field' );
                                                        $content_field_obj8->field_id=6;
                                                        $content_field_obj8->content_id=$multipleId[$key] ['id'];
                                                        $content_field_obj8->value=  $image_path;
                                                        $saved_content_field8 = $content_field->save ( 'wc_content_field', $content_field_obj8 );                                                        $content_field_obj5 = $content_field->getNewRow ( 'wc_content_field' );
                                                        $content_field_obj5->field_id=7;
                                                        $content_field_obj5->content_id=$multipleId[$key]['id'];
                                                        $content_field_obj5->value=$format;
                                                        $saved_content_field5 = $content_field->save ( 'wc_content_field', $content_field_obj5 );
                                                        $content_field_obj6 = $content_field->getNewRow ( 'wc_content_field' );
                                                        $content_field_obj6->field_id=8;
                                                        $content_field_obj6->content_id=$multipleId[$key]['id'];
                                                        $content_field_obj6->value= $save_image;
                                                        $saved_content_field6 = $content_field->save ( 'wc_content_field', $content_field_obj6 );
                                                        $content_field_obj7 = $content_field->getNewRow ( 'wc_content_field' );
                                                        $content_field_obj7->field_id=9;
                                                        $content_field_obj7->content_id=$multipleId[$key] ['id'];
                                                        $content_field_obj7->value= $resizeimg;
                                                        $saved_content_field7 = $content_field->save ( 'wc_content_field', $content_field_obj7 );
                                                        $content_field_obj9 = $content_field->getNewRow ( 'wc_content_field' );
                                                        $content_field_obj9->field_id=36;
                                                        $content_field_obj9->content_id=$multipleId[$key] ['id'];
                                                        $content_field_obj9->value= $watermark;
                                                        $saved_content_field9 = $content_field->save ( 'wc_content_field', $content_field_obj9 );
                                                        $content_field_obj10 = $content_field->getNewRow ( 'wc_content_field' );
                                                        $content_field_obj10->field_id=37;
                                                        $content_field_obj10->content_id=$multipleId[$key] ['id'];
                                                        $content_field_obj10->value= $watermarkposition;
                                                        $saved_content_field10 = $content_field->save ( 'wc_content_field', $content_field_obj10 );
                                                        $content_field_obj8 = $content_field->getNewRow ( 'wc_content_field' );
                                                        $content_field_obj8->field_id=38;
                                                        $content_field_obj8->content_id=$multipleId[$key] ['id'];
                                                        $content_field_obj8->value= $zoom;
                                                        $saved_content_field8 = $content_field->save ( 'wc_content_field', $content_field_obj8 );
                                                       
                                                        GlobalFunctions::removeOldFiles ( $name, APPLICATION_PATH . '/../public/uploads/tmp/' );

                                                        break;
                                                default:
                                                        exit();
                                                        break;
                                        }
                                }

                        }
                } else {                    
			foreach ( $array_foreach as $fields ) 
			{			
				$content_field = new Core_Model_ContentField ();
				$content_field_temp = new Core_Model_ContentFieldTemp();
				
				if($formData['id'])	//EDIT
				{
					$field = new Core_Model_Field ();
					$content_field = new Core_Model_ContentField ();							
					$content_field_obj = $fields;
					$fields = $field->find ( 'wc_field', array ('id' => $content_field_obj->field_id) );
					$fields = $fields[0];
				}
				else //INSERT
				{	
					$content_field_obj = $content_field->getNewRow ( 'wc_content_field' );
					$content_field_obj->field_id = $fields->id;
					
					$content_temp_id = array();
					if($is_temp)
						if($id['id'])
							$content_temp_id = $content_temp->find('wc_content_temp', array('content_id' => $id['id']));
					
					if(count($content_temp_id)>0)
						$content_field_obj->content_temp_id = $content_temp_id[0]->id;
					
					$content_field_obj->content_id = $id ['id'];										
				}		
					
				if ($fields->name != 'Save') 
				{	
					if ($fields->type == 'image') 
					{
						if ($_FILES["Filedata"]["name"]) 
						{		
							$uploads_dir = APPLICATION_PATH . "/../public/uploads/tmp/";
	
							if(count($_FILES["Filedata"]["error"]) < 2) {
								// Single file
								$tmp_name = $_FILES["Filedata"]["tmp_name"];
								$name = $_FILES["Filedata"]["name"];
								$ext = substr(strrchr($name, '.'), 1);
								switch(strtolower($ext)) {
									case 'jpg':
									case 'jpeg':
									case 'png':
									case 'gif':
									case 'png':
									case 'doc':
									case 'txt':
										move_uploaded_file($tmp_name, "$uploads_dir/$name");
											
										$img = GlobalFunctions::uploadFiles ($name , APPLICATION_PATH . '/../public/uploads/content/' . date ( 'Y' ) . '/' . date ( 'm' ) . '/' );
										$content_field_obj->value = date ( 'Y' ) . '/' . date ( 'm' ) . '/' . $img;
										GlobalFunctions::removeOldFiles ( $name, APPLICATION_PATH . '/../public/uploads/tmp/' );
										if($formData['id']){
											if (! GlobalFunctions::removeOldFiles ( $arr_data ['img_' . str_replace ( ' ', '_', strtolower ( $fields->name ) )], APPLICATION_PATH . '/../public/uploads/content/' )) {
												throw new Zend_Exception ( "CUSTOM_EXCEPTION:FILE NOT DELETED." );
											}
										}											
											
										break;
									default:
										exit();
										break;
								}
							}
						}
					} else if (str_replace ( ' ', '_', strtolower ( $fields->name ) ) == 'html_code'){
						$content_field_obj->value = utf8_decode( $formData [str_replace ( ' ', '_', strtolower ( $fields->name ) )] );
                                        //for carrusel
					}else if ($fields->type == 'select_images') {

						$uploads_dir = APPLICATION_PATH . "/../public/uploads/tmp/";
						$value = '';
                                                $current_images_order = $formData['images_order'];
                                                $content_field_obj->value =  $current_images_order;

						if(count($_FILES["Filedata"]["error"]) > 0){
							if(count($_FILES["Filedata"]["error"]) < 2) {
								// Single file
								$tmp_name = $_FILES["Filedata"]["tmp_name"];
								$name = $_FILES["Filedata"]["name"];
								$ext = substr(strrchr($name, '.'), 1);
								switch(strtolower($ext)) {
									case 'jpg':
									case 'jpeg':
									case 'png':
									case 'gif':
									case 'png':
									case 'doc':
									case 'txt':
										
										move_uploaded_file($tmp_name, $uploads_dir."/".$name);										
										$img = GlobalFunctions::uploadFiles ($name , APPLICATION_PATH . '/../public/uploads/content/' . date ( 'Y' ) . '/' . date ( 'm' ) . '/' );
										$content_field_obj->value = $current_images_order.date ( 'Y' ) . '/' . date ( 'm' ) . '/' . $img.',';
										GlobalFunctions::removeOldFiles ( $name, APPLICATION_PATH . '/../public/uploads/tmp/' );
											
										break;
									default:
										exit();
										break;
								}
							} else {
                                                                //multiple images
								if($_FILES["Filedata"]["error"])	
								foreach ($_FILES["Filedata"]["error"] as $key => $error) {
									if ($error == UPLOAD_ERR_OK) {
										$tmp_name = $_FILES["Filedata"]["tmp_name"][$key];
										$name = $_FILES["Filedata"]["name"][$key];
										$ext = substr(strrchr($name, '.'), 1);
										switch(strtolower($ext)) {
											case 'jpg':
											case 'jpeg':
											case 'png':
											case 'gif':
											case 'png':
											case 'doc':
											case 'txt':
												move_uploaded_file($tmp_name, "$uploads_dir/$name");
		
												$image = GlobalFunctions::uploadFiles ( $name, APPLICATION_PATH . '/../public/uploads/content/' . date ( 'Y' ) . '/' . date ( 'm' ) . '/' );
												$value .= date ( 'Y' ) . '/' . date ( 'm' ) . '/' . $image . ',';
												GlobalFunctions::removeOldFiles ( $name, APPLICATION_PATH . '/../public/uploads/tmp/' );
		
												break;
											default:
												exit();
												break;
										}
									}
										
								}
								$content_field_obj->value =  $current_images_order.$value;
							}
						}
                                                
                                                //delete old images from ../uploads/content/...
                                                $deleted_images = $formData['deleted_images'];
                                                $array_images = explode ($deleted_images );
                                                foreach ( $array_images as $ai ) {
                                                        if (! GlobalFunctions::removeOldFiles ( $ai, APPLICATION_PATH . '/../public/uploads/content/' )) {
                                                                throw new Zend_Exception ( "CUSTOM_EXCEPTION:FILE NOT DELETED." );
                                                        }
                                                }
						if($formData['id']){
							if($content_field_obj->value)							{
								$array_images = explode ( ',', $arr_data [str_replace ( ' ', '_', strtolower ( $fields->name ) )] );
								if(count($array_images)>1)
									array_pop ( $array_images );                       
//								foreach ( $array_images as $ai ) {
//									if (! GlobalFunctions::removeOldFiles ( $ai, APPLICATION_PATH . '/../public/uploads/content/' )) {
//										throw new Zend_Exception ( "CUSTOM_EXCEPTION:FILE NOT DELETED." );
//									}
//								}
							}		
						}						
							
					} else if ($fields->type == 'file') {
						if($formData ['hdnNameFile_'.str_replace ( ' ', '_', strtolower ( $fields->name ) )] == ' '){
							$content_field_obj->value = NULL;
							if(isset($arr_data))
							if($arr_data [str_replace ( ' ', '_', strtolower ( $fields->name ) )] != '')
								if (! GlobalFunctions::removeOldFiles ( $arr_data [str_replace ( ' ', '_', strtolower ( $fields->name ) )], APPLICATION_PATH . '/../public/uploads/content/' )) {
									throw new Zend_Exception ( "CUSTOM_EXCEPTION:FILE NOT DELETED." );
								}								
						}
						else
						if($formData['id'] && $formData ['hdnNameFile_'.str_replace ( ' ', '_', strtolower ( $fields->name ) )] && $formData ['hdnNameFile_'.str_replace ( ' ', '_', strtolower ( $fields->name ) )] != $arr_data [str_replace ( ' ', '_', strtolower ( $fields->name ) )]){
							$img = GlobalFunctions::uploadFiles ( $formData ['hdnNameFile_'.str_replace ( ' ', '_', strtolower ( $fields->name ) )], APPLICATION_PATH . '/../public/uploads/content/' . date ( 'Y' ) . '/' . date ( 'm' ) . '/' );
							$content_field_obj->value = date ( 'Y' ) . '/' . date ( 'm' ) . '/' . $img;
							GlobalFunctions::removeOldFiles ( $formData ['hdnNameFile_'.str_replace ( ' ', '_', strtolower ( $fields->name ) )], APPLICATION_PATH . '/../public/uploads/tmp/' );

							if($formData['id']){
								if (! GlobalFunctions::removeOldFiles ( $arr_data [str_replace ( ' ', '_', strtolower ( $fields->name ) )], APPLICATION_PATH . '/../public/uploads/content/' )) {
									throw new Zend_Exception ( "CUSTOM_EXCEPTION:FILE NOT DELETED." );
								}					
							}		
						}else 
							if($formData ['hdnNameFile_'.str_replace ( ' ', '_', strtolower ( $fields->name ) )] && !$formData['id']){
								$img = GlobalFunctions::uploadFiles ( $formData ['hdnNameFile_'.str_replace ( ' ', '_', strtolower ( $fields->name ) )], APPLICATION_PATH . '/../public/uploads/content/' . date ( 'Y' ) . '/' . date ( 'm' ) . '/' );
								$content_field_obj->value = date ( 'Y' ) . '/' . date ( 'm' ) . '/' . $img;
								GlobalFunctions::removeOldFiles ( $formData ['hdnNameFile_'.str_replace ( ' ', '_', strtolower ( $fields->name ) )], APPLICATION_PATH . '/../public/uploads/tmp/' );
							}								
							
					} else
						if(key_exists(str_replace ( ' ', '_', strtolower ( $fields->name ) ), $formData)){
							if(str_replace ( ' ', '_', strtolower ( $fields->name ) )=='content'){
								$content_field_obj->value = $formData [str_replace ( ' ', '_', strtolower ( $fields->name ) )];
							}else{
								$content_field_obj->value = GlobalFunctions::value_cleaner ( $formData [str_replace ( ' ', '_', strtolower ( $fields->name ) )] );
							}
						}
						
					// Save data
					if($is_temp){
						$saved_content_field = $content_field_temp->save ( 'wc_content_field_temp', $content_field_obj );
					}
					else 
					{
						if($formData ['approved']=='no')
						{
							$content_field_temp = new Core_Model_ContentFieldTemp();
							$content_field_obj_temp = $content_field_temp->getNewRow ( 'wc_content_field_temp' );
							$content_field_obj_temp->field_id = $fields->id;
							$content_field_obj_temp->content_temp_id = $content_temp_id['id'];
							$content_field_obj_temp->content_id = $formData['id'];
							$content_field_obj_temp->value = $content_field_obj->value;
							$saved_content_field_temp = $content_field_temp->save ( 'wc_content_field_temp', $content_field_obj_temp );
						}						
						else
						{
							$saved_content_field = $content_field->save ( 'wc_content_field', $content_field_obj );
						}
					}	
				}
			}
                } //end if multiple images

			if ($formData['content_type_id'] == 4) 
			{
				if (is_array ( $formData['frm_name_'] )) 
				{		
					$form_field = new Core_Model_FormField();
					$form_field_temp = new Core_Model_FormFieldTemp();
						
					if($formData['id'])
					{						
						if($is_temp)
						{			
							$content_obj_temp = $content_temp->find('wc_content_temp', array('id'=> $id['id']));
							$form_field_obj = $form_field_temp->find('wc_form_field_temp', array('content_temp_id'=>$content_obj_temp[0]->id));
							if(count($form_field_obj)>0)
								$form_field_temp->delete('wc_form_field_temp', array('id' => $form_field_obj[0]->id));
						}
						else
						{
							$form_field->delete('wc_form_field' , array('content_id' => $id['id']));
						}						
					}
					
					foreach ( $formData ['frm_name_'] as $key => $value ) 
					{	
						if($is_temp)
						{
							$content_obj_temp = $content_temp->find('wc_content_temp', array('content_id'=> $id['id']));
							if(count($content_obj_temp)>0)
							{
								$form_field_obj = $form_field_temp->getNewRow ('wc_form_field_temp');
								$form_field_obj->content_temp_id = $content_obj_temp[0]->id;
								$form_field_obj->name = GlobalFunctions::value_cleaner($value);
								$form_field_obj->description = GlobalFunctions::value_cleaner ($formData['frm_description_'] [$key]);
								$form_field_obj->required = $formData ['frm_required_'] [$key];
								$form_field_obj->type = $formData ['frm_element_type_'] [$key];
								$form_field_obj->weight = $formData ['frm_element_weight_'] [$key];
								$options_value = '';
									
								if(key_exists('hdn_frm_option_',$formData))
									if (key_exists( $key, $formData ['hdn_frm_option_']))
									{
										foreach ( $formData ['hdn_frm_option_'] [$key] as $options)
										{
											$options_value .= $options . ",";
										}
										$form_field_obj->options = GlobalFunctions::value_cleaner ($options_value);
									}
									//Save data
									if(!$form_field_temp->save ('wc_form_field_temp', $form_field_obj))
										$flag = 1;
							}
						}
						else
						{
							$form_field_obj = $form_field->getNewRow ('wc_form_field');
							$form_field_obj->content_id = $id ['id'];
							$form_field_obj->name = GlobalFunctions::value_cleaner($value);
							$form_field_obj->description = GlobalFunctions::value_cleaner ($formData['frm_description_'] [$key]);
							$form_field_obj->required = $formData ['frm_required_'] [$key];
							$form_field_obj->type = $formData ['frm_element_type_'] [$key];
							$form_field_obj->weight = $formData ['frm_element_weight_'] [$key];
							$options_value = '';
							
							if(key_exists('hdn_frm_option_',$formData))
								if (key_exists( $key, $formData ['hdn_frm_option_']))
								{
									foreach ( $formData ['hdn_frm_option_'] [$key] as $options)
									{
										$options_value .= $options . ",";
									}
									$form_field_obj->options = GlobalFunctions::value_cleaner ($options_value);
								}
							//Save data
							if(!$form_field->save ('wc_form_field', $form_field_obj))
								$flag = 1;
							
							if($formData ['approved']=='no')
							{
								$content_obj_temp = $content_temp->find('wc_content_temp', array('content_id'=> $id['id']));
								$form_field_obj = $form_field_temp->getNewRow ('wc_form_field_temp');
								$form_field_obj->content_temp_id = $content_obj_temp[0]->id;
								$form_field_obj->name = GlobalFunctions::value_cleaner($value);
								$form_field_obj->description = GlobalFunctions::value_cleaner ($formData['frm_description_'] [$key]);
								$form_field_obj->required = $formData ['frm_required_'] [$key];
								$form_field_obj->type = $formData ['frm_element_type_'] [$key];
								$form_field_obj->weight = $formData ['frm_element_weight_'] [$key];
								$options_value = '';
									
								if(key_exists('hdn_frm_option_',$formData))
									if (key_exists( $key, $formData ['hdn_frm_option_']))
									{
										foreach ( $formData ['hdn_frm_option_'] [$key] as $options)
										{
											$options_value .= $options . ",";
										}
										$form_field_obj->options = GlobalFunctions::value_cleaner ($options_value);
									}
									//Save data
									if(!$form_field_temp->save ('wc_form_field_temp', $form_field_obj))
										$flag = 1;
							}
						}
					}
				}	
			}
							
			if ($formData['content_type_id'] != 2 && $formData['content_type_id'] != 7) {
				if ($id || $content_temp_id && $flag == 0) {
// 					$arr_success = array('content'=>$id ['id'], 'serial'=>$session_id->section_id);
// 					echo json_encode($arr_success);
					$this->_helper->flashMessenger->addMessage ( array (
							'success' => $lang->translate ( 'Success saved' )
					) );
				} else {
					$this->_helper->flashMessenger->addMessage ( array (
							'error' => $lang->translate ( 'Errors in saving data' )
					) );
				}
			}else{
				if ($id || $content_temp_id && $flag == 0) {
					$this->_helper->flashMessenger->addMessage ( array (
							'success' => $lang->translate ( 'Success saved' )
					) );
				} else {
					$this->_helper->flashMessenger->addMessage ( array (
							'error' => $lang->translate ( 'Errors in saving data' )
					) );
				}					
			}
		}
	 }	
        
	
	/**
	 * Updates an existent content
	 */
	public function editAction() 
	{       
		//disable layout
		$this->_helper->layout->disableLayout ();
		// translate library
		$lang = Zend_Registry::get ( 'Zend_Translate' );
		
		// searchs for stored session section_id
		$session_id = new Zend_Session_Namespace ( 'id' );
		
		$content_id = $this->_request->getPost ('id');
		$section_id = $this->_request->getPost ('section_id');
		$session_id->section_id = $section_id;
               
		
		if (! $content_id) {
			$this->_helper->flashMessenger->addMessage ( array (
					'error' => $lang->translate ( 'Content access denied' )) );
			$this->_redirect ( 'core/content_content/' );
		}
		
		$content = new Core_Model_Content ();		
		$content_field = new Core_Model_ContentField ();
		$content_temp = new Core_Model_ContentTemp();
		$content_field_temp = new Core_Model_ContentFieldTemp();
		$content_type = new Core_Model_ContentType();
		
		$data = $content_temp->find( 'wc_content_temp', array ( 'content_id' => $content_id ) );		
		if(count($data)<1)		
			$data = $content->find( 'wc_content', array ( 'id' => $content_id ) );
		
		//form
		$content_form = new Core_Form_Content_Content ( array ('content_type_id' => $data[0]->content_type_id) );
		
		//website
		$website = new Core_Model_Website();
		$website_fn = $website->find('wc_website',array('id'=>$session_id->website_id));
		$website_db = $website_fn[0];
		
		if($website_db->publication_approve =='yes')
		{
			if($data[0]->approved == 'yes')
			{
				if($session_id->user_profile == '1')
				{
					$publication_approved = 'yes';					
				}
				else
				{
					$publication_approved = 'no';
				}
			}
			else
				$publication_approved = 'no';
		}
		else
		{
			$publication_approved = 'yes';
		}
		$this->view->approved_frm = $publication_approved;
						
		$content_type_data = $content_type->find ( 'wc_content_type', array ('id' => $data[0]->content_type_id) );		
		$content_type_name = $content_type_data[0]->name;
		$this->view->content_type = $content_type_name;
		
		$data_content_field = $content_field_temp->find ( 'wc_content_field_temp', array ('content_id' => $content_id) );		
		if(count($data_content_field)<1)
			$data_content_field = $content_field_temp->find ( 'wc_content_field_temp', array ('content_temp_id' => $content_id) );

		if(count($data_content_field)<1)
			$data_content_field = $content_field->find ( 'wc_content_field', array ('content_id' => $content_id) );
		
		if (! $data || ! $data_content_field) 
		{
			$this->_helper->flashMessenger->addMessage ( array ('error' => $lang->translate ( 'Content access denied' ) ) );			
		}
		
		$arr_data = get_object_vars ( $data [0] );

		if(isset($arr_data['content_id']))
			$arr_data['id'] = $arr_data['content_id'];
		$this->view->preview = $arr_data['id'];
		$arr_data['watermark_position']='C';
		foreach ( $data_content_field as $df ) 
		{
			$field = new Core_Model_Field ();			
			$data_field = $field->find ( 'wc_field', array ('id' => $df->field_id) );
			
			if($data_field [0]->type == 'file')
			{
				$hidden_file = $content_form->getElement ( 'hdnNameFile_' . str_replace ( ' ', '_', strtolower ( $data_field [0]->name ) ) );
				$hidden_file->setValue ( $df->value );
				$arr_data [str_replace ( ' ', '_', strtolower ( $data_field [0]->name ) )] = $df->value;				
			}
			else
				$arr_data [str_replace ( ' ', '_', strtolower ( $data_field [0]->name ) )] = $df->value;
		}
		//for loading current images in a sortable list
                if($arr_data['content_type_id']==7){
                    $images = (explode("," ,$arr_data['select_images']));
                    array_pop($images);
                    $this->view->images = $images;
                    $this->view->images_order = $arr_data['select_images'];
                }
		$content_form->populate ( $arr_data );
		$this->view->form = $content_form;
		$this->view->content_id = $content_id;
		
		if ($session_id->section_id)
		{
			// parent section info
			$section = new Core_Model_Section ();
			// finds parent section data stored in db
			$parent_section = $section->find ( 'wc_section', array ('id' => $session_id->section_id) );
			// parent section as array to display data on view
			$section_arr = get_object_vars ( $parent_section [0] );
			$this->view->section_data = $section_arr;
			$this->view->article = 	$parent_section[0]->article;
			$this->view->section_id = $session_id->section_id;	

			// set hidden section parent id
			$section_id_frm = new Zend_Form_Element_Hidden ( 'section_id' );
			$section_id_frm->setValue ( $session_id->section_id );
			$section_id_frm->removeDecorator ( 'Label' );
			$section_id_frm->removeDecorator ( 'HtmlTag' );
			$content_form->addElement ( $section_id_frm );
			
			//section temp variable			
			$this->view->section_temp = intval($session_id->section_temp);
			
			//get max height and max width image
			$website = new Core_Model_Website();
			$website_data = $website->find('wc_website',array('id'=>$parent_section[0]->website_id));
			$this->view->max_height = $website_data[0]->max_height;
			$this->view->max_width = $website_data[0]->max_width;			
			
		}
		else
		{
			$this->view->article = 	'';
			$this->view->section_id = null;			
			$this->view->max_height = 1000;
			$this->view->max_width = 1000;			
		}	
                
                 //die('entra');
	}
	
	/**
	 * Search an existent content according to the entered data
	 */
	public function searchAction()
	{
		if ($this->getRequest ()->isPost ()) 
		{
			$this->_helper->layout->disableLayout ();
			//session			
			$id = New Zend_Session_Namespace('id');			
			//retrieved data from post
			$formData = $this->_request->getPost ();
			
			$internal_name = mb_strtolower ( $formData ['nameField'], 'UTF-8' );
			
			/*$content = new Core_Model_Content ();
			$contents_array = $content->getContentsBySection ( 0, $id->website_id, $internal_name );*/
			
			$content = new Core_Model_Content();
			$content_temp = new Core_Model_ContentTemp();
			$contents_list = $content->getContentsBySection(0, $id->website_id, $internal_name);
				
			$content_list_arr = array();
			if(count($contents_list)>0)
			{
				foreach ($contents_list as $k => $cli)
				{
					$contents_list_published[] = $cli['id'];
					$content_list_arr[] = array('id' => $cli['id'],
							'title' => $cli['title'],
							'section_name' => $cli['section_name'],
							'section_id' => $cli['section_id'],
							'type' => $cli['type'],
							'content_type_id' => $cli['content_type_id'],
							'internal_name' => $cli['internal_name'],
							'serial_cbs' => $cli['serial_cbs'],
							'column_number' => $cli['column_number'],
							'align' => $cli['align'],
							'weight' => $cli['weight'],
							'temp' => '0'
					);
				}
			}
				
			$contents_list_temp = $content_temp->getTempContentsBySection(0, $id->website_id, $internal_name);			
				
			if(count($contents_list)>0 && count($contents_list_temp)>0)
			{
				$contents_copied_arr = array();
				//replacing sections that area eddited on temp
				foreach ($contents_list as $k => $con)
				{
					foreach ($contents_list_temp as $p => $ctp)
					{
						if($con['id'] == $ctp['content_id'])
						{
							$ctp['id'] = $ctp['content_id'];
							$content_list_res[] = array('id' => $ctp['id'],
									'title' => $ctp['title'],
									'section_name' => $ctp['section_name'],
									'section_id' => $ctp['section_id'],
									'type' => $ctp['type'],
									'content_type_id' => $ctp['content_type_id'],
									'internal_name' => $ctp['internal_name'],
									'serial_cbs' => $ctp['serial_cbs'],
									'column_number' => $ctp['column_number'],
									'align' => $ctp['align'],
									'weight' => $ctp['weight'],
									'temp' => '1'
							);
							$contents_copied_arr[] = $ctp['content_id'];
						}
					}
				}
			
				if(count($contents_copied_arr)>0)
				{
					$content_pub_missing = array_diff($contents_list_published, $contents_copied_arr);
					if(count($content_pub_missing)>0)
					{
						$move_pos = array();
			
						foreach ($content_pub_missing as $serial)
						{
							foreach ($content_list_arr as $pos => $val)
							{
								if($val['id'] == $serial)
									$move_pos[] = $pos;
							}
						}
			
						if(count($move_pos)>0)
						{
							foreach ($move_pos as $ky)
							{
								$content_list_res[] = $content_list_arr[$ky];
							}
						}
					}
				}
				$content_list_arr = $content_list_res;
			}
				
			/******
			 * Ordering contents by weight
			*/
			if(count($content_list_arr)>0)
			{
				foreach ($content_list_arr as $row)
				{
					$col_weight[$row['id']] = $row['weight'];
				}
				array_multisort($col_weight, SORT_ASC, $content_list_arr);
			}
			
			$this->view->content_results = $content_list_arr;
			$this->view->content_search_params = $internal_name;
			//section temp variable
			$this->view->section_temp = intval($id->section_temp);
		}
	}
	
	/**
	 * Search an existent content according to internal name or section
	 */
	public function linkAction() 
	{
		$this->_helper->layout->disableLayout ();
		
		$link_search_form = new Core_Form_Content_Search ();
		$this->view->form = $link_search_form;
		$this->view->showresults = false;

		// retrieved data from post
		$formData = $this->_request->getPost ();		
		
		if ($formData['search_content']=='1') 
		{
			//session
			$id = New Zend_Session_Namespace('id');
			
			$this->view->showresults = true;
			// retrieved data from post
			$formData = $this->_request->getPost ();
			
			$internal_name = mb_strtolower ( $formData ['text'], 'UTF-8' );
			$serial_sec = $formData ['section'];
			
			$contents_list = array();
			$section = new Core_Model_Section();
			$section_temp = new Core_Model_SectionTemp();
			$content = new Core_Model_Content();
			$content_temp = new Core_Model_ContentTemp();
			$contents_list = $content->getContentsBySection ( $serial_sec, $id->website_id, $internal_name );

			$content_list_arr = array();
			if(count($contents_list)>0)
			{
				foreach ($contents_list as $k => $cli)
				{
					$contents_list_published[] = $cli['id'];
					$content_list_arr[] = array('id' => $cli['id'],
							'section_id' => $cli['section_id'],
							'section_name' => $cli['section_name'],
							'article' => $cli['article'],
							'title' => $cli['title'],
							'type' => $cli['type'],
							'content_type_id' => $cli['content_type_id'],
							'internal_name' => $cli['internal_name'],
							'serial_cbs' => $cli['serial_cbs'],
							'column_number' => $cli['column_number'],
							'align' => $cli['align'],
							'weight' => $cli['weight'],
							'temp' => '0'
					);
				}
			}
				
			$contents_list_temp = $content_temp->getTempContentsBySection($serial_sec, $id->website_id, $internal_name);
			if(count($contents_list)>0 && count($contents_list_temp)>0)
			{
				$contents_copied_arr = array();
				//replacing sections that area eddited on temp
				foreach ($contents_list as $k => $con)
				{
					foreach ($contents_list_temp as $p => $ctp)
					{
						if($con['id'] == $ctp['content_id'])
						{
							$serial = $ctp['content_id'];
							$content_list_res[] = array('id' => $serial,
									'section_id' => $ctp['section_id'],
									'section_name' => $ctp['section_name'],
									'article' => $ctp['article'],
									'title' => $ctp['title'],
									'type' => $ctp['type'],
									'content_type_id' => $ctp['content_type_id'],
									'internal_name' => $ctp['internal_name'],
									'serial_cbs' => $ctp['serial_cbs'],
									'column_number' => $ctp['column_number'],
									'align' => $ctp['align'],
									'weight' => $ctp['weight'],
									'temp' => '1'
							);
							$contents_copied_arr[] = $ctp['content_id'];
						}
					}
				}
			
				if(count($contents_copied_arr)>0)
				{
					$content_pub_missing = array_diff($contents_list_published, $contents_copied_arr);
					if(count($content_pub_missing)>0)
					{
						$move_pos = array();
			
						foreach ($content_pub_missing as $serial)
						{
							foreach ($content_list_arr as $pos => $val)
							{
								if($val['id'] == $serial)
									$move_pos[] = $pos;
							}
						}
			
						if(count($move_pos)>0)
						{
							foreach ($move_pos as $ky)
							{
								$content_list_res[] = $content_list_arr[$ky];
							}
						}
					}
				}
				$content_list_arr = $content_list_res;
			}
			
			//articles			
			$articles = $section->find('wc_section', array('section_parent_id'=>$serial_sec, 'article'=>'yes'));
			if(count($articles)>0)
			{
				$content_list_arr_aux = array();
				
				foreach ($articles as $art)
				{
					$article_contents = $content->getContentsBySection ( $art->id, $id->website_id, $internal_name );
					if(count($article_contents)>0)
					{									
						foreach ($article_contents as $k => $ali)
						{
							$contents_list_published_aux[] = $ali['id'];
							$content_list_arr_aux[] = array('id' => $ali['id'],
									'section_id' => $ali['section_id'],
									'section_name' => $ali['section_name'],
									'article' => $ali['article'],
									'title' => $ali['title'],
									'type' => $ali['type'],
									'content_type_id' => $ali['content_type_id'],
									'internal_name' => $ali['internal_name'],
									'serial_cbs' => $ali['serial_cbs'],
									'column_number' => $ali['column_number'],
									'align' => $ali['align'],
									'weight' => $ali['weight'],
									'temp' => '0'
							);
						}						
						
						$contents_list_temp_aux = $content_temp->getTempContentsBySection($art->id, $id->website_id, $internal_name);
						if(count($contents_list)>0 && count($contents_list_temp_aux)>0)
						{
							$contents_copied_arr_aux = array();
							//replacing sections that area eddited on temp
							foreach ($contents_list as $k => $con)
							{
								foreach ($contents_list_temp_aux as $p => $ctt)
								{
									if($con['id'] == $ctt['content_id'])
									{
										$serial = $ctt['content_id'];
										$content_list_res_aux[] = array('id' => $serial,
												'section_id' => $ctt['section_id'],
												'section_name' => $ctt['section_name'],
												'article' => $ctt['article'],
												'title' => $ctt['title'],
												'type' => $ctt['type'],
												'content_type_id' => $ctt['content_type_id'],
												'internal_name' => $ctt['internal_name'],
												'serial_cbs' => $ctt['serial_cbs'],
												'column_number' => $ctt['column_number'],
												'align' => $ctt['align'],
												'weight' => $ctt['weight'],
												'temp' => '1'
										);
										$contents_copied_arr_aux[] = $ctt['content_id'];
									}
								}
							}
								
							if(count($contents_copied_arr_aux)>0)
							{
								$content_pub_missing_aux = array_diff($contents_list_published_aux, $contents_copied_arr_aux);
								if(count($content_pub_missing_aux)>0)
								{
									$move_pos = array();
										
									foreach ($content_pub_missing_aux as $serial)
									{
										foreach ($content_list_arr_aux as $pos => $val)
										{
											if($val['id'] == $serial)
												$move_pos[] = $pos;
										}
									}
										
									if(count($move_pos)>0)
									{
										foreach ($move_pos as $ky)
										{
											$content_list_res_aux[] = $content_list_arr_aux[$ky];
										}
									}
								}
							}
							$content_list_arr_aux = $content_list_res_aux;
						}
					}
				}
				
				if(count($content_list_arr)>0 && count($content_list_arr_aux)>0)					
				{					
					$content_list_arr = array_merge($content_list_arr, $content_list_arr_aux);
				}
			}		
			$this->view->content_results = $content_list_arr;
						
			//content will be linked to this section
			$section_obj = $section_temp->find('wc_section_temp', array('section_id'=>$id->section_id));
			if(count($section_obj)<1)			
				$section_obj = $section->find('wc_section', array('id'=>$id->section_id));
			
			$this->view->section = $section_obj[0];			
		}
	}
        
	/**
	 * Returns the sections list for the autocompleter.
	 *
	 * @param $_GET['q']
	 */
	public function sectionautocompleterAction() 
	{
		$name = $this->_request->getParam ( 'q' );
		
		$title = mb_strtolower ( $name, 'UTF-8' );
		
		// stored session
		$session_id = new Zend_Session_Namespace ( 'id' );
		$website_id = $session_id->website_id;
		
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		
		$section_array = $section->personalized_find ( 'wc_section', array ( array ( 'title', 'LIKE', $title ), array ( 'website_id', '=', $website_id ) ) );
		foreach ( $section_array as $k => &$slt ) {
			$sections_published_arr[] = $slt->id;
		}
		//temp		
		$section_array_temp = $section_temp->personalized_find ( 'wc_section_temp', array ( array ( 'title', 'LIKE', $title ), array ( 'website_id', '=', $website_id ) ) );

		if(count($section_array)>0 && count($section_array_temp)>0)
		{
			$sections_copied_arr = array();
			//replacing sections that area eddited on temp
			foreach ($section_array as $k => &$sbc)
			{
				foreach ($section_array_temp as $p => &$sct)
				{
					if($sbc->id == $sct->section_id)
					{
						$sct->id = $sct->section_id;
						$sections_list_res[] = $sct;
						$sections_copied_arr[] = $sct->section_id;
					}
				}
			}
				
			//adding sections created on temp
			if(count($sections_copied_arr)>0)
			{
				$section_pub_missing = array_diff($sections_published_arr, $sections_copied_arr);
				if(count($section_pub_missing)>0)
				{
					foreach ($section_pub_missing as $serial)
					{
						$section_obj = $section->find('wc_section', array('id'=>$serial));
						$section_obj[0]->temp = 0;
						$sections_list_res[] = $section_obj[0];
					}
				}
			}
			$section_array = $sections_list_res;
		}
						
		if (is_array($section_array)) {
			foreach ( $section_array as $c ) {
				echo $c->title . '|' . $c->id . "\n";
			}
		}
		
		// disable autorendering for this action only:
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender ();
	}
	
	/**
	 * Link a content to a section
	 */
	public function linkcontentsAction() 
	{
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender ();
		
		// translate library
		$lang = Zend_Registry::get ( 'Zend_Translate' );
		
		if ($this->getRequest ()->isPost ()) 
		{
			// retrieved data from post
			$formData = $this->_request->getPost ();
			$inserted = array ();
			$section = new Core_Model_Section();
			$section_temp = new Core_Model_SectionTemp();
			
			foreach ( $formData ['objects'] as $content_id => $status ) 
			{
				//content will be linked to this section
				$section_obj = $section_temp->find('wc_section_temp', array('section_id'=>$formData ['section_id']));
				$is_temp = true;
				if(count($section_obj)<1)
				{
					$section_obj = $section->find('wc_section', array('id'=>$formData ['section_id']));
					$is_temp = false;
				}				
				
				$content_by_section = new Core_Model_ContentBySection ();
				$content_by_section_temp = new Core_Model_ContentBySectionTemp();
				
				if($is_temp)
				{
					$content_temp = new Core_Model_ContentTemp();
					$content_obj_temp = $content_temp->find('wc_content_temp', array('content_id'=>$content_id));
				
					$section_temp = new Core_Model_SectionTemp();
					$section_obj_temp = $section_temp->find('wc_section_temp', array('section_id' => $formData ['section_id']));
						
					if(count($content_obj_temp)>0 && count($section_obj_temp))
					{						
						$content_by_section_data_temp = $content_by_section_temp->find('wc_content_by_section_temp', array('section_temp_id'=> $section_obj_temp[0]->id, 'content_temp_id'=> $content_obj_temp[0]->id));
						
						if (count ( $content_by_section_data_temp ) > 0)
							$inserted = $content_by_section_data_temp;
						else
						{
							$content_by_section_obj_temp = $content_by_section_temp->getNewRow('wc_content_by_section_temp');
							//$content_by_section_obj_temp->id = $content_by_section_data_temp[0]->id;
							//$content_by_section_obj_temp->content_by_section_id = $content_by_section_data_temp[0]->content_by_section_id;
							$content_by_section_obj_temp->section_temp_id = $section_obj_temp[0]->id;
							$content_by_section_obj_temp->content_temp_id = $content_obj_temp[0]->id;
							$content_by_section_obj_temp->weight = 1000;
							$content_by_section_obj_temp->column_number = 1;
							$content_by_section_obj_temp->align = 'left';
							$content_by_section_temp->save('wc_content_by_section_temp',$content_by_section_obj_temp);
							$inserted = $content_by_section_temp;
						}
					}
					else
					{
						$content_section_obj = $content_by_section->getNewRow ( 'wc_content_by_section' );
						$content_section_obj->section_id = $formData ['section_id'];
						$content_section_obj->content_id = $content_id;
						$content_section_obj->weight = 1000;
						$content_section_obj->column_number = 1;
						$content_section_obj->align = 'left';
						$id_content_section = $content_by_section->save ( 'wc_content_by_section', $content_section_obj );
						$inserted = $id_content_section;
					}
				}
				else
				{					
					$content_section_obj = $content_by_section->find ( 'wc_content_by_section', array ( 'section_id' => $formData ['section_id'], 'content_id' => $content_id ) );
					
					if (count ( $content_section_obj ) > 0)
						$inserted = $content_section_obj;
					else
					{
						$content_section_obj = $content_by_section->getNewRow ( 'wc_content_by_section' );
						$content_section_obj->section_id = $formData ['section_id'];
						$content_section_obj->content_id = $content_id;
						$content_section_obj->weight = 1000;
						$content_section_obj->column_number = 1;
						$content_section_obj->align = 'left';
						$id_content_section = $content_by_section->save ( 'wc_content_by_section', $content_section_obj );
						$inserted = $id_content_section;
					}
				}
			}
			
			if (count ( $inserted ) == count ( $formData ['objects'] )) 
			{
				$this->_helper->flashMessenger->addMessage ( array ( 'success' => $lang->translate ( 'Success saved' )) );
				$arr_success = array('serial'=>'linked');
				echo json_encode($arr_success);
			} 
			else 
			{
				$this->_helper->flashMessenger->addMessage ( array ('error' => $lang->translate ( 'Errors in saving data' ) ) );
			}						
		}
	}
	
	/**
	 * Deletes the relationship between a content and section
	 */
	public function disconnectAction() 
	{
		// disable autorendering for this action only:
		$this->_helper->viewRenderer->setNoRender ();
		$this->_helper->layout->disableLayout ();
		
		// translate library
		$lang = Zend_Registry::get ( 'Zend_Translate' );
		
		//content by section id 		
		$id = $this->_request->getPost ( 'id_cbs' );
		//content_id
		$content_id = $this->_request->getPost ( 'id_cont' );
		//temp
		$is_temp = $this->_request->getPost ( 'temp' );
		
		$content_by_section = new Core_Model_ContentBySection ();
		$content_by_section_temp = new Core_Model_ContentBySectionTemp();
		
		if($is_temp)
		{
			$content_by_section_data = $content_by_section_temp->find('wc_content_by_section_temp', array('id'=>$id));
			$disconnect_section = $content_by_section_temp->delete( 'wc_content_by_section_temp', array ('id' => $id) );
		}
		else
		{
			$content_by_section_data = $content_by_section->find('wc_content_by_section', array ('id' => $id));		
			$disconnect_section = $content_by_section->delete ( 'wc_content_by_section', array ('id' => $id) );
		}
		
		// succes or error messages displayed on screen
		if ($disconnect_section) 
		{
			$this->_helper->flashMessenger->addMessage ( array ('success' => $lang->translate ( 'Success disconnected' )) );
		}
		else
		{
			$this->_helper->flashMessenger->addMessage ( array ('error' => $lang->translate ( 'Errors in disconnecting data' )) );
		}
		
		if($is_temp)
		{
			if ($content_by_section_data[0]->section_temp_id)
			{
				$section_temp = new Core_Model_SectionTemp();
				$section_data = $section_temp->find('wc_section_temp',array('id'=>$content_by_section_data[0]->section_temp_id));
				$article = $section_data[0]->article;
			
				echo json_encode(array('section_id' => $section_data[0]->section_id, 'article'=>$article));
			}
			else
				echo json_encode(0);
		}
		else
		{
			if ($content_by_section_data[0]->section_id)
			{
				$section = new Core_Model_Section();
				$section_data = $section->find('wc_section',array('id'=>$content_by_section_data[0]->section_id));
				$article = $section_data[0]->article;
				
				echo json_encode(array('section_id' => $content_by_section_data[0]->section_id, 'article'=>$article));			
			}
			else
				echo json_encode(0);
		}	
	}
	
	/**
	 * Load form content
	 */
	public function loadformcontentAction() 
	{		
		$this->_helper->layout->disableLayout ();
		//translate library
		$lang = Zend_Registry::get ( 'Zend_Translate' );
		
		if ($this->getRequest ()->isPost ()) {
			//retrieved data from post
			$this->view->element_type = $this->_request->getPost ( 'element_type' );
			$this->view->number = $this->_request->getPost ( 'number' );		
		}	
	}

	/**
	 * Load form elements
	 */
	public function loadformelementAction()
	{
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender ( TRUE );
		
		if ($this->getRequest ()->isPost ()) 
		{
			$content_id = $this->_request->getPost ( 'content_id' );
			
			$form_field = new Core_Model_FormField();
			$content_temp = new Core_Model_ContentTemp();
			$form_field_temp = new Core_Model_FormFieldTemp();
			$data = '';
			
			$content_obj_temp = $content_temp->find('wc_content_temp', array('content_id' => $content_id));
			if(count($content_obj_temp)>0)
				$data = $form_field_temp->find ('wc_form_field_temp', array ('content_temp_id' => $content_obj_temp[0]->id), array('weight' => 'ASC'));
			
			if(!$data)			
				$data = $form_field->find ('wc_form_field', array ('content_id' => $content_id), array('weight' => 'ASC'));
			
			echo json_encode( $data );
		}
	}
	
	/**
	 * Redirect page on upload image or carousel content
	 */
	public function auxredirectpageAction() 
	{
		$this->_helper->viewRenderer->setNoRender ( TRUE );
		$this->_helper->redirector ( 'index', 'section_section', 'core' );
		
		echo "<script type='text/javascript'> 
					$('#cms_container').load('/core/content_content/index', {
						},function(){	
							setSectionTreeHeight();
						});
				</script>";
	}
	
	/**
	 * check if internal name already exist
	 */
	public function checkinternalnameAction()
	{		
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender ( TRUE );
		
		$content = new Core_Model_Content();
		$content_temp = new Core_Model_ContentTemp();
		
		//translate library
		$lang = Zend_Registry::get ( 'Zend_Translate' );
		
		if ($this->getRequest ()->isPost ()) 
		{
			$content_id = $this->_request->getPost ( 'content_id' );
			$internal_name = $this->_request->getPost ( 'internal_name' );
							
			if($content_id)
			{
				$data = $content->personalized_find ( 'wc_content', array (array('id','!=',$content_id), array('internal_name','==',$internal_name)));
				$data_temp = $content_temp->personalized_find ( 'wc_content_temp', array (array('content_id','!=',$content_id), array('internal_name','==',$internal_name)));
			}
			else
			{
				$data = $content->personalized_find ( 'wc_content', array (array('internal_name','==',$internal_name)));			
				$data_temp = $content_temp->personalized_find ( 'wc_content_temp', array (array('internal_name','==',$internal_name)));
			}
				
			if($data || $data_temp)
				echo json_encode ( FALSE );
			else 
				echo json_encode ( TRUE );
		}
	}
	
	/**
	 * Uploads a content file
	 */
	public function uploadfileAction()
	{
		$this->_helper->layout->disableLayout ();
		// disable autorendering for this action only:
		$this->_helper->viewRenderer->setNoRender();
	
		$formData  = $this->_request->getPost();
	
		$directory = $formData['directory'];
		$maxSize = $formData['maxSize'];
		$type = $formData['type'];

		$directory = APPLICATION_PATH. '/../'. $directory;
		if ($_FILES["content_file"]["size"] <= $maxSize && $_FILES["content_file"]["size"]!=0) 
		{
			//DETERMINING IF THE SIZE OF THE FILE UPLOADED IS VALID
			$path_parts = pathinfo($_FILES["content_file"]["name"]);
			
			if($type == 'image')
			{
				$extensions = array(0 => 'jpg', 1 => 'jpeg', 2 => 'png', 3 => 'gif', 4 => 'JPG', 5 => 'JPEG', 6 => 'PNG', 7 => 'GIF');
			}elseif($type == 'flash')
			{
				$extensions = array(0 => 'swf', 1 => 'SWF');
			}
			
			if($type == 'file')
			{
				if (is_dir($directory)) 
				{
					do {
						$tempName = 'file_' . time() . '.' . $path_parts['extension'];
					} while (file_exists($directory . $tempName));
					move_uploaded_file($_FILES["content_file"]["tmp_name"], $directory . $tempName);
					echo $tempName;
				}
				else
				{
					//ITS NOT A DIRECTORY
					echo 3;
				}				
			}
			else
			{
				if (in_array($path_parts['extension'], $extensions)) 
				{
					//DETERMINING IF THE EXTENSION OF THE FILE UPLOADED IS VALID
					if (is_dir($directory)) 
					{
						do {
							$tempName = 'file_' . time() . '.' . $path_parts['extension'];
						} while (file_exists($directory . $tempName));
						move_uploaded_file($_FILES["content_file"]["tmp_name"], $directory . $tempName);
						echo $tempName;
					} 
					else
					{
						//ITS NOT A DIRECTORY
						echo 3;
					}
				}
				else 
				{
					//INCORRECT EXTENSION
					echo 2;
				}
			}
		}
		else
		{
			//INCORRECT SIZE
			echo 1;
		}
	}
	
	/**
	 * Deletes the content file temp
	 */
	public function deletefileAction()
	{
		$this->_helper->layout->disableLayout ();
		// disable autorendering for this action only:
		$this->_helper->viewRenderer->setNoRender();
	
		$formData  = $this->_request->getPost();
	
		$file = $formData['file'];
	
		if ($file) 
		{
			if (file_exists(APPLICATION_PATH. '/../'. 'public/uploads/tmp/' . $file))
			{
				unlink(APPLICATION_PATH. '/../'. 'public/uploads/tmp/'. $file);
			}
		}
	}
}