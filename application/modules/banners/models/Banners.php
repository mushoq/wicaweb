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
	
        public function getbannersbysection($section_id){
            $table = Zend_Db_Table::getDefaultAdapter();
            $sql = "SELECT
            wc_banner.*, 
            wc_banner_by_section.section_id,
            wc_banner_by_section.area_banner_id,
            wc_banner_by_section.order_number

            FROM
            wc_banner
            INNER JOIN 
            wc_banner_by_section
            ON wc_banner.id = wc_banner_by_section.banner_id

            WHERE
            wc_banner_by_section.section_id = $section_id

            ORDER BY
            wc_banner_by_section.order_number"; 
            $select = $table->query($sql);

            $banners = $select->fetchAll();
            $banners = GlobalFunctions::converttoobject($banners);

            return $banners;
        }
        
        public function getbannersbysectionandarea($section_id, $area_id){
            $table = Zend_Db_Table::getDefaultAdapter();
            $sql = "SELECT
            wc_banner.*, 
            wc_banner_by_section.section_id,
            wc_banner_by_section.area_banner_id,
            wc_banner_by_section.order_number,
            wc_banner_counts.count_hits
            FROM
            wc_banner
            INNER JOIN 
            wc_banner_by_section
            ON wc_banner.id = wc_banner_by_section.banner_id
            INNER JOIN 
            wc_banner_counts
            ON wc_banner.id = wc_banner_counts.banner_id
            
            WHERE
            wc_banner_by_section.section_id = $section_id
            AND
            wc_banner_by_section.area_banner_id = $area_id
            ORDER BY
            wc_banner_by_section.order_number"; 
            $select = $table->query($sql);

            $banners = $select->fetchAll();
            $banners = GlobalFunctions::converttoobject($banners);

            return $banners;
        }
        
        
        public function updatehits($banner_id){
            $table = Zend_Db_Table::getDefaultAdapter();
            $sql = "UPDATE wc_banner_counts 
                SET count_hits = count_hits + 1
                WHERE banner_id = '".$banner_id."'"; 
            $table->query($sql);
        }
        
        public function updateviews($banner_id){
            $table = Zend_Db_Table::getDefaultAdapter();
            $sql = "UPDATE wc_banner_counts 
                SET count_views = count_views + 1
                WHERE banner_id = '".$banner_id."'"; 
            $table->query($sql);
        }
        public function updateorder($banner_id, $section_id, $order){
            $table = Zend_Db_Table::getDefaultAdapter();
            $sql = "UPDATE wc_banner_by_section 
                SET order_number = ".$order."
                WHERE banner_id = ".$banner_id." AND section_id = ".$section_id.""; 
            $table->query($sql);
        }
        
        public function getbannersforvinculation($section_id, $signal){
            $table = Zend_Db_Table::getDefaultAdapter();
            $sql = "SELECT
            wc_banner.*, 
            wc_banner_by_section.section_id,
            
            FROM
            wc_banner
            INNER JOIN 
            wc_banner_by_section
            ON wc_banner.id = wc_banner_by_section.banner_id
            
            WHERE
            wc_banner_by_section.section_id $signal $section_id

            ORDER BY
            wc_banner_by_section.order_number"; 
            $select = $table->query($sql);

            $banners = $select->fetchAll();
            $banners = GlobalFunctions::converttoobject($banners);

            return $banners;
        }
		
}
