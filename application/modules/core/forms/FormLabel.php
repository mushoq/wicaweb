<?php
/**
 * FormLabel
 * Adds a label in the form
 *
 * @category   WicaWeb
 * @package    Core_Form
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @author	   Esteban
 * @version    1.0
 *
 */
class Core_Form_FormLabel extends Zend_Form_Element
{  
    public $helper = 'formLabelHelper';  
    
    public function __construct($spec, $options = null) {
    	parent::__construct($spec, $options);
    	$this->removeDecorator('label');
    	$this->removeDecorator('htmlTag');
    
    }
    
}