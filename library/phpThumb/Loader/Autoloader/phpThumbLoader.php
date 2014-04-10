<?php
class phpThumb_Loader_Autoloader_phpThumbLoader implements Zend_Loader_Autoloader_Interface
{
	public function autoload($class)
	{
		if ('phpthumb' != $class){
			return false;
		}
		require_once 'phpThumb/phpthumb.class.php';
		return $class;
	}
}
