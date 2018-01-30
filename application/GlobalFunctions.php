<?php

/**
 * GlobalFunctions class store global functions that will be available through the application
 *
 * @category   wicaWeb
 * @package    Application GlobalFunctions
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license	   GNP
 * @version    1.1
 * @author      Jose Luis Landazuri - Santiago Arellano
 */


class GlobalFunctions {

	/**
	 * Build an array of words to translate according to language
	 * @param string $language
	 * @return array 
	 */
        private static $spanish_day = array(
		"Monday"=>"Lunes",
		"Tuesday"=>"Martes",
		"Wednesday"=>"Miércoles",
		"Thursday"=>"Jueves",
		"Friday"=>"Viernes",
		"Saturday"=>"Sábado",
		"Sunday"=>"Domingo",
	);

	private static $spanish_month = array(
		"January"=>"enero",
		"February"=>"febrero",
		"March"=>"marzo",
		"April"=>"abril",
		"May"=>"mayo",
		"June"=>"junio",
		"July"=>"julio",
		"August"=>"agosto",
		"September"=>"septiembre",
		"October"=>"octubre",
		"November"=>"noviembre",
		"December"=>"diciembre",
	);
	public static function translate($language) {

		// create an array to hold directory list
		$results = array();
		
		// create a handler for the directory
		$handler = opendir(APPLICATION_PATH.'/../resources/translate/'.$language);
		
		// open directory and walk through the filenames
		while ($file = readdir($handler))
		{
		
			// if file isn't this directory or its parent, add it to the results
			if ($file != "." && $file != "..") {
				$results[] = $file;
			}
		
		}
		
		// tidy up: close the handler
		closedir($handler);
		
		$text='';
		
		foreach($results as $key=>$r)
		{
			// read the directory path
			$f = fopen(APPLICATION_PATH.'/../resources/translate/'.$language.'/'.$r, "r");
			while ( $line = fgets($f, 1000) )
			{
				$text.=$line;
			}
			if(($key+1) < count($results))
			{
				$text.='|';
			}
		}
		
		$array_translate = array_map('trim',explode('|',$text));
		
		foreach($array_translate as $at)
		{
			// delete white spaces
			$aux_exp = array_map('trim',explode('>',$at));
			$array_final[$aux_exp[0]] = $aux_exp[1];
		}
		return $array_final;
	}
	
        public static function converttoobject($fetchall){
            $return = array();
		foreach($fetchall as $row)
		{
			$temp = array();
			foreach($row as $col=>$value)
			{
				$temp[$col] = utf8_encode($value);
			}
	
			//convert into object
			$obj = (object)$temp;
			array_push($return, $obj);
		}
                return $return;
        }
        
	/**
	 * Removes the special chars and returns the fixed filename
	 * @param string $filename
	 * @return string
	 */
	public static function formatFilename($filename) {
		$tofind = "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ· ";
		$replace = "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn--";
		$filename = utf8_decode($filename);
		$filename = strtr($filename, utf8_decode($tofind), $replace);
                $filename = str_replace("/-", "", $filename);
                $filename = str_replace("/", "", $filename);
                $filename = str_replace("--", "-", $filename);
		$filename = strtolower($filename);
		return utf8_encode($filename);
	}
	/**
	 * Get file weight
	 * @param string $file
	 * @return string
	 */
	public static function fileWeight($file)
        {
            $path = APPLICATION_PATH.'/../public/uploads/content/';
            $size = filesize($path.$file);
            
            $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
            return round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i];
        }
	/**
	 * Uploads the file and renames it according to convention
	 * @param string $filename
	 * @param string $path
	 * @return mixed string|boolean
	 */
	public static function uploadFiles($filename,$path){
		$original_file = $filename;
		$filename = GlobalFunctions::formatFilename($filename);
		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		$renamed_file = pathinfo($filename, PATHINFO_FILENAME).'_'.time().'.'.$extension;
		if(copy(APPLICATION_PATH. '/../public/uploads/tmp/'.$original_file, $path.$renamed_file))
			return $renamed_file;
		else
			throw new Zend_Exception("CUSTOM_EXCEPTION:FILE NOT FOUND.");			
	}
	
	/**
	 * Remove the old file that was updated
	 * @param string $filename
	 * @param string $path
	 * @return boolean
	 */
	public static function removeOldFiles($filename,$path){
		if(is_file($path.$filename) == TRUE){
			chmod($path.$filename, 0666);
			if(unlink($path.$filename))
				return true;
			else
				return false;
		}
		else
			return true;
	}
	
	/**
	 * Translate array options
	 * @param array $options	 
	 * @return array
	 */
	public static function arrayTranslate($options = array())
	{
		//add language library
		$lang = Zend_Registry::get('Zend_Translate'); 
		
		foreach ($options as $key => &$val)
		{
			//translate each value of the array
			$val = $lang->translate($val); 
		}	
		
		return $options;
	}

	/**
	 * Clean text values to insert in DB
	 * @param string $txt
	 * @return string
	 */	
	public static function value_cleaner($txt){
                //$link = mysql_connect("localhost", "amcecuad_mushoq", "@Mc3cu4d0r");
		//$txt = mysql_escape_string(strip_tags(utf8_decode($txt)));
                //$txt = mysql_real_escape_string(strip_tags(utf8_decode($txt)));
                $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
                $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
                $txt = strip_tags(utf8_decode(str_replace($search, $replace, $txt)));
		return $txt;
	}
	
	/**
	 * Return a date formatted to be saved on db
	 * @param string $value
	 * @return $string
	 */
	public static function setFormattedDate($value)
	{	
		$db_date = '';
		if($value)
		{
			$session_id = New Zend_Session_Namespace('id');
			$website = new Core_Model_Website ();
			$website_obj = $website->find ( 'wc_website', array ('id' => $session_id->website_id) );
			$website_arr = get_object_vars ( $website_obj [0] );
		
			switch ($website_arr ['date_format'])
			{
				case 'dd/mm/yyyy' :
					$splitter = '-'; //'d-m-Y'
					break;
				case 'yyyy/mm/dd' :
						$splitter = '-'; //yyyy-mm-dd
						break;
				case 'yy/mm/dd' :
					$splitter = '-'; //yy-mm-dd
					break;
				default :
					$splitter = '';
					break;
			}
		
			if($splitter!='')
				$formatted_value = str_replace("/", $splitter, $value);
			else
				$formatted_value = $value;
			$timestamp = strtotime($formatted_value);
			$db_date = date('Y-m-d',$timestamp);
		}
		
		return $db_date;	
	}
	/* Return Parent_id until root
         * @param $section_id
         * @return $parent_id
         */
        public static function parentUntilCero($section_id)
	{
                $parent_id = NULL;
		if($section_id)
		{
                    $section_id_temp = $section_id;
			do{
                            $section = new Core_Model_Section();
                            $section_obj = $section->find ( 'wc_section', array ('id' => $section_id_temp) );
                            
                            $parent_id = $section_id_temp;
                            $section_id_temp = $section_obj[0]->section_parent_id;
                        }while($section_obj[0]->section_parent_id > 0);
		}
		return $parent_id;
	}
	/**
	 * Return a date formatted to corresponding configuration
	 * @param string $value
	 * @return $string
	 */
	public static function getFormattedDate($value)
	{
		if($value)
		{
			$session_id = New Zend_Session_Namespace('id');
			$website = new Core_Model_Website ();
			$website_obj = $website->find ( 'wc_website', array ('id' => $session_id->website_id) );
			$website_arr = get_object_vars ( $website_obj [0] );
		
			switch ($website_arr ['date_format'])
			{
				case 'dd/mm/yyyy' :
					$format = 'd-m-Y';
					break;
				case 'mm/dd/yyyy' :
					$format = 'm/d/Y';
					break;
				case 'mm/dd/yy' :
					$format = 'm/d/y';
					break;
				case 'm/d/y' :
					$format = 'n/d/y';
					break;
				case 'yyyy/mm/dd' :
					$format = 'Y-m-d';
					break;
				case 'yy/mm/dd' :
					$format = 'y-m-d';
					break;		
				default :
					$format = 'Y/m/d';
					break;
			}		
			
			$date = date($format, strtotime($value));
			
			switch ($website_arr ['date_format'])
			{
				case 'dd/mm/yyyy' :
					$date = str_replace("-", "/", $date);
					break;			
				case 'yyyy/mm/dd' :
					$date = str_replace("-", "/", $date);
					break;
				case 'yy/mm/dd' :
					$date = str_replace("-", "/", $date);
					break;
				default :				
					break;
			}
		}
		return $date;
	}
	
	/**
	 *	Recursive function that builds sections tree
	 *	@param array $elements
	 *	@param int $parentId
	 *	@return array
	 */
	public static function buildSectionTree(array $elements, $parentId = 0) {
		$branch = array();
		foreach ($elements as $element) {
			if ($element['section_parent_id'] == $parentId) {
				$children = self::buildSectionTree($elements, $element['id']);
				if ($children) {
					$element['children'] = $children;
				}
				$branch[] = $element;
			}
		}
		return $branch;
	}
	
	/**
	 * Builds sections tree as html list
	 * @param array $tree
	 * @param boolean $isChild
	 * @return string
	 */
	public static function buildHtmlSectionTree($tree = array(), $isChild = false) {
		$html = '';
		if (count($tree)) {
			foreach ($tree as $item) {
				$editable = self::checkEditableSection($item['id']);
				$html.= '<li>';
				$html.= '<div class="parent_section';
				if($item['article']=='yes')
					$html.= ' article_element';
				$html.= '">';
				//get icon
				if(isset($item['children']))
					$html.= '<a section_id="'.$item['id'].'" class="open_sections" onclick="toggle_section_tree(this); return false;"><i id="open_'.$item['id'].'" class="open glyphicon glyphicon-plus"> </i></a>&nbsp;<i id="open_folder_'.$item['id'].'" class="glyphicon glyphicon-folder-close"> </i>&nbsp;';
				else{
					if($item['article']=='yes')
						$html.= '<div class="no-icon">&nbsp;</div>&nbsp;<i id="open_folder_'.$item['id'].'" class="glyphicon glyphicon-file"> </i>&nbsp;';
					else
						$html.= '<div class="no-icon">&nbsp;</div>&nbsp;<i id="open_folder_'.$item['id'].'" class="glyphicon glyphicon-folder-close"> </i>&nbsp;';
				}
				
				//parent section
				if(isset($item['title'])){
					$html.= '<span class="section_title_tree"><a id="tree_'.$item['id'].'" class="pointer" article="'.$item['article'].'" section_id="'.$item['id'].'" section_parent="'.$item['section_parent_id'].'"';
					
					if($editable)
						$html.= 'editable_section="yes"';
					else
						$html.= 'editable_section="no"';
					
					$html.= ' temp="'.$item['temp'].'">';
					
					$html.= $item['title'];
					
					$html.='</a></span>';
				}
				$html.= '</div>';
				//children
				if (isset($item['children']) && count($item['children'])>0) {
					$html.= '<ul class="sections_tree_internal hide" id="section_tree_internal_'.$item['id'].'">'.self::buildHtmlSectionTree($item['children'], true).'</ul>';
				}
				$html.= '</li>';
			}
		}
		return $html;
	}
	
	/**
	 * Builds sections tree as html list
	 * @param array $tree
	 * @param boolean $isChild
	 * @param int $level_limit
	 * @return string
	 */
	public static function buildHtmlSitemapTree($tree = array(), $isChild = false, $level_limit = 1, $level = 1, $storage= NULL) {
		$html = '';
		if (count($tree)) {
			foreach ($tree as $item) {
				$html.= '<li>';
				//parent section
				if(isset($item['title'])){
						$html.= '<a';
					if($item['area_type']=='variable'){
						if($storage)
							$html.= ' href="/site/1/indexold_indexold/index?id='.$item['id'].'">';
						else
							$html.= ' href="/site/1/index/index?id='.$item['id'].'">';
					}else
						$html.= ' href="#">';
					
					$html.= $item['title'].'</a>';
				}
				//children
				if (isset($item['children']) && count($item['children'])>0 && $level < ($level_limit)) {
					
					$html.= '<ul class="sections_tree_internal">'.self::buildHtmlSitemapTree($item['children'], true, $level_limit,$level+1).'</ul>';
				}
				$html.= '</li>';
			}
		}
		return $html;
	}
	
	/**
	 * Builds sections menu 
	 * @param array $tree
	 * @param boolean $isChild
	 * @return string
	 */
	public static function buildHtmlSectionMenu($tree = array(), $isChild = false, $storage=NULL, $selectedId=NULL) {
            
            //Get information about the current website
            $front_ids = New Zend_Session_Namespace('ids');
		$html = '';
		if (count($tree)) {
			foreach ($tree as $item) {
				//$html.= !isset($item['children'])? '<li class="dropdown" id="menu'.$item['id'].'">' : '<li>';
				$html.= '<li'; 
				if($item['id'] == $selectedId || $item['id'] == self::parentUntilCero($selectedId)){
					$html.= ' class="seccSelected" ';				
				}
				$html.= '>';
				//parent section
				if(isset($item['title'])){
					
                                    //new route with the new parameter : siteid 
					if(!$storage)
                                            if($item['homepage'] == 'yes'){
                                                $html.= '<a href="/">';
                                            }else{
                                                $html.= '<a href="/'.$item['url'].'">';
                                            }
						
					else 
						$html.= '<a href="/indexold_indexold/index?id='.$item['id'].'">';
					//$html.= !isset($item['children'])? '<a class="dropdown-toggle" data-toggle="dropdown" href="#menu'.$item['id'].'">' : '<a>';
															
					$html.= $item['title'];
					$html.='</a>';
				}
				//children
				if (isset($item['children']) && count($item['children'])>0) {
					//$html.= '<ul class="children" id="'.$item['id'].'">'.self::buildHtmlSectionTree($item['children'], true).'</ul>';
					if(!$storage)
						$html.= '<ul>'.self::buildHtmlSectionMenu($item['children'], true).'</ul>';
					else
						$html.= '<ul>'.self::buildHtmlSectionMenu($item['children'], true, 'storage').'</ul>';
				}
				$html.= '</li>';
			}
                         //$html.= '<li><a href="/contact.php" rel="shadowbox;width=320;height=436">CONTACT</a></li>';
		}		
		return $html;
	}
        public static function buildHtmlSectionMenu2($tree = array(), $isChild = false, $storage=NULL, $selectedId=NULL) {
            
            //Get information about the current website
            $front_ids = New Zend_Session_Namespace('ids');
            
		$html = '';
		if (count($tree)) {
			foreach ($tree as $item) {
				//$html.= !isset($item['children'])? '<li class="dropdown" id="menu'.$item['id'].'">' : '<li>';
				$html.= '<li'; 
				if($item['id'] == $selectedId){
					$html.= ' class="seccSelected" ';				
				}
				$html.= '>';
				//parent section
				if(isset($item['title'])){
                                    //new route with the new parameter : siteid 
					if(!$storage)
						$html.= '<a href="/'.$item['url'].'">';
					else 
						$html.= '<a href="/indexold_indexold/index?id='.$item['id'].'">';
					//$html.= !isset($item['children'])? '<a class="dropdown-toggle" data-toggle="dropdown" href="#menu'.$item['id'].'">' : '<a>';
					$html.= '['.$item['title'].']';
						
					$html.='</a>';
				}
				//children
				if (isset($item['children']) && count($item['children'])>0) {
					//$html.= '<ul class="children" id="'.$item['id'].'">'.self::buildHtmlSectionTree($item['children'], true).'</ul>';
					if(!$storage)
						$html.= '<ul>'.self::buildHtmlSectionMenu($item['children'], true).'</ul>';
					else
						$html.= '<ul>'.self::buildHtmlSectionMenu($item['children'], true, 'storage').'</ul>';
				}
				$html.= '</li>';
			}
		}		
		return $html;
	}
	
	/**
	 * Returns the correct icon for each content type
	 * @param string $content_type
	 * @return string
	 */
	public static function getContentIcons($content_type) {

		$icon = '';
		switch ($content_type)
		{
			case 'text' :
				$icon = 'glyphicon glyphicon-text-size';
				break;
			case 'image' :
				$icon = 'glyphicon glyphicon-picture';
				break;
			case 'link' :
				$icon = 'glyphicon glyphicon-link';
				break;
			case 'form' :
				$icon = 'glyphicon glyphicon-list-alt';
				break;
			case 'flash' :
				$icon = 'glyphicon glyphicon-play-circle';
				break;
			case 'flash video' :
				$icon = 'glyphicon glyphicon-facetime-video';
				break;
			case 'carousel' :
				$icon = 'glyphicon glyphicon-film';
				break;
			default :
				$icon = '';
				break;
		}
		
		return $icon;
	}
	
	/**
	 * Returns the correct icon for each content type
	 * @param string $content_type
	 * @return string
	 */
	public static function getContentPreviewForList($content) {
			
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		//get content type
		$content_type = strtolower($content['type']);
		
		$html_content_preview = '';
		switch ($content_type)
		{
			case 'text' :
				$html_content_preview = '<b>'.$lang->translate('Text').'</b>';
				if($content['title'])
					$html_content_preview.= ' - '.utf8_encode($content['title']);
				break;
			case 'image' :
				//get data of the image content 
				$content_type = new Core_Model_ContentType ();
				$data_content_type = $content_type->find ( 'wc_content_type', array ('id' => $content['content_type_id']) );
				
				$content_field = new Core_Model_ContentField();
				$content_field_temp = new Core_Model_ContentFieldTemp();
				
				/*if($content['temp']){
					$content_temp = new Core_Model_ContentTemp();
					$content_temp_obj = $content_temp->find('wc_content_temp', array('content_id'=>$content['id'])); 
					$data_content_field = $content_field->find ( 'wc_content_field_temp', array ('content_temp_id' => $content_temp_obj[0]->id) );
				}
				else*/
					$data_content_field = $content_field->find ( 'wc_content_field', array ('content_id' => $content['id']) );
				
				//create the html to show the preview list
				$html_content_preview = '<a  href="/uploads/content/' . $data_content_field[4]->value . '" class="highslide" onClick="return hs.expand(this, {captionId: '."'caption1'".'})">';
				$html_content_preview .= '<img class="preview_list_img thumbnail"';
				if($data_content_field [5]->value == 'frame'){
					$html_content_preview.= ' style="border: thin solid; color: black !important;"';
				}
				$html_content_preview .= ' src="'. imageRender::cache_image($data_content_field[4]->value, array('width' =>'100')) .'"/>';
				$html_content_preview .= '</a>';

				if($data_content_field [4]->value != '')
					$html_content_preview .= '<p>'.$data_content_field [0]->value.'</p>';
				break;
			case 'link' :
				$html_content_preview = '<b>'.$lang->translate('Link').'</b>';
				if($content['title'])
					$html_content_preview.= ' - '.utf8_encode($content['title']);
				break;
			case 'form' :
				$html_content_preview = '<b>'.$lang->translate('Form').'</b>';
				if($content['title'])
					$html_content_preview.= ' - '.utf8_encode($content['title']);
				break;
			case 'flash' :
				$html_content_preview = '<b>'.$lang->translate('Flash').'</b>';
				if($content['title'])
					$html_content_preview.= ' - '.utf8_encode($content['title']);
				break;
			case 'flash video' :
				$html_content_preview = '<b>'.$lang->translate('Flash Video').'</b>';
				if($content['title'])
					$html_content_preview.= ' - '.utf8_encode($content['title']);
				break;
			case 'carousel' :
				//get data of the image content
				$content_type = new Core_Model_ContentType ();
				$data_content_type = $content_type->find ( 'wc_content_type', array ('id' => $content['content_type_id']) );
				
				$content_field = new Core_Model_ContentField();
				$content_field_temp = new Core_Model_ContentFieldTemp();
				
				if($content['temp']){
					$content_temp = new Core_Model_ContentTemp();
					$content_temp_obj = $content_temp->find('wc_content_temp', array('content_id'=>$content['id'])); 
					$data_content_field = $content_field->find ( 'wc_content_field_temp', array ('content_temp_id' => $content_temp_obj[0]->id) );
				}
				else					
					$data_content_field = $content_field->find ( 'wc_content_field', array ('content_id' => $content['id']) );

				//generate the code to show in the preview list
				$html_content_preview = '<i class="glyphicon glyphicon-film"></i> <b>'.$lang->translate('Carousel').'</b>';
				
				$html_content_preview .= '<div id="carousel_preview_list'.$content['id'].'" class="no-border carousel" >';
				
				$array_images = explode ( ',', $data_content_field [0]->value );
				if(count($array_images)>1)
					array_pop ( $array_images );
				if($array_images){
					foreach ( $array_images as $k=>$ai ) {
						if($k>0)
							break;
						else
							$html_content_preview .= '<img class="preview_list_img thumbnail" src="' . imageRender::cache_image($ai, array('width' =>'100')) . '"/>';
							
					}
				}
				
				$html_content_preview .= '<a id="thumb_carousel_left_'.$content['id'].'" class="carousel-control_preview_list left">&lsaquo;</a>
										  <a id="thumb_carousel_right_'.$content['id'].'" class="carousel-control_preview_list right">&rsaquo;</a>';
				
				$html_content_preview .= '</div>';
				break;
			default :
				$html_content_preview = '';
				break;
		}
	
		return $html_content_preview;
	}
        
        //Toma imagen del articulo para frontEnd
        public static function getContentPreviewForArticle($content,$width, $height = '') {
                       
		//translate library
		$lang = Zend_Registry::get('Zend_Translate');
		
		//get content type
		$content_type = strtolower($content['type']);
		
		$html_content_preview = '';
		switch ($content_type)
		{
			case 'image' :
				//get data of the image content 
				$content_type = new Core_Model_ContentType ();
				$data_content_type = $content_type->find ( 'wc_content_type', array ('id' => $content['content_type_id']) );
				
				$content_field = new Core_Model_ContentField();
				$content_field_temp = new Core_Model_ContentFieldTemp();
					$data_content_field = $content_field->find ( 'wc_content_field', array ('content_id' => $content['id']) );
				
				//create the html to show the preview list
				
				
				$html_content_preview .= imageRender::cache_image($data_content_field[4]->value, array('width' =>$width, 'height'=>$height));
				break;
		}
	
		return $html_content_preview;
	}
	
	/**
	 * Builds sections tree as html list
	 * @param array $tree
	 * @param boolean $isChild
	 * @return string
	 */
	public static function buildHtmlSectionSelectorTree($tree = array(), $isChild = false) {
		$html = '';
		if (count($tree)) {
			foreach ($tree as $item) {
				$html.= '<li>';
				$html.= '<div class="parent_section">';
				//get icon
				if(isset($item['children']))
					$html.= '<a section_id="'.$item['id'].'" class="open_sections" onclick="toggle_section_selector(this); return false;"><i id="open_selector_'.$item['id'].'" class="open glyphicon glyphicon-plus"> </i></a>&nbsp;<i id="open_folder_selector_'.$item['id'].'" class="glyphicon glyphicon-folder-close"> </i>&nbsp;';
				else{
					$html.= '<div class="no-icon">&nbsp;</div>&nbsp;<i id="open_folder_selector_'.$item['id'].'" class="glyphicon glyphicon-folder-close"> </i>&nbsp;';
				}
	
				//parent section
				if(isset($item['title'])){
					$html.= '<a id="section_selector_'.$item['id'].'" class="pointer" article="'.$item['article'].'" section_id="'.$item['id'].'" section_parent="'.$item['section_parent_id'].'">';
						
					$html.= $item['title'];
						
					$html.='</a>';
				}
				$html.= '</div>';
				//children
				if (isset($item['children']) && count($item['children'])>0) {
					$html.= '<ul class="section_selector_internal hide" id="section_selector_internal_'.$item['id'].'">'.self::buildHtmlSectionSelectorTree($item['children'], true).'</ul>';
				}
				$html.= '</li>';
			}
		}
		return $html;
	}
	
	/**
	 * Checks the permissions to edit sections.
	 * Function used in the creation of the section tree.
	 * @return boolean
	 */
	public static function checkEditableSection($section_id) {
		//session
		$id = New Zend_Session_Namespace('id');
		$edit = false;
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
			if(count($cms_arr)>0)
			{
				foreach($cms_arr as $action_id => $opt)
				{
					if($opt['action']=='edit'){
						$edit = true;	
					}
				}
										
				if($id->user_allowed_sections)
				{
					//current section info
					$section = New Core_Model_Section();
					$section_temp = New Core_Model_SectionTemp();
					/*if($is_temp)
					{
						$section_aux = $section_temp->find('wc_section_temp',array('id'=>$section_id));
						$is_article = $section_aux[0]->article;
						$section_parent_id = $section_aux[0]->section_parent_id;
						
						$user_allowed_sections_arr = explode(',',$id->user_allowed_sections);
						foreach ($user_allowed_sections_arr as $uas)
						{
							$serial = '';
							$serial_arr = explode('_',$uas);
							
							if($serial_arr[0]=='t')
							{
								$serial = $serial_arr[1];
							}
							
							if($serial)
							{
								if($section_id == $serial)
								{
									$edit = true;
									break;
								}
								else
								{
									if($is_article=='yes' && $section_parent_id==$serial){
										$edit = true;
										break;
									}
									else{
										$edit = false;
									}
								}
							}
							else
								$edit = false;
						}
					}
					else
					{*/
						$section_aux = $section->find('wc_section',array('id'=>$section_id));
						$is_article = $section_aux[0]->article;
						$section_parent_id = $section_aux[0]->section_parent_id;
						
						$user_allowed_sections_arr = explode(',',$id->user_allowed_sections);
						foreach ($user_allowed_sections_arr as $serial)
						{
							/*$serial = '';
							$serial_arr = explode('_',$uas);
							
							if($serial_arr[0]=='f')
							{
								$serial = $serial_arr[1];
							}*/
								
							if($serial)
							{
								if($section_id == $serial)
								{
									$edit = true;
									break;
								}
								else
								{
									if($is_article=='yes' && $section_parent_id==$serial){
										$edit = true;
										break;
									}
									else{
										$edit = false;
									}
								}
							}
							else 
								$edit = false;
						}
					//}
				}				
			}
		} 
		return $edit;
	}
	
	/**
	 * Checks the permissions to delete sections.
	 * Function used in the creation of the section tree.
	 * @return boolean
	 */
	public static function checkErasableSection($section_id) {
		//session
		$id = New Zend_Session_Namespace('id');
		$delete = false;
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
			if(count($cms_arr)>0)
			{				
				foreach($cms_arr as $action_id => $opt)
				{
					if($opt['action']=='delete'){
						$delete = true;
					}
				}
	
				if($id->user_allowed_sections)
				{					
					/*if($is_temp)
					{
						//current section info
						$section_temp = New Core_Model_SectionTemp();
						$section_aux = $section_temp->find('wc_section_temp',array('id'=>$section_id));
						$is_article = $section_aux[0]->article;
						$section_parent_id = $section_aux[0]->section_parent_id;

						$user_allowed_sections_arr = explode(',',$id->user_allowed_sections);
						foreach ($user_allowed_sections_arr as $uas)
						{
							$serial = '';
							$serial_arr = explode('_',$uas);
													
							if($serial_arr[0]=='t')
							{
								$serial = $serial_arr[1];
							}
						
							if($serial)
							{
								if($section_id == $serial)
								{
									$delete = true;
									break;
								}
								else
								{
									if($is_article=='yes' && $section_parent_id==$serial)
									{
										$delete = true;
										break;
									}
									else{
										$delete = false;
									}
								}
							}
						}
					}
					else 
					{*/
						//current section info
						$section = New Core_Model_Section();
						$section_aux = $section->find('wc_section',array('id'=>$section_id));
						$is_article = $section_aux[0]->article;
						$section_parent_id = $section_aux[0]->section_parent_id;
							
						$user_allowed_sections_arr = explode(',',$id->user_allowed_sections);
						foreach ($user_allowed_sections_arr as $serial)
						{
							/*$serial = '';
							$serial_arr = explode('_',$uas);
												
							if($serial_arr[0]=='f')
							{
								$serial = $serial_arr[1];
							}*/
						
							if($serial)
							{
								if($section_id == $serial)
								{
									$delete = true;
									break;
								}
								else
								{
									if($is_article=='yes' && $section_parent_id==$serial)
									{
										$delete = true;
										break;
									}
									else
									{
										$delete = false;
									}
								}
							}
							else
								$delete = false;
						}
					//}
				}
			}
		}
		return $delete;
	}
	
	/**
	 * Truncates an string 
	 * @param int $length
	 * @param boolean $cut_words
	 * @return string
	 */
	public static function truncate($string, $length, $cut_words=true)
	{
		if(strlen($string)>$length)
		{
			if($cut_words)
			{
				return substr($string, 0, $length).'...';
			}
			else
			{
				if(substr($string, $length-1, 1) == ' ')
				{
					return substr($string, 0, $length).'...';
				}
				else
				{
					return self::truncate($string, $length+1, $cut_words);
				}
			}
		}
		return $string;
	}
	
	/**
	 * Calculates the width of the image according to the available space
	 * @param string $area_width -> span of the area
	 * @param int $section_cols -> section available columns
	 * @param int $content_cols -> content columns
	 * @return decimal $content_width_number -> width in pixels
	 */
	public static function getImageWithForRender($area_width,$section_cols,$content_cols)
	{	
		//calculate width span	
		$row_factor = 12 / $section_cols;
		
		//recover render values
		$session_render_vals = new Zend_Session_Namespace('render_vals');
		//var that helps to check if the call comes from edit content or not.
		$edit = $session_render_vals->edit_preview;
		
		if($edit=='yes'){
			$col_factor = 12;
		}
		else{
			if($content_cols)
				$col_factor = $row_factor * $content_cols;
			else
				$col_factor = 12;
		}
		
		//fixed span value
		$fixed_col_md_12 = 1250;
		
		//var with the fixed percentages of the corresponding span values
		$span_percentages = array();
		$span_percentages['col-md-12'] = 99.99999998999999;
		$span_percentages['col-md-11'] = 91.489361693;
		$span_percentages['col-md-10'] = 82.97872339599999;
		$span_percentages['col-md-9'] = 74.468085099;
		$span_percentages['col-md-8'] = 65.95744680199999;
		$span_percentages['col-md-7'] = 57.446808505;
		$span_percentages['col-md-6'] = 48.93617020799999;
		$span_percentages['col-md-5'] = 40.425531911;
		$span_percentages['col-md-4'] = 31.914893614;
		$span_percentages['col-md-3'] = 23.404255317;
		$span_percentages['col-md-2'] = 14.89361702;
		$span_percentages['col-md-1'] = 6.382978723;
		$span_percentages['container'] = 99.99999998999999;
		
		$area_width_number = 0;
		$area_width_number = ($fixed_col_md_12*$span_percentages[$area_width])/100;
				
		if($area_width_number)
			$content_width_number = ($area_width_number*$span_percentages['col-md-'.$col_factor])/100;
		
		$content_width_number = ($content_width_number-30);
		
		return $content_width_number;
	}
	
	/**
	 * Calculates the width of the image according to the available space
	 * @param string $image_filename
	 * @param decimal $resized_width
	 * @return string
	 */
	public static function checkImageSize($image_filename,$resized_width)
	{
		//set the path for the content images
		$path = APPLICATION_PATH.'/../public/uploads/content/';
		$style = '';
		if($image_filename){
			//get the image size
			$image_data = getimagesize($path.$image_filename);
			$width = $image_data[0];
			$height = $image_data[1];
			
			//check if the resized image is bigger than the available space
			if($width>$resized_width)
				$style = "width:100%;";
		}
		return $style;
	}
	
	/**
	 * Get the actual language abbreviation for the selected website 
	 * @param string $id_website
	 * @return string
	 */
	public static function getLanguageAbbreviationOfWebsite($website_id)
	{
		//Get language_id of selected website
		$website = new Core_Model_Website();
		$website_aux = $website->find('wc_website',array('id'=>$website_id));
		$website_language_id=$website_aux[0]->language_id;
		
		//Get language abbreviation for set in locale
		
		$language = new Core_Model_WebsiteLanguage();
		$language_aux = $language->find('wc_website_language',array('id'=>$website_language_id));
		$language_abbreviation = $language_aux[0]->abbreviation;
		
			
		return $language_abbreviation;
	}
	
	/**
	 * Returns info about the watermark to use it in 
	 * @param int $website_id
	 * @return array
	 */
	public static function getWatermark($website_id)
	{
		//return array
		$watermark_data = array();
		if($website_id){
			
			$website_model = New Core_Model_Website();
			$my_website = $website_model->find('wc_website', array('id'=>$website_id)); //get the selected website dataÃ§
			//check if the website exists
			
			if($my_website){
				$my_website_data = get_object_vars($my_website[0]); //make an array of the object data
				
				//set the file name
				if($my_website_data['watermark'])
					$watermark_data['file'] =  $my_website_data['watermark']; 
				else 
					$watermark_data['file'] = '0';

				//set the position of the watermark
				if($my_website_data['watermark_pos'])
					$watermark_data['pos'] =  $my_website_data['watermark_pos'];
				else
					$watermark_data['pos'] = '0';
			}
		}		
		return $watermark_data;
	}

	/**
	 * Returns installation file existence
	 * @param 
	 * @return boolean
	 */	
	public static function checkInstallationFile(){
		
		if(file_exists(APPLICATION_PATH . '/../public/installer.lock'))
		{
			return true; 
		}else
			return false;
	}

	/**
	 * Create a sequence number
	 */
	public static function create_sequence() {
		list($usec, $sec) = explode(' ', microtime());
		return (float) $sec + ((float) $usec * 100000);
	}

	/**
	 * generate public users activation key
	 * @param unknown_type $fSize
	 * @param unknown_type $linSize
	 * @param unknown_type $type
	 * @param unknown_type $length
	 * @return string
	 */
	public static function generateActivationKey($fSize,$linSize,$type=true,$length=0){
		srand(self::create_sequence());
		$password="";
		$max_chars = round(rand($fSize,$linSize));
		$chars = array();
		for ($i="a"; $i<"z"; $i++) $chars[] = $i;
		$chars[] = "z";
		for ($i=0; $i<$max_chars; $i++) {
			$letra = round(rand(0, 1));
			if ($type == false)
				$password .= $chars[round(rand(0, count($chars)-1))];
			else
				$password .= round(rand(0, 9));
		}
	
		$password=md5($password);
	
		if($length)
			$password=substr($password,0,$length);
		else
			$password=substr($password,0,7);
	
		return $password;
	}

	
	public static function move_to_storage(){
		 		 
		$table = new Zend_Db_Table('wc_section');
		$select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
		$select->setIntegrityCheck(false)
		->join('wc_website', 'wc_website.id = wc_section.website_id', array('id'=>'wc_section.id'));	
		$select->where("DATE_ADD(wc_section.expire_date, INTERVAL wc_website.section_expiration_time DAY) <= NOW()");
		$select->where("section_parent_id IS NULL");

// 		Zend_Debug::dump($select->__toString());die;
		$result = $table->fetchAll($select);
		
		if($result){
			foreach($result as $res){
				
				//subsections		
				$subsection_arr = self::getChild($branch = array(), $res->id);
	//  			Zend_Debug::dump($subsection_arr);die;
				
				if($subsection_arr){
					
					//copy parent
					self::save_storage($res,'parent');
									
					while ($subsection_arr) {
						foreach($subsection_arr as $sub_arr){
							
							$section = new Core_Model_Section();
							$section_childs = $section->find('wc_section',array('section_parent_id'=>$sub_arr->id));
					
							if($section_childs){
								//copy parent
								self::save_storage($sub_arr,'parent');
	// 							echo 'parent';
							}else{
								//save section with no subsections
								self::save_storage($sub_arr,'child');
	// 							echo 'child';
							}		
						}
						$subsection_arr = self::getChild($branch = array(), $res->id);
	// 					$subsection_arr = array();
					}
					//save section with no subsections
					self::save_storage($res,'child');	
	// 				echo 'child';			
				}
				else{
					//save section with no subsections
					self::save_storage($res,'child');
	// 				echo 'child';
				}
				
			}
		}
		
		
		$table = new Zend_Db_Table('wc_section');
		$select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
		$select->setIntegrityCheck(false)
		->join('wc_website', 'wc_website.id = wc_section.website_id', array('id'=>'wc_section.id'));
		$select->where("DATE_ADD(wc_section.expire_date, INTERVAL wc_website.section_expiration_time DAY) <= NOW()");
		
		// 		Zend_Debug::dump($select->__toString());die;
		$result = $table->fetchAll($select);
		
		if($result){
			foreach($result as $res){
		
				//subsections
				$subsection_arr = self::getChild($branch = array(), $res->id);
				
				$parentsection_arr = self::getParents($branch = array(), $res->id);
				
				if($parentsection_arr){
						foreach(array_reverse($parentsection_arr) as $par_arr){
							self::save_storage($par_arr,'parent');
						}
				}				
				
				if($subsection_arr){
						
					//copy parent
					self::save_storage($res,'parent');
						
					while ($subsection_arr) {
						foreach($subsection_arr as $sub_arr){
								
							$section = new Core_Model_Section();
							$section_childs = $section->find('wc_section',array('section_parent_id'=>$sub_arr->id));
								
							if($section_childs){
								//copy parent
								self::save_storage($sub_arr,'parent');
								// 							echo 'parent';
							}else{
								//save section with no subsections
								self::save_storage($sub_arr,'child');
								// 							echo 'child';
							}
						}
						$subsection_arr = self::getChild($branch = array(), $res->id);
						// 					$subsection_arr = array();
					}
					//save section with no subsections
					self::save_storage($res,'child');
				}
				else{
					//save section with no subsections
					self::save_storage($res,'child');
				}
		
			}
		}		
			
	}
	
	/**
	 * Get child section
	 */
	public static function getChild(&$branch = array(), $parentId)
	{
		$section = new Core_Model_Section();
		
		$subsection_obj = $section->find('wc_section',array('section_parent_id'=>$parentId));	
		
		if(count($subsection_obj)>0)
		{
			foreach ($subsection_obj as $sub)
			{
				$branch[] = $sub;
				$children = self::getChild($branch, $sub->id);
			}
		}
		
		return $branch;
	}
	
	/**
	 * Get parent sections
	 */
	public static function getParents(&$branch = array(), $parentId)
	{
		$section = new Core_Model_Section();
	
		$subsection_obj = $section->find('wc_section',array('id'=>$parentId));
		if($subsection_obj){
			$parent_sec = $section->find('wc_section',array('id'=>$subsection_obj[0]->section_parent_id));
			$temp_stt = 0;
		
			if ($parent_sec)
			{
				$branch[] = $parent_sec[0];			
				$children = self::getParents($branch, $parent_sec[0]->id);
			}
		}
		return $branch;
		
	}	
	
	/**
	 * Get child section storage
	 */
	public static function getChildStorage(&$branch = array(), $parentId)
	{
		$section = new Core_Model_SectionStorage();
	
		$subsection_obj = $section->find('wc_section_storage',array('section_parent_id'=>$parentId));
	
		if(count($subsection_obj)>0)
		{
			foreach ($subsection_obj as $sub)
			{
				$branch[] = $sub;
				$children = self::getChildStorage($branch, $sub->id);
			}
		}
	
		return $branch;
	}
	
	/**
	 * Get parent sections storage
	 */
	public static function getParentsStorage(&$branch = array(), $parentId)
	{
		$section = new Core_Model_SectionStorage();
	
		$subsection_obj = $section->find('wc_section_storage',array('id'=>$parentId));
		if($subsection_obj){
			$parent_sec = $section->find('wc_section_storage',array('id'=>$subsection_obj[0]->section_parent_id));
			$temp_stt = 0;
	
			if ($parent_sec)
			{
				$branch[] = $parent_sec[0];
				$children = self::getParentsStorage($branch, $parent_sec[0]->id);
			}
		}
		return $branch;
	
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
	
	public static function save_storage($res, $relationship){
		
		if($relationship == 'child'){
			//section_id passed in URL
			$id = $res->id;
			//session
			$session = new Zend_Session_Namespace('id');
			
			$section = new Core_Model_Section();
			$section_temp = new Core_Model_SectionTemp();
			$section_obj_temp = $section_temp->find('wc_section_temp', array('section_id'=>$id));
			$content_temp = new Core_Model_ContentTemp();
				
			//subsections
			$subsection_arr = self::buildSectionChildrenTree($branch = array(), $id);
			
// 			Zend_Debug::dump($subsection_arr);die;
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
	// 						$contents_temp_aux[] = $lit['content_id'];
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
	// 		$contents_temp_aux = array();
	// 		$content_temp_arr = array();
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
		}	
		
		//Check if exist
		$section_storage = new Core_Model_SectionStorage();
		$section_storage_data = $section_storage->find('wc_section_storage',array('id'=>$res->id));
			
		//Save section storage
		$section_storage = new Core_Model_SectionStorage();
		$section_storage_obj = $section_storage->getNewRow ( 'wc_section_storage' );
		$section_storage_obj->id = $res->id;
		$section_storage_obj->section_parent_id = $res->section_parent_id;
		$section_storage_obj->website_id = $res->website_id;
		$section_storage_obj->section_template_id = $res->section_template_id;
		$section_storage_obj->internal_name = $res->internal_name;
		$section_storage_obj->title = $res->title;
		$section_storage_obj->subtitle = $res->subtitle;
		$section_storage_obj->title_browser = $res->title_browser;
		$section_storage_obj->synopsis = $res->synopsis;
		$section_storage_obj->keywords = $res->keywords;
		$section_storage_obj->type = $res->type;
		$section_storage_obj->created_by_id = $res->created_by_id;
		$section_storage_obj->updated_by_id = $res->updated_by_id;
		$section_storage_obj->creation_date = $res->creation_date;
		$section_storage_obj->last_update_date = $res->last_update_date;
		$section_storage_obj->approved = $res->approved;
		$section_storage_obj->author = $res->author;
		$section_storage_obj->publication_status = $res->publication_status;
		$section_storage_obj->feature = $res->feature;
		$section_storage_obj->highlight = $res->highlight;
		$section_storage_obj->publish_date = $res->publish_date;
		$section_storage_obj->expire_date = $res->expire_date;
		$section_storage_obj->show_publish_date = $res->show_publish_date;
		$section_storage_obj->rss_available = $res->rss_available;
		$section_storage_obj->external_link = $res->external_link;
		$section_storage_obj->target = $res->target;
		$section_storage_obj->comments = $res->comments;
		$section_storage_obj->external_comment_script = $res->external_comment_script;
		$section_storage_obj->display_menu = $res->display_menu;
		$section_storage_obj->homepage = $res->homepage;
		$section_storage_obj->order_number = $res->order_number;
		$section_storage_obj->article = $res->article;
		// Save data
		// 			Zend_Debug::dump($section_storage_obj);die;
		if($section_storage_data){
			$id = $section_storage->save ( 'wc_section_storage', $section_storage_obj );
		}else{
			$id = $section_storage->save ( 'wc_section_storage', $section_storage_obj, 'ins' );
		}
			
		//get profiles
		$section_profile = new Core_Model_SectionProfile();
		$section_profile_data = $section_profile->find('wc_section_profile',array('section_id'=>$res->id));
			
		if($section_profile_data){
			foreach($section_profile_data as $spd){
				$section_profile = new Core_Model_SectionProfile();
				$section_profile_obj = $section_profile->getNewRow ( 'wc_section_profile' );
				$section_profile_obj->profile_id =  $spd->profile_id;
				$section_profile_obj->section_storage_id =  $spd->section_id;
		
				$section_profile->save ( 'wc_section_profile', $section_profile_obj );
			}
		}
			
		//check if exist
		$section_module_area_storage = new Core_Model_SectionModuleAreaStorage();
		$section_module_area_storage_data = $section_module_area_storage->find('wc_section_module_area_storage',array('section_id'=>$res->id));
			
		//get data
		$section_module_area = new Core_Model_SectionModuleArea();
		$section_module_area_data = $section_module_area->find('wc_section_module_area',array('section_id'=>$res->id));
		
		//save section module area
		if($section_module_area_data){
			foreach($section_module_area_data as $smad){
				$section_module_area_storage = new Core_Model_SectionModuleAreaStorage();
				$section_module_area_storage_obj = $section_module_area_storage->getNewRow ( 'wc_section_module_area_storage' );
				$section_module_area_storage_obj->id =  $smad->id;
				$section_module_area_storage_obj->area_id =  $smad->area_id;
				$section_module_area_storage_obj->section_id =  $smad->section_id;
				$section_module_area_storage_obj->module_description_id =  $smad->module_description_id;
			
				if($section_module_area_storage_data)
					$section_module_area_storage->save ( 'wc_section_module_area_storage', $section_module_area_storage_obj );
				else
					$section_module_area_storage->save ( 'wc_section_module_area_storage', $section_module_area_storage_obj, 'ins' );
			}
		}
		
		//Check if exist
		$section_prints_storage = new Core_Model_SectionPrintsStorage();
		$section_prints_storage_data = $section_prints_storage->find('wc_section_prints_storage',array('section_id'=>$res->id));
		
		//get data
		$section_prints = new Core_Model_SectionPrints();
		$section_prints_data = $section_prints->find('wc_section_prints',array('section_id'=>$res->id));
			
		//save section prints
		if($section_prints_data){
			$section_prints_storage = new Core_Model_SectionPrintsStorage();
			$section_prints_storage_obj = $section_prints_storage->getNewRow ( 'wc_section_prints_storage' );
			$section_prints_storage_obj->id =  $section_prints_data[0]->id;
			$section_prints_storage_obj->section_id =  $section_prints_data[0]->section_id;
			$section_prints_storage_obj->count =  $section_prints_data[0]->count;
		
			if($section_prints_storage_data)
				$section_prints_storage->save ( 'wc_section_prints_storage', $section_prints_storage_obj );
			else
				$section_prints_storage->save ( 'wc_section_prints_storage', $section_prints_storage_obj, 'ins' );
		
		}
		
		//Check if exist
		$section_image_storage = new Core_Model_SectionImageStorage();
		$section_image_storage_data = $section_image_storage->find('wc_section_image_storage',array('section_id'=>$res->id));
		
		//get data
		$section_image = new Core_Model_SectionImage();
		$section_image_data = $section_image->find('wc_section_image',array('section_id'=>$res->id));
		
		//save image
		if($section_image_data){
			$section_image_storage = new Core_Model_SectionImageStorage();
			$section_image_storage_obj = $section_image_storage->getNewRow ( 'wc_section_image_storage' );
			$section_image_storage_obj->id =  $section_image_data[0]->id;
			$section_image_storage_obj->section_id =  $section_image_data[0]->section_id;
			$section_image_storage_obj->name =  $section_image_data[0]->name;
			$section_image_storage_obj->file_name =  $section_image_data[0]->file_name;
				
			if($section_image_storage_data)
				$section_image_storage->save ( 'wc_section_image_storage', $section_image_storage_obj );
			else
				$section_image_storage->save ( 'wc_section_image_storage', $section_image_storage_obj,'ins' );
		
		}
		
		/*			//Check if exist
		 $section_comment_storage = new Core_Model_SectionCommentStorage();
		$section_comment_storage_data = $section_comment_storage->find('wc_section_comment_storage',array('section_id'=>$res->id));
			
		//get data
		$section_comment = new Core_Model_SectionComment();
		$section_comment_data = $section_comment->find('wc_section_comment',array('section_id'=>$res->id));
			
		//save comment
		if($section_comment_data){
		$section_comment_storage = new Core_Model_SectionCommentStorage();
		$section_comment_storage_obj = $section_comment_storage->getNewRow ( 'wc_section_comment_storage' );
		$section_comment_storage_obj->id =  $section_comment_data[0]->id;
		$section_comment_storage_obj->section_id =  $section_comment_data[0]->section_id;
		$section_comment_storage_obj->name =  $section_comment_data[0]->name;
		$section_comment_storage_obj->file_name =  $section_comment_data[0]->file_name;
			
		if($section_comment_storage_data)
			$section_comment_storage->save ( 'wc_section_comment_storage', $section_comment_storage_obj );
		else
			$section_comment_storage->save ( 'wc_section_comment_storage', $section_comment_storage_obj,'ins' );
		}	*/
			
		//get contents
		$content_by_section = new Core_Model_ContentBySection();
		$content_by_section_data = $content_by_section->find('wc_content_by_section',array('section_id'=>$res->id));
			
		if($content_by_section_data){
			foreach($content_by_section_data as $cbs){
				$content = new Core_Model_Content();
				$content_data = $content->find('wc_content',array('id'=>$cbs->content_id));
			
				//check if exist
				$content_storage = new Core_Model_ContentStorage();
				$content_storage_data = $content_storage->find('wc_content_storage',array('id'=>$cbs->content_id));
			
				//save content storage
				$content_storage = new Core_Model_ContentStorage();
				$content_storage_obj = $content_storage->getNewRow ( 'wc_content_storage' );
				$content_storage_obj->id = $content_data[0]->id;
				$content_storage_obj->content_type_id = $content_data[0]->content_type_id;
				$content_storage_obj->website_id = $content_data[0]->website_id;
				$content_storage_obj->internal_name = GlobalFunctions::value_cleaner($content_data[0]->internal_name);
				$content_storage_obj->title = GlobalFunctions::value_cleaner($content_data[0]->title);
				$content_storage_obj->created_by = $content_data[0]->created_by;
				$content_storage_obj->updated_by = $content_data[0]->updated_by;
				$content_storage_obj->creation_date = $content_data[0]->creation_date;
				$content_storage_obj->last_update_date = $content_data[0]->last_update_date;
				$content_storage_obj->approved = $content_data[0]->approved;
				$content_storage_obj->status = $content_data[0]->status;
			
				if($content_storage_data)
					$content_storage->save ( 'wc_content_storage', $content_storage_obj );
				else
					$content_storage->save ( 'wc_content_storage', $content_storage_obj,'ins' );
				
			}
		}
			
		if($content_by_section_data){
			foreach($content_by_section_data as $cbs2){
				//check if exist
				$content_by_section_storage = new Core_Model_ContentBySectionStorage();
				$content_by_section_storage_data = $content_by_section_storage->find('wc_content_by_section_storage',array('section_id'=>$cbs2->section_id,'content_id'=>$cbs2->content_id));
				
				//save content by section storage
				$content_by_section_storage = new Core_Model_ContentBySectionStorage();
				$content_by_section_storage_obj = $content_by_section_storage->getNewRow ( 'wc_content_by_section_storage' );
				$content_by_section_storage_obj->id = $cbs2->id;
				$content_by_section_storage_obj->section_id = $cbs2->section_id;
				$content_by_section_storage_obj->content_id = $cbs2->content_id;
				$content_by_section_storage_obj->weight = $cbs2->weight;
				$content_by_section_storage_obj->column_number = $cbs2->column_number;
				$content_by_section_storage_obj->align = $cbs2->align;
				
				if($content_by_section_storage_data)
					$content_by_section_storage->save ( 'wc_content_by_section_storage', $content_by_section_storage_obj );
				else
					$content_by_section_storage->save ( 'wc_content_by_section_storage', $content_by_section_storage_obj,'ins' );
				
				
				//check if exist
				$form_field = new Core_Model_FormField();
				$form_field_data = $form_field->find('wc_form_field',array('content_id'=>$cbs2->content_id));
				
				if($form_field_data){
					//check if exist on storage
					$form_field_storage = new Core_Model_FormFieldStorage();
					$form_field_storage_data = $form_field_storage->find('wc_form_field_storage',array('content_id'=>$cbs2->content_id));
						
					//save form field storage
					$form_field_storage = new Core_Model_FormFieldStorage();
					$form_field_storage_obj = $form_field_storage->getNewRow ( 'wc_form_field_storage' );
					$form_field_storage_obj->id = $form_field_data[0]->id;
					$form_field_storage_obj->content_id = $form_field_data[0]->content_id;
					$form_field_storage_obj->name = GlobalFunctions::value_cleaner($form_field_data[0]->name);
					$form_field_storage_obj->description = GlobalFunctions::value_cleaner($form_field_data[0]->description);
					$form_field_storage_obj->type = $form_field_data[0]->type;
					$form_field_storage_obj->options = $form_field_data[0]->options;
					$form_field_storage_obj->required = $form_field_data[0]->required;
					$form_field_storage_obj->weight = $form_field_data[0]->weight;
						
					if($form_field_storage_data)
						$form_field_storage->save ( 'wc_form_field_storage', $form_field_storage_obj );
					else
						$form_field_storage->save ( 'wc_form_field_storage', $form_field_storage_obj,'ins' );
				
				}
				
				//get content fields
				$content_field = new Core_Model_ContentField();
				$content_field_data = $content_field->find('wc_content_field',array('content_id'=>$cbs2->content_id));
				
				if($content_field_data){
					foreach($content_field_data as $cfd){
				
						//check if exist on storage
						$content_field_storage = new Core_Model_ContentFieldStorage();
						$content_field_storage_data = $content_field_storage->find('wc_content_field_storage',array('id'=>$cfd->id));
						
						//save content field storage
						$content_field_storage = new Core_Model_ContentFieldStorage();
						$content_field_storage_obj = $content_field_storage->getNewRow ( 'wc_content_field_storage' );
						$content_field_storage_obj->id = $cfd->id;
						$content_field_storage_obj->field_id = $cfd->field_id;
						$content_field_storage_obj->content_id= $cfd->content_id;
						$content_field_storage_obj->value= GlobalFunctions::value_cleaner($cfd->value);
						
						if($content_field_storage_data)
							$content_field_storage->save ( 'wc_content_field_storage', $content_field_storage_obj );
						else
							$content_field_storage->save ( 'wc_content_field_storage', $content_field_storage_obj,'ins' );
						
					}
				}
				
				if($relationship == 'child'){	
					
					//delete content field
					$form_field = new Core_Model_FormField();
					$form_field->delete('wc_form_field',array('content_id'=>$cbs2->content_id));
					
					//delete content field
					$content_field = new Core_Model_ContentField();
					$content_field->delete('wc_content_field',array('content_id'=>$cbs2->content_id));
					
					//delete content by section
					$content_by_section = new Core_Model_ContentBySection();
					$content_by_section->delete('wc_content_by_section',array('section_id'=>$res->id));
						
					//delete content
					$content = new Core_Model_Content();
					$content->delete('wc_content',array('id'=>$cbs2->content_id));
				}
					
			}
		}
		
		if($relationship == 'child'){

			//delete section module area
			$section_module_area = new Core_Model_SectionModuleArea();
			$section_module_area->delete('wc_section_module_area',array('section_id'=>$res->id));
				
			//delete section prints
			$section_prints = new Core_Model_SectionPrints();
			$section_prints->delete('wc_section_prints',array('section_id'=>$res->id));
			
			//delete section image
			$section_image = new Core_Model_SectionImage();
			$section_image->delete('wc_section_image',array('section_id'=>$res->id));
				
			//delete section profile
			$section_profile = new Core_Model_SectionProfile();
			$section_profile->delete('wc_section_profile',array('section_id'=>$res->id));
				
			//delete section
			$section = new Core_Model_Section();
			$section->delete('wc_section',array('id'=>$res->id));	
		}	
	}
        
    public static function getCrums($idSeccion){
            $obj = new Core_Model_Section();
             $section_module_area = new Core_Model_SectionModuleArea();
             $area_aux = new Core_Model_Area();

            $crum = array();
            $seccion = $obj->find('wc_section',array('id'=>$idSeccion));
            
            $crum[] = $seccion[0];
            while(count($seccion)>0){
                    
                    $seccion = $obj->find('wc_section',array('id'=>$seccion[0]->section_parent_id));
                    if(count($seccion)>0)
                        $crum[] = $seccion[0];
            }  
            
            $crum = array_reverse($crum);

            $crum_arr = array();    	
            //sections list array
            if($crum) 
            {
                    foreach ($crum as $sec)
                    {
                        $area = 'wica_area_content';
                        if($sec->article == 'no'){
                            $area = $section_module_area->find('wc_section_module_area',array('section_id'=>$sec->id));
                            $area_data = $area_aux->find('wc_area',array('id'=>$area[0]->area_id));
                            $area = $area_data[0]->name;
                        }
                        
                        $crum_arr[] = array('id'=>$sec->id,
                            'section_parent_id'=>$sec->section_parent_id,
                            'title'=>$sec->title,
                            'article'=>$sec->article,
                            'url'=>$sec->url,
                            'area' => $area
                        );
                    }
            }
            return $crum_arr;
        }
        
        public static function sendMail($website_id, $email, $subject, $newFilename, $body)
        {
        
            //get smpt credential from website information
            $website = new Core_Model_Website();
            $website_data = $website->find('wc_website',array('id'=>$website_id));
            //create a transport to register smpt server credentials
            if($website_data[0]->smtp_hostname){
                    $tr = new Zend_Mail_Transport_Smtp($website_data[0]->smtp_hostname,
                            array('ssl' => 'ssl',
                                            'port'=>$website_data[0]->smtp_port, 
                                            'auth' => 'login',
                                            'username' => $website_data[0]->smtp_username,
                                            'password' => $website_data[0]->smtp_password, 
                                            'register'=>true));


                    Zend_Mail::setDefaultTransport($tr);						 
                    $mail = new Zend_Mail();
                    $mail->setFrom($website_data[0]->info_email, utf8_decode($website_data[0]->name));
                    $descripcion = '<html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Formulario Din&aacute;mica</title>
                        <style type="text/css">
                        body {
                            background-color:#FFFFFF;
                            font-family:Arial, Helvetica, sans-serif;
                        }
                        .texto {
                            font-size:12px;
                            font-weight:normal;
                            color:#5B5B5B;
                        }
                        </style>
                    </head>
                    <body>
                    <table width="673"  border="0" align="center" cellpadding="0" cellspacing="2">
                <tr>
                        <td height="101" colspan="2"><img src="'.$website_data[0]->website_url.'/uploads/website/'.$website_data[0]->logo.'"></td>
                </tr>
            <tr bgcolor="#EEEEEE">
                        <td colspan="2" class="texto"><p align="center">Hola, este es un mensaje de formulario en el sitio Web '.utf8_decode($website_data[0]->name).':</font> <br>
                                        </p></td>
                </tr>
            <tr bgcolor="#FFFFFF" >

            <td style="border-bottom:1px solid #EEEEEE;" width="55%" class="texto" colspan="2">'.utf8_decode($body).'</td>
            </tr>
            <tr> 
            <td align="center" colspan="2"><font color="red" size="1">&nbsp;</font> <br>
             <font color="red" size="1">&nbsp;
             </font>
             </td>
             </tr>
            <tr>
            <td colspan="2" bgcolor="#323232">
            &nbsp;
            </td>
            </tr>
            </table>
            <p align="center"><a style="text-decoration: none;" href="'.$website_data[0]->website_url.'"><font size="3" color="#000000">'.$website_data[0]->website_url.'</font></a></p>

            </body>
            </html>';
                    $mail->setBodyHtml($descripcion);
                    $mail->addTo($email);
                    $mail->setSubject($subject);
                    $fileInfo= explode(".", $newFilename); 
                    $extension = '.'.end($fileInfo); 
                    if($newFilename){
                    $content = file_get_contents(realpath('.').'/uploads/tmp/'.$newFilename);
                        $attachment = new Zend_Mime_Part($content);
                        $attachment->type        = 'application/'.$extension;
                        $attachment->disposition = Zend_Mime::DISPOSITION_INLINE;
                        $attachment->encoding    = Zend_Mime::ENCODING_BASE64;
                        $attachment->filename    = $newFilename;
                        $mail->addAttachment($attachment); 
                    Zend_Session::namespaceUnset('user');
            }
            $sent = true; 
            try{
                $mail->send($tr);
            } catch(Exception $e){
                $sent = false;
            }

            if($newFilename){
                unlink(realpath('.').'/uploads/tmp/'.$newFilename);
            }
                $send = 'send';
            }else{
                $send = 'error';						
            }
            return $send;
        }
        
        public static function spanishDateStr($date)
	{
		$timeStamp = strtotime($date);
		$day = self::$spanish_day[date('l', $timeStamp)];
		$month = self::$spanish_month[date('F', $timeStamp)];

		return $day . ', ' . date('d' , $timeStamp)  . ' de ' . $month  . ' de ' . date('Y' , $timeStamp);
	}
        /**
         * 
         * @param date $date
         * @return txt format day in spanish
         */
        public static function spanishDay($date) {
            $timeStamp = strtotime($date);
            $day = self::$spanish_day[date('l', $timeStamp)];
	    return $day.' '. date('d' , $timeStamp);
            
        }
        
        public static function spanishMonth($date) {
             $timeStamp = strtotime($date);
             $month = self::$spanish_month[date('F', $timeStamp)];
             return $month;
            
            
        }
}
