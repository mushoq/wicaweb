<?php

/**
 * Flash Messages. This helper renders messages in the views.
 *
 * @category   WicaWeb
 * @package    Core_View_Helper
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	  Esteban
 * @version    1.0
 *
 */
class Zend_View_Helper_FlashMessages extends Zend_View_Helper_Abstract
{
	/**
	 * This function gets the message from the controller and generates a string to render the messages.
	 * @return string
	 */
    public function flashMessages()
    {
        $messages = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->getMessages();
        $output = '';
       
        if (!empty($messages)) {
        	$output .= '<div id="messages" class="center">';
            foreach ($messages as $message) {
                $output .= '<div class="alert alert-' . key($message) . ' size_alerts"><a class="close" data-dismiss="alert" href="#">×</a><strong>' . current($message) . '</strong></div>';
            }
            $output .= '</div>';
        }
       
        return $output;
    }
}