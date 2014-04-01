<?php

/**
 * Login form build extructure of login
 *
 * @category   wicaWeb
 * @package    Core Forms Login 
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license	   GNP
 * @author	   Santiago Arellano
 * @version    1.0
 */

class Core_Form_Login_Login extends Zend_Form {
	
	public function init() {
		
		$lang = Zend_Registry::get('Zend_Translate');
		
		$this->setAttrib('id', 'frmLogin');
		$this->setAttrib('class', 'well');
		
		$username = $this->addElement ( 'text', 'username', array (
				'filters' => array (
						'StringTrim',
						'StringToLower' 
				),
				'validators' => array (
						'Alpha',
						array (
								'StringLength',
								false,
								array (
										3,
										20 
								) 
						) 
				),
				'required' => true,
				'label' => $lang->translate('User').':' ,
				'decorators' => array (
										'ViewHelper',
										array (array('data' => 'HtmlTag'), array (
																				'tag' => 'div',
																				'class' => 'controls'
																			)
										),
										array('Label', array(
															'tag' => 'div',
															'class' => 'control-label'
														)
										),
										array ('HtmlTag', array (
																	'tag' => 'div',
																	'class' => 'control-group'
															)
										),
								),
		) );
		
		$password = $this->addElement ( 'password', 'password', array (
				'filters' => array (
						'StringTrim' 
				),
				'validators' => array (
						'Alnum',
						array (
								'StringLength',
								false,
								array (
										6,
										20 
								) 
						) 
				),
				'required' => true,
				'label' => $lang->translate('Password').':',
				'decorators' => array (
										array('ViewHelper'),
										array (array('data' => 'HtmlTag'), array (
																				'tag' => 'div',
																				'class' => 'controls'
																			)
										),
										array('Label', array(
															'tag' => 'div',
															'class' => 'control-label'
														)
										),
										array ('HtmlTag', array (
																	'tag' => 'div',
																	'class' => 'control-group'
															)
										),
								),
		) );
		
		$login = $this->addElement ( 'submit', 'login', array (
				'required' => false,
				'ignore' => true,
				'label' => $lang->translate('Login'), 
				'class'  => 'btn btn-primary',
				'decorators' => array (
						array('ViewHelper'),
						array ('HtmlTag', array (
													'tag' => 'div',
													'class' => 'control-group'
											)
						),
				),
				
		) );
		
		$this->setDecorators ( array (
				'FormElements',
				array (
						'HtmlTag',
						array (
								'tag' => 'div',
								'class' => 'row-fluid'
						) 
				),
				array (
						'Description',
						array (
								'placement' => 'prepend',
								'class'=> 'alert alert-error' 
						) 
				),
				'Form' 
		) );
		
		
	}
}
