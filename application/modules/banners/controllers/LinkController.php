<?php
/**
 *	Functinallity on banners 
 *
 * @category   WicaWeb
 * @package    Banners_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   Diego Perez
 */

class Banners_LinkController extends Zend_Controller_Action
{
    public function indexAction()
    {	
        //Disable layout for action	
        $this->_helper->layout->disableLayout ();
        $this->_helper->viewRenderer->setNoRender();
        $href = $this->_getParam('href');
        $banner_id = $this->_getParam('banner_id');

        $obj = new Banners_Model_Banners();

        $obj->updatehits($banner_id);

        $banner = $obj->find('wc_banner', array('id'=>$banner_id));
        
        $this->_helper->redirector->gotoUrlAndExit($banner[0]->link);
    }
}
