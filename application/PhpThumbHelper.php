<?php

/**
 * GlobalFunctions class store global functions that will be available through the application
 *
 * @category   wicaWeb
 * @package    Application PhpThumbHelper
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license	   GNP
 * @author	   
 * @version    1.0
 * 
 * Description:
 * Originaly migrated from http://github.com/DanielMedia/phpThumb-Helper/blob/master/phpthumb.php
 * cakephp helper
 * This file is migrated and adapted from an older WicaWeb 
 */

define ('DS', '/');

/*
 * Helper class with specific functions to implement phpThumb
 */
class PhpThumbHelper {
    
    protected $_php_thumb;
    protected $_options;
    protected $_file_extension;
    protected $_cache_filename;
    protected $_thumb_data;
    protected $_error;
    protected $_error_detail;
    
    
    
    public function init($options_arr = array())    {
        $this->_options = $options_arr;
        $this->set_file_extension();
        $this->_thumb_data = array();
        $this->_error = 0;
        
    }
    
    public function set_file_extension()    {
        $this->_file_extension = substr($this->_options['src'], strrpos($this->_options['src'], '.'), strlen($this->_options['src']));
    }
    
    public function set_cache_filename()    {
        ksort($this->_options);
        $filename_parts = array();
        $cacheable_properties = array('src', 'new', 'w', 'h', 'wp', 'hp', 'wl', 'hl', 'ws', 'hs', 'f', 'q', 'sx', 'sy', 'sw', 'sh', 'zc', 'bc', 'bg', 'fltr');
        
        foreach($this->_options as $key => $value)    {
            if(in_array($key, $cacheable_properties))    {
                $filename_parts[$key] = $value;
            }
        }
        
        $this->_cache_filename = '';
        
        foreach($filename_parts as $key => $value)    {
            $this->_cache_filename .= $key . $value;
        }
        
        $last_modified = date("F d Y H:i:s.", filectime($this->_options['src']));
        
        $md5_hash = md5($this->_cache_filename . $last_modified);
        
        $broad_directories = '';
		for ($i = 0; $i < 4; $i++) {
			$broad_directories .= DS.substr($md5_hash, 0, $i + 1);
		}
        
        if (!is_dir($this->_options['save_path'] . $broad_directories))
        {
            mkdir($this->_options['save_path'] . $broad_directories, 0777, TRUE);
        }

        $this->_cache_filename = $this->_options['save_path'] . $broad_directories . DS . $this->create_url($this->_options['title']) . '-' . $md5_hash . $this->_file_extension;
    }
    
    public function image_is_cached()    {
        if(is_file($this->_cache_filename))    {
            return true;
        }
        return false;
    }
    
    public function create_thumb()    {
		
    	//call to the phpthumb class. This class was previously included in the bootstrap
        $this->_php_thumb = new phpthumb();
        $this->_php_thumb->setParameter('config_allow_src_above_docroot', true);
        
        foreach($this->_php_thumb as $var => $value) {
            if(isset($this->_options[$var]))    {
                $this->_php_thumb->setParameter($var, $this->_options[$var]);
            }
        }
        
        if($this->_php_thumb->GenerateThumbnail()) {
            $this->_php_thumb->RenderToFile($this->_cache_filename);
        } else {
            $this->_error = 1;
            $this->_error_detail = ereg_replace("[^A-Za-z0-9\/: .]", "", $this->php_thumb->fatalerror);
        }
              
    }
    
    public function get_thumb_data()    {
    	$this->_thumb_data['error'] = $this->_error;
    	
        if($this->_error)    {
            $this->_thumb_data['error_detail'] = $this->_error_detail;
            $this->_thumb_data['src'] = $this->_options['error_image_path'];
        } else    {
            $this->_thumb_data['src'] = $this->_options['display_path'] . '/' . str_replace($this->_options['save_path'].DS, '', $this->_cache_filename);
        }
        
        if(isset($this->_options['w']))    {
            $this->_thumb_data['w'] = $this->_options['w'];
        }
        
        if(isset($this->_options['h']))    {
             $this->_thumb_data['h'] = $this->_options['h'];
        }
        
        return $this->_thumb_data;
    }
    
    public function validate()	{
    	if(!is_file($this->_options['src']))	{
    		$this->_error = 1;
    		$this->_error_detail = 'File ' . $this->_options['src'] . ' does not exist';
    		return;
    	}
    	$valid_extensions = array('.gif', '.jpg', '.jpeg', '.png');
    	
    	if(!in_array($this->_file_extension, $valid_extensions))	{
    		$this->_error = 1;
    		$this->_error_detail = 'File ' . $this->_options['src'] . ' is not a supported image type';
    		return;
    	}
    }
    
    public function generate($options = array())    {
    	self::init($options);
    	$this->validate();
    	
    	if(!$this->_error)    {
    		$this->set_cache_filename();
        	if(!$this->image_is_cached())    {
        	    $this->create_thumb();
        	}
        }

        return $this->get_thumb_data();
    }

	public function create_url($url)
	{
		$url = trim($url);
		$url = preg_replace('/[^ a-z0-9_]/i', '', $url);
		$url = preg_replace('/[_]/i', '-', $url);
		$url = preg_replace('/ +/', '-', $url);
		$url = strtolower($url);
		
		return $url;
	}	
	
    
 } // end of PhpThumb

/**
 * imageRender
 * This class works with phpThumbHelper class to create and return the corresponding info to render the generated images
 **/
class imageRender 
 {
    
    /**
     * Returns a string with the corresponding data of the cache file of an image with phpthumb
     * 
     * @return string
     **/
    public static function cache_image($name, $params)
    {
        
        $generate_params = array(
            'save_path' => APPLICATION_PATH.'/../public/uploads/cache',
            'display_path' => '/uploads/cache',
            'error_image_path' => 'error.jpg',
            'src' => APPLICATION_PATH.'/../public/uploads/content/' . $name,
        );
        
        $allowed_params = array(
            'width'=>'0',
            'height'=>'0',
            'watermark'=>'0',
            'watermark_pos'=>'0',
			'title'=>'0'
        );
        
        $params = array_merge($allowed_params, $params);
        
        if ($params['width']!='0')
        {
            $generate_params['w'] = $params['width'];
        }
        
        if ($params['height']!='0')
        {
            $generate_params['h'] = $params['height'];
        }
        
        if ($params['watermark']!='0')
        {
            $generate_params['fltr'] = 'wmi|' .  APPLICATION_PATH.'/../public/uploads/website'. DS . $params['watermark'] . '|' . $params['watermark_pos'] ; 
        }

		if ($params['title']!='0')
		{
			$generate_params['title'] = $params['title'];
		}
		else
		{
			$name =  substr($name, strrpos($name, DS) + 1, strlen($name)) ;
			$generate_params['title'] = substr($name, 0, strpos($name, '.'));
		}

        $helper = new PhpThumbHelper();
        $thumbnail = $helper->generate(
            $generate_params
        );

        if ($thumbnail['error'] == 1)
        {
//             Kohana::log('error', 'Error generating thumbnail with phpthumb of image ' . $name . ' with params '. serialize($params));
			echo 'Error generating thumbnail with phpthumb of image ' . $name . ' with params '. ($params);
        }
        
        return $thumbnail['src'];
    }
    
    
    /**
     * Returns a string with the corresponding data of the cache file of an image with phpthumb
     *
     * @return string
     **/
    public static function cache_image_product($name, $params)
    {
    
    	$generate_params = array(
    			'save_path' => APPLICATION_PATH.'/../public/uploads/cache',
    			'display_path' => '/uploads/cache',
    			'error_image_path' => 'error.jpg',
    			'src' => APPLICATION_PATH.'/../public/uploads/products/' . $name,
    	);
    
    	$allowed_params = array(
    			'width'=>'0',
    			'height'=>'0',
    			'watermark'=>'0',
    			'watermark_pos'=>'0',
    			'title'=>'0'
    	);
    
    	$params = array_merge($allowed_params, $params);
    
    	if ($params['width']!='0')
    	{
    		$generate_params['w'] = $params['width'];
    	}
    
    	if ($params['height']!='0')
    	{
    		$generate_params['h'] = $params['height'];
    	}
    
    	if ($params['watermark']!='0')
    	{
    		$generate_params['fltr'] = 'wmi|' .  APPLICATION_PATH.'/../public/uploads/website'. DS . $params['watermark'] . '|' . $params['watermark_pos'] ;
    	}
    
    	if ($params['title']!='0')
    	{
    		$generate_params['title'] = $params['title'];
    	}
    	else
    	{
    		$name =  substr($name, strrpos($name, DS) + 1, strlen($name)) ;
    		$generate_params['title'] = substr($name, 0, strpos($name, '.'));
    	}
    
    	$helper = new PhpThumbHelper();
    	$thumbnail = $helper->generate(
    			$generate_params
    	);
    
    	if ($thumbnail['error'] == 1)
    	{
    		//             Kohana::log('error', 'Error generating thumbnail with phpthumb of image ' . $name . ' with params '. serialize($params));
    		echo 'Error generating thumbnail with phpthumb of image ' . $name . ' with params '. ($params);
    	}
    
    	return $thumbnail['src'];
    }    
 } // END class html_Core
