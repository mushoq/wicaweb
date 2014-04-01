<?php

/**
 * ErrorController controls the error exceptions
 *
 * @category   wicaWeb
 * @package    Core controllers ErrorController
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license	   GNP
 * @author	   Santiago Arellano
 * @version    1.0
 */

class Core_ErrorController extends Zend_Controller_Action
{

    public function errorAction()
    {
    	
        $errors = $this->_getParam('error_handler');
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->_helper->layout->disableLayout();
                $this->view->message = 'P&aacute;gina no encontrada';
                break;
            default:
            	
            	$arr_message = explode(":",$errors->exception->getMessage());
            	if($arr_message[0]=='CUSTOM_EXCEPTION')
            	{
            		$this->getResponse()->setHttpResponseCode(404);
            		$priority = Zend_Log::NOTICE;
            		$this->_helper->layout->disableLayout();
            		$this->view->message = $arr_message[1]; 	           		
            	}
            	else
            	{
            		// application error
            		$this->getResponse()->setHttpResponseCode(500);
            		$priority = Zend_Log::CRIT;
            		$this->_helper->layout->disableLayout();
            		$this->view->message = 'Error en la Aplicaci&oacute;n';
            	}

                break;
        }
        
        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $log->log($this->view->message, $priority, $errors->exception);
            $log->log('Request Parameters', $priority, $errors->request->getParams());
        }
        
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        
        $this->view->request   = $errors->request;
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }


}

