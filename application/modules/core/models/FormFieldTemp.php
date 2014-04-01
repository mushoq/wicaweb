<?php
/**
 *	Model form field temp contains form object types
*
* @category   WicaWeb
* @package    Core_Model
* @copyright  Copyright (c) WicaWeb - Mushoq
* @license    GNP
* @version    1.0
* @author	  David Rosales
*/

class Core_Model_FormFieldTemp extends Core_Model_Factory
{
	public static $form_type = array('textfield'=>'Campo de Texto', 'textarea'=>'Área de Texto', 'radiobutton'=>'Botón de Radio', 'dropdown'=>'Lista Desplegable', 'checkbox'=>'Casilla de Verificación', 'comment'=>'Comentario', 'file'=>'Archivo');

}