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

class Zend_View_Helper_BannerHelper extends Zend_View_Helper_Abstract {
	
    function bannerHelper($section_id, $area_id) 
    {
        $banner_obj = new Banners_Model_Banners();
        
        $banners_list = $banner_obj->getbannersbysectionandarea($section_id, $area_id);
        $return = '';
        foreach($banners_list as $key => $value){
            
            $today = date('Y-m-d');	
            $show_banner = false;
            $show_banner1 = false;
            $show_banner2 = false; 
            if($value->type == 'calendar'){
                if($value->publish_date){		

                    list($ysp, $msp, $dsp) = explode('-', $today);
                    list($yep, $mep, $dep) = explode('-', $value->publish_date);
                    $now = mktime(0, 0, 0, $msp, $dsp, $ysp);
                    $publish = mktime(0, 0, 0, $mep, $dep, $yep);
                                
                    if($publish < $now || $publish == $now){
                        $show_banner1 = true;				
                    }
                }

                if($value->expire_date){
                    $show_banner = false;        
                    list($ysp, $msp, $dsp) = explode('-', $today);
                    list($yep, $mep, $dep) = explode('-', $value->expire_date);
                    $now = mktime(0, 0, 0, $msp, $dsp, $ysp);
                    $expire = mktime(0, 0, 0, $mep, $dep, $yep);

                    if($expire > $now || $expire == $now){				
                        $show_banner2 = true;
                    }
                }
                $show_banner = $show_banner2 && $show_banner1;
            }else{
                
                if($value->count_hits < $value->hits){
                    $show_banner = true;
                }
            }
            
            
            if($show_banner){
                $banner_obj->updateviews($value->id);
                

                    if($value->id && $value->content && $value->banner_type){
                        $return .= '<div id="banner_'.$value->id.'" class="banner">';
                        switch ($value->banner_type)
                        {
                            case 'flash' :
                                    list($widthSWF, $heightSWF, $typeSWF, $attrSWF) = getimagesize('uploads/banners/' . $value->content);
                                    //var_dump($widthSWF, $heightSWF, $typeSWF, $attrSWF);die();
                                    $return .= '
                                    <object type="application/x-shockwave-flash" data="/uploads/banners/' . $value->content . '" width="'.$widthSWF.'" height="'.$heightSWF.'" style="width:'.$widthSWF.'px; height:'.$heightSWF.'px;">
                                    <param name="movie" value="/uploads/banners/'.$value->content.'" />
                                    <param name="quality" value="high" />
                                    <param name="wmode" value="opaque" />
                                    </object>
                                    ';					

                                    break;

                            case 'image':
                                    
                                    if($value->link){
                                            $return .= '<a href="/bannerlink/'.$value->id.'/' . $section_id. '" target="_blank" >';
                                    }
                                    $return .= '<img src="/uploads/banners/'.$value->content.'" />';
                                    if($value->link)
                                            $return .= '</a>';							
                                    

                                    break;

                            case 'html' :							
                                    $return .= $value->content;
                                    break;
                        }				
                    	$return .= '</div>';			
                    

                
                    }
                
            }
            
        }
        return $return;
    }
}
