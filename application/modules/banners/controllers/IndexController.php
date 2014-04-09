<?php
/**
 *	Banners module
 *
 * @category   WicaWeb
 * @package    Banners_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Diego Perez
 */

class Banners_IndexController extends Zend_Controller_Action
{
	/**
	 * Loads header, footer and menu
	 */
    public function init()
    {

		//Create Zend layout
		$layout = new Zend_Layout();
		// Set a layout scripts path
		$layout->setLayoutPath(APPLICATION_PATH.'/modules/core/layouts/scripts/');
		// choose a different layout script:
		$layout->setLayout('core');
		
		//session
		$id = New Zend_Session_Namespace('id');

		/** sections tree**/
		
		//Create section and section temp model
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

		//Check if user profile is admin profile
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
		
			/** Begin filter sections with variable area only (Banner Module Control)**/

			//Find all sections with variable content
			$sections_list_aux = $section->getSectionWithVariableContent();
				
			//sections list id array
			if(isset($sections_list_aux))
			{
				foreach ($sections_list_aux as $sec)
				{
					$sections_arr_id_aux[] = array('id'=>$sec['id']);
				}
			}
		
			//Get sections with content variable and non temp
			if(isset($sections_arr_id_aux)){
				foreach ($sections_arr as $sav){
					foreach ($sections_arr_id_aux as $sa)
					{
						if($sav['id']==$sa['id']){
							$sections_arr_list[] = $sav;
						}
					}
				}
			}
		
			/** End filter sections with fixed area only (End Banner Module Control)**/
		
			$sections_arr = array();
				
			if(isset($sections_arr_list)){
				$sections_arr = $sections_arr_list;
			}
		
		
		}
		else //No Admin Profile
		{
			$subsection_arr = array();
			$section_aux = array();
			
			//Get of session var the sections allowed by actual user
			$user_allowed_sections_arr = explode(',',$id->user_allowed_sections);

			/** Begin filter sections with variable area only (Banner Module Control)**/
			
			//Find all sections with variable content
			$sections_list_aux = $section->getSectionWithVariableContent();
	 
			//sections list array
			if($sections_list_aux)
			{
				foreach ($sections_list_aux as $sec)
				{
					$sections_arr_variable[] = array('id'=>$sec->id,
							'section_parent_id'=>$sec->section_parent_id,
							'title'=>$sec->title,
							'article'=>$sec->article
					);
				}
			}
		
			/** End filter sections with fixed area only (End Banner Module Control)**/
			
			//Get available sections for user
			
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
		
			//Get only variable sections (Banner Module Control)
		
			if($sections_arr_variable){
				foreach ($sections_arr as $sav){
					foreach ($sections_arr_variable as $sa)
					{
						if($sav['id']==$sa['id']){
							$sections_arr_list[] = $sav;
						}
					}
				}
			}
		
			$sections_arr = array();
			
			if(isset($sections_arr_list)){
				$sections_arr = $sections_arr_list;
			}
		
		}

		// Ordering sections by article and number
		
		$sort_col_number = array();
		$sort_col_article = array();
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
		 
		//Register html_list in view
		$this->view->data = $html_list;

		//Disabled display section bar in index
		$this->view->displaysectionbar = false;
		 
		$cms_arr = array();
		 
		//Get module_id by module_name
		 
		$module_obj = new Core_Model_Module();
		$module = $module_obj->find('wc_module',array('name'=>'Banners'));
		$module_id = $module[0]->id;
		 
		//Get user module action for banner 
		if($id->user_modules_actions){
			foreach ($id->user_modules_actions as $k => $mod)
			{
				 
				if($mod->module_id == $module_id)
				{
					$cms_arr[$mod->action_id] = array('action'=> $mod->action_name, 'title'=>$mod->action_title);
				}
			}
		}

		//Put sections array in session var
		$id->sections_banner_array = $sections_arr;
		
		//Put user module actions in view
		$this->view->cms_links = $cms_arr;
    	
    	// render final layout
    	echo $layout->render();
    }
    
    /**
     * Builds parent sections tree
     */
    public static function buildSectionParentTree(&$branch = array(), $parentId)
    {
    	$section = new Core_Model_Section();
    	$subsection_obj = $section->find('wc_section',array('id'=>$parentId));
    	$branch[] = $parentId;
    
    	if ($subsection_obj[0]->section_parent_id) {
    		$children = self::buildSectionParentTree($branch, $subsection_obj[0]->section_parent_id);
    	}
    	return $branch;
    }    

	/**
	 * Loads section contents according id or specific section
	 */
	public function indexAction()
    {
    	
    	$this->_helper->layout->disableLayout ();
    	$this->_helper->viewRenderer->setNoRender();
    	
    }

}
