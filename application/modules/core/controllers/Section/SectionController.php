<?php
/**
 *	A section is defined with some data that allows it to be a container of objects.
 *	A section that uses temporality is considered as an article. 
 *
 * @category   WicaWeb
 * @package    Core_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq 
 * @license    GNP
 * @version    1.01
 * @author	David Rosales, Jose Luis Landazuri
 */

class Core_Section_SectionController extends Zend_Controller_Action
{
	/**
	 *	Loads sections tree and displays on partials sections
	 */
	public function init()
	{
		//session
		$id = New Zend_Session_Namespace('id');
            
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		$sections_arr = array();
		
		//find existent sections on db according website
		$sections_list = $section->personalized_find('wc_section', array(array('website_id','=',$id->website_id)),array('article','order_number'));
		if(count($sections_list)>0)
		{
			foreach ($sections_list as $k => &$slt)
			{
				$sections_published_arr[] = $slt->id;
				$slt->temp = 0;
			}
		}
		$sections_list_temp = $section_temp->personalized_find('wc_section_temp', array(array('website_id','=',$id->website_id)),array('article','order_number'));
		if(count($sections_list_temp)>0)
		{
			foreach ($sections_list_temp as $k => &$stp)
			{
				$stp->temp = 1;
			}
		}
		
		if(count($sections_list)>0 && count($sections_list_temp)>0)
		{
			$sections_copied_arr = array();
			//replacing sections that area eddited on temp
			foreach ($sections_list as $k => &$sbc)
			{				
				foreach ($sections_list_temp as $p => &$sct)
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
			$sections_list = $sections_list_res;
		}
		
		if($id->user_profile == '1')
		{
			//sections list array
			if(count($sections_list)>0)
			{
				foreach ($sections_list as $sec)
				{					
					$sections_arr[] = array('id'=>$sec->id,
											'temp'=>$sec->temp,
											'section_parent_id'=>$sec->section_parent_id,
											'title'=>$sec->title,
											'article'=>$sec->article,
											'order_number'=>$sec->order_number
											);
				}
			}
		}
		else
		{
			$subsection_arr = array();
			$section_aux = array();
			$user_allowed_sections_arr = explode(',',$id->user_allowed_sections);
			
			foreach ($user_allowed_sections_arr as $serial)
			{								
				foreach ($sections_list as $asc)
				{
					if($asc->id == $serial)
					{
						$section_aux[] = $asc;
					}
				}
			}			
			$available_sections = $section_aux;

			foreach ($available_sections as $sec)
			{				
				$sections_arr[] = array('id'=>$sec->id,
						'temp'=>$sec->temp,
						'section_parent_id'=>$sec->section_parent_id,
						'title'=>$sec->title,
						'article'=>$sec->article,
						'order_number'=>$sec->order_number
				);
												
				//articles
				$article_obj_temp = $section_temp->find('wc_section_temp',array('section_parent_id'=>$sec->id, 'article'=>'yes'));
				$article_obj = $section->find('wc_section',array('section_parent_id'=>$sec->id, 'article'=>'yes'));
				
				if(count($article_obj)>0)
				{
					foreach ($article_obj as $k => &$sbc)
					{
						$sbc->temp = 0;						
						if(count($article_obj_temp)>0)
						{
							foreach ($article_obj_temp as &$sct)
							{
								if($sbc->id == $sct->section_id)
								{
									$sct->temp = 1;
									$article_obj[$k] = $sct;
								}
							}
						}
					}
				}
				
				if(count($article_obj)>0)
				{
					foreach ($article_obj as $art)
					{
						if(isset($art->section_id))
							$serial_sec = $art->section_id;
						else
							$serial_sec = $art->id;
						
						$sections_arr[] = array('id'=>$serial_sec,
												'temp'=>$art->temp,
												'section_parent_id'=>$art->section_parent_id,
												'title'=>$art->title,
												'article'=>$art->article,
												'order_number'=>$art->order_number
												);
					}
				}
				
				//parent allowed sections
				if($sec->section_parent_id)
				{
					$subsection_arr[] = self::buildSectionParentTree($branch = array(), $sec->section_parent_id);
				}
			}
			
			if(count($subsection_arr)>0)
			{
				//parent sections array
				foreach ($subsection_arr as $key => $sub)
				{
					foreach ($sub as $val)
					{
						$subsection_list[$val['id']] = $val['id'];
						$subsection_list_stt[$val['id']] = $val['temp'];
					}
				}
									
				$subsection_aux = array_unique($subsection_list);				
				if(count($subsection_aux)>0)
				{
					foreach ($subsection_aux as $k => &$sbc)
					{
						foreach ($sections_arr as $sct)
						{
							if($sct['id'] == $sbc && $sct['temp'] == intval($subsection_list_stt[$sbc]))
							{								
								unset($subsection_aux[$k]);
							}
						}			
					}	
					//non repeated sections					
					foreach ($subsection_aux as $sec)
					{
						if($subsection_list_stt[$sec])						
						{
							$subsection_obj = $section_temp->find('wc_section_temp', array('section_id'=>$sec));
							$temp_subsec = 1; 
						}
						else
						{
							$subsection_obj = $section->find('wc_section', array('id'=>$sec));
							$temp_subsec = 0;
						}
						
						foreach ($subsection_obj as $obj)
						{		
							if(isset($obj->section_id))
							{
								$serial_sec = $obj->section_id;
							}
							else
							{
								$serial_sec = $obj->id;
							}	
											
							$sections_arr[] = array('id'=>$serial_sec,
													'temp'=>$temp_subsec,
													'section_parent_id'=>$obj->section_parent_id,
													'title'=>$obj->title,
													'article'=>$obj->article,
													'order_number'=>$obj->order_number
													);
						}
					}
				}
			}	
		}
		
		/******
		 * Ordering sections by article and number
		*/
		$sort_col_article = array();
		$sort_col_number = array();
		foreach ($sections_arr as $key=> $row) {
			if($row['article']=='yes')
				$sort_col_article[$key] = 1;
			else
				$sort_col_article[$key] = 2;
			$sort_col_number[$key] = $row['order_number'];
		}
		array_multisort($sort_col_article, SORT_ASC, $sort_col_number, SORT_ASC, $sections_arr);
		
		//string with sections tree html
		$html_list = '';
		if(count($sections_arr)>0)
		{
			//sections tree - parents and children as array
			$sections_tree = GlobalFunctions::buildSectionTree($sections_arr);			
			//sections tree as list
		    $html_list = GlobalFunctions::buildHtmlSectionTree($sections_tree);
		}
		
		$this->view->data = $html_list;
		
		$this->view->displaysectionbar = true;
	
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

		$this->view->cms_links = $cms_arr;
	}		
	
	/**
	 * Sections tree 
	 */
	public function sectionstreedataAction()
	{
		$this->_helper->layout->disableLayout ();
		
		//session
		$id = New Zend_Session_Namespace('id');
		
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		$sections_arr = array();
		
		//find existent sections on db according website
		$sections_list = $section->personalized_find('wc_section', array(array('website_id','=',$id->website_id)),array('article','order_number'));
		if(count($sections_list)>0)
		{
			foreach ($sections_list as $k => &$slt)
			{
				$sections_published_arr[] = $slt->id;
				$slt->temp = 0;
			}
		}
		$sections_list_temp = $section_temp->personalized_find('wc_section_temp', array(array('website_id','=',$id->website_id)),array('article','order_number'));
		if(count($sections_list_temp)>0)
		{
			foreach ($sections_list_temp as $k => &$stp)
			{
				$stp->temp = 1;
			}
		}
		
		if(count($sections_list)>0 && count($sections_list_temp)>0)
		{
			$section_copied = array();
			//replacing sections that area eddited on temp
			foreach ($sections_list as $k => &$sbc)
			{				
				foreach ($sections_list_temp as $p => &$sct)
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
			$sections_list = $sections_list_res;
		}
		
		if($id->user_profile == '1')
		{
			//sections list array
			if(count($sections_list)>0)
			{
				foreach ($sections_list as $sec)
				{					
					$sections_arr[] = array('id'=>$sec->id,
											'temp'=>$sec->temp,
											'section_parent_id'=>$sec->section_parent_id,
											'title'=>$sec->title,
											'article'=>$sec->article,
											'order_number'=>$sec->order_number
											);
				}
			}
		}
		else
		{
			$subsection_arr = array();
			$section_aux = array();
			$user_allowed_sections_arr = explode(',',$id->user_allowed_sections);
			
			foreach ($user_allowed_sections_arr as $serial)
			{								
				foreach ($sections_list as $asc)
				{
					if($asc->id == $serial)
					{
						$section_aux[] = $asc;
					}
				}
			}			
			$available_sections = $section_aux;

			foreach ($available_sections as $sec)
			{				
				$sections_arr[] = array('id'=>$sec->id,
						'temp'=>$sec->temp,
						'section_parent_id'=>$sec->section_parent_id,
						'title'=>$sec->title,
						'article'=>$sec->article,
						'order_number'=>$sec->order_number
				);
												
			//articles
				$article_obj_temp = $section_temp->find('wc_section_temp',array('section_parent_id'=>$sec->id, 'article'=>'yes'));
				$article_obj = $section->find('wc_section',array('section_parent_id'=>$sec->id, 'article'=>'yes'));
				
				if(count($article_obj)>0)
				{
					foreach ($article_obj as $k => &$sbc)
					{
						$sbc->temp = 0;
						if(count($article_obj_temp)>0)
						{
							foreach ($article_obj_temp as &$sct)
							{
								if($sbc->id == $sct->section_id)
								{
									$sct->temp = 1;
									$article_obj[$k] = $sct;
								}
							}
						}
					}
				}
				
				if(count($article_obj)>0)
				{
					foreach ($article_obj as $art)
					{
						if(isset($art->section_id))
							$serial_sec = $art->section_id;
						else
							$serial_sec = $art->id;
						
						$sections_arr[] = array('id'=>$serial_sec,
												'temp'=>$art->temp,
												'section_parent_id'=>$art->section_parent_id,
												'title'=>$art->title,
												'article'=>$art->article,
												'order_number'=>$art->order_number
												);
					}
				}
				
				//parent allowed sections
				if($sec->section_parent_id)
				{
					$subsection_arr[] = self::buildSectionParentTree($branch = array(), $sec->section_parent_id);
				}
			}
			
			if(count($subsection_arr)>0)
			{
				//parent sections array
				foreach ($subsection_arr as $key => $sub)
				{
					foreach ($sub as $val)
					{
						$subsection_list[$val['id']] = $val['id'];
						$subsection_list_stt[$val['id']] = $val['temp'];
					}
				}
									
				$subsection_aux = array_unique($subsection_list);				
				if(count($subsection_aux)>0)
				{
					foreach ($subsection_aux as $k => &$sbc)
					{
						foreach ($sections_arr as $sct)
						{
							if($sct['id'] == $sbc && $sct['temp'] == intval($subsection_list_stt[$sbc]))
							{								
								unset($subsection_aux[$k]);
							}
						}			
					}	
					//non repeated sections					
					foreach ($subsection_aux as $sec)
					{
						if($subsection_list_stt[$sec])						
						{
							$subsection_obj = $section_temp->find('wc_section_temp', array('section_id'=>$sec));
							$temp_subsec = 1; 
						}
						else
						{
							$subsection_obj = $section->find('wc_section', array('id'=>$sec));
							$temp_subsec = 0;
						}
						
						foreach ($subsection_obj as $obj)
						{		
							if(isset($obj->section_id))
							{
								$serial_sec = $obj->section_id;
							}
							else
							{
								$serial_sec = $obj->id;
							}	
											
							$sections_arr[] = array('id'=>$serial_sec,
													'temp'=>$temp_subsec,
													'section_parent_id'=>$obj->section_parent_id,
													'title'=>$obj->title,
													'article'=>$obj->article,
													'order_number'=>$obj->order_number
													);
						}
					}
				}
			}	
		}
		
		/******
		 * Ordering sections by article and number
		*/
		$sort_col = array();
		foreach ($sections_arr as $key=> $row) {
			if($row['article']=='yes')
				$sort_col_article[$key] = 1;
			else
				$sort_col_article[$key] = 2;
			$sort_col_number[$key] = $row['order_number'];
		}
		array_multisort($sort_col_article, SORT_ASC, $sort_col_number, SORT_ASC, $sections_arr);
		
		//string with sections tree html
		$html_list = '';
		if(count($sections_arr)>0)
		{
			//sections tree - parents and children as array
			$sections_tree = GlobalFunctions::buildSectionTree($sections_arr);			
			//sections tree as list
		    $html_list = GlobalFunctions::buildHtmlSectionTree($sections_tree);
		}
		
		$this->view->sections = $html_list;
		
		$this->view->displaysectionbar = true;
	
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

		$this->view->cms_links = $cms_arr;
	}
	
	/**
	 * Builds parent sections tree
	 */
	public static function buildSectionParentTree(&$branch = array(), $parentId)
	{		
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		
		$subsection_temp_obj = $section_temp->find('wc_section_temp',array('section_id'=>$parentId));
		if(count($subsection_temp_obj)>0)
		{
			$serial_sec = $subsection_temp_obj[0]->section_parent_id;
			$temp_stt = 1;
		}
		else
		{		
			$subsection_obj = $section->find('wc_section',array('id'=>$parentId));
			$serial_sec = $subsection_obj[0]->section_parent_id;
			$temp_stt = 0;
		}
		
		$branch[] = array(	'id'=>$parentId, 
							'temp'=>$temp_stt
						);
		
		if ($serial_sec) 
		{			
			$children = self::buildSectionParentTree($branch, $serial_sec);			
		}				
		return $branch;
	}
	
	/**
	 * Core Home displays layout
	 */
	public function indexAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		//session
		$id = New Zend_Session_Namespace('id');
		$id->__unset('section_id');
		$id->__unset('section_temp');
	}
	
	/**
	 * Loads sections to be ordered
	 */
	public function sectionlistAction()
	{		
		$this->_helper->layout->disableLayout ();
				
		//session stores website_id
		$id = New Zend_Session_Namespace('id');
				
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		
		$available_sections = $section->personalized_find('wc_section', array(array('website_id','=',$id->website_id)),array('article','order_number'));
		if(count($available_sections)>0)
		{
			foreach ($available_sections as $k => &$slt)
			{
				$sections_published_arr[] = $slt->id;
				$slt->temp = 0;
			}
		}
		$available_sections_temp = $section_temp->personalized_find('wc_section_temp', array(array('website_id','=',$id->website_id)),array('article','order_number'));
		if(count($available_sections_temp)>0)
		{
			foreach ($available_sections_temp as $k => &$stp)
			{
				$section_temp_pos[] = $k;
				$stp->temp = 1;
			}
		}
		$sections_total_num = 0;
		$assigned_sections_num = 0;
		
		if(count($available_sections)>0 && count($available_sections_temp)>0)
		{
			$sections_copied_arr = array();
			//replacing sections that area eddited on temp
			foreach ($available_sections as $k => &$sbc)
			{
				foreach ($available_sections_temp as $p => &$sct)
				{
					if($sbc->id == $sct->section_id)
					{
						$sct->id = $sct->section_id;
						$available_sections_res[] = $sct;
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
						$available_sections_res[] = $section_obj[0];
					}
				}
			}
			$available_sections = $available_sections_res;
		}
		
		//available parent section list according profile
		if($id->user_profile == '1')
		{				 
			if(count($available_sections)>0)
			{
				foreach ($available_sections as &$sli)
				{
					if(!$sli->section_parent_id)
						$sections_total_num++;

					if(GlobalFunctions::checkEditableSection($sli->id))
					{
						$sli->editable_section = 'yes';
					}
					else
					{
						$sli->editable_section = 'no';
					}

					if(GlobalFunctions::checkErasableSection($sli->id))
					{
						$sli->erasable_section = 'yes';
					}
					else
					{
						$sli->erasable_section = 'no';
					}
					
					if($sli->temp)
					{
						if($sli->section_parent_id == NULL)
						{
							$section_aux[] = $sli;
							$assigned_sections_num++;
						}
					}
					else
					{
						if($sli->section_parent_id == NULL)
						{
							$section_aux[] = $sli;
							$assigned_sections_num++;
						}
					}					
				}
				$available_sections = $section_aux;				
			}
		}
		else
		{
			foreach ($available_sections as &$sli)
			{
				if(!$sli->section_parent_id)
					$sections_total_num++;
			}
			
			$section_aux = array();
			$user_allowed_sections_arr = explode(',',$id->user_allowed_sections);
				
			foreach ($user_allowed_sections_arr as $serial)
			{		
				foreach ($available_sections as $asc)
				{
					if($asc->id == $serial)
					{
						if($asc->section_parent_id == NULL)
						{
							$section_aux[] = $asc;
							$assigned_sections_num++;
						}
					}
				}
			}			
			$available_sections = $section_aux;

			if(count($available_sections)>0)
			{
				foreach ($available_sections as &$slt)
				{
					if(GlobalFunctions::checkEditableSection($slt->id))
					{
						$slt->editable_section = 'yes';
					}
					else
					{
						$slt->editable_section = 'no';
					}
			
					if(GlobalFunctions::checkErasableSection($slt->id))
					{
						$slt->erasable_section = 'yes';
					}
					else
					{
						$slt->erasable_section = 'no';
					}
				}
			}
		}
		
		$this->view->total_sec = $sections_total_num;
		$this->view->assigned_sec = $assigned_sections_num;
		
		/******
		 * Ordering sections by order_number
		*/
		$sort_col_number = array();
		foreach ($available_sections as $row)
		{
			$sort_col_number[$row->id] = $row->order_number;
		}
		array_multisort($sort_col_number, SORT_ASC, $available_sections);
						
		$this->view->section = $available_sections;
	}
	
	/**
	 * Loads section and its contents
	 */
	public function sectiondetailsAction()
	{
		$this->_helper->layout->disableLayout ();
		
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		//session
		$id = New Zend_Session_Namespace('id');
		
		//Get website_id
		$website_id = $id->website_id;
		$website_aux = new Core_Model_Website();
		$website_data = $website_aux->find('wc_website',array('id'=>$website_id));
		$this->view->website_data = $website_data;
		
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		
		$section_id = $this->_request->getParam ('id');
		
		$section_chk = $section_temp->find('wc_section_temp', array('section_id'=>$section_id));
		if(count($section_chk)>0)
		{
			$search_temp = 1;
		}
		else
		{
			$search_temp = $this->_request->getParam ('is_section_temp');
		}
		$this->view->website_id = $website_id;
		//will contain section object data as array
		$section_arr = array();
					
		if($section_id)
		{
			//flag used to describe if something is temp
			$using_temp = false;
			
			//finds parent section data stored in db
			if($search_temp)
			{
				$parent_section = $section_temp->find('wc_section_temp', array('section_id'=>$section_id));	
				$using_temp = true;
			}
			else
			{
				$parent_section = $section->find('wc_section', array('id'=>$section_id));
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
			$section_template_data = $section_template->find("wc_section_template", array('id' => $parent_section[0]->section_template_id));
			$this->view->columns = $section_template_data[0]->column_number;

			//parent section as array to display data on view
			$section_arr = get_object_vars($parent_section[0]);
			//section_id stored in session namespace
			$id->section_id = $section_id;
			$id->section_temp = $search_temp;			
			
			//subsections
			$subsections_list = $section->find('wc_section', array('section_parent_id'=>$section_id, 'article'=>'no'), array('order_number'=>'ASC'));
			if(count($subsections_list)>0)
			{
				foreach ($subsections_list as &$sll)
				{
					$sll->temp = 0;
				}
			}
			//if a subsection is being eddited and stored in temp
			$subsections_list_temp = $section->find('wc_section_temp', array('section_parent_id'=>$section_id, 'article'=>'no'), array('order_number'=>'ASC'));
			if(count($subsections_list_temp)>0)
			{
				foreach ($subsections_list_temp as &$slp)
				{
					$slp->temp = 1;
					$using_temp = true;
				}
			}		
			
			if(count($subsections_list)>0 && count($subsections_list_temp)>0)
			{
				foreach ($subsections_list as $k => &$sbc)
				{
					foreach ($subsections_list_temp as &$sct)
					{
						if($sbc->id == $sct->section_id)
						{							
							$sct->id = $sct->section_id;
							$subsections_list[$k] = $sct;
						}
					}
				}
			}
						
			if(count($subsections_list)>0)
			{
				foreach ($subsections_list as &$sub)
				{
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
					
					$sort_col_number[$sub->id] = $sub->order_number;
				}
				/******
				 * Ordering sections by order_number
				*/
				array_multisort($sort_col_number, SORT_ASC, $subsections_list);
			}
					
			$this->view->subsections = $subsections_list;
			
			//articles
			if($search_temp)
			{
				$articles_list = $section->find('wc_section', array('section_parent_id'=>$parent_section[0]->section_id,'article'=>'yes'), array('order_number'=>'ASC'));
			}
			else
			{
				$articles_list = $section->find('wc_section', array('section_parent_id'=>$parent_section[0]->id,'article'=>'yes'), array('order_number'=>'ASC'));
			}
			
			if(count($articles_list)>0)
			{
				foreach ($articles_list as $k => &$ali)
				{
					$ali->temp = 0;
				}
			}
			//if an article is being eddited and stored in temp
			$articles_list_temp = $section_temp->find('wc_section_temp', array('section_parent_id'=>$section_id,'article'=>'yes'), array('order_number'=>'ASC'));
			if(count($articles_list_temp)>0)
			{
				foreach ($articles_list_temp as $k => &$alt)
				{
					$alt->temp = 0;
				}
			}
			
			if(count($articles_list)>0 && count($articles_list_temp)>0)
			{
				foreach ($articles_list as $k => &$sbc)
				{
					foreach ($articles_list_temp as &$sct)
					{
						if($sbc->id == $sct->section_id)
						{
							$sct->temp = 1;
							$articles_list[$k] = $sct;
							$using_temp = true;
						}
					}
				}
			}
			
			if(count($articles_list)>0)
			{
				foreach ($articles_list as &$art)
				{							
					if(isset($art->section_id))
					{
						$article_id = $art->section_id;
						$art->id = $art->section_id;
					}
					else
						$article_id = $art->id;
					
					$art->title = GlobalFunctions::truncate($art->title, 100, false);
					$art->synopsis = GlobalFunctions::truncate($art->synopsis, 240, false);
										
					if(GlobalFunctions::checkEditableSection($art->id))
					{
						$art->editable_section = 'yes';
					}
					else
					{
						$art->editable_section = 'no';
					}
					
					if(GlobalFunctions::checkErasableSection($art->id))
					{
						$art->erasable_section = 'yes';
					}
					else
					{
						$art->erasable_section = 'no';
					}
					
					//search for an image 
					$content = new Core_Model_Content();
					$pictures_list = $content->getContentsBySection($article_id, $id->website_id, null, 2);
					if(count($pictures_list)>0)
					{
						$art->image = $pictures_list[0];
					}
					else 
					{
						$art->image = null;
					}
					
					$col_weight[$art->id] = $art->order_number;
				}
				
				/******
				 * Ordering contents by weight
				*/
				array_multisort($col_weight, SORT_ASC, $articles_list);
			}
			
			$this->view->articles = $articles_list;
		}
		
		$this->view->info = $section_arr;
		
		//contents per section
		$content_list_arr = array();
		
		if($section_id)
		{
			$content = new Core_Model_Content();
			$content_temp = new Core_Model_ContentTemp();
			$contents_list = $content->getContentsBySection($section_id, $id->website_id);
			
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
			
			$contents_list_temp = $content_temp->getTempContentsBySection($section_id, $id->website_id);			
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
							$serial = $ctp['content_id'];
							$content_list_res[] = array('id' => $serial,
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
							$using_temp = true;
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
					$col_weight_articles[$row['temp'].'_'.$row['id']] = $row['weight'];
				}
				array_multisort($col_weight_articles, SORT_ASC, $content_list_arr);
			}
		}		
 		$this->view->contents = $content_list_arr;
 		$this->view->available_temp = $using_temp;
                $this->view->website_id = $id->website_id;
 		
	}
	
	/**
	 * Saves section or content order
	 */
	public function saveorderAction()
	{
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		$this->_helper->layout->disableLayout ();
		//disable autorendering for this action
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
				if(isset($formData['section_order']))				
					$order_list = GlobalFunctions::value_cleaner($formData['section_order']);
				elseif(isset($formData['subsection_order']))
					$order_list = GlobalFunctions::value_cleaner($formData['subsection_order']);
				
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
                                                                $section_obj->display_menu2 = GlobalFunctions::value_cleaner($stored_section_data[0]->display_menu2);
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
                                                                $section_obj->display_menu2 = GlobalFunctions::value_cleaner($stored_section_data[0]->display_menu2);
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
					$arr_success = array('serial'=>$session_id->section_id, 'temp'=>$session_id->section_temp);
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
					$content_owner = GlobalFunctions::value_cleaner($formData['content_owner']);
				
					if($session_id->section_id)
						$arr_success = array('serial'=>$session_id->section_id, 'temp'=>intval($session_id->section_temp), 'owner'=>$content_owner);
					else
						$arr_success = array('serial'=>'saved');
				}
			}	
						
			echo json_encode($arr_success);
			$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved order')));					
		}
	}
	
	/**
	 * Creates a section according website configuration
	 */
	public function newAction()
	{				
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');

		$this->_helper->layout->disableLayout ();
		
		$section_form = New Core_Form_Section_Section();	
			
		//searchs for stored session section_id 
		$id = New Zend_Session_Namespace('id');
		//section
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		
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
			//parent section as array to display data on view
			$section_arr = get_object_vars($parent_section[0]);
			$this->view->parent_section = $section_arr;
			
			//set hidden section parent id
			$section_parent_id = New Zend_Form_Element_Hidden('section_parent_id');
			$section_parent_id->setValue($id->section_id);
			$section_parent_id->removeDecorator('Label');
			$section_parent_id->removeDecorator('HtmlTag');
			$section_form->addElement($section_parent_id);	

			//set hidden parent show menu
			$parent_show_menu = New Zend_Form_Element_Hidden('parent_show_menu');
			$parent_show_menu->setValue($section_arr['display_menu']);
			$parent_show_menu->removeDecorator('Label');
			$parent_show_menu->removeDecorator('HtmlTag');
			$section_form->addElement($parent_show_menu);
                        
                        //set hidden parent show menu2
			$parent_show_menu2 = New Zend_Form_Element_Hidden('parent_show_menu2');
			$parent_show_menu2->setValue($section_arr['display_menu2']);
			$parent_show_menu2->removeDecorator('Label');
			$parent_show_menu2->removeDecorator('HtmlTag');
			$section_form->addElement($parent_show_menu2);
			
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
		}
		else
		{
			$section_temp = new Zend_Form_Element_Hidden('section_temp');
			$section_temp->removeDecorator('Label');
			$section_temp->removeDecorator('HtmlTag');
			
			if($website_db->publication_approve =='yes')
			{
				if($id->user_profile == '1')
				{
					$section_temp->setValue('0');
				}
				else
				{
					$section_temp->setValue('1');
				}
			}
			else
			{
				$section_temp->setValue('0');
			}
			$section_form->addElement($section_temp);
		}
		
		//Actual section homepage
		$section_act = array();
		$section_hom = $section->find('wc_section',array('website_id'=>$id->website_id, 'homepage'=>'yes'));
		if(count($section_hom)>0)
			$section_act = $section_hom[0];
		$this->view->section_act = $section_act;
		
		//template areas
		$areas = new Core_Model_Area();
		$area_options = $areas->find('wc_area', array('template_id'=>$website_db->template_id));
		$this->view->template_areas = $area_options;
			
		$this->view->form = $section_form;
	}
	
	/**
	 * Loads section to be updated
	 */
	public function editAction()
	{
		$this->_helper->layout->disableLayout();
	
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		//searchs for stored session
		$session_id = New Zend_Session_Namespace('id');
		
		//website
		$website = new Core_Model_Website();
		$website_fn = $website->find('wc_website',array('id'=>$session_id->website_id));
		$website_db = $website_fn[0];
				
		//section_id
		$section_id = $this->_getParam('id');
		$search_temp = $this->_getParam('is_section_temp');
	
		//session 
		$session_id->section_id = $section_id;
		
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();		
		
		$section_parent_id = NULL;
		if($search_temp)
		{
			//search in section_temp table
			$section_data = $section_temp->find('wc_section_temp', array('section_id'=>$section_id));
			if($section_data[0]->section_parent_id)
			{
				$section_parent_id = $section_data[0]->section_parent_id;
			}
			$publication_approved = 'no';
			$publication_status = 'nonpublished';
			$serial_sec = $section_data[0]->section_id;
		}
		else
		{
			$section_data = $section->find('wc_section', array('id'=>$section_id));		
			$serial_sec = $section_data[0]->id;
			
			if($section_data[0]->section_parent_id)
				$section_parent_id = $section_data[0]->section_parent_id;
			
			if($website_db->publication_approve =='yes')
			{
				if($section_data[0]->approved == 'yes' && $section_data[0]->publication_status == 'published')
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
		
		$section_form = New Core_Form_Section_Section();
		//section as array to populate form
		$section_arr = get_object_vars($section_data[0]);
	
		$section_arr['id'] = $serial_sec;		
		$section_arr['section_parent_id'] = $section_parent_id;
		$section_arr['section_temp'] = $search_temp;			
		$section_arr['approved'] = $publication_approved;
		$section_arr['publication_status'] = $publication_status;
		
		if($section_arr['publish_date']){
                         $section_arr['hora_inicio']= substr($section_arr['publish_date'], -8);
			$section_arr['publish_date'] = GlobalFunctions::getFormattedDate($section_arr['publish_date']);
                        
                }
	
		if($section_arr['expire_date']){
                        $section_arr['hora_fin']= substr($section_arr['expire_date'], -8);
			$section_arr['expire_date'] = GlobalFunctions::getFormattedDate($section_arr['expire_date']);
                }
	
		if($section_arr['external_link'])
			$section_arr['link'] = 'yes';
		else
			$section_arr['link'] = 'no';
		
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
		$section_form->addElement($subsection);
		
		//menu
		$section_arr['menu'] = $section_data[0]->display_menu;
                //menu2
		$section_arr['menu2'] = $section_data[0]->display_menu2;
	
		//section images
		$section_image = new Core_Model_SectionImage();
		if($search_temp)
		{			
			$section_image_arr = $section_image->find('wc_section_image_temp', array('section_temp_id'=>$section_data[0]->id));
		}
		else
		{
			$section_image_arr = $section_image->find('wc_section_image', array('section_id'=>$section_id));
		}				
		$images_num = count($section_image_arr);
	
		if (!empty($section_image_arr))
		{
			//load the current image
			foreach ($section_image_arr as $k => $obj)
			{				
                                $section_arr['name_img_'.($obj->numImageSection)] = $obj->name;
				$section_arr['file_img_'.($obj->numImageSection)] = $obj->file_name;
				$section_arr['id_img_'.($obj->numImageSection)] = $obj->id;
	
				$image_preview = New Zend_Form_Element_Image('imageprw_'.($obj->numImageSection));
				$image_preview->setImage('/uploads/content/'.$obj->file_name);
				$image_preview->setLabel($obj->name);
				$image_preview->setAttrib('style', 'width:150px;');
				$image_preview->setAttrib('onclick', 'return false;');
				$section_form->addElement($image_preview);
			}
		}
	
		//section area
		$section_areas = new Core_Model_SectionModuleArea();
		$section_areas_temp = new Core_Model_SectionModuleAreaTemp();
                $idArea = null;
		
		if($search_temp)
		{
			$section_area = $section_areas_temp->find('wc_section_module_area_temp', array('section_temp_id'=>$section_data[0]->id));
		}
		else
		{
			$section_area = $section_areas->find('wc_section_module_area', array('section_id'=>$section_id));
		}
		
		//areas
		$areas = new Core_Model_Area();
               
		
		if(count($section_area)>0)
		{			
			$number = $areas->find('wc_area',array('id'=>$section_area[0]->area_id));
                        $section_arr['section_area'] = $number[0]->area_number;
			$section_arr['section_area_id'] = $section_area[0]->id;
                        $idArea = $number[0]->id;
                        
		}
                $this->view->idArea = $idArea;
	
		if($section_arr['section_parent_id'])
		{
			//finds parent section data stored in db
			$parent_section = $section->find('wc_section', array('id'=>$section_arr['section_parent_id']));			
			$parent_section_temp = $section_temp->find('wc_section_temp', array('section_id'=>$section_arr['section_parent_id']));
			//parent section as array to display data on view
			if(count($parent_section_temp)>0)
				$section_parent_arr = get_object_vars($parent_section_temp[0]);
			elseif(count($parent_section)>0)
				$section_parent_arr = get_object_vars($parent_section[0]);
			
			$this->view->parent_section = $section_parent_arr;
			$section_arr['parent_show_menu'] = $section_parent_arr['display_menu'];
                        $section_arr['parent_show_menu2'] = $section_parent_arr['display_menu2'];
		}
	
		//fill the form with stored data		
		$section_form->populate($section_arr);
		$this->view->form = $section_form;
	
		//website		
		$this->view->website_db = $website_db;
	
		//Actual section homepage
		$section_act = array();
		$section_hom = $section->find('wc_section',array('website_id'=>$session_id->website_id, 'homepage'=>'yes'));
		if(count($section_hom)>0)
			$section_act = $section_hom[0];
		$this->view->section_act = $section_act;
		
		//template areas		
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

		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		
		//section_id section_id retrieved when section update
		$section_id = $formData['id'];
		$search_temp = $formData['section_temp'];
		//publication approved - yes/no
		$publication_approved = $formData['approved'];
		//publication status - published/nonpublished
		$publication_status = $formData['publication_status'];
		
		if($section_id)
		{
			//section images
			$section_image = new Core_Model_SectionImage();
			
			if($search_temp)
			{			
				$section_data = $section_temp->find('wc_section_temp', array('section_id'=>$section_id));
				$section_image_arr = $section_image->find('wc_section_image_temp', array('section_temp_id'=>$section_data[0]->id));				
			}			
			else
			{								
				$section_data = $section->find('wc_section', array('id'=>$section_id));
				$section_image_arr = $section_image->find('wc_section_image', array('section_id'=>$section_id));
			}
			
			//section as array to populate form
			$section_arr = get_object_vars($section_data[0]);			
			
			$images_num = count($section_image_arr);			
			if (!empty($section_image_arr))
			{
				//load the current image
				foreach ($section_image_arr as $k => $obj)
				{
					$section_arr['name_img_'.($k+1)] = $obj->name;
					$section_arr['file_img_'.($k+1)] = $obj->file_name;
					$section_arr['id_img_'.($k+1)] = $obj->id;			
				}
			}
		}
                
		//searchs for stored session section_id
		$id = New Zend_Session_Namespace('id');
		
		//check if there is already a section as homepage
		if(GlobalFunctions::value_cleaner($formData['homepage'])=='yes')
		{
			//check for publication approved option
			if($publication_approved == 'yes' && $publication_status == 'published')
			{
				$section_home = $section->personalized_find('wc_section', array(array('homepage','=','yes'), array('website_id','=',$id->website_id)));
				if($section_home)
				{
					$section_nothome = $section->getNewRow('wc_section');
					$section_nothome->id = $section_home[0]->id;
					$section_nothome->section_parent_id = $section_home[0]->section_parent_id;
					$section_nothome->website_id = $section_home[0]->website_id;
					$section_nothome->section_template_id = $section_home[0]->section_template_id;
					$section_nothome->internal_name = GlobalFunctions::value_cleaner($section_home[0]->internal_name);
					$section_nothome->title = GlobalFunctions::value_cleaner($section_home[0]->title);
					$section_nothome->subtitle = GlobalFunctions::value_cleaner($section_home[0]->subtitle);
					$section_nothome->title_browser = GlobalFunctions::value_cleaner($section_home[0]->title_browser);
					$section_nothome->synopsis = $section_home[0]->synopsis;
					$section_nothome->keywords = GlobalFunctions::value_cleaner($section_home[0]->keywords);
					$section_nothome->type = GlobalFunctions::value_cleaner($section_home[0]->type);
					$section_nothome->created_by_id = $section_home[0]->created_by_id;
					$section_nothome->updated_by_id = $id->user_id;
					$section_nothome->creation_date = $section_home[0]->creation_date;
					$section_nothome->last_update_date = date('Y-m-d h%i%s');
					$section_nothome->approved = GlobalFunctions::value_cleaner($section_home[0]->approved);
					$section_nothome->author = GlobalFunctions::value_cleaner($section_home[0]->author);
					$section_nothome->publication_status = GlobalFunctions::value_cleaner($section_home[0]->publication_status);
					$section_nothome->order_number = GlobalFunctions::value_cleaner($section_home[0]->order_number);
					$section_nothome->feature = GlobalFunctions::value_cleaner($section_home[0]->feature);
					$section_nothome->highlight = GlobalFunctions::value_cleaner($section_home[0]->highlight);
					$section_nothome->publish_date = $section_home[0]->publish_date;
					$section_nothome->expire_date = $section_home[0]->expire_date;
					$section_nothome->show_publish_date = GlobalFunctions::value_cleaner($section_home[0]->show_publish_date);
					$section_nothome->rss_available = GlobalFunctions::value_cleaner($section_home[0]->rss_available);
					$section_nothome->external_link = GlobalFunctions::value_cleaner($section_home[0]->external_link);
					$section_nothome->target = GlobalFunctions::value_cleaner($section_home[0]->target);
					$section_nothome->comments = GlobalFunctions::value_cleaner($section_home[0]->comments);
					$section_nothome->external_comment_script = GlobalFunctions::value_cleaner($section_home[0]->external_comment_script);
					$section_nothome->display_menu = GlobalFunctions::value_cleaner($section_home[0]->display_menu);
                                        $section_nothome->display_menu2 = GlobalFunctions::value_cleaner($section_home[0]->display_menu2);
					$section_nothome->homepage = 'no';
					$section_nothome->article = $section_home[0]->article;
					//Save data
					$section_nothome_id = $section->save('wc_section',$section_nothome);
				}
			}
			$homepage_opt = 'yes';
		}
		else
		{
			$available_sections = $section->find('wc_section', array('website_id'=>$id->website_id));
			if(count($available_sections)==0)
				$homepage_opt = 'yes';
			else
				$homepage_opt = 'no';
		}
		
		//save section		
		$section_act = $section->getNewRow('wc_section');
		$section_tmp = $section->getNewRow('wc_section_temp');
		
		$stored_section_data = array();
                
                $order_sections = $section->find('wc_section', array('section_parent_id'=>$formData['section_parent_id'], 'article'=>'no'));
                $order_number = count($order_sections) + 1;
		
		if($publication_approved == 'yes' && $publication_status == 'published')	
		{			
			//publication approved is not considered in website config
			if($section_id)
			{		
				//edit section				
				$stored_section_data = $section->find('wc_section', array('id'=>$section_id));
				$section_act->id = $stored_section_data[0]->id;
				$section_act->website_id = $stored_section_data[0]->website_id;
			}
			else
			{
				//new section
				$section_act->website_id = $id->website_id;
			}
			
			$section_act->section_parent_id = $formData['section_parent_id'];
			$section_act->section_template_id = $formData['section_template_id'];
			$section_act->internal_name =  GlobalFunctions::value_cleaner($formData['internal_name']);
			$section_act->title = GlobalFunctions::value_cleaner($formData['title']);
			$section_act->subtitle = GlobalFunctions::value_cleaner($formData['subtitle']);
			$section_act->title_browser = GlobalFunctions::value_cleaner($formData['title_browser']);
			$section_act->synopsis = $formData['synopsis'];
			$section_act->keywords = GlobalFunctions::value_cleaner($formData['keywords']);
			$section_act->type = GlobalFunctions::value_cleaner($formData['type']);
			if(count($stored_section_data)>0)
			{
				$section_act->created_by_id = $stored_section_data[0]->created_by_id;
				$section_act->updated_by_id = $id->user_id;
				$section_act->creation_date = $stored_section_data[0]->creation_date;
				$section_act->last_update_date = date('Y-m-d h%i%s');
				$section_act->order_number = $stored_section_data[0]->order_number;
			}
			else
			{
				$section_act->created_by_id = $id->user_id;
				$section_act->updated_by_id = NULL;
				$section_act->creation_date = date('Y-m-d h%i%s');
				$section_act->last_update_date = NULL;
				$section_act->order_number = $order_number;
			}
			if($publication_approved == 'yes' && $publication_status == 'published')
			{
				$section_act->approved = $publication_approved;
				$section_act->publication_status = $publication_status;
			}
			else
			{
				$section_act->approved = 'no';
				$section_act->publication_status = 'nonpublished';
			}
			$section_act->author = GlobalFunctions::value_cleaner($formData['author']);
			$section_act->feature = GlobalFunctions::value_cleaner($formData['feature']);
			$section_act->highlight = GlobalFunctions::value_cleaner($formData['highlight']);
			$section_act->publish_date = GlobalFunctions::setFormattedDate($formData['publish_date']);
			$section_act->expire_date = GlobalFunctions::setFormattedDate($formData['expire_date']);
			$section_act->show_publish_date = GlobalFunctions::value_cleaner($formData['show_publish_date']);
			$section_act->rss_available = GlobalFunctions::value_cleaner($formData['rss_available']);
			$section_act->external_link = GlobalFunctions::value_cleaner($formData['external_link']);
			$section_act->target = GlobalFunctions::value_cleaner($formData['target']);
			$section_act->comments = GlobalFunctions::value_cleaner($formData['comments']);
			$section_act->external_comment_script = NULL;
			$section_act->display_menu = GlobalFunctions::value_cleaner($formData['menu']);
                        $section_act->display_menu2 = GlobalFunctions::value_cleaner($formData['menu2']);
			$section_act->homepage = $homepage_opt;
			$section_act->article = 'no';
			$saved_section_id = $section->save('wc_section', $section_act);
			
			$id->section_id = $saved_section_id['id'];
		}
		else
		{	
			if($search_temp)
			{
				if($section_id)
				{
					//edit section already in temp
					$stored_section_data = $section->find('wc_section_temp', array('section_id'=>$section_id));
					
					$section_tmp->id = $stored_section_data[0]->id;
					$section_tmp->section_id = $stored_section_data[0]->section_id;
					$section_tmp->website_id = $stored_section_data[0]->website_id;
					
					$section_tmp->section_parent_id = $formData['section_parent_id'];
					$section_tmp->section_template_id = $formData['section_template_id'];
					$section_tmp->internal_name =  GlobalFunctions::value_cleaner($formData['internal_name']);
					$section_tmp->title = GlobalFunctions::value_cleaner($formData['title']);
					$section_tmp->subtitle = GlobalFunctions::value_cleaner($formData['subtitle']);
					$section_tmp->title_browser = GlobalFunctions::value_cleaner($formData['title_browser']);
					$section_tmp->synopsis = $formData['synopsis'];
					$section_tmp->keywords = GlobalFunctions::value_cleaner($formData['keywords']);
					$section_tmp->type = GlobalFunctions::value_cleaner($formData['type']);
					if(count($stored_section_data)>0)
					{
						$section_tmp->created_by_id = $stored_section_data[0]->created_by_id;
						$section_tmp->updated_by_id = $id->user_id;
						$section_tmp->creation_date = $stored_section_data[0]->creation_date;
						$section_tmp->last_update_date = date('Y-m-d h%i%s');
						$section_tmp->order_number = $stored_section_data[0]->order_number;
					}
					else
					{
						$section_tmp->created_by_id = $id->user_id;
						$section_tmp->updated_by_id = NULL;
						$section_tmp->creation_date = date('Y-m-d h%i%s');
						$section_tmp->last_update_date = NULL;
						$section_tmp->order_number = $order_number;
					}
					if($publication_approved == 'yes' && $publication_status == 'published')
					{
						$section_tmp->approved = $publication_approved;
						$section_tmp->publication_status = $publication_status;
					}
					else
					{
						$section_tmp->approved = 'no';
						$section_tmp->publication_status = 'nonpublished';
					}
					$section_tmp->author = GlobalFunctions::value_cleaner($formData['author']);
					$section_tmp->feature = GlobalFunctions::value_cleaner($formData['feature']);
					$section_tmp->highlight = GlobalFunctions::value_cleaner($formData['highlight']);
					$section_tmp->publish_date = GlobalFunctions::setFormattedDate($formData['publish_date']).' '.$formData['hora_inicio'];
					$section_tmp->expire_date = GlobalFunctions::setFormattedDate($formData['expire_date']).' '.$formData['hora_fin'];
					$section_tmp->show_publish_date = GlobalFunctions::value_cleaner($formData['show_publish_date']);
					$section_tmp->rss_available = GlobalFunctions::value_cleaner($formData['rss_available']);
					$section_tmp->external_link = GlobalFunctions::value_cleaner($formData['external_link']);
					$section_tmp->target = GlobalFunctions::value_cleaner($formData['target']);
					$section_tmp->comments = GlobalFunctions::value_cleaner($formData['comments']);
					$section_tmp->external_comment_script = NULL;
					$section_tmp->display_menu = GlobalFunctions::value_cleaner($formData['menu']);
                                        $section_tmp->display_menu2 = GlobalFunctions::value_cleaner($formData['menu2']);
					$section_tmp->homepage = $homepage_opt;
					$section_tmp->article = 'no';
					$section_tmp_id = $section_temp->save('wc_section_temp',$section_tmp);
					
					$id->section_id = $section_id;
				}
				else
				{
					//new section act then copied to temp
					$section_act->website_id = $id->website_id;	
					$section_act->section_parent_id = $formData['section_parent_id'];
					$section_act->section_template_id = $formData['section_template_id'];
					$section_act->internal_name =  GlobalFunctions::value_cleaner($formData['internal_name']);
					$section_act->title = GlobalFunctions::value_cleaner($formData['title']);
					$section_act->subtitle = GlobalFunctions::value_cleaner($formData['subtitle']);
					$section_act->title_browser = GlobalFunctions::value_cleaner($formData['title_browser']);
					$section_act->synopsis = $formData['synopsis'];
					$section_act->keywords = GlobalFunctions::value_cleaner($formData['keywords']);
					$section_act->type = GlobalFunctions::value_cleaner($formData['type']);
					if(count($stored_section_data)>0)
					{
						$section_act->created_by_id = $stored_section_data[0]->created_by_id;
						$section_act->updated_by_id = $id->user_id;
						$section_act->creation_date = $stored_section_data[0]->creation_date;
						$section_act->last_update_date = date('Y-m-d h%i%s');
						$section_act->order_number = $stored_section_data[0]->order_number;
					}
					else
					{
						$section_act->created_by_id = $id->user_id;
						$section_act->updated_by_id = NULL;
						$section_act->creation_date = date('Y-m-d h%i%s');
						$section_act->last_update_date = NULL;
						$section_act->order_number = $order_number;
					}
					if($publication_approved == 'yes' && $publication_status == 'published')
					{
						$section_act->approved = $publication_approved;
						$section_act->publication_status = $publication_status;
					}
					else
					{
						$section_act->approved = 'no';
						$section_act->publication_status = 'nonpublished';
					}
					$section_act->author = GlobalFunctions::value_cleaner($formData['author']);
					$section_act->feature = GlobalFunctions::value_cleaner($formData['feature']);
					$section_act->highlight = GlobalFunctions::value_cleaner($formData['highlight']);
					$section_act->publish_date = GlobalFunctions::setFormattedDate($formData['publish_date']).' '.$formData['hora_inicio'];
					$section_act->expire_date = GlobalFunctions::setFormattedDate($formData['expire_date']).' '.$formData['hora_fin'];
					$section_act->show_publish_date = GlobalFunctions::value_cleaner($formData['show_publish_date']);
					$section_act->rss_available = GlobalFunctions::value_cleaner($formData['rss_available']);
					$section_act->external_link = GlobalFunctions::value_cleaner($formData['external_link']);
					$section_act->target = GlobalFunctions::value_cleaner($formData['target']);
					$section_act->comments = GlobalFunctions::value_cleaner($formData['comments']);
					$section_act->external_comment_script = NULL;
					$section_act->display_menu = GlobalFunctions::value_cleaner($formData['menu']);
                                        $section_act->display_menu2 = GlobalFunctions::value_cleaner($formData['menu2']);
					$section_act->homepage = $homepage_opt;
					$section_act->article = 'no';
					$saved_section_id = $section->save('wc_section', $section_act);
					
					$id->section_id = $saved_section_id['id'];
					
					//then used in temp
					$stored_section_data = $section->find('wc_section', array('id'=>$saved_section_id['id']));
					//new section temp 
					$section_tmp->section_id = $stored_section_data[0]->id;
					$section_tmp->website_id = $stored_section_data[0]->website_id;
					
					$section_tmp->section_parent_id = $formData['section_parent_id'];
					$section_tmp->section_template_id = $formData['section_template_id'];
					$section_tmp->internal_name =  GlobalFunctions::value_cleaner($formData['internal_name']);
					$section_tmp->title = GlobalFunctions::value_cleaner($formData['title']);
					$section_tmp->subtitle = GlobalFunctions::value_cleaner($formData['subtitle']);
					$section_tmp->title_browser = GlobalFunctions::value_cleaner($formData['title_browser']);
					$section_tmp->synopsis = $formData['synopsis'];
					$section_tmp->keywords = GlobalFunctions::value_cleaner($formData['keywords']);
					$section_tmp->type = GlobalFunctions::value_cleaner($formData['type']);
					if(count($stored_section_data)>0)
					{
						$section_tmp->created_by_id = $stored_section_data[0]->created_by_id;
						$section_tmp->updated_by_id = $id->user_id;
						$section_tmp->creation_date = $stored_section_data[0]->creation_date;
						$section_tmp->last_update_date = date('Y-m-d h%i%s');
						$section_tmp->order_number = $stored_section_data[0]->order_number;
					}
					else
					{
						$section_tmp->created_by_id = $id->user_id;
						$section_tmp->updated_by_id = NULL;
						$section_tmp->creation_date = date('Y-m-d h%i%s');
						$section_tmp->last_update_date = NULL;
						$section_tmp->order_number = $order_number;
					}
					if($publication_approved == 'yes' && $publication_status == 'published')
					{
						$section_tmp->approved = $publication_approved;
						$section_tmp->publication_status = $publication_status;
					}
					else
					{
						$section_tmp->approved = 'no';
						$section_tmp->publication_status = 'nonpublished';
					}
					$section_tmp->author = GlobalFunctions::value_cleaner($formData['author']);
					$section_tmp->feature = GlobalFunctions::value_cleaner($formData['feature']);
					$section_tmp->highlight = GlobalFunctions::value_cleaner($formData['highlight']);
					$section_tmp->publish_date = GlobalFunctions::setFormattedDate($formData['publish_date']).' '.$formData['hora_inicio'];
					$section_tmp->expire_date = GlobalFunctions::setFormattedDate($formData['expire_date']).' '.$formData['hora_fin'];
					$section_tmp->show_publish_date = GlobalFunctions::value_cleaner($formData['show_publish_date']);
					$section_tmp->rss_available = GlobalFunctions::value_cleaner($formData['rss_available']);
					$section_tmp->external_link = GlobalFunctions::value_cleaner($formData['external_link']);
					$section_tmp->target = GlobalFunctions::value_cleaner($formData['target']);
					$section_tmp->comments = GlobalFunctions::value_cleaner($formData['comments']);
					$section_tmp->external_comment_script = NULL;
					$section_tmp->display_menu = GlobalFunctions::value_cleaner($formData['menu']);
                                        $section_tmp->display_menu2 = GlobalFunctions::value_cleaner($formData['menu2']);
					$section_tmp->homepage = $homepage_opt;
					$section_tmp->article = 'no';
					$section_tmp_id = $section_temp->save('wc_section_temp',$section_tmp);
				}
			}
			else
			{
				if($section_id)
				{
					$stored_section_data = $section->find('wc_section', array('id'=>$section_id));
					//new section temp 
					$section_tmp->section_id = $stored_section_data[0]->id;
					$section_tmp->website_id = $stored_section_data[0]->website_id;
					
					$section_tmp->section_parent_id = $formData['section_parent_id'];
					$section_tmp->section_template_id = $formData['section_template_id'];
					$section_tmp->internal_name =  GlobalFunctions::value_cleaner($formData['internal_name']);
					$section_tmp->title = GlobalFunctions::value_cleaner($formData['title']);
					$section_tmp->subtitle = GlobalFunctions::value_cleaner($formData['subtitle']);
					$section_tmp->title_browser = GlobalFunctions::value_cleaner($formData['title_browser']);
					$section_tmp->synopsis = $formData['synopsis'];
					$section_tmp->keywords = GlobalFunctions::value_cleaner($formData['keywords']);
					$section_tmp->type = GlobalFunctions::value_cleaner($formData['type']);
					if(count($stored_section_data)>0)
					{
						$section_tmp->created_by_id = $stored_section_data[0]->created_by_id;
						$section_tmp->updated_by_id = $id->user_id;
						$section_tmp->creation_date = $stored_section_data[0]->creation_date;
						$section_tmp->last_update_date = date('Y-m-d h%i%s');
						$section_tmp->order_number = $stored_section_data[0]->order_number;
					}
					else
					{
						$section_tmp->created_by_id = $id->user_id;
						$section_tmp->updated_by_id = NULL;
						$section_tmp->creation_date = date('Y-m-d h%i%s');
						$section_tmp->last_update_date = NULL;
						$section_tmp->order_number = $order_number;
					}
					if($publication_approved == 'yes' && $publication_status == 'published')
					{
						$section_tmp->approved = $publication_approved;
						$section_tmp->publication_status = $publication_status;
					}
					else
					{
						$section_tmp->approved = 'no';
						$section_tmp->publication_status = 'nonpublished';
					}
					$section_tmp->author = GlobalFunctions::value_cleaner($formData['author']);
					$section_tmp->feature = GlobalFunctions::value_cleaner($formData['feature']);
					$section_tmp->highlight = GlobalFunctions::value_cleaner($formData['highlight']);
					$section_tmp->publish_date = GlobalFunctions::setFormattedDate($formData['publish_date']).' '.$formData['hora_inicio'];
					$section_tmp->expire_date = GlobalFunctions::setFormattedDate($formData['expire_date']).' '.$formData['hora_fin'];
					$section_tmp->show_publish_date = GlobalFunctions::value_cleaner($formData['show_publish_date']);
					$section_tmp->rss_available = GlobalFunctions::value_cleaner($formData['rss_available']);
					$section_tmp->external_link = GlobalFunctions::value_cleaner($formData['external_link']);
					$section_tmp->target = GlobalFunctions::value_cleaner($formData['target']);
					$section_tmp->comments = GlobalFunctions::value_cleaner($formData['comments']);
					$section_tmp->external_comment_script = NULL;
					$section_tmp->display_menu = GlobalFunctions::value_cleaner($formData['menu']);
                                        $section_tmp->display_menu2 = GlobalFunctions::value_cleaner($formData['menu2']);
					$section_tmp->homepage = $homepage_opt;
					$section_tmp->article = 'no';
					$section_tmp_id = $section_temp->save('wc_section_temp',$section_tmp);
				}
			}
		}		
		
		//module area
		$section_areas = new Core_Model_SectionModuleArea();
		$section_areas_temp = new Core_Model_SectionModuleAreaTemp();
		
		if($publication_approved == 'yes' && $publication_status == 'published')
		{
			$section_module_area = $section_areas->getNewRow('wc_section_module_area');
		}
		else
		{
			$section_module_area = $section_areas_temp->getNewRow('wc_section_module_area_temp');
		}
		
		if($publication_approved == 'yes' && $publication_status == 'published')
		{						
			if(isset($formData['section_area_id']))
			{								
				$stored_area = $section_areas->find('wc_section_module_area', array('id'=>$formData['section_area_id']));
				if(count($stored_area)>0)
				{					
					$section_module_area->id = $stored_area[0]->id;
				}
			}
			$section_module_area->section_id = $id->section_id;
			$section_module_area->area_id = $formData['area'];
			//save section area
			$section_area_id = $section_areas->save('wc_section_module_area', $section_module_area);
		}
		else
		{
			if($search_temp)
			{
				if(isset($formData['section_area_id']))
				{
					$stored_area = $section_areas->find('wc_section_module_area_temp', array('id'=>$formData['section_area_id']));
					if($stored_area)
					{					
						$section_module_area->id = $stored_area[0]->id;
						$section_module_area->section_module_area_id = $stored_area[0]->section_module_area_id;
					}
				}
				$section_module_area->section_temp_id = $section_tmp_id['id'];
				$section_module_area->area_id = $formData['area'];
				//save section area
				$section_area_id = $section_areas->save('wc_section_module_area_temp',$section_module_area);
			}
			else
			{
				if(isset($formData['section_area_id']))
				{
					$stored_area = $section_areas->find('wc_section_module_area', array('id'=>$formData['section_area_id']));
					if(count($stored_area)>0)
					{
						$section_module_area->section_module_area_id = $stored_area[0]->id;
					}
				}
				$section_module_area->section_temp_id = $section_tmp_id['id'];
				$section_module_area->area_id = $formData['area'];
				//save section area
				$section_area_id = $section_areas->save('wc_section_module_area_temp',$section_module_area);
			}
		}
		
		//store images id's returned from save
		$image_id = array();
		 
		//MOVE AND RENAME SECTION IMAGE FILES
		if($formData['section_images']!=0)
		{			
			//path to upload image
			if(!is_dir(APPLICATION_PATH. '/../public/uploads/content/'))
			{
				$path = APPLICATION_PATH. '/../public/uploads/content/';
				mkdir($path);
				chmod($path, 0777);
			}

			if(!is_dir(APPLICATION_PATH. '/../public/uploads/content/'.date('Y')))
			{
				$path = APPLICATION_PATH. '/../public/uploads/content/'.date('Y');
				mkdir($path);
				chmod($path, 0777);
			}

			if(!is_dir(APPLICATION_PATH. '/../public/uploads/content/'.date('Y').'/'.date('m')))
			{
				$path = APPLICATION_PATH. '/../public/uploads/content/'.date('Y').'/'.date('m');
				mkdir($path);
				chmod($path, 0777);
			}	

			$section_image = new Core_Model_SectionImage();
			$section_image_temp = new Core_Model_SectionImageTemp();
					
			if($publication_approved == 'yes' && $publication_status == 'published')
			{
                            $section_image_obj = $section_image_temp->getNewRow('wc_section_image_temp');
                            //$section_image_obj = $section_image->getNewRow('wc_section_image');
			}
			else
			{
                            $section_image_obj = $section_image->getNewRow('wc_section_image');
			    //$section_image_obj = $section_image_temp->getNewRow('wc_section_image_temp');
			}
			
			for ($i=1; $i<=$formData['section_images'];$i++)
			{	
                            $section_image_obj = new stdClass();
				//checks if image is new or already exists
				if($formData['id_img_'.$i])
				{
					if($publication_approved == 'yes' && $publication_status == 'published')
					{
						//existent image stored in db and folder
						$data_image = $section_image->find('wc_section_image', array('id'=>$formData['id_img_'.$i]));
						$section_image_obj = $data_image[0];
					}
					else
					{
						if($search_temp)
						{
							//existent image stored in db and folder
							$data_image = $section_image_temp->find('wc_section_image_temp', array('id'=>$formData['id_img_'.$i]));
							$section_image_obj = $data_image[0];
						}
						else
						{
							//existent image stored in db and folder
							$data_image = $section_image->find('wc_section_image', array('id'=>$formData['id_img_'.$i]));
							//new image temp
							$section_image_obj->section_image_id = $data_image[0]->id;
							$section_image_obj->section_temp_id = $section_tmp_id['id'];
							$section_image_obj->name = $data_image[0]->name;
							$section_image_obj->file_name = $data_image[0]->file_name;
						}
					}
				}
				else
				{
					if($formData['hdnNameFile_'.$i])
					{
						//new image	
						if($publication_approved == 'yes' && $publication_status == 'published')
						{
							$section_image_obj->section_id = $id->section_id;
						}
						else
						{
							$section_image_obj->section_temp_id = $section_tmp_id['id'];
						}
					}
				}
					
				//if image file uploaded to create new or update
				if($formData['hdnNameFile_'.$i])
				{
					$img = GlobalFunctions::uploadFiles($formData['hdnNameFile_'.$i], APPLICATION_PATH. '/../public/uploads/content/'.date('Y').'/'.date('m').'/');
				
					//delete old image file
					if(isset($section_arr['file_img_'.$i]))
					{
						if($search_temp)
						{
							//existent image stored in db and folder
							$data_picture = $section_image->find('wc_section_image', array('file_name'=>$section_arr['file_img_'.$i]));
							if(count($data_picture)<1)
							{
								list($folder,$subfolder,$file) = explode('/',$section_arr['file_img_'.$i]);
								if(!GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/'))
								{
									throw new Zend_Exception("CUSTOM_EXCEPTION:FILE NOT DELETED.");
								}
							}
						}
					}
					$section_image_obj->file_name = date('Y').'/'.date('m').'/'.$img;
				}								
			
				//image name
				if($formData['id_img_'.$i] || $formData['hdnNameFile_'.$i])					
				{
					$section_image_obj->name = GlobalFunctions::value_cleaner($formData['name_img_'.$i]);
                                        $section_image_obj->numImageSection = $i;
					// Save data
					if($publication_approved == 'yes' && $publication_status == 'published')
					{
						$section_image_id = $section_image->save('wc_section_image',$section_image_obj);
					}
					else
					{
						$section_image_id = $section_image->save('wc_section_image_temp',$section_image_obj);
					}
					$image_id[] = $section_image_id;
				}
				
				if($formData['hdnNameFile_'.$i])
				{
					//remove images temp files
					GlobalFunctions::removeOldFiles($formData['hdnNameFile_'.$i], APPLICATION_PATH. '/../public/uploads/tmp/');
				}				
			}	
		}

		//succes or error messages displayed on screen
		if($id->section_id && $section_area_id)						
		{
			if($publication_approved == 'yes' && $publication_status == 'published')
			{
				$section_temp_flg = 0;
			}
			elseif($publication_approved == 'no' && $publication_status == 'nonpublished')
			{
				$section_temp_flg = 1;
			}
			
			$id->section_temp = $section_temp_flg;
						
			//adding to section profile when edit access
			if($id->user_profile != '1')
			{
				if($id->user_modules_actions)
				{
					foreach ($id->user_modules_actions as $k => $mod)
					{
						if($mod->module_id == '2' && $mod->action_name == 'edit')
						{
							$section_profile = new Core_Model_SectionProfile();
							$registered_section = $section_profile->find('wc_section_profile', array('section_id'=>$id->section_id));
							
							if(count($registered_section)<1)
							{								
								$new_section = $section_profile->getNewRow('wc_section_profile');
								$new_section->profile_id = $id->user_profile;
								$new_section->section_id = $id->section_id;
	
								$saved_section_profile = $section_profile->save('wc_section_profile', $new_section);
								
								if($saved_section_profile['id'])
								{									
									$options_arr = array();
									$options_str = '';
									$section_profile = new Core_Model_SectionProfile();
									$sections_published_opt = $section_profile->getPublishedSectionsByProfile($id->user_profile, $id->website_id);	
									if(count($sections_published_opt)>0)
									{
										foreach ($sections_published_opt as $ky => $stp)
										{
											if($stp->section_id)
												$options_arr[] = $stp->section_id;
										}
									}
									
									if(count($options_arr)>0)
										$options_str = implode(',',$options_arr);
												
									$id->user_allowed_sections = $options_str;
								}
							}
						}
					}
				}
			}
			
			$arr_success = array('serial'=>$id->section_id, 'section_temp'=>$section_temp_flg);
			echo json_encode($arr_success);
			$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
		}
		else
		{
			$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in saving data')));
		}							
	}
	
	/**
	 * Builds children sections tree
	 */
	public static function buildSectionChildrenTree(&$branch = array(), $parentId)
	{
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		
		$subsection_obj = $section->find('wc_section',array('section_parent_id'=>$parentId));
		if(count($subsection_obj)>0)
		{
			foreach ($subsection_obj as $k => &$slt)
			{
				$subsections_published_arr[] = $slt->id;				
			}
		}
		
		$subsection_temp_obj = $section_temp->find('wc_section_temp',array('section_parent_id'=>$parentId));
		
		if(count($subsection_obj)>0 && count($subsection_temp_obj)>0)
		{
			$subsection_copied = array();
			//replacing sections that area eddited on temp
			foreach ($subsection_obj as $k => &$sbc)
			{				
				foreach ($subsection_temp_obj as $p => &$sct)
				{					
					if($sbc->id == $sct->section_id)
					{
						$sct->id = $sct->section_id;						
						$subsections_list_res[] = $sct;				
						$subsections_copied_arr[] = $sct->section_id;
					}					
				}
			}
			
			//adding subsections created on temp
			if(count($subsections_copied_arr)>0)
			{
				$subsection_pub_missing = array_diff($subsections_published_arr, $subsections_copied_arr);
				if(count($subsection_pub_missing)>0)
				{
					foreach ($subsection_pub_missing as $serial)
					{
						$section_obj = $section->find('wc_section', array('id'=>$serial));						
						$subsections_list_res[] = $section_obj[0];
					}
				}
			}
			$subsection_obj = $subsections_list_res;
		}
		
		if(count($subsection_obj)>0)
		{
			foreach ($subsection_obj as $sub)
			{
				$branch[] = $sub->id;
				$children = self::buildSectionChildrenTree($branch, $sub->id);
			}
		}
		
		return $branch;
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
		
		//session
		$session = new Zend_Session_Namespace('id');
				
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		$section_obj_temp = $section_temp->find('wc_section_temp', array('section_id'=>$id));
		
		//subsections		
		$subsection_arr = self::buildSectionChildrenTree($branch = array(), $id);
		
		if($subsection_arr)
		{
			$subsection_aux = array_unique($subsection_arr);
			//array order desc			
			rsort($subsection_aux);
						
			if(count($subsection_aux)>0)
			{
				foreach ($subsection_aux as $k => $subsection)
				{
					//subsection
					$subsection_obj_temp = $section_temp->find('wc_section_temp', array('section_id'=>$subsection));
					
					//delete subsections content
					$subsection_content = new Core_Model_ContentBySection();
					$subsection_content_obj = $subsection_content->find('wc_content_by_section', array('section_id'=>$subsection));
					if(count($subsection_content_obj)>0)
					{
						foreach ($subsection_content_obj as $content)
						{
							$delete_content_section = $subsection_content->delete('wc_content_by_section', array('id'=>$content->id));
						}
					}
					
					//delete subsections images
					$subsection_image = new Core_Model_SectionImage();
					$subsection_image_obj = $subsection_image->find('wc_section_image', array('section_id'=>$subsection));
					if(count($subsection_image_obj)>0)
					{
						foreach ($subsection_image_obj as $image)
						{
							list($folder,$subfolder,$file) = explode('/',$image->file_name);
							if(is_file(APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/'.$file) == TRUE)
							{
								if(!GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/')){
									throw new Zend_Exception("CUSTOM_EXCEPTION:FILE NOT DELETED.");
								}
							}
							$delete_image = $subsection_image->delete('wc_section_image', array('id'=>$image->id));
						}
					}
					//delete subsections images TEMP
					$subsection_image_temp = new Core_Model_SectionImageTemp();
					if(count($subsection_obj_temp)>0)
					{
						$subsection_image_obj_temp = $subsection_image_temp->find('wc_section_image_temp', array('section_temp_id'=>$subsection_obj_temp[0]->id));
						if(count($subsection_image_obj_temp)>0)
						{
							foreach ($subsection_image_obj_temp as $image)
							{
								list($folder,$subfolder,$file) = explode('/',$image->file_name);
								if(is_file(APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/'.$file) == TRUE)
								{
									GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/');
								}
								$delete_image_temp = $subsection_image_temp->delete('wc_section_image_temp', array('id'=>$image->id));
							}
						}
					}
					
					//delete subsection module area
					$section_module_area = new Core_Model_SectionModuleArea();
					$section_area = $section_module_area->find('wc_section_module_area',array('section_id'=>$subsection));
					if(count($section_area)>0)
						$delete_section_area = $section_module_area->delete('wc_section_module_area',array('id'=>$section_area[0]->id));
					//delete subsection module area TEMP
					$section_module_area_temp = new Core_Model_SectionModuleAreaTemp();
					if(count($subsection_obj_temp)>0)
					{
						$section_area_temp = $section_module_area_temp->find('wc_section_module_area_temp',array('section_temp_id'=>$subsection_obj_temp[0]->id));
						if(count($subsection_obj_temp)>0)
							$delete_section_area_temp = $section_module_area_temp->delete('wc_section_module_area_temp',array('id'=>$section_area_temp[0]->id));					
					}
					//delete subsection_profile
					$section_profile = new Core_Model_SectionProfile();
					$section_profile_arr = $section_profile->find('wc_section_profile',array('section_id'=>$subsection));
					if(count($section_profile_arr)>0)
					{
						foreach ($section_profile_arr as $spr)
						{
							$delete_old_spr = $section_profile->delete('wc_section_profile', array('id'=>$spr->id));
						}
					}
					
					//delete subsection prints
					$section_prints_obj = new Core_Model_SectionPrints();
					$section_prints = $section_prints_obj->find('wc_section_prints', array('section_id'=>$subsection));
					if(count($section_prints)>0)
					{
						$delete_section_prints = $section_prints_obj->delete('wc_section_prints', array('id'=>$section_prints[0]->id));
					}
					
					//delete subsection
					$delete_subsection = $section->delete('wc_section', array('id'=>$subsection));
					//delete subsection TEMP					
					if(count($subsection_obj_temp)>0)
						$delete_subsection = $section_temp->delete('wc_section_temp', array('id'=>$subsection_obj_temp[0]->id));					
				}
			}
		}
				
		//delete section content
		$section_content = new Core_Model_ContentBySection();
		$section_content_obj = $section_content->find('wc_content_by_section', array('section_id'=>$id));
		if(count($section_content_obj)>0)
		{
			foreach ($section_content_obj as $content)
			{
				$delete_content_section = $section_content->delete('wc_content_by_section', array('id'=>$content->id));
			}
		}
		
		//delete section images
		$section_image = new Core_Model_SectionImage();
		$section_image_obj = $section_image->find('wc_section_image', array('section_id'=>$id));
		if(count($section_image_obj)>0)
		{
			foreach ($section_image_obj as $image)
			{
				list($folder,$subfolder,$file) = explode('/',$image->file_name);
				if(is_file(APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/'.$file) == TRUE)
				{
					if(!GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/')){
						throw new Zend_Exception("CUSTOM_EXCEPTION:FILE NOT DELETED.");
					}
				}
				$delete_image = $section_image->delete('wc_section_image', array('id'=>$image->id));
			}
		}
		//delete section images TEMP
		$section_image_temp = new Core_Model_SectionImageTemp();		
		if(count($section_obj_temp)>0)
		{
			$section_image_obj_temp = $section_image_temp->find('wc_section_image_temp', array('section_temp_id'=>$section_obj_temp[0]->id));
			if(count($section_image_obj_temp)>0)
			{
				foreach ($section_image_obj_temp as $image)
				{
					list($folder,$subfolder,$file) = explode('/',$image->file_name);
					if(is_file(APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/'.$file) == TRUE)
					{
						GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/');
					}
					$delete_image_temp = $section_image_temp->delete('wc_section_image_temp', array('id'=>$image->id));
				}
			}
		}
		
		//delete section module area
		$section_module_area = new Core_Model_SectionModuleArea();				
		$section_area = $section_module_area->find('wc_section_module_area',array('section_id'=>$id));
		if(count($section_area)>0)
			$delete_section_area = $section_module_area->delete('wc_section_module_area',array('id'=>$section_area[0]->id));
		//delete section module area TEMP
		$section_module_area_temp = new Core_Model_SectionModuleAreaTemp();
		if(count($section_obj_temp)>0)
		{
			$section_area_temp = $section_module_area_temp->find('wc_section_module_area_temp',array('section_temp_id'=>$section_obj_temp[0]->id));
			if(count($section_area_temp)>0)
				$delete_section_area_temp = $section_module_area_temp->delete('wc_section_module_area_temp',array('id'=>$section_area_temp[0]->id));		
		}
		//delete section_profile
		$section_profile = new Core_Model_SectionProfile();
		$section_profile_arr = $section_profile->find('wc_section_profile',array('section_id'=>$id));
		if(count($section_profile_arr)>0)
		{
			foreach ($section_profile_arr as $spr)
			{
				$delete_old_spr = $section_profile->delete('wc_section_profile', array('id'=>$spr->id));
			}
		}
		
		//delete section prints
		$section_prints_obj = new Core_Model_SectionPrints();
		$section_prints = $section_prints_obj->find('wc_section_prints', array('section_id'=>$id));
		if(count($section_prints)>0)
		{
			$delete_section_prints = $section_prints_obj->delete('wc_section_prints', array('id'=>$section_prints[0]->id));
		}

		//delete section
		$delete_section = $section->delete('wc_section', array('id'=>$id));
		//delete section TEMP
		if(count($section_obj_temp)>0)
			$delete_section_temp = $section_temp->delete('wc_section_temp', array('id'=>$section_obj_temp[0]->id));
		
		//succes or error messages displayed on screen
		if($delete_section)
		{		
			//adding to section profile when edit access
			if($session->user_profile != '1')
			{
				$section_profile = new Core_Model_SectionProfile();
				$sections_published_opt = $section_profile->getPublishedSectionsByProfile($session->user_profile, $session->website_id);	
				if(count($sections_published_opt)>0)
				{
					foreach ($sections_published_opt as $ky => $stp)
					{
						if($stp->section_id)
							$options_arr[] = $stp->section_id;
					}
				}
				
				if(count($options_arr)>0)
					$options_str = implode(',',$options_arr);
				
				$session->user_allowed_sections = $options_str;
			}				
			
			$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success deleted')));		
			$arr_success = array('serial'=>'deleted');
			echo json_encode($arr_success);
		}
		else
		{
			$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in deleting data')));					
		}				
	}

	/**
	 * Search an existent section according to the entered data
	 */
	public function searchAction()
	{
		$this->_helper->layout->disableLayout ();
		
		if ($this->getRequest()->isPost())
		{
			$section = New Core_Model_Section();
			$section_temp = new Core_Model_SectionTemp();
			//session
			$session = new Zend_Session_Namespace('id');
			
			//retrieved data from post
			$formData  = $this->_request->getPost();
						
			$internal_name = mb_strtolower($formData['nameField'], 'UTF-8');
			
			$search_results = array();
			
			$sections_arr = $section->personalized_find('wc_section', array(array('internal_name', 'LIKE', $internal_name), array('website_id','=',$session->website_id)));
			if(count($sections_arr)>0)
			{
				foreach ($sections_arr as &$sar)
				{
					$sar->temp = 0;
					
					$section_temp_obj = $section_temp->find('wc_section_temp', array('section_id'=>$sar->id));
					if(count($section_temp_obj)>0)
					{
						$sections_arr_aux = $section_temp->personalized_find('wc_section_temp', array(array('section_id', '=', $sar->id), array('internal_name', 'LIKE', $internal_name), array('article', '=', 'no'), array('website_id','=',$session->website_id)));
						if(count($sections_arr_aux)>0)
						{
							foreach ($sections_arr_aux as &$sax)
							{
								$sax->temp = 1;
								$search_results[] =  $sax;
							}
						}
					}
					else
					{
						$search_results[] = $sar;
					}
				}
				
				foreach ($search_results as &$sli)
				{
					if(isset($sli->section_id))
						$sli->id = $sli->section_id;
					
					if(GlobalFunctions::checkEditableSection($sli->id))
					{
						$sli->editable_section = 'yes';
					}
					else
					{
						$sli->editable_section = 'no';
					}
				}
			}
							
			$this->view->section_results = $search_results;
			$this->view->section_search_params =  $internal_name;
		}
	}
	
	/**
	 * Loads available sections by website
	 */
	public function sectionsbywebsiteAction() 
	{
		$this->_helper->layout->disableLayout ();
		
		$website_id = $this->_request->getParam ( 'website_id' );
		$this->view->website_id = $website_id;
		
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		$sections_arr = array();
		
		//find existent sections on db according website
		$sections_list = $section->personalized_find('wc_section', array(array('website_id','=',$website_id), array('article','=','no')));
		if(count($sections_list)>0)
		{
			foreach ($sections_list as $k => &$slt)
			{
				$sections_published_arr[] = $slt->id;
				$slt->temp = 0;
			}
		}
		$sections_list_temp = $section_temp->personalized_find('wc_section_temp', array(array('website_id','=',$website_id), array('article','=','no')));
		if(count($sections_list_temp)>0)
		{
			foreach ($sections_list_temp as $k => &$stp)
			{
				$stp->temp = 1;
			}
		}
		
		if(count($sections_list)>0 && count($sections_list_temp)>0)
		{
			$sections_copied_arr = array();
			//replacing sections that area eddited on temp
			foreach ($sections_list as $k => &$sbc)
			{
				foreach ($sections_list_temp as $p => &$sct)
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
			$sections_list = $sections_list_res;
		}
		
		$this->view->section = $sections_list;
		
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
		
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		
		//session
		$session = new Zend_Session_Namespace('id');
	
		if ($this->getRequest ()->isPost ()) 
		{
			$section_id = $this->_request->getPost('section_id');
			$internal_name = $this->_request->getPost('internal_name');
				
			$internal_name_param = mb_strtolower($internal_name, 'UTF-8');
			
			if($section_id)
			{			
				$data = $section->personalized_find('wc_section', array (array('id','!=',$section_id), array('internal_name','==',$internal_name_param), array('article','=','no'), array('website_id','=',$session->website_id)));
				$data_temp = $section_temp->personalized_find('wc_section_temp', array (array('section_id','!=',$section_id), array('internal_name','==',$internal_name_param), array('article','=','no'), array('website_id','=',$session->website_id)));
			}
			else
			{
				$data = $section->personalized_find('wc_section', array (array('internal_name','==',$internal_name_param), array('article','=','no'), array('website_id','=',$session->website_id)));
				$data_temp = $section_temp->personalized_find('wc_section_temp', array (array('internal_name','==',$internal_name_param), array('article','=','no'), array('website_id','=',$session->website_id)));
			}
			
			if($data || $data_temp)
				echo json_encode ( FALSE );
			else
				echo json_encode ( TRUE );
		}	
	}
	
	/**
	 * Uploads a section picture
	 */
	public function uploadfileAction() 
	{			
		$this->_helper->layout->disableLayout ();
		// disable autorendering for this action only:
		$this->_helper->viewRenderer->setNoRender();
						
		$formData  = $this->_request->getPost();
		
		$directory = $formData['directory'];			
		$maxSize = $formData['maxSize'];	

		$directory = APPLICATION_PATH. '/../'. $directory;
		if ($_FILES["section_photos"]["size"] <= $maxSize) {//DETERMINING IF THE SIZE OF THE FILE UPLOADED IS VALID
			$path_parts = pathinfo($_FILES["section_photos"]["name"]);
			$extensions = array(0 => 'jpg', 1 => 'jpeg', 2 => 'png', 3 => 'gif', 4 => 'JPG', 5 => 'JPEG', 6 => 'PNG', 7 => 'GIF');

			if (in_array($path_parts['extension'], $extensions)) {//DETERMINING IF THE EXTENSION OF THE FILE UPLOADED IS VALID
				if (is_dir($directory)) {
					do {
						$tempName = 'pic_' . time() . '.' . $path_parts['extension'];
					} while (file_exists($directory . $tempName));
					move_uploaded_file($_FILES["section_photos"]["tmp_name"], $directory . $tempName);
					echo $tempName;
				} else {//ITS NOT A DIRECTORY
					echo 3;
				}
			} else {//INCORRECT EXTENSION
				echo 2;
			}
		} else {//INCORRECT SIZE
			echo 1;
		}
	}
	
	/**
	 * Deletes the section temp picture
	 */
	public function deletetemppictureAction()
	{
		$this->_helper->layout->disableLayout ();
		// disable autorendering for this action only:
		$this->_helper->viewRenderer->setNoRender();
	
		$formData  = $this->_request->getPost();	
		$temp_file = $formData['file_tmp'];
				
		if ($temp_file) 
		{			
			if (file_exists(APPLICATION_PATH. '/../'. 'public/uploads/tmp/' . $temp_file)) 
			{
				unlink(APPLICATION_PATH. '/../'. 'public/uploads/tmp/'. $temp_file);
			}
		}
	}

	/**
	 * Deletes the section picture 
	 */
	public function deletepictureAction() 
	{
		$this->_helper->layout->disableLayout();
		// disable autorendering for this action only:
		$this->_helper->viewRenderer->setNoRender();

		$formData  = $this->_request->getPost();
		$image_id = $formData['image_id'];
		$search_temp = $formData['is_temp'];
		$image_folder_del = false;	
		
		$section_image = new Core_Model_SectionImage();
		$section_image_temp	= new Core_Model_SectionImageTemp();
		
		if($search_temp)
		{	
			$section_image_obj = $section_image_temp->find('wc_section_image_temp', array('id'=>$image_id));
			//existent image stored in db and folder
			$data_picture = $section_image->find('wc_section_image', array('file_name'=>$section_image_obj[0]->file_name));
			if(count($data_picture)<1)
			{
				$image_folder_del = true;
			}
		}
		else
		{			
			$section_image_obj = $section_image->find('wc_section_image', array('id'=>$image_id));
			$image_folder_del = true;
		}
		
		if($section_image_obj)
		{
			foreach ($section_image_obj as $image)
			{
				if($image_folder_del)
				{
					list($folder,$subfolder,$file) = explode('/',$image->file_name);
					if(!GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/'))
					{
						throw new Zend_Exception("CUSTOM_EXCEPTION:FILE NOT DELETED.");
					}
				}
				
				if($search_temp)
				{
					$delete_image = $section_image_temp->delete('wc_section_image_temp', array('id'=>$image->id));
				}
				else
				{
					$delete_image = $section_image->delete('wc_section_image', array('id'=>$image->id));
				}
			}
		}
	}
	
	/**
	 * Sections (articles not considered) to be used on subsections
	 */
	public function sectionstreelistAction()
	{
		$this->_helper->layout->disableLayout ();
	
		//session
		$session = new Zend_Session_Namespace('id');
		
		$formData  = $this->_request->getPost();		
		$search_temp = $formData['is_temp'];
		
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		$sections_arr = array();
		$serial_sec = '';
		
		//find existent sections on db according website
		$sections_list = $section->personalized_find('wc_section',array(array('website_id','=',$session->website_id),array('article','=','no'), array('id','!=',$session->section_id), array('section_parent_id','=','')),array('article','order_number'));
		if(count($sections_list)>0)
		{
			foreach ($sections_list as $k => &$slt)
			{
				$slt->temp = 0;
				$sections_published_arr[] = $slt->id;
			}
		}
		$sections_list_aux = $section->personalized_find('wc_section',array(array('website_id','=',$session->website_id), array('id','!=',$serial_sec), array('section_parent_id','<>',$session->section_id),array('article','=','no')),array('article','order_number'));
		if(count($sections_list_aux)>0)
		{
			foreach ($sections_list_aux as $k => &$sla)
			{
				$sla->temp = 0;
				$sections_list[] = $sla;
				$sections_published_arr[] = $sla->id;
			}
		}
			
		$sections_list_temp = $section_temp->personalized_find('wc_section_temp',array(array('website_id','=',$session->website_id),array('article','=','no'), array('id','!=',$session->section_id), array('section_parent_id','=','')),array('article','order_number'));
		if(count($sections_list_temp)>0)
		{
			foreach ($sections_list_temp as $k => &$stp)
			{
				$stp->temp = 1;
			}
		}
		$sections_list_temp_aux = $section_temp->personalized_find('wc_section_temp',array(array('website_id','=',$session->website_id), array('id','!=',$session->section_id), array('section_parent_id','<>',$session->section_id),array('article','=','no')),array('article','order_number'));
		if(count($sections_list_temp_aux)>0)
		{
			foreach ($sections_list_temp_aux as $ky => &$slt)
			{
				$slt->temp = 1;
				$sections_list_temp[] = $slt;				
			}
		}
		
		if(count($sections_list)>0 && count($sections_list_temp)>0)
		{
			$sections_copied_arr = array();
			//replacing sections that area eddited on temp
			foreach ($sections_list as $k => &$sbc)
			{
				foreach ($sections_list_temp as $p => &$sct)
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
			if(count($sections_list_res)>0)
				$sections_list = $sections_list_res;
		}
		
		if(count($sections_list)>0)
		{
			foreach ($sections_list as $sec)
			{
				$sections_arr[] = array('id'=>$sec->id,
						'temp'=>$sec->temp,
						'section_parent_id'=>$sec->section_parent_id,
						'title'=>$sec->title,
						'article'=>$sec->article,
						'order_number'=>$sec->order_number
				);
			}
		}
		
		/******
		 * Ordering sections by article and number
		 */
		$sort_col = array();
		foreach ($sections_arr as $key=> $row) {
			if($row['article']=='yes')
				$sort_col_article[$key] = 1;
			else
				$sort_col_article[$key] = 2;
			$sort_col_number[$key] = $row['order_number'];
		}
		array_multisort($sort_col_article, SORT_ASC, $sort_col_number, SORT_ASC, $sections_arr);
			
		//string with sections tree html
		$html_list = '';
		if(count($sections_arr)>0)
		{
			//sections tree - parents and children as array
			$sections_tree = GlobalFunctions::buildSectionTree($sections_arr);
			//sections tree as list
			$html_list = GlobalFunctions::buildHtmlSectionSelectorTree($sections_tree);
		}
		
		$this->view->sections = $html_list;
	}
	
	/**
	 * Section preview shows rendered contents in layout 
	 */
	public function sectionpreviewAction()
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
		$section_id = $this->_getParam('section_id');
		
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
					$section_parent = $section_obj_temp->find('wc_section_temp', array('section_id'=>$section->section_parent_id));
					if(count($section_parent)<1)
						$section_parent = $section_obj->find('wc_section', array('id'=>$section->section_parent_id));
					
					if(count($section_parent)>0)
					{
						foreach ($section_parent as $parent)
						{
							if(isset($parent->section_id))
								$section_id = $parent->section_id;
							else
								$section_id = $parent->id;
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
					$section_module_area_temp = new Core_Model_SectionModuleAreaTemp();					
					$area = $section_module_area_temp->find('wc_section_module_area_temp',array('section_temp_id'=>$section_data_arr[0]->id));
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
					$area = $section_module_area->find('wc_section_module_area',array('section_id'=>$section_id));
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
	
		if ($this->getRequest()->isPost())
		{
			//retrieved data from post
			$formData  = $this->_request->getPost();				
			$session_id = New Zend_Session_Namespace('id');		
						
			//Save contents
			$order_list = GlobalFunctions::value_cleaner($formData['preview_content_order']);
			$order_arr = explode(',', $order_list);
			$count = 1;

			$content_by_section = new Core_Model_ContentBySection();
			$content_by_section_temp = new Core_Model_ContentBySectionTemp();
			
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

	/**
	 * Approves section changes
	 */
	public function approveAction()
	{
		$this->_helper->layout->disableLayout ();
		// disable autorendering for this action
		$this->_helper->viewRenderer->setNoRender();
	
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
	
		//section_id passed in URL
		$id = $this->_getParam('id');
	
		//session
		$session = new Zend_Session_Namespace('id');
	
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		$section_obj_temp = $section_temp->find('wc_section_temp', array('section_id'=>$id));
		$content_temp = new Core_Model_ContentTemp();
	
		//subsections
		$subsection_arr = self::buildSectionChildrenTree($branch = array(), $id);
		
		if($subsection_arr)
		{
			$subsection_aux = array_unique($subsection_arr);
			//array order desc
			rsort($subsection_aux);
	
			if(count($subsection_aux)>0)
			{	
				foreach ($subsection_aux as $k => $subsection)
				{
					//subsection
					$subsection_obj_temp = $section_temp->find('wc_section_temp', array('section_id'=>$subsection));
					
					$content = new Core_Model_Content();
					$content_temp = new Core_Model_ContentTemp();
					$content_list_arr = array();
					
					//available contents per section
					$contents_list = $content->getContentsBySection($subsection, $session->website_id);					
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
					
					$contents_list_temp = $content_temp->getTempContentsBySection($subsection, $session->website_id);
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
					
					/*Contents to be copied or replaced*/
					if(count($content_list_arr)>0)
					{
						foreach ($content_list_arr as $cont)
						{							
							$content_obj_temp = $content_temp->find('wc_content_temp', array('content_id'=>$cont['id']));
							if(count($content_obj_temp)>0)
							{
								//content
								$content = new Core_Model_Content ();
								$content_obj = $content->getNewRow ( 'wc_content' );
								$content_obj->id = $content_obj_temp[0]->content_id;
								$content_obj->content_type_id = GlobalFunctions::value_cleaner( $content_obj_temp[0]->content_type_id );
								$content_obj->website_id = $content_obj_temp[0]->website_id;
								$content_obj->internal_name = GlobalFunctions::value_cleaner ( $content_obj_temp[0]->internal_name );
								$content_obj->title = GlobalFunctions::value_cleaner ( $content_obj_temp[0]->title );
								$content_obj->created_by = $content_obj_temp[0]->created_by;
								$content_obj->updated_by = $session->user_id;
								$content_obj->creation_date = $content_obj_temp[0]->creation_date;
								$content_obj->last_update_date = date ( 'Y-m-d h%i%s' );
								$content_obj->approved = 'yes';
								$content_obj->status = 'active';
								$content_saved = $content->save ( 'wc_content', $content_obj );
								
								if($content_saved['id'])
								{
									if($content_saved['id']==$cont['id'])
									{
										$content_field_temp = new Core_Model_ContentFieldTemp();
										$content_field_obj_temp = $content_field_temp->find('wc_content_field_temp', array('content_temp_id'=>$content_obj_temp[0]->id, 'content_id'=>$cont['id']));
										if(count($content_field_obj_temp)>0)
										{
											foreach ($content_field_obj_temp as $field_tmp)
											{
												$content_field = new Core_Model_ContentField();
												$content_field_obj = $content_field->find('wc_content_field', array('content_id'=>$field_tmp->content_id, 'field_id'=>$field_tmp->field_id));
												$content_field_obj_upd = $content_field->getNewRow ( 'wc_content_field' );
												
												if(count($content_field_obj)>0)
												{
													$content_field_obj_upd->id = $content_field_obj[0]->id;
													$content_field_obj_upd->field_id = $content_field_obj[0]->field_id;
													$content_field_obj_upd->content_id = $content_field_obj[0]->content_id;
												}
												else
												{													
													$content_field_obj_upd->field_id = $field_tmp->field_id;
													$content_field_obj_upd->content_id = $content_saved['id'];
												}
												$content_field_obj_upd->value = $field_tmp->value;
												$saved_content_field_upd = $content_field->save ( 'wc_content_field', $content_field_obj_upd );
												//delete content field temp
												$delete_content_field_pub = $content_field_temp->delete('wc_content_field_temp', array('id'=>$field_tmp->id));
											}
										}																									
									}
								}
								
								if($content_obj_temp[0]->content_type_id == '4')
								{
									$form_field = new Core_Model_FormField();
									$form_field_temp = new Core_Model_FormFieldTemp();
									
									$form_obj_temp = $form_field_temp->find('wc_form_field_temp', array('content_temp_id'=>$content_obj_temp[0]->id));
									if(count($form_obj_temp)>0)
									{
										foreach($form_obj_temp as $frm)
										{
											$content_tmp_obj = $content_temp->find('wc_content_temp', array('id'=>$frm->content_temp_id));
											if(count($content_tmp_obj)>0)
											{		
												$form_field_obj = $form_field->getNewRow ('wc_form_field');
												
												if($content_tmp_obj[0]->content_id)
												{
													$form_field_obj_pub = $form_field->find('wc_form_field', array('content_id'=>$content_tmp_obj[0]->content_id));
													if(count($form_field_obj_pub)>0)
													{
														foreach ($form_field_obj_pub as $pub)
														{
															$form_field->delete('wc_form_field' , array('id' => $pub->id));
														}
													}
													$form_field_obj->content_id = $content_tmp_obj[0]->content_id;
												}
												else
												{
													$form_field_obj->content_id = $content_saved['id'];
												}
													
												$form_field_obj->name = GlobalFunctions::value_cleaner($frm->name);
												$form_field_obj->description = GlobalFunctions::value_cleaner ($frm->description);
												$form_field_obj->required = $frm->required;
												$form_field_obj->type = $frm->type;
												$form_field_obj->weight = $frm->weight;
												$form_field_obj->options = GlobalFunctions::value_cleaner ($frm->options);
												$save_form = $form_field->save ('wc_form_field', $form_field_obj);
												
												//delete
												$delete_form_temp = $form_field_temp->delete('wc_form_field_temp', array('id'=>$frm->id));
											}
										}
									}
								}
								
								if(count($content_obj_temp)>0 && count($subsection_obj_temp)>0)
								{
									$content_by_section_temp = new Core_Model_ContentBySectionTemp();
									$content_by_section_data_temp = $content_by_section_temp->find('wc_content_by_section_temp', array('section_temp_id'=> $subsection_obj_temp[0]->id, 'content_temp_id'=> $content_obj_temp[0]->id));
									
									if(count($content_by_section_data_temp)>0)
									{
										$content_by_section = new Core_Model_ContentBySection();
										$content_by_section_obj = $content_by_section->getNewRow('wc_content_by_section');									
										if($content_by_section_data_temp[0]->content_by_section_id)
										{
											$content_by_section_obj->id = $content_by_section_data_temp[0]->content_by_section_id;
										}
										$content_by_section_obj->section_id = $subsection;
										$content_by_section_obj->content_id = $cont['id'];
										$content_by_section_obj->weight = $content_by_section_data_temp[0]->weight;
										$content_by_section_obj->column_number = $content_by_section_data_temp[0]->column_number;
										$content_by_section_obj->align = $content_by_section_data_temp[0]->align;									
										$content_by_section_pub = $content_by_section->save('wc_content_by_section',$content_by_section_obj);
										
										//delete content by section temp
										$delete_content_section = $content_by_section_temp->delete('wc_content_by_section_temp', array('id'=>$content_by_section_data_temp[0]->id));

										//delete content temp
										$delete_content_temp = $content_temp->delete('wc_content_temp', array('id'=>$content_obj_temp[0]->id));										
									}
								}
							}
						}
					}

					//copy subsections images TEMP
					$subsection_image_temp = new Core_Model_SectionImageTemp();
					$subsection_image = new Core_Model_SectionImage();
					if(count($subsection_obj_temp)>0)
					{
						$subsection_image_obj_temp = $subsection_image_temp->find('wc_section_image_temp', array('section_temp_id'=>$subsection_obj_temp[0]->id));
						if(count($subsection_image_obj_temp)>0)
						{
							foreach ($subsection_image_obj_temp as $image)
							{
								$subsection_image_obj = $subsection_image->find('wc_section_image', array('id'=>$image->section_image_id));
								if(count($subsection_image_obj)>0)
								{
									//update
									list($folder,$subfolder,$file) = explode('/',$subsection_image_obj[0]->file_name);
									if(is_file(APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/'.$file) == TRUE)
									{
										if(!GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/')){
											throw new Zend_Exception("CUSTOM_EXCEPTION:FILE NOT DELETED.");
										}
									}
									$image_obj = $subsection_image->getNewRow('wc_section_image');
									$image_obj->id = $subsection_image_obj[0]->id;
									$image_obj->section_id = $subsection_image_obj[0]->section_id;
									$image_obj->name = $image->name;
									$image_obj->file_name = $image->file_name;
								}
								else
								{
									//new
									$image_obj = $subsection_image->getNewRow('wc_section_image');									
									$image_obj->section_id = $subsection;
									$image_obj->name = $image->name;
									$image_obj->file_name = $image->file_name;
								}
								
								//delete temp image
								$delete_image_temp = $subsection_image_temp->delete('wc_section_image_temp', array('id'=>$image->id));
							}
						}
					}
										
					//copy subsection module area TEMP					
					if(count($subsection_obj_temp)>0)
					{
						$section_module_area_temp = new Core_Model_SectionModuleAreaTemp();
						$section_area_temp = $section_module_area_temp->find('wc_section_module_area_temp',array('section_temp_id'=>$subsection_obj_temp[0]->id));
						if(count($section_area_temp)>0)
						{
							$section_module_area = new Core_Model_SectionModuleArea();
							if($section_area_temp[0]->section_module_area_id)
							{								
								$section_area = $section_module_area->find('wc_section_module_area',array('id'=>$section_area_temp[0]->section_module_area_id));
								if(count($section_area)>0)
								{
									$section_area_obj = $section_module_area->getNewRow('wc_section_module_area');
									$section_area_obj->id = $section_area[0]->id;
									$section_area_obj->section_id = $subsection;
									$section_area_obj->area_id = $section_area_temp[0]->area_id;
								}
							}
							else
							{
								$section_area_obj = $section_module_area->getNewRow('wc_section_module_area');								
								$section_area_obj->section_id = $subsection;
								$section_area_obj->area_id = $section_area_temp[0]->area_id;
							}
							//saved area
							$saved_section_area = $section_module_area->save('wc_section_module_area', $section_area_obj);
							//delete temp area
							$delete_section_area_temp = $section_module_area_temp->delete('wc_section_module_area_temp',array('id'=>$section_area_temp[0]->id));
						}
					}
					
					//copy to section delete subsection TEMP
					if(count($subsection_obj_temp)>0)
					{
						$stored_section_data = $subsection_obj_temp;
						//$section_obj = $stored_section_data[0];
						$section_obj = $section->getNewRow('wc_section');
						$section_obj->id = $stored_section_data[0]->section_id;
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
						$section_obj->updated_by_id = $session->user_id;
						$section_obj->creation_date = $stored_section_data[0]->creation_date;
						$section_obj->last_update_date = date('Y-m-d h%i%s');
						$section_obj->approved = 'yes';
						$section_obj->author = GlobalFunctions::value_cleaner($stored_section_data[0]->author);
						$section_obj->publication_status = 'published';
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
                                                $section_obj->display_menu2 = GlobalFunctions::value_cleaner($stored_section_data[0]->display_menu2);
						$section_obj->homepage = GlobalFunctions::value_cleaner($stored_section_data[0]->homepage);
						$section_obj->order_number = GlobalFunctions::value_cleaner($stored_section_data[0]->order_number);
						$section_obj->article = GlobalFunctions::value_cleaner($stored_section_data[0]->article);
						$serial_id = $section->save('wc_section',$section_obj);
						
						//delete subsection
						$delete_subsection = $section_temp->delete('wc_section_temp', array('id'=>$subsection_obj_temp[0]->id));
					}
				}
			}
		}	
		
		/*section approvals*/
		$content_list_arr = array();
		$contents_list_published = array();
		$contents_temp_aux = array();
		$content_temp_arr = array();
		$contents_copied_arr = array();
		
		$section_obj_temp = $section_temp->find('wc_section_temp', array('section_id'=>$id));
			
		$content = new Core_Model_Content();
		$content_temp = new Core_Model_ContentTemp();
		$content_list_arr = array();
			
		//available contents per section
		$contents_list = $content->getContentsBySection($id, $session->website_id);
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
			
		$contents_list_temp = $content_temp->getTempContentsBySection($id, $session->website_id);
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
						'temp' => '1'
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
					
		/*Contents to be copied or replaced*/
		if(count($content_list_arr)>0)
		{
			foreach ($content_list_arr as $cont)
			{
				$content_obj_temp = $content_temp->find('wc_content_temp', array('content_id'=>$cont['id']));
				if(count($content_obj_temp)>0)
				{
					//content
					$content = new Core_Model_Content ();
					$content_obj = $content->getNewRow ( 'wc_content' );
					$content_obj->id = $content_obj_temp[0]->content_id;
					$content_obj->content_type_id = GlobalFunctions::value_cleaner( $content_obj_temp[0]->content_type_id );
					$content_obj->website_id = $content_obj_temp[0]->website_id;
					$content_obj->internal_name = GlobalFunctions::value_cleaner ( $content_obj_temp[0]->internal_name );
					$content_obj->title = GlobalFunctions::value_cleaner ( $content_obj_temp[0]->title );
					$content_obj->created_by = $content_obj_temp[0]->created_by;
					$content_obj->updated_by = $session->user_id;
					$content_obj->creation_date = $content_obj_temp[0]->creation_date;
					$content_obj->last_update_date = date ( 'Y-m-d h%i%s' );
					$content_obj->approved = 'yes';
					$content_obj->status = 'active';
					$content_saved = $content->save ( 'wc_content', $content_obj );
		
					if($content_saved['id'])
					{
						if($content_saved['id']==$cont['id'])
						{
							$content_field_temp = new Core_Model_ContentFieldTemp();
							$content_field_obj_temp = $content_field_temp->find('wc_content_field_temp', array('content_temp_id'=>$content_obj_temp[0]->id, 'content_id'=>$cont['id']));
							if(count($content_field_obj_temp)>0)
							{
								foreach ($content_field_obj_temp as $field_tmp)
								{
									$content_field = new Core_Model_ContentField();
									$content_field_obj = $content_field->find('wc_content_field', array('content_id'=>$field_tmp->content_id, 'field_id'=>$field_tmp->field_id));
									$content_field_obj_upd = $content_field->getNewRow ( 'wc_content_field' );
												
									if(count($content_field_obj)>0)
									{
										$content_field_obj_upd->id = $content_field_obj[0]->id;
										$content_field_obj_upd->field_id = $content_field_obj[0]->field_id;
										$content_field_obj_upd->content_id = $content_field_obj[0]->content_id;
									}
									else
									{													
										$content_field_obj_upd->field_id = $field_tmp->field_id;
										$content_field_obj_upd->content_id = $content_saved['id'];
									}
									$content_field_obj_upd->value = $field_tmp->value;
									$saved_content_field_upd = $content_field->save ( 'wc_content_field', $content_field_obj_upd );
									
									//delete content field temp
									$delete_content_field_pub = $content_field_temp->delete('wc_content_field_temp', array('id'=>$field_tmp->id));
								}
							}
						}
					}
					
					if($content_obj_temp[0]->content_type_id == '4')
					{
						$form_field = new Core_Model_FormField();
						$form_field_temp = new Core_Model_FormFieldTemp();
							
						$form_obj_temp = $form_field_temp->find('wc_form_field_temp', array('content_temp_id'=>$content_obj_temp[0]->id));
						if(count($form_obj_temp)>0)
						{
							foreach($form_obj_temp as $frm)
							{
								$content_tmp_obj = $content_temp->find('wc_content_temp', array('id'=>$frm->content_temp_id));
								if(count($content_tmp_obj)>0)
								{
									$form_field_obj = $form_field->getNewRow ('wc_form_field');
					
									if($content_tmp_obj[0]->content_id)
									{
										$form_field_obj_pub = $form_field->find('wc_form_field', array('content_id'=>$content_tmp_obj[0]->content_id));
										if(count($form_field_obj_pub)>0)
										{
											foreach ($form_field_obj_pub as $pub)
											{
												$form_field->delete('wc_form_field' , array('id' => $pub->id));
											}
										}
										$form_field_obj->content_id = $content_tmp_obj[0]->content_id;
									}
									else
									{
										$form_field_obj->content_id = $content_saved['id'];
									}
										
									$form_field_obj->name = GlobalFunctions::value_cleaner($frm->name);
									$form_field_obj->description = GlobalFunctions::value_cleaner ($frm->description);
									$form_field_obj->required = $frm->required;
									$form_field_obj->type = $frm->type;
									$form_field_obj->weight = $frm->weight;
									$form_field_obj->options = GlobalFunctions::value_cleaner ($frm->options);
									$save_form = $form_field->save ('wc_form_field', $form_field_obj);
					
									//delete
									$delete_form_temp = $form_field_temp->delete('wc_form_field_temp', array('id'=>$frm->id));
								}
							}
						}
					}
		
					if(count($content_obj_temp)>0 && count($section_obj_temp)>0)
					{
						$content_by_section_temp = new Core_Model_ContentBySectionTemp();
						$content_by_section_data_temp = $content_by_section_temp->find('wc_content_by_section_temp', array('section_temp_id'=> $section_obj_temp[0]->id, 'content_temp_id'=> $content_obj_temp[0]->id));
						if(count($content_by_section_data_temp)>0)
						{
							$content_by_section = new Core_Model_ContentBySection();
							$content_by_section_obj = $content_by_section->getNewRow('wc_content_by_section');
							if($content_by_section_data_temp[0]->content_by_section_id)
							{
								$content_by_section_obj->id = $content_by_section_data_temp[0]->content_by_section_id;
							}
							$content_by_section_obj->section_id = $id;
							$content_by_section_obj->content_id = $cont['id'];
							$content_by_section_obj->weight = $content_by_section_data_temp[0]->weight;
							$content_by_section_obj->column_number = $content_by_section_data_temp[0]->column_number;
							$content_by_section_obj->align = $content_by_section_data_temp[0]->align;
							$content_by_section_pub = $content_by_section->save('wc_content_by_section',$content_by_section_obj);
		
							//delete content by section temp
							$delete_content_section = $content_by_section_temp->delete('wc_content_by_section_temp', array('id'=>$content_by_section_data_temp[0]->id));
						}
					}
					//delete content temp
					$delete_content_temp = $content_temp->delete('wc_content_temp', array('id'=>$content_obj_temp[0]->id));
				}
			}
		}
		
		//copy section images TEMP
		$section_image_temp = new Core_Model_SectionImageTemp();
		$section_image = new Core_Model_SectionImage();
		if(count($section_obj_temp)>0)
		{
			$section_image_obj_temp = $section_image_temp->find('wc_section_image_temp', array('section_temp_id'=>$section_obj_temp[0]->id));
			if(count($section_image_obj_temp)>0)
			{
				foreach ($section_image_obj_temp as $image)
				{
					$section_image_obj = $section_image->find('wc_section_image', array('id'=>$image->section_image_id));
					if(count($section_image_obj)>0)
					{
						//update
						list($folder,$subfolder,$file) = explode('/',$section_image_obj[0]->file_name);
						if(is_file(APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/'.$file) == TRUE)
						{
							if(!GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/')){
								throw new Zend_Exception("CUSTOM_EXCEPTION:FILE NOT DELETED.");
							}
						}
						$image_obj = $section_image->getNewRow('wc_section_image');
						$image_obj->id = $section_image_obj[0]->id;
						$image_obj->section_id = $section_image_obj[0]->section_id;
						$image_obj->name = $image->name;
						$image_obj->file_name = $image->file_name;
					}
					else
					{
						//new
						$image_obj = $section_image->getNewRow('wc_section_image');
						$image_obj->section_id = $id;
						$image_obj->name = $image->name;
						$image_obj->file_name = $image->file_name;
					}
		
					//delete temp image
					$delete_image_temp = $section_image_temp->delete('wc_section_image_temp', array('id'=>$image->id));
				}
			}
		}
		
		//copy section module area TEMP
		if(count($section_obj_temp)>0)
		{
			$section_module_area_temp = new Core_Model_SectionModuleAreaTemp();
			$section_area_temp = $section_module_area_temp->find('wc_section_module_area_temp',array('section_temp_id'=>$section_obj_temp[0]->id));
			if(count($section_area_temp)>0)
			{
				$section_module_area = new Core_Model_SectionModuleArea();
				if($section_area_temp[0]->section_module_area_id)
				{
					$section_area = $section_module_area->find('wc_section_module_area',array('id'=>$section_area_temp[0]->section_module_area_id));
					if(count($section_area)>0)
					{
						$section_area_obj = $section_module_area->getNewRow('wc_section_module_area');
						$section_area_obj->id = $section_area[0]->id;
						$section_area_obj->section_id = $id;
						$section_area_obj->area_id = $section_area_temp[0]->area_id;
					}
				}
				else
				{
					$section_area_obj = $section_module_area->getNewRow('wc_section_module_area');
					$section_area_obj->section_id = $id;
					$section_area_obj->area_id = $section_area_temp[0]->area_id;
				}
				//saved area
				$saved_section_area_pub = $section_module_area->save('wc_section_module_area', $section_area_obj);
				//delete temp area
				$delete_section_area_temp = $section_module_area_temp->delete('wc_section_module_area_temp',array('id'=>$section_area_temp[0]->id));
			}
		}
		
		$delete_section = '';
			
		//copy to section delete section TEMP
		if(count($section_obj_temp)>0)
		{
			$stored_section_data = $section_obj_temp;
			//$section_obj = $stored_section_data[0];
			$section_obj = $section->getNewRow('wc_section');
			$section_obj->id = $stored_section_data[0]->section_id;
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
			$section_obj->updated_by_id = $session->user_id;
			$section_obj->creation_date = $stored_section_data[0]->creation_date;
			$section_obj->last_update_date = date('Y-m-d h%i%s');
			$section_obj->approved = 'yes';
			$section_obj->author = GlobalFunctions::value_cleaner($stored_section_data[0]->author);
			$section_obj->publication_status = 'published';
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
                        $section_obj->display_menu2 = GlobalFunctions::value_cleaner($stored_section_data[0]->display_menu2);
			$section_obj->homepage = GlobalFunctions::value_cleaner($stored_section_data[0]->homepage);
			$section_obj->order_number = GlobalFunctions::value_cleaner($stored_section_data[0]->order_number);
			$section_obj->article = GlobalFunctions::value_cleaner($stored_section_data[0]->article);
			$serial_id = $section->save('wc_section',$section_obj);
		
			//delete section
			$delete_section = $section_temp->delete('wc_section_temp', array('id'=>$section_obj_temp[0]->id));
		}
		
		//succes or error messages displayed on screen
		if($delete_section)
		{		
			$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success approved')));
			$arr_success = array('serial'=>'approved');
			echo json_encode($arr_success);
		}
		else
		{
			$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in approving temp data')));
		}
	}

	/**
	 * Keep current site accoriding published contents
	 */
	public function keepcurrentAction()
	{
		$this->_helper->layout->disableLayout ();
		// disable autorendering for this action
		$this->_helper->viewRenderer->setNoRender();
	
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
	
		//section_id passed in URL
		$id = $this->_getParam('id');
	
		//session
		$session = new Zend_Session_Namespace('id');
	
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		$section_obj_temp = $section_temp->find('wc_section_temp', array('section_id'=>$id));
		$content_temp = new Core_Model_ContentTemp();
	
		//subsections
		$subsection_arr = self::buildSectionChildrenTree($branch = array(), $id);
	
		if($subsection_arr)
		{
			$subsection_aux = array_unique($subsection_arr);
			//array order desc
			rsort($subsection_aux);
	
			if(count($subsection_aux)>0)
			{
				foreach ($subsection_aux as $k => $subsection)
				{
					//subsection
					$subsection_obj_temp = $section_temp->find('wc_section_temp', array('section_id'=>$subsection));
						
					$content = new Core_Model_Content();
					$content_temp = new Core_Model_ContentTemp();
					$content_list_arr = array();
						
					//available contents per section
					$contents_list_temp = $content_temp->getTempContentsBySection($subsection, $session->website_id);
					
					foreach ($contents_list_temp as $ps => $lit)
					{
						$contents_temp_aux[] = $lit['content_id'];
						$content_list_arr[] = array('id' => $lit['content_id'],
								'section_id' => $lit['section_id'],
								'title' => $lit['title'],
								'type' => $lit['type'],
								'content_type_id' => $lit['content_type_id'],
								'internal_name' => $lit['internal_name'],
								'serial_cbs' => $lit['serial_cbs'],
								'column_number' => $lit['column_number'],
								'align' => $lit['align'],
								'weight' => $lit['weight'],
								'temp' => '1'
						);
					}
						
					/*Contents to be copied or replaced*/
					if(count($content_list_arr)>0)
					{						
						foreach ($content_list_arr as $cont)
						{
							$content_obj_temp = $content_temp->find('wc_content_temp', array('content_id'=>$cont['id']));
							if(count($content_obj_temp)>0)
							{
								$content_field_temp = new Core_Model_ContentFieldTemp();
								$content_field_obj_temp = $content_field_temp->find('wc_content_field_temp', array('content_temp_id'=>$content_obj_temp[0]->id, 'content_id'=>$cont['id']));
								if(count($content_field_obj_temp)>0)
								{
									foreach ($content_field_obj_temp as $field_tmp)
									{												
										//delete content field temp
										$delete_content_field_pub = $content_field_temp->delete('wc_content_field_temp', array('id'=>$field_tmp->id));
									}
								}
								
								if($content_obj_temp[0]->content_type_id == '4')
								{
									$form_field = new Core_Model_FormField();
									$form_field_temp = new Core_Model_FormFieldTemp();
										
									$form_obj_temp = $form_field_temp->find('wc_form_field_temp', array('content_temp_id'=>$content_obj_temp[0]->id));
									if(count($form_obj_temp)>0)
									{
										foreach($form_obj_temp as $frm)
										{
											$content_tmp_obj = $content_temp->find('wc_content_temp', array('id'=>$frm->content_temp_id));
											if(count($content_tmp_obj)>0)
											{
												//delete
												$delete_form_temp = $form_field_temp->delete('wc_form_field_temp', array('id'=>$frm->id));
											}
										}
									}
								}
	
								if(count($content_obj_temp)>0 && count($subsection_obj_temp)>0)
								{
									$content_by_section_temp = new Core_Model_ContentBySectionTemp();
									$content_by_section_data_temp = $content_by_section_temp->find('wc_content_by_section_temp', array('section_temp_id'=> $subsection_obj_temp[0]->id, 'content_temp_id'=> $content_obj_temp[0]->id));										
									if(count($content_by_section_data_temp)>0)
									{
										//delete content by section temp
										$delete_content_section = $content_by_section_temp->delete('wc_content_by_section_temp', array('id'=>$content_by_section_data_temp[0]->id));
									}
								}
								
								//delete content temp
								$delete_content_temp = $content_temp->delete('wc_content_temp', array('id'=>$content_obj_temp[0]->id));
							}
						}
					}
	
					//delete subsections images TEMP
					$subsection_image_temp = new Core_Model_SectionImageTemp();
					$subsection_image = new Core_Model_SectionImage();
					if(count($subsection_obj_temp)>0)
					{
						$subsection_image_obj_temp = $subsection_image_temp->find('wc_section_image_temp', array('section_temp_id'=>$subsection_obj_temp[0]->id));
						if(count($subsection_image_obj_temp)>0)
						{
							foreach ($subsection_image_obj_temp as $image)
							{	
								//can erase image physically
								list($folder,$subfolder,$file) = explode('/',$image->file_name);
								if(is_file(APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/'.$file) == TRUE)
								{
									if(!GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/')){
										throw new Zend_Exception("CUSTOM_EXCEPTION:FILE NOT DELETED.");
									}
								}
								
								//delete temp image
								$delete_image_temp = $subsection_image_temp->delete('wc_section_image_temp', array('id'=>$image->id));
							}
						}
					}
	
					//delete subsection module area TEMP
					if(count($subsection_obj_temp)>0)
					{
						$section_module_area_temp = new Core_Model_SectionModuleAreaTemp();
						$section_area_temp = $section_module_area_temp->find('wc_section_module_area_temp',array('section_temp_id'=>$subsection_obj_temp[0]->id));
						if(count($section_area_temp)>0)
						{
							//delete temp area
							$delete_section_area_temp = $section_module_area_temp->delete('wc_section_module_area_temp',array('id'=>$section_area_temp[0]->id));
						}
					}
						
					//delete to section delete subsection TEMP
					if(count($subsection_obj_temp)>0)
					{
						//delete subsection
						$delete_subsection = $section_temp->delete('wc_section_temp', array('id'=>$subsection_obj_temp[0]->id));
					}
				}
			}
		}
	
		/*section approvals*/
		$content_list_arr = array();
		$contents_list_published = array();
		$contents_temp_aux = array();
		$content_temp_arr = array();
		$contents_copied_arr = array();
		$content_obj_temp = array();
		
		//$section_obj_temp = $section_temp->find('wc_section_temp', array('section_id'=>$id));
			
		$content = new Core_Model_Content();
		$content_temp = new Core_Model_ContentTemp();
		
			
		//available contents per temp section			
		$contents_list_temp = $content_temp->getTempContentsBySection($id, $session->website_id);
		if(count($contents_list_temp)>0)
		{
			foreach ($contents_list_temp as $ps => $lit)
			{
				$content_list_arr[] = array('id' => $lit['content_id'],
						'section_id' => $lit['section_id'],
						'title' => $lit['title'],
						'type' => $lit['type'],
						'content_type_id' => $lit['content_type_id'],
						'internal_name' => $lit['internal_name'],
						'serial_cbs' => $lit['serial_cbs'],
						'column_number' => $lit['column_number'],
						'align' => $lit['align'],
						'weight' => $lit['weight'],
						'temp' => '1'
				);
			}
		}
			
		/*delete contents*/
		if(count($content_list_arr)>0)
		{
			foreach ($content_list_arr as $cont)
			{
				$content_obj_temp = $content_temp->find('wc_content_temp', array('content_id'=>$cont['id']));
				if(count($content_obj_temp)>0)
				{
					//content					
					$content_field_temp = new Core_Model_ContentFieldTemp();
					$content_field_obj_temp = $content_field_temp->find('wc_content_field_temp', array('content_temp_id'=>$content_obj_temp[0]->id));
					if(count($content_field_obj_temp)>0)
					{
						foreach ($content_field_obj_temp as $field_tmp)
						{									
							//delete content field temp
							$delete_content_field_pub = $content_field_temp->delete('wc_content_field_temp', array('id'=>$field_tmp->id));
						}
					}
	
					if(count($content_obj_temp)>0 && count($section_obj_temp)>0)
					{
						$content_by_section_temp = new Core_Model_ContentBySectionTemp();
						//Zend_Debug::dump($section_obj_temp[0]->id.' y '.$content_obj_temp[0]->id);
						$content_by_section_data_temp = $content_by_section_temp->find('wc_content_by_section_temp', array('section_temp_id'=> $section_obj_temp[0]->id, 'content_temp_id'=> $content_obj_temp[0]->id));
						if(count($content_by_section_data_temp)>0)
						{								
							//delete content by section temp
							$delete_content_section = $content_by_section_temp->delete('wc_content_by_section_temp', array('id'=>$content_by_section_data_temp[0]->id));
						}
					}
					
					if($content_obj_temp[0]->content_type_id == '4')
					{
						$form_field = new Core_Model_FormField();
						$form_field_temp = new Core_Model_FormFieldTemp();
							
						$form_obj_temp = $form_field_temp->find('wc_form_field_temp', array('content_temp_id'=>$content_obj_temp[0]->id));
						if(count($form_obj_temp)>0)
						{
							foreach($form_obj_temp as $frm)
							{
								$content_tmp_obj = $content_temp->find('wc_content_temp', array('id'=>$frm->content_temp_id));
								if(count($content_tmp_obj)>0)
								{
									//delete
									$delete_form_temp = $form_field_temp->delete('wc_form_field_temp', array('id'=>$frm->id));
								}
							}
						}
						else 
						{
							$form_obj_temp = $form_field->find('wc_form_field', array('content_id'=>$cont['id']));
							if(count($form_obj_temp)>0)
							{
								foreach($form_obj_temp as $frm)
								{
									$content_tmp_obj = $content_temp->find('wc_content_temp', array('id'=>$frm->content_temp_id));
									if(count($content_tmp_obj)>0)
									{
										//delete
										$delete_form_temp = $form_field_temp->delete('wc_form_field', array('id'=>$frm->id));
									}
								}
							}
						}
					}
					
					//delete content temp
					$delete_content_temp = $content_temp->delete('wc_content_temp', array('id'=>$content_obj_temp[0]->id));
				}
			}
		}
		
		//delete section images TEMP
		$section_image_temp = new Core_Model_SectionImageTemp();
		$section_image = new Core_Model_SectionImage();
		if(count($section_obj_temp)>0)
		{
			$section_image_obj_temp = $section_image_temp->find('wc_section_image_temp', array('section_temp_id'=>$section_obj_temp[0]->id));
			if(count($section_image_obj_temp)>0)
			{
				foreach ($section_image_obj_temp as $image)
				{
					//update
					list($folder,$subfolder,$file) = explode('/',$image->file_name);
					if(is_file(APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/'.$file) == TRUE)
					{
						if(!GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/content/'.$folder.'/'.$subfolder.'/')){
							throw new Zend_Exception("CUSTOM_EXCEPTION:FILE NOT DELETED.");
						}
					}
	
					//delete temp image
					$delete_image_temp = $section_image_temp->delete('wc_section_image_temp', array('id'=>$image->id));
				}
			}
		}
	
		//delete section module area TEMP
		if(count($section_obj_temp)>0)
		{
			$section_module_area_temp = new Core_Model_SectionModuleAreaTemp();
			$section_area_temp = $section_module_area_temp->find('wc_section_module_area_temp',array('section_temp_id'=>$section_obj_temp[0]->id));
			if(count($section_area_temp)>0)
			{				
				//delete temp area
				$delete_section_area_temp = $section_module_area_temp->delete('wc_section_module_area_temp',array('id'=>$section_area_temp[0]->id));
			}
		}
	
		$delete_section = '';
			
		//copy to section delete section TEMP
		if(count($section_obj_temp)>0)
		{
			//delete section
			$delete_section = $section_temp->delete('wc_section_temp', array('id'=>$section_obj_temp[0]->id));
		}
	
		//succes or error messages displayed on screen
		if($delete_section)
		{
			$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success keep current')));
			$arr_success = array('serial'=>'approved');
			echo json_encode($arr_success);
		}
		else
		{
			$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors to keep current contents')));
		}
	}
}
