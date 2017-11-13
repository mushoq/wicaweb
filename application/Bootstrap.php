<?php

/**
 * Bootstrap class store settings that will be available through the application
 *
 * @category   wicaWeb
 * @package    Application GlobalFunctions
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license	   GNP
 * @version    1.1
 * @author      Jose Luis Landazuri - Santiago Arellano
 */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	
	/*
	 * Initialize GlobalFunctions file 
	 */
	protected function _initGlobalFunctions(){
            if (get_magic_quotes_gpc()) {

            function stripMagicQuotes(&$value) {
                $value = (is_array($value)) ? array_map('stripMagicQuotes', $value) : stripslashes($value);
                return $value;
            }

            stripMagicQuotes($_GET);
            stripMagicQuotes($_POST);
            stripMagicQuotes($_COOKIE);
        }
        require_once 'GlobalFunctions.php';
	}
	
	protected function _initPhpThumbHelper(){
		require_once 'PhpThumbHelper.php';
	}
		
	/*
	 * Initialize layouts
	 */
	protected function _initLayoutHelper() {
		$this->bootstrap ( 'frontController' );
		$layout = Zend_Controller_Action_HelperBroker::addHelper ( new ModuleLayoutLoader () );
	
	}
	
	/*
	 * Register Zend Framework Error Handler Pluging
	 */
	protected function _initErrorPlugin() {
		$front = Zend_Controller_Front::getInstance ();
		$front->registerPlugin ( new Zend_Controller_Plugin_ErrorHandler ( array (
				'module' => 'core',
				'controller' => 'error',
				'action' => 'error' 
		) ) );
		$front->throwExceptions ( false );
	
	}
	/*
	 * Get file with words to translate
	 */
	protected function _initTranslation() {

		
		
		Zend_Loader::loadClass('Zend_Translate');
		
/*		$translate = new Zend_Translate(
				'array',
				APPLICATION_PATH.'/configs/languages/',
				'es',
				array('scan' => Zend_Translate::LOCALE_FILENAME)
		);*/
		$translate = new Zend_Translate(
				'array',
				APPLICATION_PATH.'/configs/languages/',
				'en',
				array('scan' => Zend_Translate::LOCALE_FILENAME)
		);
		

		$id = New Zend_Session_Namespace('id');
		$website_language = $id->website_language;
		$locale = new Zend_Locale();
		
// 		if(isset($website_language)){
// 			$locale->setLocale($website_language);
// 		}else{
// 			$locale->setLocale(Zend_Locale::BROWSER);
// 		}
		$locale->setlocale(LC_ALL, 'en');
		//$locale = new Zend_Locale();
		
		// setting the right locale
		if ($translate->isAvailable($locale->getLanguage())) {
			$translate->setLocale($website_language);
		} else {
			//$translate->setLocale('es');
			$translate->setLocale('en');
		}
                
                $translate->setLocale($website_language);
		
		Zend_Registry::set('Zend_Translate', $translate);
                
                date_default_timezone_set('America/Guayaquil');
		
	}
	
	/*
	 * Get phpThumbs class
	 */
	protected function _initAutoloading() {
		$autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->pushAutoloader(new phpThumb_Loader_Autoloader_phpThumbLoader());
		$autoloader->pushAutoloader(new phpThumb_Loader_Autoloader_phpThumbFunctionsLoader());
	}
	

public function _initRoute()
	
	{
	
		// get instance of front controller
	
		$frontController  = Zend_Controller_Front::getInstance();
	
		// define new route class
	
		// this route with define the route for
	
		// http://www.example.com/explore/product/10
	
		// the id of the product found under variable name �id�
	
		// to retrive it $this->getRequest->getParam(�id)
	
		// in the index action of product controller
                if(strpos($_SERVER['REQUEST_URI'], 'core') == false && $_SERVER['REQUEST_URI'] != '/banners' && $_SERVER['REQUEST_URI'] != '/products'){ 
                    
                        $route33 = new Zend_Controller_Router_Route(
	
				':section_name',array(
                                    
                                                'siteid' => '1',
	
						'controller' => 'index',
	
						'module' => 'default' ,
	
						'action' => 'index',
	
						'id' => 1,
						
						'section_name' => ''));
	
	
						// add this route to the front controller
	
						$frontController->getRouter()->addRoute('ruta33',$route33);
                                                
                        $route34 = new Zend_Controller_Router_Route(
	
				':section_name/:product_name',array(
                                    
                                                'siteid' => '1',
	
						'controller' => 'index',
	
						'module' => 'default' ,
	
						'action' => 'index',
	
						'id' => 1,
						
						'section_name' => ''));
	
	
						// add this route to the front controller
	
						$frontController->getRouter()->addRoute('ruta34',$route34);
                    
                        
                            
                                                
                            
                    
                }
                $tinyurl = new Zend_Controller_Router_Route(
	
				't/:id',array(
                                            'siteid' => '1',
	
                                            'controller' => 'index',

                                            'module' => 'default' ,

                                            'action' => 'index',

                                            'id' => 1,

                                            'title' => ''

                                            ));
	
	
						// add this route to the front controller
	
						$frontController->getRouter()->addRoute('tiny',$tinyurl);
                $tinyurlsite = new Zend_Controller_Router_Route(
	
				's/:siteid/:id',array(
                                    
                                            'siteid' => '1',
	
                                            'controller' => 'index',

                                            'module' => 'default' ,

                                            'action' => 'index',

                                            'id' => 1,

                                            'title' => ''

                                            ));
	
	
						// add this route to the front controller
	
						$frontController->getRouter()->addRoute('tinysite',$tinyurlsite);
                $routebanners = new Zend_Controller_Router_Route(
	
				'bannerlink/:banner_id/:href',array(
                                    
                                                
	
						'controller' => 'link',
	
						'module' => 'banners' ,
	
						'action' => 'index',
	
						'banner_id' => 1,
                                    
                                                'href' => 1,
						
						));
	
	
						// add this route to the front controller
	
						$frontController->getRouter()->addRoute('rutabanners',$routebanners);
                                                
		$route = new Zend_Controller_Router_Route(
	
				'site/:siteid/section/:id/:title',array(
                                    
                                                'siteid' => '1',
	
						'controller' => 'index',
	
						'module' => 'default' ,
	
						'action' => 'index',
	
						'id' => 1,
						
						'title' => ''));
	
	
						// add this route to the front controller
	
						$frontController->getRouter()->addRoute('sectionSiteRoute',$route);
                                                
                $route2 = new Zend_Controller_Router_Route(
	
				'section/:id/:title',array(
                                    
                                                'siteid' => '1',
	
						'controller' => 'index',
	
						'module' => 'default' ,
	
						'action' => 'index',
	
						'id' => 1,
						
						'title' => ''));
	
	
						// add this route to the front controller
	
						$frontController->getRouter()->addRoute('sectionRoute',$route2);
                                                
              $route3 = new Zend_Controller_Router_Route(
	
				'site/:siteid/article/:id/:title',array(
                                    
                                                'siteid' => '1',
	
						'controller' => 'index',
	
						'module' => 'default' ,
	
						'action' => 'index',
	
						'id' => 1,
						
						'title' => ''));
	
	
						// add this route to the front controller
	
						$frontController->getRouter()->addRoute('articleSiteRoute',$route3);
                                                
                $route4 = new Zend_Controller_Router_Route(
	
				'article/:id/:title',array(
                                    
                                                'siteid' => '1',
	
						'controller' => 'index',
	
						'module' => 'default' ,
	
						'action' => 'index',
	
						'id' => 1,
						
						'title' => ''));
	
	
						// add this route to the front controller
	
						$frontController->getRouter()->addRoute('articleRoute',$route4);
                                                
                                                
               $products = new Zend_Controller_Router_Route(
	
				'product/:id/:product_id/:title',array(
                                    
                                                'siteid' => '1',
	
						'controller' => 'index',
	
						'module' => 'default' ,
	
						'action' => 'index',
	
						'id' => 1,
                                    
                                                'product_id' => 1,
						
						'title' => ''));
	
	
						// add this route to the front controller
	
						$frontController->getRouter()->addRoute('productsRoute',$products);
                                                
                $productsSite = new Zend_Controller_Router_Route(
	
				'site/:siteid/product/:id/:product_id/:title',array(
                                    
                                                'siteid' => '1',
	
						'controller' => 'index',
	
						'module' => 'default' ,
	
						'action' => 'index',
	
						'id' => 1,
                                    
                                                'product_id' => 1,
						
						'title' => ''));
	
	
						// add this route to the front controller
	
						$frontController->getRouter()->addRoute('productsSiteRoute',$productsSite);
                                                
                
                                                
}
}
class ModuleLayoutLoader extends Zend_Controller_Action_Helper_Abstract// looks up layout by module in application.ini
{
	public function preDispatch() {

		$bootstrap = $this->getActionController ()->getInvokeArg ( 'bootstrap' );
		$config = $bootstrap->getOptions ();
		$moduleName = $this->getRequest ()->getModuleName ();
		
		//check if layout module source exist
		if (isset ( $config [$moduleName] ['resources'] ['layout'] ['layout'] )) {
			$layoutScript = $config [$moduleName] ['resources'] ['layout'] ['layout'];
			Zend_Layout::getMvcInstance ()->setLayout ( $layoutScript );
			
			if (isset ( $config [$moduleName] ['resources'] ['layout'] ['layoutPath'] )) {
				$layoutPath = $config [$moduleName] ['resources'] ['layout'] ['layoutPath'];
				$moduleDir = Zend_Controller_Front::getInstance ()->getModuleDirectory ();
				Zend_Layout::getMvcInstance ()->setLayoutPath ( $layoutPath );
			}
		} else {
			$layoutScript = $config ['resources'] ['layout'] ['layout'];
			Zend_Layout::getMvcInstance ()->setLayout ( $layoutScript );
			
			if (isset ( $config ['resources'] ['layout'] ['layoutPath'] )) {
				$layoutPath = $config ['resources'] ['layout'] ['layoutPath'];
				$moduleDir = Zend_Controller_Front::getInstance ()->getModuleDirectory ();
				Zend_Layout::getMvcInstance ()->setLayoutPath ( $layoutPath );
			}
		}
		/*
		//Check session expiration
		if(!$this->checkIsLogin() && !$this->checkIsDefault()){
			//Check if session exist
			$id = New Zend_Session_Namespace('id');
			if($id->user_id=='' || $id->user_id==null){
				//Redirect to login
				header("Location: /core");
			}
		}*/
		
	
	}
	
	public function checkIsLogin()
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
		//Check if actual action is login
		$login = false;
		if($request->getActionName()=='index'){
			if($request->getControllerName()=='index'){
				if($request->getModuleName()=='core'){
					$login = true;
				}
			}
		}
		return $login;
	}
	
	public function checkIsDefault()
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
		//Check if actual action is login
		$default = false;
		if($request->getModuleName()=='default'){
			$default = true;
		}
		return $default;
	}
}
