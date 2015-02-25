<?php
/**
 *	Model form field contains specific functions
*
* @category   WicaWeb
* @package    Core_Model
* @copyright  Copyright (c) WicaWeb - Mushoq
* @license    GNP
* @version    1.0
* @author	  Santiago Arellano
*/

class Core_Model_FormField extends Core_Model_Factory
{
	public static $form_type = array('textfield'=>'Campo de Texto', 'emailfield'=>'Campo de Texto Email', 'datepicker'=>'Selector de fecha', 'textarea'=>'Área de Texto', 'radiobutton'=>'Botón de Radio', 'dropdown'=>'Lista Desplegable', 'checkbox'=>'Casilla de Verificación', 'comment'=>'Comentario', 'file'=>'Archivo');

}