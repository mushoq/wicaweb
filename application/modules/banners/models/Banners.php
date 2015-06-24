<?php
/**
 *	Banners model
 *
 * @category   WicaWeb
 * @package    Banners_Model
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Diego Perez
 */

class Banners_Model_Banners extends Core_Model_Factory
{
		
	public static $publication_type = array('calendar'=>'Calendar','hits'=>'Hits');
	public static $status_banner = array('active'=>'Active','inactive'=>'Inactive');
	
	/**
	 * This function get the last saved banner.
	 * @param
	 * @return array $last_banner_saved
	 */
	public static function getLastBannerId(){
	
		//get table adapter
		$adapter = Zend_Db_Table::getDefaultAdapter();
	
		$sql = 'SELECT MAX(id) as last_banner FROM banner';
			
		$data = $adapter->query($sql);
	
		$result = $data->fetchall();
	
		$last_banner = array();
		if($result){
			$last_banner = $result;
		}
	
		return $last_banner;
	}
	
	/**
	 * Renders section contents
	 */
	public function rendercontents($section_id, $storage)
	{
		$banners_list = array();
	
		//Get module_id by module_name
		$module_obj = new Core_Model_Module();
		$module = $module_obj->find('wc_module',array('name'=>'Banners'));
		$module_id = $module[0]->id;
	
		/** Get banner list **/
		//Get module description by module(Banners)
		$module_description_obj = new Core_Model_ModuleDescription();
		$module_description_list = $module_description_obj->find('wc_module_description',array('module_id'=>$module_id));
	
		//Create section module area model
		if($storage == 'no')
			$section_module_area_obj = new Core_Model_SectionModuleArea();
		else
			if($storage == 'yes')
			$section_module_area_obj = new Core_Model_SectionModuleAreaStorage();
		
		//Get banners by section
		foreach ($module_description_list as $md)
		{
			if($storage == 'no')
				$section_module_area_item = $section_module_area_obj->find('wc_section_module_area',array('module_description_id'=>$md->id,'section_id'=>$section_id));
			else
				if($storage == 'yes')
					$section_module_area_item = $section_module_area_obj->find('wc_section_module_area_storage',array('module_description_id'=>$md->id,'section_id'=>$section_id));
			
			if($section_module_area_item)
			{
				foreach ($section_module_area_item as $sma)
				{
					$section_module_area_list[] = $sma;
				}
			}
		}
		 
		//Get module_description_id of section_module_area
		if(isset($section_module_area_list))
		{
			foreach ($section_module_area_list as $smal)
			{
				$module_description_banners = $module_description_obj->find('wc_module_description',array('id'=>$smal->module_description_id));
				if($module_description_banners)
				{
					foreach ($module_description_banners as &$mdi)
					{
						$template_areas = new Core_Model_Area();
						$area_tpl = $template_areas->find('wc_area',array('id'=>$smal->area_id));
						if(count($area_tpl)>0)
						{
							$mdi->area = $area_tpl[0]->name;
						}
						$module_descriptions_banners_list[] = $mdi;
					}
	
				}
			}
		}
		 
		//Get banner data by module_description
		if(isset($module_descriptions_banners_list))
		{
			//Get banners list data by module_area
			$banner_obj = new Banners_Model_Banners();
			$banner_counts_obj = new Banners_Model_BannerCount();
	
			foreach ($module_descriptions_banners_list as $mdbl)
			{
				$banner_item = $banner_obj->find('banner', array('id' => $mdbl->row_id, 'status'=>'active'));
				$banner_counts_itm = $banner_counts_obj->find('banner_counts', array('banner_id' => $mdbl->row_id));
				if(count($banner_item)>0 && count($banner_counts_itm)>0)
				{
					$banner_field_arr = array(get_object_vars($banner_item[0]));
					$banner_count_arr = array(get_object_vars($banner_counts_itm[0]));
	
					foreach ($banner_field_arr as $k => $bfa)
					{
						$banner_field_arr[$k]['area'] = $mdbl->area;
						foreach ($banner_count_arr as $bca)
						{
							if($bfa['id'] == $bca['banner_id'])
								$banner_field_arr[$k]['count_hits'] = $bca['count_hits'];
						}
					}
					$banners_list[] = $banner_field_arr[0];
				}
			}
		}
	
		//Ordering banners by order_number
		if(isset($banners_list))
		{
			$sort_col_number = array();
			foreach ($banners_list as $key=> $row)
			{
				$sort_col_number[$key] = $row['order_number'];
			}
			array_multisort($sort_col_number, SORT_ASC, $banners_list);
		}
	
		return $banners_list;
	}
		
}
