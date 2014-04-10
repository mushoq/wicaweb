<?php
/**
 *	Products model
 *
 * @category   WicaWeb
 * @package    Products_Model
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Santiago Arellano
 */

class Products_Model_Products extends Core_Model_Factory
{
	public static $available = array('yes'=>'Yes','no'=>'No');
	public static $feature = array('yes'=>'Yes','no'=>'No');
	public static $status = array('active'=>'Active','inactive'=>'Inactive');
	
	/**
	 * Renders section contents
	 */
	public function rendercontents($section_id, $storage='no')
	{
		$products_list = array();
		
		//Get module_id by module_name
		$module_obj = new Core_Model_Module();
		$module = $module_obj->find('wc_module',array('name'=>'Products'));
		$module_id = $module[0]->id;
		
		/** Get product list **/
		//Get module description by module(Products)
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
				$module_description_products = $module_description_obj->find('wc_module_description',array('id'=>$smal->module_description_id));
				if($module_description_products)
				{
					foreach ($module_description_products as &$mdi)
					{
						$template_areas = new Core_Model_Area();
						$area_tpl = $template_areas->find('wc_area',array('id'=>$smal->area_id));
						if(count($area_tpl)>0)
						{
							$mdi->area = $area_tpl[0]->name;
						}
						$module_descriptions_products_list[] = $mdi;
					}
		
				}
			}
		}

		//Get banner data by module_description
		if(isset($module_descriptions_products_list))
		{
			//Get banners list data by module_area
			$product_obj = new Products_Model_Products();
			$product_catalog_obj = new Products_Model_ProductCatalog();
			
			foreach ($module_descriptions_products_list as $mdbl)
			{
				$product_item = $product_obj->find('product', array('id' => $mdbl->row_id, 'available' => 'yes'));
				$product_catalog_itm = $product_catalog_obj->find('product_catalog', array('product_id' => $mdbl->row_id));
				
				if(count($product_item)>0 && count($product_catalog_itm)>0)
				{
					$product_field_arr = array(get_object_vars($product_item[0]));
					foreach ($product_field_arr as $k => $pfa)
					{
						$product_field_arr[$k]['catalog'] = array();
						$product_field_arr[$k]['area'] = $mdbl->area;
						
						foreach ($product_catalog_itm as $pca)
						{
							
							if($pfa['id'] == $pca->product_id )
								$product_field_arr[$k]['catalog'][] = $pca;
						}
					}
					$products_list[] = $product_field_arr[0];
				}
			}
		}
		
		//Ordering banners by order_number
		if(isset($products_list))
		{
			$sort_col_number = array();
			foreach ($products_list as $key=> $row)
			{
				$sort_col_number[$key] = $row['order_number'];
			}
			array_multisort($sort_col_number, SORT_ASC, $products_list);
		}
		
		return $products_list;		
	}
		
}