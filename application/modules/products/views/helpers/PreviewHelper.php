<?php
/**
 * Load object preview
 *
 * @category   wicaWeb
 * @package    Banners_view_helpers
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license	   GNP
 * @author	   Santiago Arellano
 * @version    1.0
 */

class Zend_View_Helper_PreviewHelper extends Zend_View_Helper_Abstract {
	
	function previewHelper($content_id, $content, $content_type) 
	{		
		//Proceed according to the object type			
		switch ($content_type)
		{	
			case 'flash' :
							$return = '<div id="alignment_cont_'.$content_id.'">
										<object type="application/x-shockwave-flash" data="/uploads/banners/' . $content . '" width="320" height="240">
											<param name="movie" value="/uploads/banners/' . $content . '" />
											<param name="quality" value="high" />
											<param name="wmode" value="opaque" />
										</object>
									</div>';								
							return $return;				
							break;
			
			case 'image':
							$return = '<div id="banner_'.$content_id.'" class="overflow_hidden"><img';
							$return .= ' src="/uploads/banners/'.$content.'" ';
							$return .= '/>';
							$return .= '</div>';				
							return  $return;
							break;
			
			case 'html' :				
							return '<div id="banner_'.$content_id.'">'.$content.'</div>';
							break;
		}		
	}
}