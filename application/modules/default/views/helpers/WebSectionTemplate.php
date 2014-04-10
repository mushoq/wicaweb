<?php
class Zend_View_Helper_WebSectionTemplate extends Zend_View_Helper_Abstract
{
	
	
	public function webSectionTemplate($area)
	{
		$html = '';		
		
		$html.= '<div class="row-fluid">';
			$html.= '<div class="span12">';
				$html.= '<div class="page-header">';
					$html.= '<h1>Header Layout Default</h1>';
				$html.= '</div>';
			$html.= '</div>';
		$html.= '</div>';
		$html.= '<div class="row-fluid">';
			$html.= '<div class="span12">';
				$html.= '<div class="page-header">';
					$html.= '<h1>Menu <a href="/">'.$lang->translate('Home').'</a></h1>';
				$html.= '</div>';
			$html.= '</div>';		  				  		
	  	$html.= '</div>';
	  	$html.= '<div class="row-fluid">';
	  		$html.= '<div class="span6">';
	  			$html.= '<div class="row-fluid">';			  			
					$html.= '<div class="page-header">';
						$html.= '<h2>area 1</h2>';
						$html.= $area[1];		
					$html.= '</div>';
				$html.= '</div>';
	  		$html.= '</div>';
	  		$html.= '<div class="span6">';
	  			$html.= '<div class="row-fluid">';			  			
					$html.= '<div class="page-header">';
						$html.= '<h2>area 2</h2>';
						$html.= $area[2];		
					$html.= '</div>';
				$html.= '</div>';
	  			$html.= '<div class="row-fluid">';			  			
					$html.= '<div class="page-header">';
						$html.= '<h2>area 3</h2>';
						$html.= $area[3];		
					$html.= '</div>';
				$html.= '</div>';
	  		$html.= '</div>';
	  	$html.= '</div>';		
		
		//$html.= $area_1.' '.$area_2.' '.$area_3;
		
		return $html;
	}
}