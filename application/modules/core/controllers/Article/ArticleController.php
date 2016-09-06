<?php
/**
 *	An article has contents and belongs to a section.	
 *
 * @category   WicaWeb
 * @package    Core_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq 
 * @license    GNP
 * @version    1.0
 * @author	   David Rosales
 */

class Core_Article_ArticleController extends Zend_Controller_Action
{
    /**
     *
     * @var Core_Model_Section 
     */
    private $_modelSection=null;
    
    public function init()
    { 
        $this->_modelSection = new Core_Model_Section();
    }
    /**
	 * Loads articles' contents
	 */
	public function articledetailsAction()
	{
		$this->_helper->layout->disableLayout ();
		
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
			
		$article_id = $this->_request->getParam ( 'id' );
		$search_temp = $this->_request->getParam ('is_section_temp');
	
		//will contain article object data as array
		$section_arr = array();
		//contents per article
		$content_list_arr = array();
		
		//session stores section_id if passed as param, already has website_id
		$id = New Zend_Session_Namespace('id');
		
		if($article_id)
		{
			$article = new Core_Model_Section();
			$article_temp = new Core_Model_SectionTemp();
			
			//finds parent section data stored in db
			if($search_temp)
			{
				$parent_section = $article_temp->find('wc_section_temp', array('section_id'=>$article_id));				
			}
			else
			{
				$parent_section = $article->find('wc_section', array('id'=>$article_id));
			}
			
			foreach ($parent_section as &$sub)
			{
				if(isset($sub->section_id))
					$sub->id = $sub->section_id;
			
				if($search_temp)
				{
					$sub->temp = 1;
				}
				else
				{
					$sub->temp = 0;
				}
			
				if(GlobalFunctions::checkEditableSection($sub->id))
				{
					$sub->editable_section = 'yes';
				}
				else
				{
					$sub->editable_section = 'no';
				}
					
				if(GlobalFunctions::checkErasableSection($sub->id))
				{
					$sub->erasable_section = 'yes';
				}
				else
				{
					$sub->erasable_section = 'no';
				}
			}
			//get number of columns on template
			$section_template = new Core_Model_SectionTemplate();
			$section_template_data = $section_template->find("wc_section_template", array('id' => $parent_section[0]->section_template_id), array('name'=>'ASC'));
			$this->view->columns = $section_template_data[0]->column_number;
			
			//parent section as array to display data on view
			$section_arr = get_object_vars($parent_section[0]);
			//section_id stored in session namespace
			$id->section_id = $article_id;
			$id->section_temp = intval($search_temp);
		}		

		$this->view->info = $section_arr;
 		
		if($article_id)
		{
			$content = new Core_Model_Content();
			$content_temp = new Core_Model_ContentTemp();
			$contents_list = $content->getContentsBySection($article_id, $id->website_id);
				
			if(count($contents_list)>0)
			{
				foreach ($contents_list as $k => $cli)
				{
					$contents_list_published[] = $cli['id'];
					$content_list_arr[] = array('id' => $cli['id'],
							'section_id' => $cli['section_id'],
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
				
			$contents_list_temp = $content_temp->getTempContentsBySection($article_id, $id->website_id);
			if(count($contents_list)>0 && count($contents_list_temp)>0)
			{
				foreach ($contents_list_temp as $ps => $lit)
				{
					$contents_temp_aux[] = $lit['content_id'];
					$content_temp_arr[] = array('id' => $lit['content_id'],
							'section_id' => $lit['section_id'],
							'title' => $lit['title'],
							'type' => $lit['type'],
							'content_type_id' => $lit['content_type_id'],
							'internal_name' => $lit['internal_name'],
							'serial_cbs' => $lit['serial_cbs'],
							'column_number' => $lit['column_number'],
							'align' => $lit['align'],
							'weight' => $lit['weight'],
							'temp' => '0'
					);
				}
				
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
									'section_id' => $ctp['section_id'],
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
		
				if(count($contents_copied_arr)>0 || count($contents_temp_aux)>0)
				{
					//contents published replaced with temp	
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
					
					//temp contents missing
					$content_temp_missing = array_diff($contents_temp_aux, $contents_copied_arr);
					if(count($content_temp_missing)>0)
					{
						$move_pos = array();
					
						foreach ($content_temp_missing as $serial)
						{
							foreach ($content_temp_arr as $pos => $val)
							{
								if($val['id'] == $serial)
									$move_pos[] = $pos;
							}
						}
					
						if(count($move_pos)>0)
						{
							foreach ($move_pos as $ky)
							{
								$content_list_res[] = $content_temp_arr[$ky];
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
		}
		$this->view->contents = $content_list_arr;
		
		//allowed actions
		$cms_arr = array();		
		if($id->user_modules_actions)
		{
			foreach ($id->user_modules_actions as $k => $mod)
			{
				if($mod->module_id == '2')
				{
					$cms_arr[$mod->action_id] = array('action'=> $mod->action_name, 'title'=>$mod->action_title);
				}
			}
		}
                 //Get website_id
		$website_id = $id->website_id;
		$website_aux = new Core_Model_Website();
		$website_data = $website_aux->find('wc_website',array('id'=>$website_id));
		$this->view->website_data = $website_data;
		$this->view->cms_links = $cms_arr;
                $this->view->website_id = $id->website_id;
	}
	
	/**
	 * Saves article order
	 */
/*	public function saveorderAction()
	{
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		$this->_helper->layout->disableLayout ();
		// disable autorendering for this action
		$this->_helper->viewRenderer->setNoRender();
		
	if ($this->getRequest()->isPost())
		{
			//retrieved data from post
			$formData  = $this->_request->getPost();
							
			$session_id = New Zend_Session_Namespace('id');

			if($formData['identifier']=='sections')
			{
				$section = new Core_Model_Section();
				$section_temp = new Core_Model_SectionTemp();
				
				//Save sections		
				$order_list = GlobalFunctions::value_cleaner($formData['section_order']);
				
				$order_arr = explode(',', $order_list);					
				$count = 1;
				
				if(count($order_arr)>0)
				{
					//save sections according order
					foreach ($order_arr as $order)												
					{	
						if($order)
						{
							$options = explode('_', $order);
							$section_id = $options[1];
							$is_temp = $options[0];
							
							if($is_temp)
							{
								$stored_section_data = $section_temp->find('wc_section_temp', array('section_id'=>$section_id));
								$section_obj = $section->getNewRow('wc_section_temp');
								$section_obj->id = $stored_section_data[0]->id;
								$section_obj->section_parent_id = $stored_section_data[0]->section_parent_id;
								$section_obj->section_id = $stored_section_data[0]->section_id;
								$section_obj->section_parent_id = $stored_section_data[0]->section_parent_id;
								$section_obj->website_id = $stored_section_data[0]->website_id;
								$section_obj->section_template_id = $stored_section_data[0]->section_template_id;
								$section_obj->internal_name = GlobalFunctions::value_cleaner($stored_section_data[0]->internal_name);
								$section_obj->title = GlobalFunctions::value_cleaner($stored_section_data[0]->title);
								$section_obj->subtitle = GlobalFunctions::value_cleaner($stored_section_data[0]->subtitle);
								$section_obj->title_browser = GlobalFunctions::value_cleaner($stored_section_data[0]->title_browser);
								$section_obj->synopsis = $stored_section_data[0]->synopsis;
								$section_obj->keywords = GlobalFunctions::value_cleaner($stored_section_data[0]->keywords);
								$section_obj->type = GlobalFunctions::value_cleaner($stored_section_data[0]->type);
								$section_obj->created_by_id = $stored_section_data[0]->created_by_id;
								$section_obj->updated_by_id = $session_id->user_id;
								$section_obj->creation_date = $stored_section_data[0]->creation_date;
								$section_obj->last_update_date = date('Y-m-d h%i%s');
								$section_obj->approved = GlobalFunctions::value_cleaner($stored_section_data[0]->approved);
								$section_obj->author = GlobalFunctions::value_cleaner($stored_section_data[0]->author);
								$section_obj->publication_status = GlobalFunctions::value_cleaner($stored_section_data[0]->publication_status);
								$section_obj->feature = GlobalFunctions::value_cleaner($stored_section_data[0]->feature);
								$section_obj->highlight = GlobalFunctions::value_cleaner($stored_section_data[0]->highlight);
								$section_obj->publish_date = $stored_section_data[0]->publish_date;
								$section_obj->expire_date = $stored_section_data[0]->expire_date;
								$section_obj->show_publish_date = GlobalFunctions::value_cleaner($stored_section_data[0]->show_publish_date);
								$section_obj->rss_available = GlobalFunctions::value_cleaner($stored_section_data[0]->rss_available);
								$section_obj->external_link = GlobalFunctions::value_cleaner($stored_section_data[0]->external_link);
								$section_obj->target = GlobalFunctions::value_cleaner($stored_section_data[0]->target);
								$section_obj->comments = GlobalFunctions::value_cleaner($stored_section_data[0]->comments);
								$section_obj->external_comment_script = GlobalFunctions::value_cleaner($stored_section_data[0]->external_comment_script);
								$section_obj->display_menu = GlobalFunctions::value_cleaner($stored_section_data[0]->display_menu);
								$section_obj->homepage = GlobalFunctions::value_cleaner($stored_section_data[0]->homepage);
								$section_obj->order_number = GlobalFunctions::value_cleaner($count);
								$section_obj->article = GlobalFunctions::value_cleaner($stored_section_data[0]->article);
								$serial_id = $section->save('wc_section_temp',$section_obj);
								$count++;
							}
							else
							{
								$stored_section_data = $section->find('wc_section', array('id'=>$section_id));
								//$section_obj = $stored_section_data[0];
								$section_obj = $section->getNewRow('wc_section');
								$section_obj->id = $stored_section_data[0]->id;
								$section_obj->section_parent_id = $stored_section_data[0]->section_parent_id;
								$section_obj->website_id = $stored_section_data[0]->website_id;
								$section_obj->section_template_id = $stored_section_data[0]->section_template_id;
								$section_obj->internal_name = GlobalFunctions::value_cleaner($stored_section_data[0]->internal_name);
								$section_obj->title = GlobalFunctions::value_cleaner($stored_section_data[0]->title);
								$section_obj->subtitle = GlobalFunctions::value_cleaner($stored_section_data[0]->subtitle);
								$section_obj->title_browser = GlobalFunctions::value_cleaner($stored_section_data[0]->title_browser);
								$section_obj->synopsis = $stored_section_data[0]->synopsis;
								$section_obj->keywords = GlobalFunctions::value_cleaner($stored_section_data[0]->keywords);
								$section_obj->type = GlobalFunctions::value_cleaner($stored_section_data[0]->type);
								$section_obj->created_by_id = $stored_section_data[0]->created_by_id;
								$section_obj->updated_by_id = $session_id->user_id;
								$section_obj->creation_date = $stored_section_data[0]->creation_date;
								$section_obj->last_update_date = date('Y-m-d h%i%s');
								$section_obj->approved = GlobalFunctions::value_cleaner($stored_section_data[0]->approved);
								$section_obj->author = GlobalFunctions::value_cleaner($stored_section_data[0]->author);
								$section_obj->publication_status = GlobalFunctions::value_cleaner($stored_section_data[0]->publication_status);
								$section_obj->feature = GlobalFunctions::value_cleaner($stored_section_data[0]->feature);
								$section_obj->highlight = GlobalFunctions::value_cleaner($stored_section_data[0]->highlight);
								$section_obj->publish_date = $stored_section_data[0]->publish_date;
								$section_obj->expire_date = $stored_section_data[0]->expire_date;
								$section_obj->show_publish_date = GlobalFunctions::value_cleaner($stored_section_data[0]->show_publish_date);
								$section_obj->rss_available = GlobalFunctions::value_cleaner($stored_section_data[0]->rss_available);
								$section_obj->external_link = GlobalFunctions::value_cleaner($stored_section_data[0]->external_link);
								$section_obj->target = GlobalFunctions::value_cleaner($stored_section_data[0]->target);
								$section_obj->comments = GlobalFunctions::value_cleaner($stored_section_data[0]->comments);
								$section_obj->external_comment_script = GlobalFunctions::value_cleaner($stored_section_data[0]->external_comment_script);
								$section_obj->display_menu = GlobalFunctions::value_cleaner($stored_section_data[0]->display_menu);
								$section_obj->homepage = GlobalFunctions::value_cleaner($stored_section_data[0]->homepage);
								$section_obj->order_number = GlobalFunctions::value_cleaner($count);
								$section_obj->article = GlobalFunctions::value_cleaner($stored_section_data[0]->article);
								$serial_id = $section->save('wc_section',$section_obj);						
								$count++;
							}
						}
					}
				}
				if($session_id->section_id)
					$arr_success = array('serial'=>$session_id->section_id);
				else
					$arr_success = array('serial'=>'saved');
			}
			else			
			{
				//Save contents
				$order_list = GlobalFunctions::value_cleaner($formData['content_order']);
				$order_arr = explode(',', $order_list);
				$count = 1;
				
				//save contents according order
				if(count($order_arr)>0)
				{
					//save sections according order
					foreach ($order_arr as $order)												
					{	
						if($order)
						{
							$options = explode('_', $order);
							$content_id = $options[1];
							$is_temp = $options[0];
							
							if($is_temp)
							{
								$content_temp = new Core_Model_ContentTemp();
								$content_obj_temp = $content_temp->find('wc_content_temp', array('content_id'=>$content_id));
								
								$section_temp = new Core_Model_SectionTemp();
								$section_obj_temp = $section_temp->find('wc_section_temp', array('section_id' => $session_id->section_id));
								
								if(count($content_obj_temp)>0 && count($section_obj_temp))
								{
									$content_by_section_temp = new Core_Model_ContentBySectionTemp();
									$content_by_section_data_temp = $content_by_section_temp->find('wc_content_by_section_temp', array('section_temp_id'=> $section_obj_temp[0]->id, 'content_temp_id'=> $content_obj_temp[0]->id));
									$content_by_section_obj_temp = $content_by_section_temp->getNewRow('wc_content_by_section_temp');
									$content_by_section_obj_temp->id = $content_by_section_data_temp[0]->id;
									$content_by_section_obj_temp->content_by_section_id = $content_by_section_data_temp[0]->content_by_section_id;
									$content_by_section_obj_temp->section_temp_id = $content_by_section_data_temp[0]->section_temp_id;
									$content_by_section_obj_temp->content_temp_id = $content_by_section_data_temp[0]->content_temp_id;
									$content_by_section_obj_temp->weight = $count;
									$content_by_section_obj_temp->column_number = $formData['content_columns_'.$content_id];
									$content_by_section_obj_temp->align = $formData['content_align_'.$content_id];
									$content_by_section_temp->save('wc_content_by_section_temp',$content_by_section_obj_temp);
									$count++;
								}
							}
							else
							{
								$content_by_section = new Core_Model_ContentBySection();
								$content_by_section_data = $content_by_section->find('wc_content_by_section', array('section_id'=> $session_id->section_id, 'content_id'=> $content_id));
								$content_by_section_obj = $content_by_section->getNewRow('wc_content_by_section');
								$content_by_section_obj->id = $content_by_section_data[0]->id;
								$content_by_section_obj->section_id = $content_by_section_data[0]->section_id;
								$content_by_section_obj->content_id = $content_by_section_data[0]->content_id;
								$content_by_section_obj->weight = $count;
								$content_by_section_obj->column_number = $formData['content_columns_'.$content_id];
								$content_by_section_obj->align = $formData['content_align_'.$content_id];							
								$content_by_section->save('wc_content_by_section',$content_by_section_obj);
								$count++;
							}
						}
					}
				
					if($session_id->section_id)
						$arr_success = array('serial'=>$session_id->section_id);
					else
						$arr_success = array('serial'=>'saved');
				}
			}	
						
			echo json_encode($arr_success);
			$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved order')));					
		}
	}*/
	
	/**
	 * Creates an article according website configuration
	 */
	public function newAction()
	{				
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');

		$this->_helper->layout->disableLayout ();
		
		$section_form = New Core_Form_Article_Article();
		$section_temp = new Core_Model_SectionTemp();
		$section = New Core_Model_Section();	
			
		//searchs for stored session section_id 
		$id = New Zend_Session_Namespace('id');
		
		//website
		$website = new Core_Model_Website();
		$website_fn = $website->find('wc_website',array('id'=>$id->website_id));
		$website_db = $website_fn[0];
		$this->view->website_db = $website_db;
			
		if($id->section_id)
		{
			//finds parent section data stored in db
			if($id->section_temp)
				$parent_section = $section_temp->find('wc_section_temp', array('section_id'=>$id->section_id));
			else
				$parent_section = $section->find('wc_section', array('id'=>$id->section_id));
			
			//set hidden section parent id
			$section_parent_id = New Zend_Form_Element_Hidden('section_parent_id');
			$section_parent_id->setValue($id->section_id);
			$section_parent_id->removeDecorator('Label');
			$section_parent_id->removeDecorator('HtmlTag');
			$section_form->addElement($section_parent_id);

			$approved_frm = new Zend_Form_Element_Hidden('approved');
			$approved_frm->removeDecorator('Label');
			$approved_frm->removeDecorator('HtmlTag');
				
			$publication_status_frm = new Zend_Form_Element_Hidden('publication_status');
			$publication_status_frm->removeDecorator('Label');
			$publication_status_frm->removeDecorator('HtmlTag');
			
			if($website_db->publication_approve =='yes')
			{
				$section_data = $parent_section;
			
				if($section_data[0]->approved == 'yes' && $section_data[0]->publication_status == 'published')
				{
					if($id->user_profile == '1')
					{
						$publication_approved = 'yes';
						$publication_status = 'published';
					}
					else
					{
						$publication_approved = 'no';
						$publication_status = 'published';
					}
				}
				else
				{
					$publication_approved = 'no';
					$publication_status = 'nonpublished';
				}
			}
			else
			{
				if($id->section_temp)
				{
					$publication_approved = 'no';
					$publication_status = 'nonpublished';
				}
				else
				{
					$publication_approved = 'yes';
					$publication_status = 'published';
				}
			}
				
			$approved_frm->setValue($publication_approved);
			$section_form->addElement($approved_frm);
				
			$publication_status_frm->setValue($publication_status);
			$section_form->addElement($publication_status_frm);
			
			$section_temp = new Zend_Form_Element_Hidden('section_temp');
			$section_temp->removeDecorator('Label');
			$section_temp->removeDecorator('HtmlTag');
			$section_temp->setValue($id->section_temp);
			$section_form->addElement($section_temp);	
                        
                 }else{
                     echo '<script>alert("Por favor escoja una secci�n para ingresar un art�culo");</script>';
                     die();
                     
                 }	
		
		//website	
		$website = new Core_Model_Website();
		$website_fn = $website->find('wc_website',array('id'=>$id->website_id));
		$website_db = $website_fn[0];
		$this->view->website_db = $website_db;
		
		//template areas
		$areas = new Core_Model_Area();
		$area_options = $areas->find('wc_area', array('template_id'=>$website_db->template_id));
		$this->view->template_areas = $area_options;
			
		$this->view->form = $section_form;		
		$id->__unset('section_id');
	}
	
	/**
	 * Loads article to be updated
	 */
	public function editAction()
	{
		$this->_helper->layout->disableLayout ();
	
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		//searchs for stored session
		$session_id = New Zend_Session_Namespace('id');
		
		//website
		$website = new Core_Model_Website();
		$website_fn = $website->find('wc_website',array('id'=>$session_id->website_id));
		$website_db = $website_fn[0];
			
		$article_form = New Core_Form_Article_Article();
	
		//article_id passed in URL
		$article_id = $this->_getParam('id');
		$search_temp = $this->_getParam('is_section_temp');
		
		$session_id->section_id = $article_id;
		
		$article = new Core_Model_Section();
		$article_temp = new Core_Model_SectionTemp();
		
		$section_parent_id = NULL;
		if($search_temp)
		{
			//search in section_temp table
			$article_data = $article_temp->find('wc_section_temp', array('section_id'=>$article_id));
			if($article_data[0]->section_parent_id)
			{
				$section_parent_id = $article_data[0]->section_parent_id;
			}
			$publication_approved = 'no';
			$publication_status = 'nonpublished';
			$serial_sec = $article_data[0]->section_id;
		}
		else
		{
			$article_data = $article->find('wc_section', array('id'=>$article_id));
			$serial_sec = $article_data[0]->id;
		
			if($article_data[0]->section_parent_id)
				$section_parent_id = $article_data[0]->section_parent_id;
		
			if($website_db->publication_approve =='yes')
			{
				if($article_data[0]->approved == 'yes' && $article_data[0]->publication_status == 'published')
				{
					if($session_id->user_profile == '1')
					{
						$publication_approved = 'yes';
						$publication_status = 'published';
					}
					else
					{
						$publication_approved = 'no';
						$publication_status = 'nonpublished';
					}
				}
			}
			else
			{
				$publication_approved = 'yes';
				$publication_status = 'published';
			}
		}
		
		//section as array to populate form
		$article_arr = get_object_vars($article_data[0]);
		
		$article_arr['id'] = $serial_sec;
		$article_arr['section_parent_id'] = $section_parent_id;
		$article_arr['section_temp'] = $search_temp;
		$article_arr['approved'] = $publication_approved;
		$article_arr['publication_status'] = $publication_status;
                $article_arr['title'] = str_replace('\\','',$article_data[0]->title);
                $article_arr['internal_name'] = str_replace('\\','',$article_data[0]->internal_name);
                
		/*$article_data = $article->find('wc_section', array('id'=>$article_id));
		//article as array to populate form
		$article_arr = get_object_vars($article_data[0]);*/
	
		if($article_arr['publish_date']){
                        $article_arr['hora_inicio'] = substr($article_arr['publish_date'], -8);
			$article_arr['publish_date'] = GlobalFunctions::getFormattedDate($article_arr['publish_date']);
                }
	
		if($article_arr['expire_date']){
                        $article_arr['hora_fin'] = substr($article_arr['expire_date'], -8);
			$article_arr['expire_date'] = GlobalFunctions::getFormattedDate($article_arr['expire_date']);
                }
		
		if($article_arr['section_parent_id'])
		{
			//finds parent section data stored in db
			$parent_section = $article->find('wc_section', array('id'=>$article_arr['section_parent_id']));
			$parent_section_temp = $article_temp->find('wc_section_temp', array('section_id'=>$article_arr['section_parent_id']));
			//parent section as array to display data on view
			if(count($parent_section_temp)>0)
				$section_parent_arr = get_object_vars($parent_section_temp[0]);
			elseif(count($parent_section)>0)
				$section_parent_arr = get_object_vars($parent_section[0]);				
			$this->view->parent_section = $section_parent_arr;
		}
                $article_arr['order_highlight_value'] = $article_data[0]->order_highlight;
                $article_arr['order_feature_value'] = $article_data[0]->order_feature;
	
		$section = new Core_Model_Section();
                $section_temp = new Core_Model_SectionTemp();	
                                $subsection = new Zend_Form_Element_Button('subsection_of');
                $subsection->setAttrib('class', 'btn btn-default');

                if($section_parent_id)
                {
                $parent_temp = $section_temp->find('wc_section_temp',array('section_id'=>$section_parent_id));

                if($parent_temp)
                {	
                $subsection->setLabel($parent_temp[0]->title);
                }
                else
                {
                $parent = $section->find('wc_section',array('id'=>$section_parent_id));
                $subsection->setLabel($parent[0]->title);
                }
                }
                else
                {
                $subsection->setLabel($lang->translate('Main section'));
                }
                                //fill the form with stored data
                $article_form->populate($article_arr);
                $article_form->addElement($subsection);
                $this->view->form = $article_form;
	
		//website		
		$this->view->website_db = $website_db;
	
		//template areas
		$areas = new Core_Model_Area();
		$area_options = $areas->find('wc_area', array('template_id'=>$website_db->template_id));
		$this->view->template_areas = $area_options;	
	}
	
	/**
	 * Saves a new section or updates a section
	 */
	public function saveAction()
	{
		$this->_helper->layout->disableLayout ();
		// disable autorendering for this action 
		$this->_helper->viewRenderer->setNoRender();
		
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
				
		//retrieved data from post
		$formData  = $this->_request->getPost();

		$article = new Core_Model_Section();
		$article_temp = new Core_Model_SectionTemp();
		
		//article_id retrieved when article update
		$article_id = $formData['id'];
		$search_temp = $formData['section_temp'];
		//publication approved - yes/no
		$publication_approved = $formData['approved'];
		//publication status - published/nonpublished
		$publication_status = $formData['publication_status'];
		
		if($article_id)
		{
			if($search_temp)
			{
				$article_data = $article->find('wc_section_temp', array('section_id'=>$article_id));
			}
			else
			{
				$article_data = $article->find('wc_section', array('id'=>$article_id));
			}
			//article as array to populate form
			$article_arr = get_object_vars($article_data[0]);
		}
		
		//searchs for stored session article_id
		$id = New Zend_Session_Namespace('id');
		
		//save article
		$article_act = $article->getNewRow('wc_section');
		$article_tmp = $article_temp->getNewRow('wc_section_temp');
		
		$stored_section_data = array();
		$homepage_opt = 'no';
		
	if($publication_approved == 'yes' && $publication_status == 'published')
		{
			//publication approved is not considered in website config
			if($article_id)
			{
				//edit section
				$stored_section_data = $article->find('wc_section', array('id'=>$article_id));
				$article_act->id = $stored_section_data[0]->id;
				$article_act->website_id = $stored_section_data[0]->website_id;
			}
			else
			{
				//new section
				$article_act->website_id = $id->website_id;
			}
				
			$article_act->section_parent_id = $formData['section_parent_id'];
			$article_act->section_template_id = $formData['section_template_id'];
			$article_act->internal_name =  GlobalFunctions::value_cleaner($formData['internal_name']);
			$article_act->title = GlobalFunctions::value_cleaner($formData['title']);
			$article_act->subtitle = GlobalFunctions::value_cleaner($formData['subtitle']);
			$article_act->title_browser = GlobalFunctions::value_cleaner($formData['title_browser']);
			$article_act->synopsis = $formData['synopsis'];
			$article_act->keywords = GlobalFunctions::value_cleaner($formData['keywords']);
			$article_act->type = GlobalFunctions::value_cleaner($formData['type']);
			if(count($stored_section_data)>0)
			{
				$article_act->created_by_id = $stored_section_data[0]->created_by_id;
				$article_act->updated_by_id = $id->user_id;
				$article_act->creation_date = $stored_section_data[0]->creation_date;
				$article_act->last_update_date = date('Y-m-d h%i%s');
				$article_act->order_number = $stored_section_data[0]->order_number;
			}
			else
			{
				$article_act->created_by_id = $id->user_id;
				$article_act->updated_by_id = NULL;
				$article_act->creation_date = date('Y-m-d h%i%s');
				$article_act->last_update_date = NULL;
                            $article_act->order_number = 1;
			}
			if($publication_approved == 'yes' && $publication_status == 'published')
			{
				$article_act->approved = $publication_approved;
				$article_act->publication_status = $publication_status;
			}
			else
			{
				$article_act->approved = 'no';
				$article_act->publication_status = 'nonpublished';
			}
			$article_act->author = GlobalFunctions::value_cleaner($formData['author']);
			$article_act->feature = GlobalFunctions::value_cleaner($formData['feature']);
                        $article_act->order_feature =(isset($formData['order_feature_value']))?GlobalFunctions::value_cleaner($formData['order_feature_value']):NULL;
			$article_act->highlight = GlobalFunctions::value_cleaner($formData['highlight']);
                        $article_act->order_highlight = (isset($formData['order_highlight_value']))?GlobalFunctions::value_cleaner($formData['order_highlight_value']):NULL;
                        
                        if(!isset($formData['hora_inicio']))
                            $formData['hora_inicio'] = '';
                        
			$article_act->publish_date = GlobalFunctions::setFormattedDate($formData['publish_date']);
                        
                        if(!isset($formData['hora_fin']))
                            $formData['hora_fin'] = '';
                        
			$article_act->expire_date = GlobalFunctions::setFormattedDate($formData['expire_date']);
			$article_act->show_publish_date = GlobalFunctions::value_cleaner($formData['show_publish_date']);
			$article_act->rss_available = GlobalFunctions::value_cleaner($formData['rss_available']);
			$article_act->external_link = 'no';
			$article_act->target = 'self';
			$article_act->comments = GlobalFunctions::value_cleaner($formData['comments']);
			$article_act->external_comment_script = NULL;
			$article_act->display_menu = 'no';
			$article_act->homepage = $homepage_opt;
			$article_act->article = 'yes';
                        $article_act->archived ='no';
			$saved_section_id = $article->save('wc_section', $article_act);
				
			$id->section_id = $saved_section_id['id'];
		}
		else
		{
			if($search_temp)
			{
				if($article_id)
				{
					//edit section already in temp
					$stored_section_data = $article->find('wc_section_temp', array('section_id'=>$article_id));
						
					$article_tmp->id = $stored_section_data[0]->id;
					$article_tmp->section_id = $stored_section_data[0]->section_id;
					$article_tmp->website_id = $stored_section_data[0]->website_id;
						
					$article_tmp->section_parent_id = $formData['section_parent_id'];
					$article_tmp->section_template_id = $formData['section_template_id'];
					$article_tmp->internal_name =  GlobalFunctions::value_cleaner($formData['internal_name']);
					$article_tmp->title = GlobalFunctions::value_cleaner($formData['title']);
					$article_tmp->subtitle = GlobalFunctions::value_cleaner($formData['subtitle']);
					$article_tmp->title_browser = GlobalFunctions::value_cleaner($formData['title_browser']);
					$article_tmp->synopsis = $formData['synopsis'];
					$article_tmp->keywords = GlobalFunctions::value_cleaner($formData['keywords']);
					$article_tmp->type = GlobalFunctions::value_cleaner($formData['type']);
					if(count($stored_section_data)>0)
					{
						$article_tmp->created_by_id = $stored_section_data[0]->created_by_id;
						$article_tmp->updated_by_id = $id->user_id;
						$article_tmp->creation_date = $stored_section_data[0]->creation_date;
						$article_tmp->last_update_date = date('Y-m-d h%i%s');
						$article_tmp->order_number = $stored_section_data[0]->order_number;
					}
					else
					{
						$article_tmp->created_by_id = $id->user_id;
						$article_tmp->updated_by_id = NULL;
						$article_tmp->creation_date = date('Y-m-d h%i%s');
						$article_tmp->last_update_date = NULL;
						$article_tmp->order_number = NULL;
					}
					if($publication_approved == 'yes' && $publication_status == 'published')
					{
						$article_tmp->approved = $publication_approved;
						$article_tmp->publication_status = $publication_status;
					}
					else
					{
						$article_tmp->approved = 'no';
						$article_tmp->publication_status = 'nonpublished';
					}
					$article_tmp->author = GlobalFunctions::value_cleaner($formData['author']);
					$article_tmp->feature = GlobalFunctions::value_cleaner($formData['feature']);
                                        $article_tmp->order_feature = (isset($formData['order_feature_value']))?GlobalFunctions::value_cleaner($formData['order_feature_value']):NULL;
					$article_tmp->highlight = GlobalFunctions::value_cleaner($formData['highlight']);
                                        $article_tmp->order_highlight = (isset($formData['order_highlight_value']))?GlobalFunctions::value_cleaner($formData['order_highlight_value']):NULL;
					$article_tmp->publish_date = GlobalFunctions::setFormattedDate($formData['publish_date']);
					$article_tmp->expire_date = GlobalFunctions::setFormattedDate($formData['expire_date']);
					$article_tmp->show_publish_date = GlobalFunctions::value_cleaner($formData['show_publish_date']);
					$article_tmp->rss_available = GlobalFunctions::value_cleaner($formData['rss_available']);
					$article_tmp->external_link = 'no';
					$article_tmp->target = 'self';
					$article_tmp->comments = GlobalFunctions::value_cleaner($formData['comments']);
					$article_tmp->external_comment_script = NULL;
					$article_tmp->display_menu = 'no';
					$article_tmp->homepage = $homepage_opt;
					$article_tmp->article = 'yes';
                                        $article_tmp->archived ='no';
					$article_tmp_id = $article_temp->save('wc_section_temp',$article_tmp);
						
					$id->section_id = $article_id;
				}
				else
				{
					//new section act then copied to temp
					$article_act->website_id = $id->website_id;
					$article_act->section_parent_id = $formData['section_parent_id'];
					$article_act->section_template_id = $formData['section_template_id'];
					$article_act->internal_name =  GlobalFunctions::value_cleaner($formData['internal_name']);
					$article_act->title = GlobalFunctions::value_cleaner($formData['title']);
					$article_act->subtitle = GlobalFunctions::value_cleaner($formData['subtitle']);
					$article_act->title_browser = GlobalFunctions::value_cleaner($formData['title_browser']);
					$article_act->synopsis = $formData['synopsis'];
					$article_act->keywords = GlobalFunctions::value_cleaner($formData['keywords']);
					$article_act->type = GlobalFunctions::value_cleaner($formData['type']);
					if(count($stored_section_data)>0)
					{
						$article_act->created_by_id = $stored_section_data[0]->created_by_id;
						$article_act->updated_by_id = $id->user_id;
						$article_act->creation_date = $stored_section_data[0]->creation_date;
						$article_act->last_update_date = date('Y-m-d h%i%s');
						$article_act->order_number = $stored_section_data[0]->order_number;
					}
					else
					{
						$article_act->created_by_id = $id->user_id;
						$article_act->updated_by_id = NULL;
						$article_act->creation_date = date('Y-m-d h%i%s');
						$article_act->last_update_date = NULL;
						$article_act->order_number = NULL;
					}
					if($publication_approved == 'yes' && $publication_status == 'published')
					{
						$article_act->approved = $publication_approved;
						$article_act->publication_status = $publication_status;
					}
					else
					{
						$article_act->approved = 'no';
						$article_act->publication_status = 'nonpublished';
					}
					$article_act->author = GlobalFunctions::value_cleaner($formData['author']);
					$article_act->feature = GlobalFunctions::value_cleaner($formData['feature']);
                                        $article_act->order_feature =(isset($formData['order_feature_value']))?GlobalFunctions::value_cleaner($formData['order_feature_value']):NULL;
					$article_act->highlight = GlobalFunctions::value_cleaner($formData['highlight']);
                                        $article_act->order_highlight =(isset($formData['order_highlight_value']))?GlobalFunctions::value_cleaner($formData['order_highlight_value']):NULL;
					$article_act->publish_date = GlobalFunctions::setFormattedDate($formData['publish_date']);
					$article_act->expire_date = GlobalFunctions::setFormattedDate($formData['expire_date']);
					$article_act->show_publish_date = GlobalFunctions::value_cleaner($formData['show_publish_date']);
					$article_act->rss_available = GlobalFunctions::value_cleaner($formData['rss_available']);
					$article_act->external_link = 'no';
					$article_act->target = 'self';
					$article_act->comments = GlobalFunctions::value_cleaner($formData['comments']);
					$article_act->external_comment_script = NULL;
					$article_act->display_menu = 'no';
					$article_act->homepage = $homepage_opt;
					$article_act->article = 'yes';
                                        $article_act->archived ='no';
					$saved_section_id = $article->save('wc_section', $article_act);
						
					$id->section_id = $saved_section_id['id'];
						
					//then used in temp
					$stored_section_data = $article->find('wc_section', array('id'=>$saved_section_id['id']));
					//new section temp
					$article_tmp->section_id = $stored_section_data[0]->id;
					$article_tmp->website_id = $stored_section_data[0]->website_id;
						
					$article_tmp->section_parent_id = $formData['section_parent_id'];
					$article_tmp->section_template_id = $formData['section_template_id'];
					$article_tmp->internal_name =  GlobalFunctions::value_cleaner($formData['internal_name']);
					$article_tmp->title = GlobalFunctions::value_cleaner($formData['title']);
					$article_tmp->subtitle = GlobalFunctions::value_cleaner($formData['subtitle']);
					$article_tmp->title_browser = GlobalFunctions::value_cleaner($formData['title_browser']);
					$article_tmp->synopsis = $formData['synopsis'];
					$article_tmp->keywords = GlobalFunctions::value_cleaner($formData['keywords']);
					$article_tmp->type = GlobalFunctions::value_cleaner($formData['type']);
					if(count($stored_section_data)>0)
					{
						$article_tmp->created_by_id = $stored_section_data[0]->created_by_id;
						$article_tmp->updated_by_id = $id->user_id;
						$article_tmp->creation_date = $stored_section_data[0]->creation_date;
						$article_tmp->last_update_date = date('Y-m-d h%i%s');
						$article_tmp->order_number = $stored_section_data[0]->order_number;
					}
					else
					{
						$article_tmp->created_by_id = $id->user_id;
						$article_tmp->updated_by_id = NULL;
						$article_tmp->creation_date = date('Y-m-d h%i%s');
						$article_tmp->last_update_date = NULL;
						$article_tmp->order_number = NULL;
					}
					if($publication_approved == 'yes' && $publication_status == 'published')
					{
						$article_tmp->approved = $publication_approved;
						$article_tmp->publication_status = $publication_status;
					}
					else
					{
						$article_tmp->approved = 'no';
						$article_tmp->publication_status = 'nonpublished';
					}
					$article_tmp->author = GlobalFunctions::value_cleaner($formData['author']);
					$article_tmp->feature = GlobalFunctions::value_cleaner($formData['feature']);
                                        $article_tmp->order_feature =(isset($formData['order_feature_value']))?GlobalFunctions::value_cleaner($formData['order_feature_value']):NULL;
					$article_tmp->highlight = GlobalFunctions::value_cleaner($formData['highlight']);
                                        $article_tmp->order_highlight = (isset($formData['order_highlight_value']))?GlobalFunctions::value_cleaner($formData['order_highlight_value']):NULL;
					$article_tmp->publish_date = GlobalFunctions::setFormattedDate($formData['publish_date']);
					$article_tmp->expire_date = GlobalFunctions::setFormattedDate($formData['expire_date']);
					$article_tmp->show_publish_date = GlobalFunctions::value_cleaner($formData['show_publish_date']);
					$article_tmp->rss_available = GlobalFunctions::value_cleaner($formData['rss_available']);
					$article_tmp->external_link = 'no';
					$article_tmp->target = 'self';
					$article_tmp->comments = GlobalFunctions::value_cleaner($formData['comments']);
					$article_tmp->external_comment_script = NULL;
					$article_tmp->display_menu = 'no';
					$article_tmp->homepage = $homepage_opt;
					$article_tmp->article = 'yes';
                                    $article_act->archived ='no';
					$article_tmp_id = $article_temp->save('wc_section_temp',$article_tmp);
				}
			}
			else
			{
				if($article_id)
				{
					$stored_section_data = $article->find('wc_section', array('id'=>$article_id));
					//new section temp
					$article_tmp->section_id = $stored_section_data[0]->id;
					$article_tmp->website_id = $stored_section_data[0]->website_id;
						
					$article_tmp->section_parent_id = $formData['section_parent_id'];
					$article_tmp->section_template_id = $formData['section_template_id'];
					$article_tmp->internal_name =  GlobalFunctions::value_cleaner($formData['internal_name']);
					$article_tmp->title = GlobalFunctions::value_cleaner($formData['title']);
					$article_tmp->subtitle = GlobalFunctions::value_cleaner($formData['subtitle']);
					$article_tmp->title_browser = GlobalFunctions::value_cleaner($formData['title_browser']);
					$article_tmp->synopsis = $formData['synopsis'];
					$article_tmp->keywords = GlobalFunctions::value_cleaner($formData['keywords']);
					$article_tmp->type = GlobalFunctions::value_cleaner($formData['type']);
					if(count($stored_section_data)>0)
					{
						$article_tmp->created_by_id = $stored_section_data[0]->created_by_id;
						$article_tmp->updated_by_id = $id->user_id;
						$article_tmp->creation_date = $stored_section_data[0]->creation_date;
						$article_tmp->last_update_date = date('Y-m-d h%i%s');
						$article_tmp->order_number = $stored_section_data[0]->order_number;
					}
					else
					{
						$article_tmp->created_by_id = $id->user_id;
						$article_tmp->updated_by_id = NULL;
						$article_tmp->creation_date = date('Y-m-d h%i%s');
						$article_tmp->last_update_date = NULL;
						$article_tmp->order_number = NULL;
					}
					if($publication_approved == 'yes' && $publication_status == 'published')
					{
						$article_tmp->approved = $publication_approved;
						$article_tmp->publication_status = $publication_status;
					}
					else
					{
						$article_tmp->approved = 'no';
						$article_tmp->publication_status = 'nonpublished';
					}
					$article_tmp->author = GlobalFunctions::value_cleaner($formData['author']);
					$article_tmp->feature = GlobalFunctions::value_cleaner($formData['feature']);
                                        $article_tmp->order_feature =(isset($formData['order_feature_value']))?GlobalFunctions::value_cleaner($formData['order_feature_value']):NULL;
					$article_tmp->highlight = GlobalFunctions::value_cleaner($formData['highlight']);
                                        $article_tmp->order_highlight = (isset($formData['order_highlight_value']))?GlobalFunctions::value_cleaner($formData['order_highlight_value']):NULL;
					$article_tmp->publish_date = GlobalFunctions::setFormattedDate($formData['publish_date']);
					$article_tmp->expire_date = GlobalFunctions::setFormattedDate($formData['expire_date']);
					$article_tmp->show_publish_date = GlobalFunctions::value_cleaner($formData['show_publish_date']);
					$article_tmp->rss_available = GlobalFunctions::value_cleaner($formData['rss_available']);
					$article_tmp->external_link = 'no';
					$article_tmp->target = 'self';
					$article_tmp->comments = GlobalFunctions::value_cleaner($formData['comments']);
					$article_tmp->external_comment_script = NULL;
					$article_tmp->display_menu = 'no';
					$article_tmp->homepage = $homepage_opt;
					$article_tmp->article = 'yes';
                                        $article_act->archived ='no';
					$article_tmp_id = $article_temp->save('wc_section_temp',$article_tmp);
				}
			}
		}
		
		/*$article_obj = $article->getNewRow('wc_section');
		if($article_id)
		{
			$stored_article_data = $article->find('wc_section', array('id'=>$article_id));
			$article_obj->id = $stored_article_data[0]->id;
			$article_obj->section_parent_id = $stored_article_data[0]->section_parent_id;
			$article_obj->website_id = $stored_article_data[0]->website_id;
		}
		else
		{
			$article_obj->section_parent_id = $formData['section_parent_id'];
			$article_obj->website_id = $id->website_id;
		}
		
		$article_obj->section_template_id = $formData['section_template_id'];
		$article_obj->internal_name =  GlobalFunctions::value_cleaner($formData['internal_name']);
		$article_obj->title = GlobalFunctions::value_cleaner($formData['title']);
		$article_obj->subtitle = GlobalFunctions::value_cleaner($formData['subtitle']);
		$article_obj->title_browser = GlobalFunctions::value_cleaner($formData['title_browser']);
		$article_obj->synopsis = $formData['synopsis'];
		$article_obj->keywords = GlobalFunctions::value_cleaner($formData['keywords']);
		$article_obj->type = GlobalFunctions::value_cleaner($formData['type']);
		if($article_id)
		{
			$article_obj->created_by_id = $stored_article_data[0]->created_by_id;
			$article_obj->updated_by_id = $id->user_id;
			$article_obj->creation_date = $stored_article_data[0]->creation_date;
			$article_obj->last_update_date = date('Y-m-d h%i%s');
			$article_obj->order_number = $stored_article_data[0]->order_number;
		}
		else
		{
			$article_obj->created_by_id = $id->user_id;
			$article_obj->updated_by_id = NULL;
			$article_obj->creation_date = date('Y-m-d h%i%s');
			$article_obj->last_update_date = NULL;
			$article_obj->order_number = NULL;
		}
		$article_obj->approved = GlobalFunctions::value_cleaner($formData['approved']);
		$article_obj->author = GlobalFunctions::value_cleaner($formData['author']);
		$article_obj->publication_status = GlobalFunctions::value_cleaner($formData['publication_status']);
		$article_obj->feature = GlobalFunctions::value_cleaner($formData['feature']);
		$article_obj->highlight = GlobalFunctions::value_cleaner($formData['highlight']);
		$article_obj->publish_date = GlobalFunctions::setFormattedDate($formData['publish_date']);
		$article_obj->expire_date = GlobalFunctions::setFormattedDate($formData['expire_date']);
		$article_obj->show_publish_date = GlobalFunctions::value_cleaner($formData['show_publish_date']);
		$article_obj->rss_available = GlobalFunctions::value_cleaner($formData['rss_available']);
		$article_obj->external_link = 'no';
		$article_obj->target = 'self';
		$article_obj->comments = GlobalFunctions::value_cleaner($formData['comments']);
		$article_obj->external_comment_script = NULL;
		$article_obj->display_menu = 'no';
		$article_obj->homepage = 'no';
		$article_obj->article = 'yes';		
		//Save article data
		$article_id = $article->save('wc_section',$article_obj);*/
		
		//succes or error messages displayed on screen
		if($id->section_id)
		{
			if($publication_approved == 'yes' && $publication_status == 'published')
			{
				$section_temp_flg = 0;
			}
			elseif($publication_approved == 'no' && $publication_status == 'nonpublished')
			{
				$section_temp_flg = 1;
			}
			
			$arr_success = array('serial'=>$id->section_id, 'article_temp'=>$section_temp_flg);
			echo json_encode($arr_success);
			$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
		}
		else
		{
			$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in saving data')));
		}
	}
	
	/**
	 * Deletes an existent section
	 */
	public function deleteAction()
	{
		$this->_helper->layout->disableLayout ();
		// disable autorendering for this action
		$this->_helper->viewRenderer->setNoRender();		
		
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		//section_id passed in URL
		$id = $this->_getParam('id');
                
		$section = new Core_Model_Section();
                $section_area = $section->find('wc_section_module_area',array('section_id'=>$id));
                
		if(count($section_area)>0){
			$delete_section_area = $section->delete('wc_section_module_area',array('id'=>$section_area[0]->id));
                }
                
                $section_prints_obj = new Core_Model_SectionPrints();
		$section_prints = $section_prints_obj->find('wc_section_prints', array('section_id'=>$id));
		if(count($section_prints)>0)
		{
			$delete_section_prints = $section_prints_obj->delete('wc_section_prints', array('id'=>$section_prints[0]->id));
		}
                
               
		
		$section_temp = new Core_Model_SectionTemp();
		$section_obj_temp = $section_temp->find('wc_section_temp', array('section_id'=>$id));
		
		//delete section content published
		$section_content = new Core_Model_ContentBySection();
		$section_content_obj = $section_content->find('wc_content_by_section', array('section_id'=>$id));
		
		if(count($section_content_obj)>0)
		{
			foreach ($section_content_obj as $content)
			{
				$delete_content_section = $section_content->delete('wc_content_by_section', array('id'=>$content->id));
			}
		}

		if(count($section_obj_temp)>0)
		{
			//delete section content temp
			$section_content_temp = new Core_Model_ContentBySectionTemp();
			$section_content_obj_temp = $section_content_temp->find('wc_content_by_section_temp', array('section_temp_id'=>$section_content_obj[0]->id));
			
			if(count($section_content_obj_temp)>0)
			{
				foreach ($section_content_obj_temp as $content_temp)
				{
					$delete_content_section_tmp = $section_content_temp->delete('wc_content_by_section_temp', array('id'=>$content->id));
				}
			}
			
			//section
			$delete_section_temp = $section->delete('wc_section_temp', array('section_id'=>$id));
		}
		
                $borrar = new Core_Model_Section();
		//section
                $article = $section->find('wc_section', array('id'=>$id));
                $parent_section = $section->find('wc_section', array('id'=>$article[0]->section_parent_id));
		$delete_section = $borrar->delete('wc_section', array('id'=>$id));
		
		//succes or error messages displayed on screen
		if($delete_section)
		{					
			$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success deleted')));		
                        echo json_encode(array('serial'=>'deleted','section_id' => $parent_section[0]->id, 'section_parent'=>$parent_section[0]->section_parent_id));
			
		}
		else
		{
			$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in deleting data')));					
		}				
	}		
	
	/**
	 * Checks if section internal name already exist on db
	 */
	public function checkinternalnameAction()
	{
	
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender ( TRUE );
	
		//translate library
		$lang = Zend_Registry::get ( 'Zend_Translate' );
		
		//session
		$session = new Zend_Session_Namespace('id');
	
		if ($this->getRequest ()->isPost()) 
		{
			$section_id = $this->_request->getPost ( 'section_id' );
			$internal_name = $this->_request->getPost ( 'internal_name' );
				
			$section = new Core_Model_Section();
			$section_temp = new Core_Model_SectionTemp();			
				
			$internal_name_param = mb_strtolower($internal_name, 'UTF-8');
			
			if($section_id)
			{			
				$data = $section->personalized_find ( 'wc_section', array(array('id','!=',$section_id), array('internal_name','==',$internal_name_param), array('article','=','yes'), array('website_id','=',$session->website_id)));
				$data_temp = $section_temp->personalized_find ( 'wc_section_temp', array(array('section_id','!=',$section_id), array('internal_name','==',$internal_name_param), array('article','=','yes'), array('website_id','=',$session->website_id)));
			}
			else
			{
				$data = $section->personalized_find ( 'wc_section', array(array('internal_name','==',$internal_name_param), array('article','=','yes'), array('website_id','=',$session->website_id)));				
				$data_temp = $section_temp->personalized_find ( 'wc_section_temp', array(array('internal_name','==',$internal_name_param), array('article','=','yes'), array('website_id','=',$session->website_id)));
			}
			if($data || $data_temp)
				echo json_encode ( FALSE );
			else
				echo json_encode ( TRUE );
		}	
	}

	/**
	 * Article preview shows rendered contents in layout
	 */
	public function articlepreviewAction()
	{
		$this->_helper->layout->disableLayout ();
		
		$session = New Zend_Session_Namespace('id');
		
		//Actual website.
		$website = new Core_Model_Website();
		$website_obj = $website->find('wc_website', array('id'=>$session->website_id));
		$website_data = get_object_vars($website_obj[0]); //make an array of the object data
		
		$area_tpl = array();
		//find areas according website template config
		if(count($website_obj)>0)
		{
			$website_template = new Core_Model_WebsiteTemplate();
			$web_tpl = $website_template->find('wc_website_template',array('id'=>$website_obj[0]->template_id));
		
			$template_areas = new Core_Model_Area();
			$area_tpl = $template_areas->find('wc_area',array('template_id'=>$web_tpl[0]->id));
		}
		$this->view->areas = $area_tpl;
		
		//website template
		$template_id = $website_data['template_id'];
		$template_model = new Core_Model_WebsiteTemplate();
		$template_aux = $template_model->find('wc_website_template', array('id'=>$template_id));
		$template_data = get_object_vars($template_aux[0]); //make an array of the object data
		
		//template render
		if(count($template_data)>0)
		{
			$filename_tpl = $template_data['file_name'];
		}
		else
		{
			$filename_tpl = "";
		}
			
		if($filename_tpl)
		{
			$render = "";
			$webtemplate = fopen(APPLICATION_PATH."/modules/default/views/scripts/partials/".$filename_tpl, "r");
			if($webtemplate)
			{
				//Output a line of the file until the end is reached
				while(!feof($webtemplate))
				{
					$render.= fgets($webtemplate);
				}
				fclose($webtemplate);
			}
		}
		$this->view->templaterender = $render;
		
		//section_id
		$section_id = $this->_getParam('article_id');
		
		//section model
		$section_obj = new Core_Model_Section();
		$section_obj_temp = new Core_Model_SectionTemp();
		
		//section area for variable content
		$section_area = 0;
		//content list
		$contents_list = array();
			
		if($section_id)
		{
			//finds section data stored in db
			$section_data_arr = $section_obj_temp->find('wc_section_temp', array('section_id'=>$section_id, 'website_id'=>$session->website_id));
			$is_temp = true;
			if(count($section_data_arr)<1)
			{
				$section_data_arr = $section_obj->find('wc_section', array('id'=>$section_id, 'website_id'=>$session->website_id));
				$is_temp = false;
			}
		}
			
		//related section_id content data
		if(count($section_data_arr)>0)
		{
			foreach ($section_data_arr as $k=>$section)
			{
				//id is related to section or article when searching for section_area
				//same name $section_id guarantees sections build correctly
				if($section->article == 'no')
				{
					if(isset($section->section_id))
						$section_id = $section->section_id;
					else
						$section_id = $section->id;
				}
				else
				{
					if(isset($section->section_id))
						$section_id = $section->section_id;
					else
						$section_id = $section->id;
					
					
					$section_parent = $section_obj_temp->find('wc_section_temp', array('section_id'=>$section->section_parent_id));
					if(count($section_parent)<1)
						$section_parent = $section_obj->find('wc_section', array('id'=>$section->section_parent_id));
						
					if(count($section_parent)>0)
					{
						foreach ($section_parent as $parent)
						{
							if(isset($parent->section_id))
								$section_parent_id = $parent->section_id;
							else
								$section_parent_id = $parent->id;
						}
					}
				}
		
				//section template
				$section_template = new Core_Model_SectionTemplate();
				$section_tpl = $section_template->find('wc_section_template',array('id'=>$section->section_template_id), array('name'=>'ASC'));
				$section_filename_tpl = $section_tpl[0]->file_name;
				$column_number = $section_tpl[0]->column_number;
					
				//section area
				if($is_temp)
				{
					$section_module_area = new Core_Model_SectionModuleArea();
					$section_module_area_temp = new Core_Model_SectionModuleAreaTemp();
					$area = $section_module_area_temp->find('wc_section_module_area_temp',array('section_temp_id'=>$section_data_arr[0]->section_parent_id));
					if(count($area)<1)
						$area = $section_module_area->find('wc_section_module_area',array('section_id'=>$section_data_arr[0]->section_parent_id));
					
					if(count($area)>0)
					{
						$area_aux = new Core_Model_Area();
						$area_data = $area_aux->find('wc_area',array('id'=>$area[0]->area_id));
						if(count($area_data)>0)
						{
							$section_area = $area_data[0]->area_number;
							$section_area_name = $area_data[0]->name;
							$section_area_width = $area_data[0]->width;
						}
					}
				}
				else
				{
					$section_module_area = new Core_Model_SectionModuleArea();
					$area = $section_module_area->find('wc_section_module_area',array('section_id'=>$section_parent_id));
					if(count($area)>0)
					{
						$area_aux = new Core_Model_Area();
						$area_data = $area_aux->find('wc_area',array('id'=>$area[0]->area_id));
						if(count($area_data)>0)
						{
							$section_area = $area_data[0]->area_number;
							$section_area_name = $area_data[0]->name;
							$section_area_width = $area_data[0]->width;
						}
					}
				}
								
				//contents per section
				$content_list_arr = array();
		
				if($section_id)
				{
					$content = new Core_Model_Content();
					$content_temp = new Core_Model_ContentTemp();
					$contents_list = $content->getContentsBySection($section_id, $session->website_id);
		
					if(count($contents_list)>0)
					{
						foreach ($contents_list as $k => $cli)
						{
							$contents_list_published[] = $cli['id'];
							$content_list_arr[] = array('content_id' => $cli['id'],
									'section_id' => $cli['section_id'],
									'section' => $cli['section_name'],
									'title' => $cli['title'],
									'type' => $cli['type'],
									'content_type_id' => $cli['content_type_id'],
									'internal_name' => $cli['internal_name'],
									'serial_cbs' => $cli['serial_cbs'],
									'columns' => $cli['column_number'],
									'align' => $cli['align'],
									'weight' => $cli['weight'],
									'temp' => '0'
							);
						}
					}
		
					$contents_list_temp = $content_temp->getTempContentsBySection($section_id, $session->website_id);
		
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
									$content_list_res[] = array('content_id' => $ctp['content_id'],
											'section_id' => $ctp['section_id'],
											'section' => $ctp['section_name'],
											'title' => $ctp['title'],
											'type' => $ctp['type'],
											'content_type_id' => $ctp['content_type_id'],
											'internal_name' => $ctp['internal_name'],
											'serial_cbs' => $ctp['serial_cbs'],
											'columns' => $ctp['column_number'],
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
										if($val['content_id'] == $serial)
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
							$col_weight_articles[$row['temp'].'_'.$row['content_id']] = $row['weight'];
						}
						array_multisort($col_weight_articles, SORT_ASC, $content_list_arr);
					}
				}
		
				$contents_prw[$section_id]['order_number'] = '0';
				$contents_prw[$section_id]['filename'] = $section_filename_tpl;
				$contents_prw[$section_id]['area'] = $section_area_name;
				$contents_prw[$section_id]['area_width'] = $section_area_width;
				$contents_prw[$section_id]['column_number'] = $column_number;
				$contents_prw[$section_id]['section_title'] = $section->title;
				$contents_prw[$section_id]['section_subtitle'] = $section->subtitle;
				$contents_prw[$section_id]['content'] = $content_list_arr;
			}
		}
		
		$this->view->section_contents = $contents_prw;
		
		//profile access
		$cms_arr = array();
		if($session->user_modules_actions){
			foreach ($session->user_modules_actions as $k => $mod)
			{
				if($mod->module_id == '2')
				{
					$cms_arr[$mod->action_id] = array('action'=> $mod->action_name, 'title'=>$mod->action_title);
				}
			}
		}
		$this->view->cms_links = $cms_arr;
		
	}
	
	/**
	 * Saves content order changed in preview
	 */
	public function savepreviewAction()
	{
	//translate library
		$lang = Zend_Registry::get('Zend_Translate');
	
		$this->_helper->layout->disableLayout ();
		// disable autorendering for this action
		$this->_helper->viewRenderer->setNoRender();
                $content_by_section = new Core_Model_ContentBySection();
	
		if ($this->getRequest()->isPost())
		{
			//retrieved data from post
			$formData  = $this->_request->getPost();				
			$session_id = New Zend_Session_Namespace('id');		
						
			//Save contents
			$order_list = GlobalFunctions::value_cleaner($formData['preview_content_order']);
			$order_arr = explode(',', $order_list);
			$count = 1;

			//save contents according order
			if(count($order_arr)>0)
			{
				foreach ($order_arr as $container_cont)
				{
					//last find of section_name
					$pos_occ = strrpos($container_cont, 'content_');					
					if($pos_occ)
					{
						//next part of string after >
						$content_id = substr($container_cont, $pos_occ+8);
					}
					else
					{
						$content_id = null;
					}
													
					if($content_id && $session_id->section_temp)
					{	
						$content_temp = new Core_Model_ContentTemp();
						$content_obj_temp = $content_temp->find('wc_content_temp', array('content_id'=>$content_id));
						
						$section_temp = new Core_Model_SectionTemp();
						$section_obj_temp = $section_temp->find('wc_section_temp', array('section_id' => $session_id->section_id));
						
						
						$content_by_section_temp = new Core_Model_ContentBySectionTemp();
						
						if(count($content_obj_temp)>0 && count($section_obj_temp))
						{							
							$content_by_section_data_temp = $content_by_section_temp->find('wc_content_by_section_temp', array('section_temp_id'=> $section_obj_temp[0]->id, 'content_temp_id'=> $content_obj_temp[0]->id));							
							if(count($content_by_section_data_temp)>0)
							{
								$content_by_section_obj_temp = $content_by_section_temp->getNewRow('wc_content_by_section_temp');
								$content_by_section_obj_temp->id = $content_by_section_data_temp[0]->id;
								$content_by_section_obj_temp->content_by_section_id = $content_by_section_data_temp[0]->content_by_section_id;
								$content_by_section_obj_temp->section_temp_id = $content_by_section_data_temp[0]->section_temp_id;
								$content_by_section_obj_temp->content_temp_id = $content_by_section_data_temp[0]->content_temp_id;
								$content_by_section_obj_temp->weight = $count;
								$content_by_section_obj_temp->column_number = $formData['columns_content_'.$content_id];
								$content_by_section_obj_temp->align = $formData['align_content_'.$content_id];
								$content_by_section_temp->save('wc_content_by_section_temp',$content_by_section_obj_temp);
								$count++;
							}
							else
							{
								$content_by_section_data = $content_by_section->find('wc_content_by_section', array('section_id'=> $session_id->section_id, 'content_id'=> $content_id));
								if(count($content_by_section_data)>0)
								{
									$content_by_section_obj_temp = $content_by_section_temp->getNewRow('wc_content_by_section_temp');
									$content_by_section_obj_temp->content_by_section_id = $content_by_section_data[0]->id;
									$content_by_section_obj_temp->section_temp_id = $section_obj_temp[0]->id;
									$content_by_section_obj_temp->content_temp_id = $content_obj_temp[0]->id;
									$content_by_section_obj_temp->weight = $count;
									$content_by_section_obj_temp->column_number = $formData['columns_content_'.$content_id];
									$content_by_section_obj_temp->align = $formData['align_content_'.$content_id];
									$content_by_section_temp->save('wc_content_by_section_temp', $content_by_section_obj_temp);
									$count++;
								}
							}
						}
						elseif(count($section_obj_temp)>0)
						{
							//content							
							$content_obj_temp = $content_temp->getNewRow('wc_content_temp');
							$stored_content = $content_temp->find('wc_content', array('id'=>$content_id));
							
							if(count($stored_content)>0)
							{
								$content_obj_temp->content_id = $stored_content[0]->id;
								$content_obj_temp->content_type_id = $stored_content[0]->content_type_id;
								$content_obj_temp->website_id = $stored_content[0]->website_id;
								$content_obj_temp->internal_name = GlobalFunctions::value_cleaner ( $stored_content[0]->internal_name );
								$content_obj_temp->title = GlobalFunctions::value_cleaner ( $stored_content[0]->title );
								$content_obj_temp->created_by = $stored_content[0]->created_by;
								$content_obj_temp->creation_date = date ( 'Y-m-d h%i%s' );
								$content_obj_temp->approved = 'no';
								$content_obj_temp->status = 'active';
								// Save data
								$content_temp_id = $content_temp->save ( 'wc_content_temp', $content_obj_temp );
							}
							
							//fields
							$content_field = new Core_Model_ContentField ();
							$data_content_field = $content_field->find ( 'wc_content_field', array ('content_id' => $content_id));
							
							if(count($data_content_field)>0)
							{
								foreach ( $data_content_field as $fields)
								{
									$field = new Core_Model_Field ();
									$content_field = new Core_Model_ContentField ();
									$content_field_obj = $fields;
									$fields = $field->find ( 'wc_field', array ('id' => $content_field_obj->field_id) );
									
									$content_field_temp = new Core_Model_ContentFieldTemp();
									$content_field_obj_temp = $content_field_temp->getNewRow ( 'wc_content_field_temp' );
									$content_field_obj_temp->field_id = $fields[0]->id;
									$content_field_obj_temp->content_temp_id = $content_temp_id['id'];
									$content_field_obj_temp->content_id = $content_id;
									$content_field_obj_temp->value = $content_field_obj->value;
									$saved_content_field_temp = $content_field_temp->save ( 'wc_content_field_temp', $content_field_obj_temp );
								}
							}
							
							$content_by_section_data = $content_by_section->find('wc_content_by_section', array('section_id'=> $session_id->section_id, 'content_id'=> $content_id));							
							if(count($content_by_section_data)>0)
							{
								$content_by_section_obj_temp = $content_by_section_temp->getNewRow('wc_content_by_section_temp');								
								$content_by_section_obj_temp->content_by_section_id = $content_by_section_data[0]->id;
								$content_by_section_obj_temp->section_temp_id = $section_obj_temp[0]->id;
								$content_by_section_obj_temp->content_temp_id = $content_temp_id['id'];
								$content_by_section_obj_temp->weight = $count;
								$content_by_section_obj_temp->column_number = $formData['columns_content_'.$content_id];
								$content_by_section_obj_temp->align = $formData['align_content_'.$content_id];
								$content_by_section_temp->save('wc_content_by_section_temp', $content_by_section_obj_temp);
								$count++;
							}
						}						
					}
					elseif($content_id && !$session_id->section_temp)
					{						
						$content_by_section_data = $content_by_section->find('wc_content_by_section', array('section_id'=> $session_id->section_id, 'content_id'=> $content_id));
						$content_by_section_obj = $content_by_section->getNewRow('wc_content_by_section');
						if(count($content_by_section_data)>0)
						{
							$content_by_section_obj->id = $content_by_section_data[0]->id;
							$content_by_section_obj->section_id = $content_by_section_data[0]->section_id;
							$content_by_section_obj->content_id = $content_by_section_data[0]->content_id;
							$content_by_section_obj->weight = $count;
							$content_by_section_obj->column_number = $formData['columns_content_'.$content_id];
							$content_by_section_obj->align = $formData['align_content_'.$content_id];
							$content_by_section->save('wc_content_by_section',$content_by_section_obj);
							$count++;
						}
					}
				}				
				if($session_id->section_id)
					$arr_success = array('serial'=>$session_id->section_id);
				else
					$arr_success = array('serial'=>'saved');
			}		
	
			echo json_encode($arr_success);			
			$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved order')));
		}
	}
        public function orderAction() {
            $this->_helper->layout->disableLayout (); 
            $post = $this->getRequest()->getParams();
          //searchs for stored session article_id
           $id = New Zend_Session_Namespace('id');
		
            $section = new Core_Model_Section();
            if(isset($post['feature'])){
                //$articles_list = $section->find('wc_section', array('article'=>'yes', 'feature'=>'yes'), array('order_feature'=>'ASC'));
                $articles_list = $section->find_between('wc_section', array(array('article','=','yes'), array('feature','=','yes'), array('website_id','=',$id->website_id),array(date('Y-m-d'),'BETWEEN','publish_date','expire_date')), array('order_feature ASC'));
                $typeArticle ='feature';
            }
            if(isset($post['highlight'])){
                //$articles_list = $section->find('wc_section', array('article'=>'yes', 'highlight'=>'yes'), array('order_highlight'=>'ASC'));
                $articles_list = $section->find_between('wc_section', array(array('article','=','yes'), array('highlight','=','yes'), array('website_id','=',$id->website_id),array(date('Y-m-d'),'BETWEEN','publish_date','expire_date')), array('order_highlight ASC'));
                $typeArticle ='highlight';
            }
            if(isset($post['homepage'])){
                //$articles_list = $section->find('wc_section', array('article'=>'yes', 'homepage_down'=>'yes'), array('order_hompeage_down'=>'ASC'));
                $articles_list = $section->find_between('wc_section', array(array('article','=','yes'), array('homepage_down','=','yes'), array('website_id','=',$id->website_id),array(date('Y-m-d'),'BETWEEN','publish_date','expire_date')), array('order_hompeage_down ASC'));
                $typeArticle ='homepage';
            }
            $this->view->articles = $articles_list;
            $this->view->typeArticle =$typeArticle;
            $this->view->idArticle = $post['idArticle'];
        }
        public function saveorderAction() {
            $this->_helper->layout->disableLayout (); 
            $post = $this->getRequest()->getParams(); 
            $order_list = GlobalFunctions::value_cleaner($post['section_order']);
            $order_arr = explode(',', $order_list);
            if(count($order_arr) > 0){
                foreach ($order_arr as $orden => $item) {
                    $section_id = str_replace ('_','', $item);
                    if($post['identifier']=='feature'){
                         $data = array('order_feature'=>$orden+1); 
                         $this->_modelSection->saveOrderArticle($data, $section_id);
                        
                    }
                    if($post['identifier']=='highlight'){
                         $data = array('order_highlight'=>$orden+1); 
                         $this->_modelSection->saveOrderArticle($data, $section_id);
                        
                    }
                     if($post['identifier']=='homepage'){
                         $data = array('order_hompeage_down'=>$orden+1); 
                         $this->_modelSection->saveOrderArticle($data, $section_id);
                        
                    }
                   if($post['idArticle'] ==$section_id){
                        echo json_encode(array("opcion" =>$post['identifier'], "orden"=>($orden+1)));
                      
                   }
                    
                }
            }
            //echo $result;
            die();
            
        }
        public function ubicacionAction() {
             $this->_helper->layout->disableLayout ();
             $post = $this->getRequest()->getParams();
            
             $this->view->position = $post['position'];
             $this->view->colorTexto = ($post['colorTexto']!='')?$post['colorTexto']:'ffffff';
             
        }
        
        public function saveconfigAction() {
             $this->_helper->layout->disableLayout ();
             $post = $this->getRequest()->getParams();
             $data = array($post['opcion']=>$post['valorOpcion']);
             $this->_modelSection->saveConfigArticle($data, $post['idArticle']);
             die(); 
             
            
        }
        
}
