<?php
/**
 *	Products actions
 *
 * @category   WicaWeb
 * @package    Products_Controller
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   David Rosales
 */

class Products_ProductsController extends Zend_Controller_Action
{
	public function init()
	{
		//Create Zend layout
		$layout = new Zend_Layout();
		// Set a layout scripts path
		$layout->setLayoutPath(APPLICATION_PATH.'/modules/core/layouts/scripts/');
		// choose a different layout script:
		$layout->setLayout('core');
		
		//session
		$id = New Zend_Session_Namespace('id');
				
		$section = new Core_Model_Section();
		$section_temp = new Core_Model_SectionTemp();
		$sections_arr = array();
		
		//find existent sections on db according website
		$sections_list = $section->personalized_find('wc_section', array(array('website_id','=',$id->website_id)),array('article','order_number'));
		if(count($sections_list)>0)
		{
			foreach ($sections_list as $k => &$slt)
			{
				$sections_published_arr[] = $slt->id;
				$slt->temp = 0;
			}
		}
		$sections_list_temp = $section_temp->personalized_find('wc_section_temp', array(array('website_id','=',$id->website_id)),array('article','order_number'));
		if(count($sections_list_temp)>0)
		{
			foreach ($sections_list_temp as $k => &$stp)
			{
				$stp->temp = 1;
			}
		}
		
		if(count($sections_list)>0 && count($sections_list_temp)>0)
		{
			$sections_copied_arr = array();
			//replacing sections that area eddited on temp
			foreach ($sections_list as $k => &$sbc)
			{				
				foreach ($sections_list_temp as $p => &$sct)
				{					
					if($sbc->id == $sct->section_id)
					{
						$sct->id = $sct->section_id;						
						$sections_list_res[] = $sct;				
						$sections_copied_arr[] = $sct->section_id;
					}					
				}
			}
			
			//adding sections created on temp
			if(count($sections_copied_arr)>0)
			{
				$section_pub_missing = array_diff($sections_published_arr, $sections_copied_arr);
				if(count($section_pub_missing)>0)
				{
					foreach ($section_pub_missing as $serial)
					{
						$section_obj = $section->find('wc_section', array('id'=>$serial));
						$section_obj[0]->temp = 0; 
						$sections_list_res[] = $section_obj[0];
					}
				}
			}
			$sections_list = $sections_list_res;
		}
		
		if($id->user_profile == '1')
		{
			//sections list array
			if(count($sections_list)>0)
			{
				foreach ($sections_list as $sec)
				{					
					$sections_arr[] = array('id'=>$sec->id,
											'temp'=>$sec->temp,
											'section_parent_id'=>$sec->section_parent_id,
											'title'=>$sec->title,
											'article'=>$sec->article,
											'order_number'=>$sec->order_number
											);
				}
			}
		}
		else
		{
			$subsection_arr = array();
			$section_aux = array();
			$user_allowed_sections_arr = explode(',',$id->user_allowed_sections);
			
			foreach ($user_allowed_sections_arr as $serial)
			{								
				foreach ($sections_list as $asc)
				{
					if($asc->id == $serial)
					{
						$section_aux[] = $asc;
					}
				}
			}			
			$available_sections = $section_aux;

			foreach ($available_sections as $sec)
			{				
				$sections_arr[] = array('id'=>$sec->id,
						'temp'=>$sec->temp,
						'section_parent_id'=>$sec->section_parent_id,
						'title'=>$sec->title,
						'article'=>$sec->article,
						'order_number'=>$sec->order_number
				);
				
				//parent allowed sections
				if($sec->section_parent_id)
				{
					$subsection_arr[] = self::buildSectionParentTree($branch = array(), $sec->section_parent_id);
				}
			}
			
			if(count($subsection_arr)>0)
			{
				//parent sections array
				foreach ($subsection_arr as $key => $sub)
				{
					foreach ($sub as $val)
					{
						$subsection_list[$val['id']] = $val['id'];
						$subsection_list_stt[$val['id']] = $val['temp'];
					}
				}
									
				$subsection_aux = array_unique($subsection_list);				
				if(count($subsection_aux)>0)
				{
					foreach ($subsection_aux as $k => &$sbc)
					{
						foreach ($sections_arr as $sct)
						{
							if($sct['id'] == $sbc && $sct['temp'] == intval($subsection_list_stt[$sbc]))
							{								
								unset($subsection_aux[$k]);
							}
						}			
					}	
					//non repeated sections					
					foreach ($subsection_aux as $sec)
					{
						if($subsection_list_stt[$sec])						
						{
							$subsection_obj = $section_temp->find('wc_section_temp', array('section_id'=>$sec));
							$temp_subsec = 1; 
						}
						else
						{
							$subsection_obj = $section->find('wc_section', array('id'=>$sec));
							$temp_subsec = 0;
						}
						
						foreach ($subsection_obj as $obj)
						{		
							if(isset($obj->section_id))
							{
								$serial_sec = $obj->section_id;
							}
							else
							{
								$serial_sec = $obj->id;
							}	
											
							$sections_arr[] = array('id'=>$serial_sec,
													'temp'=>$temp_subsec,
													'section_parent_id'=>$obj->section_parent_id,
													'title'=>$obj->title,
													'article'=>$obj->article,
													'order_number'=>$obj->order_number
													);
						}
					}
				}
			}	
		}
		
		/******
		 * Ordering sections by article and number
		*/
		$sort_col_number = array();
		foreach ($sections_arr as $key=> $row) {			
			$sort_col_number[$key] = $row['order_number'];
		}
		array_multisort($sort_col_number, SORT_ASC, $sections_arr);
		
		//string with sections tree html
		$html_list = '';
		if(count($sections_arr)>0)
		{
			//sections tree - parents and children as array
			$sections_tree = GlobalFunctions::buildSectionTree($sections_arr);			
			//sections tree as list
		    $html_list = GlobalFunctions::buildHtmlSectionTree($sections_tree);
		}		
		$this->view->data = $html_list;
		
		/**
		 * Modules
		 */		

		//Disabled display section bar in index
		$this->view->displaysectionbar = false;
		 
		$cms_arr = array();
		 
		//Get module_id by module_name
		 
		$module_obj = new Core_Model_Module();
		$module = $module_obj->find('wc_module',array('name'=>'Products'));
		$module_id = $module[0]->id;
		 
		//Get user module action for product 
		if($id->user_modules_actions){
			foreach ($id->user_modules_actions as $k => $mod)
			{				 
				if($mod->module_id == $module_id)
				{
					$cms_arr[$mod->action_id] = array('action'=> $mod->action_name, 'title'=>$mod->action_title);
				}
			}
		}

		//Put sections array in session var
		$id->sections_product_array = $sections_arr;
		
		//Put user module actions in view
		$this->view->cms_links = $cms_arr;   
	}
	
	/**
	 * Loads products to be ordered
	 */
	public function indexAction()
	{	
		//Disable layout for action	
		$this->_helper->layout->disableLayout ();
		
		//session stores website_id
		$id = New Zend_Session_Namespace('id');

		//get section_id
		$section_id = $this->_getParam('id');
		
		//Section id in view
		$this->view->section_id = $section_id;
		$this->view->website_id = $id->website_id;
		//Get module_id by module_name
		$module_obj = new Core_Model_Module();
		$module = $module_obj->find('wc_module',array('name'=>'Products'));
		$module_id = $module[0]->id;
		
		/** Get products list **/
		
		//Get module description by module (products)
		$module_description_obj = new Core_Model_ModuleDescription();
		$module_description_list = $module_description_obj->find('wc_module_description',array('module_id'=>$module_id));
		
		//Create section module area model
		$section_module_area_obj = new Core_Model_SectionModuleArea();
		
		if($module_description_list){
			//Check if is home link
			if($section_id=='all') //Is home link
			{
				//Get all products
				$product_obj = new Products_Model_Products();
				$products_list_obj = $product_obj->find('product', array('website_id'=>$id->website_id));
				
				//Convert objClass to normal array
				if($products_list_obj){
					foreach ($products_list_obj as $bl){
						$products_list[] = get_object_vars($bl);
					}
				}					
			}
			else //Is section tree link
			{
				//Get products by section
				foreach ($module_description_list as $md){
					$section_module_area_item = $section_module_area_obj->find('wc_section_module_area',array('module_description_id'=>$md->id,'section_id'=>$section_id));
					if($section_module_area_item){
						foreach ($section_module_area_item as $sma){
							$section_module_area_list[] = $sma;
						}
					}
				}
				
				//Get module_description_id of section_module_area
				if(isset($section_module_area_list)){					
					foreach ($section_module_area_list as $smal){
						$module_description_products = $module_description_obj->find('wc_module_description',array('id'=>$smal->module_description_id));
						if($module_description_products){
							foreach ($module_description_products as $mdi){
								$module_descriptions_products_list[] = $mdi;
							}							
						}
					}					
				}
			
				//Get product data by module_description
				if(isset($module_descriptions_products_list)){
						
					//Get products list data by module_area
						$product_obj = new Products_Model_Products();
						foreach ($module_descriptions_products_list as $mdbl){
							$product_item = $product_obj->find('product',array('id'=>$mdbl->row_id));
							if($product_item){
								foreach ($product_item as $bi){
									$products_list[] = get_object_vars($bi);
								}
						
							}
						}
				}
			}
			
			//Ordering products by order_number
			if(isset($products_list)){
				$sort_col_number = array();
				foreach ($products_list as $key=> $row) {
					$sort_col_number[$key] = $row['order_number'];
				}
				array_multisort($sort_col_number, SORT_ASC, $products_list);
					
				if(isset($products_list)){
					$this->view->products_list = $products_list;
				}
			}			
		}
		
		$cms_arr = array();		
		if($id->user_modules_actions)
		{
			foreach ($id->user_modules_actions as $k => $mod)
			{
				if($mod->module_id == '2')
				{
					$cms_arr[$mod->action_id] = array('action'=> $mod->action_name, 'title'=>$mod->action_title);
				}
			}
		}
		
		$this->view->cms_links = $cms_arr;
	}

	/**
	 * Creates a new product
	 */
    public function newAction()
    {
        $id = New Zend_Session_Namespace('id');
        $this->view->website_id = $id->website_id;        
    	//Get section_id
    	$section_id = $this->_getParam('id');
  	    	    	
    	//Disable layout for action
    	$this->_helper->layout->disableLayout ();
   	    	
    	//set hidden section parent id
    	$product_form = new Products_Form_Products();
    	$section_id_element = new Zend_Form_Element_Hidden ( 'section_id' );
    	$section_id_element->setValue ($section_id );
    	$section_id_element->removeDecorator ( 'Label' );
    	$section_id_element->removeDecorator ( 'HtmlTag' );
    	$product_form->addElement ( $section_id_element );
        
        //website_id
        $website_id_element = new Zend_Form_Element_Hidden ( 'website_id' );
    	$website_id_element->setValue ($id->website_id );
    	$website_id_element->removeDecorator ( 'Label' );
    	$website_id_element->removeDecorator ( 'HtmlTag' );
    	$product_form->addElement ( $website_id_element );
        
    	$product_form->setMethod('post');
    	$this->view->section_id = $section_id;
    	$this->view->form = $product_form;
    	
    }
    
    /**
     * Loads products feature form
     */
    public function loadproductfeatureAction()
    {
    	//Disable layout for this form
    	$this->_helper->layout->disableLayout ();
    }    
    
    /**
     * Updates a product
     */
    public function editAction()
    {    	 
    	//Get section_id
    	$section_id = $this->_getParam('section_id');
        $id = New Zend_Session_Namespace('id');
        $this->view->website_id = $id->website_id;
    	//Disable layout for this form
    	$this->_helper->layout->disableLayout ();
    	 
    	//session
    	$id = New Zend_Session_Namespace('id');

    	//Find template according website
    	$website = new Core_Model_Website();
    	$website_data = $website->find('wc_website',array('id'=>$id->website_id));
    	$template_id = $website_data[0]->template_id;
    
    	//Get fixed areas by template
    	$area = new Core_Model_Area();
    	$area_data = $area->personalized_find('wc_area',array(array('template_id','=',$template_id),array('type','LIKE','fixed')));

    	//Get request params
    	$request_params = $this->getRequest()->getParams();
  	    	
    	
    	//Get product_id
    	$product_id = $this->_getParam('product_id');
    	
    	//Get product data for edit
    	$product_aux = new Products_Model_Products();
    	$product_data = $product_aux->find('product',array('id'=>$product_id));
    	
    	$product_form = new Products_Form_Products();
    	
    	$arr_data = get_object_vars($product_data[0]); //make object data array    	
    	$arr_data['product_file_img'] = $arr_data['image'];
               
    	$image_preview = New Zend_Form_Element_Image('product_imageprw');
    	$image_preview->setImage('/uploads/products/'.$arr_data['image']);    	
    	$image_preview->setAttrib('style', 'width:150px;');
    	$image_preview->setAttrib('onclick', 'return false;');
    	$product_form->addElement($image_preview);
        
        $arr_data['product_file_ficha'] = $arr_data['ficha']; 
       	
    	$arr_data['section_id'] = $section_id;
        $arr_data['website_id'] = $id->website_id;
    	//Populate form with data
    	$product_form->populate($arr_data);
    	
    	$catalog = new Products_Model_ProductCatalog();
    	$products_catalog = $catalog->find('product_catalog', array('product_id'=>$product_id));    	
    	$this->view->products = $products_catalog;
    	
    	$this->view->section_id = $section_id;
    	$this->view->form = $product_form;    	 
    }
    
    /**
     * Creates or updates a product
     */
    public function saveAction()
    {    
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    	
    	//Get section_id
    	$section_id = $this->_getParam('section_id');
    	
		//disable autorendering for this action
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender();
		
		//Create a product Form
		$form = New Products_Form_Products();
		$form->setMethod('post');
		
		if ($this->getRequest()->isPost()) 
		{
			$formData = $this->getRequest()->getPost();			

			//create product model
			$product =  new Products_Model_Products();
			$product_obj = $product->getNewRow('product');
			 
			//save data
                        

			//Check if id exist 
			if(array_key_exists('id',$formData)){ //Is update product
				$product_obj->id = GlobalFunctions::value_cleaner($formData['id']);	
			}			
			
			$product_obj->name = GlobalFunctions::value_cleaner($formData['name']);
                        $product_obj->website_id = GlobalFunctions::value_cleaner($formData['website_id']);
			$product_obj->description = $formData['description'];
			$product_obj->available = GlobalFunctions::value_cleaner($formData['available']);
			$product_obj->status = GlobalFunctions::value_cleaner($formData['status']);
			$product_obj->feature = GlobalFunctions::value_cleaner($formData['feature']);
                        $product_obj->highlight = GlobalFunctions::value_cleaner($formData['highlight']);
						
			//path to upload image
			if(!is_dir(APPLICATION_PATH. '/../public/uploads/products/'))
			{
				$path = APPLICATION_PATH. '/../public/uploads/products/';
				mkdir($path);
				chmod($path, 0777);
			}
			
			if(!is_dir(APPLICATION_PATH. '/../public/uploads/products/'.date('Y')))
			{
				$path = APPLICATION_PATH. '/../public/uploads/products/'.date('Y');
				mkdir($path);
				chmod($path, 0777);
			}
			
			if(!is_dir(APPLICATION_PATH. '/../public/uploads/products/'.date('Y').'/'.date('m')))
			{
				$path = APPLICATION_PATH. '/../public/uploads/products/'.date('Y').'/'.date('m');
				mkdir($path);
				chmod($path, 0777);
			}
			
			//if image file uploaded to create new or update
			if($formData['product_hdnNameFile'])
			{
				if(isset($formData['product_file_img']))
				{
					//delete old image file
					if($formData['product_file_img']!="")
					{
					
						list($folder,$subfolder,$file) = explode('/',$formData['product_file_img']);
						GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/products/'.$folder.'/'.$subfolder.'/');					
					}
				}

				//Save image in object
				$img = GlobalFunctions::uploadFiles($formData['product_hdnNameFile'], APPLICATION_PATH. '/../public/uploads/products/'.date('Y').'/'.date('m').'/');
				$product_obj->image = date('Y').'/'.date('m').'/'.$img;				
											
				//remove images temp files
				GlobalFunctions::removeOldFiles($formData['product_hdnNameFile'], APPLICATION_PATH. '/../public/uploads/tmp/');
			}
			else
			{
				//Same image
				$product_obj->image = $formData['product_file_img'];
			}
                        
                        //if FICHA file uploaded to create new or update
			if($formData['product_hdnNameFicha'])
			{
				if(isset($formData['product_file_ficha']))
				{
					//delete old image file
					if($formData['product_file_ficha']!="")
					{
					
						list($folder,$subfolder,$file) = explode('/',$formData['product_file_ficha']);
						GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/products/'.$folder.'/'.$subfolder.'/');					
					}
				}

				//Save image in object
				$img = GlobalFunctions::uploadFiles($formData['product_hdnNameFicha'], APPLICATION_PATH. '/../public/uploads/products/'.date('Y').'/'.date('m').'/');
				$product_obj->ficha = date('Y').'/'.date('m').'/'.$img;				
											
				//remove images temp files
				GlobalFunctions::removeOldFiles($formData['product_hdnNameFICHA'], APPLICATION_PATH. '/../public/uploads/tmp/');
			}
			else
			{
				//Same image
				$product_obj->ficha = $formData['product_file_ficha'];
			}
// 			Zend_Debug::dump($_POST);die;
			// Save data
			$saved_product = $product->save('product',$product_obj);
			
			if(key_exists('hdn_product_code_', $_POST))
				if($_POST['hdn_product_code_'])
				{
					if($formData['id'] && $saved_product['id'])
					{
						
						$product_cat = new Products_Model_ProductCatalog();
						$products_cat = $product_cat->find('product_catalog',array('product_id' => $formData['id'])); 
						
						foreach($products_cat as $prd_cat){
							
							if(key_exists('hdn_product_id_', $_POST)){
								if(!in_array($prd_cat->id, $_POST['hdn_product_id_'])){
									$product_catalog = new Products_Model_ProductCatalog();
									$product_catalog_obj = $product_catalog->delete('product_catalog',array('id'=>$prd_cat->id));
									//delete old image file
									if($prd_cat->image!="")
									{
										list($folder,$subfolder,$file) = explode('/',$prd_cat->image);
										GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/products/'.$folder.'/'.$subfolder.'/');
									}
								}
							}							
						}
						
						
						//edit product catalog
						foreach ($_POST['hdn_product_code_'] as $pos => $pro_catalog)
						{
							$product_catalog = new Products_Model_ProductCatalog();
							$product_catalog_obj = $product_catalog->getNewRow('product_catalog');
							
							if(isset($_POST['hdn_product_id_'][$pos]))								
								$product_catalog_obj->id = $_POST['hdn_product_id_'][$pos];
							$product_catalog_obj->product_id = $saved_product['id'];
							$product_catalog_obj->code = GlobalFunctions::value_cleaner($_POST['hdn_product_code_'][$pos]);
							$product_catalog_obj->description = GlobalFunctions::value_cleaner($_POST['hdn_product_description_'][$pos]);
							$product_catalog_obj->price = $_POST['hdn_product_price_'][$pos];
							$product_catalog_obj->price_sale = $_POST['hdn_product_price_sale_'][$pos];
							$product_catalog_obj->weight = $_POST['hdn_product_weight_'][$pos];

							//if image file uploaded to create new or update
							if($_POST['hdn_product_hdnNameFile_'][$pos])
							{
								if(isset($_POST['hdn_product_file_img_'][$pos]))
								{
									//delete old image file
									if($_POST['hdn_product_file_img_'][$pos]!="")
									{
										list($folder,$subfolder,$file) = explode('/',$_POST['hdn_product_file_img_'][$pos]);
										GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/products/'.$folder.'/'.$subfolder.'/');
									}
								}
							
								//Save image in object
								$img = GlobalFunctions::uploadFiles($_POST['hdn_product_hdnNameFile_'][$pos], APPLICATION_PATH. '/../public/uploads/products/'.date('Y').'/'.date('m').'/');
								$product_catalog_obj->image = date('Y').'/'.date('m').'/'.$img;
							
								//remove images temp files
								GlobalFunctions::removeOldFiles($_POST['hdn_product_hdnNameFile_'][$pos], APPLICATION_PATH. '/../public/uploads/tmp/');
							}
							else
							{
								//Same image
								$product_catalog_obj->image = $_POST['hdn_product_file_img_'][$pos];
							}
							
							$saved_product_catalog_obj = $product_catalog->save('product_catalog', $product_catalog_obj);
						}
					}
					else
					{
						//new product catalog
						if($saved_product['id'])
						{
							foreach ($_POST['hdn_product_code_'] as $pos => $pro_catalog)
							{
								$product_catalog = new Products_Model_ProductCatalog();
								$product_catalog_obj = $product_catalog->getNewRow('product_catalog');
																
								$product_catalog_obj->product_id = $saved_product['id'];
								$product_catalog_obj->code = GlobalFunctions::value_cleaner($_POST['hdn_product_code_'][$pos]);
								$product_catalog_obj->description = GlobalFunctions::value_cleaner($_POST['hdn_product_description_'][$pos]);
								$product_catalog_obj->price = $_POST['hdn_product_price_'][$pos];
								$product_catalog_obj->price_sale = $_POST['hdn_product_price_sale_'][$pos];
								$product_catalog_obj->weight = $_POST['hdn_product_weight_'][$pos];
								
								//if image file uploaded to create new or update
								if($_POST['hdn_product_hdnNameFile_'][$pos])
								{
									if(isset($_POST['hdn_product_file_img_'][$pos]))
									{
										//delete old image file
										if($_POST['hdn_product_file_img_'][$pos]!="")
										{
											list($folder,$subfolder,$file) = explode('/',$_POST['hdn_product_file_img_'][$pos]);
											GlobalFunctions::removeOldFiles($file, APPLICATION_PATH. '/../public/uploads/products/'.$folder.'/'.$subfolder.'/');
										}
									}
								
									//Save image in object
									$img = GlobalFunctions::uploadFiles($_POST['hdn_product_hdnNameFile_'][$pos], APPLICATION_PATH. '/../public/uploads/products/'.date('Y').'/'.date('m').'/');
									$product_catalog_obj->image = date('Y').'/'.date('m').'/'.$img;
								
									//remove images temp files
									GlobalFunctions::removeOldFiles($_POST['hdn_product_hdnNameFile_'][$pos], APPLICATION_PATH. '/../public/uploads/tmp/');
								}
								else
								{
									//Same image
									$product_catalog_obj->image = $_POST['hdn_product_file_img_'][$pos];
								}
								
								$saved_product_catalog_obj = $product_catalog->save('product_catalog', $product_catalog_obj);
									
							}
						}
					}
				}
			
			
			if($saved_product)
			{				
				//If id  then Update product
				if($formData['id'])
				{
					$arr_success = array('section_id'=>$section_id);
					
					echo json_encode($arr_success);
					//success message
					$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success updated')));					
				}
				else
				{
					//Is new product					
					//create module description model
					$module_description =  new Core_Model_ModuleDescription();
					$module_description_obj = $module_description->getNewRow('wc_module_description');
					
					//Get module_id by module_name						
					$module_obj = new Core_Model_Module();
					$module = $module_obj->find('wc_module',array('name'=>'products'));
					$module_id = $module[0]->id;
					
					//Save data in module description table
					$module_description_obj->module_id= $module_id;
					$module_description_obj->row_id= $saved_product['id'];
					
					// Save data
					$saved_module_description = $module_description->save('wc_module_description',$module_description_obj);
					
					if($saved_module_description)
					{
						//create section module area model
						$section_module_area =  new Core_Model_SectionModuleArea();
						$section_module_area_obj = $section_module_area->getNewRow('wc_section_module_area');
							
							
						//Save data in section module area table
						$section_area_id = $section_module_area->personalized_find('wc_section_module_area', array(array('section_id','=',$section_id), array('module_description_id','=','')));
					
						$section_module_area_obj->section_id= $section_id;
						$section_module_area_obj->area_id= $section_area_id[0]->area_id;
						$section_module_area_obj->module_description_id= $saved_module_description['id'];
							
						// Save data
						$saved_section_module_area = $section_module_area->save('wc_section_module_area',$section_module_area_obj);
								
						$arr_success = array('section_id'=>$section_id);
						echo json_encode($arr_success);
						//success message
						$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved')));
									
					}
					else
					{
						$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in saving data')));
					}
				}
			}
		}
    }

    /**
     * Saves product order
     */
    public function saveorderAction()
    {
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    
    	$this->_helper->layout->disableLayout ();
    	//disable autorendering for this action
    	$this->_helper->viewRenderer->setNoRender();
    
    	if ($this->getRequest()->isPost())
    	{
    		//Create product Model
    		$product = new Products_Model_Products();
    			
    		//retrieved data from post
    		$formData  = $this->_request->getPost();
    			
    		$session_id = New Zend_Session_Namespace('id');
    		
    		if($formData['identifier']=='products')
    		{ 
    			//Get products order by form data
    			if(isset($formData['product_order']))
    				$order_list = GlobalFunctions::value_cleaner($formData['product_order']);
    
    			$order_arr = explode(',', $order_list);
    			$count = 1;
    
    			if(count($order_arr)>0)
    			{
    				//save products according order
    				foreach ($order_arr as $order)
    				{
    					if($order)
    					{
	    						$options = explode('_', $order);
	    						$product_id = $options[0];

    							$product_data = $product->find('product', array('id'=>$product_id));
    							
    							//Create product object for update with new order
    							$product_obj = $product->getNewRow('product');
    							$product_obj->id = $product_data[0]->id;
                                                        $product_obj->website_id = GlobalFunctions::value_cleaner($product_data[0]->website_id);
    							$product_obj->name = GlobalFunctions::value_cleaner($product_data[0]->name);
    							$product_obj->description = GlobalFunctions::value_cleaner($product_data[0]->description);
    							$product_obj->image = GlobalFunctions::value_cleaner($product_data[0]->image);
                                                        $product_obj->ficha = GlobalFunctions::value_cleaner($product_data[0]->ficha);
    							$product_obj->available = GlobalFunctions::value_cleaner($product_data[0]->available);
    							$product_obj->status = GlobalFunctions::value_cleaner($product_data[0]->status);
    							$product_obj->feature = GlobalFunctions::value_cleaner($product_data[0]->feature);   
                                                        $product_obj->highlight = GlobalFunctions::value_cleaner($product_data[0]->highlight);                                                         
    							$product_obj->order_number = GlobalFunctions::value_cleaner($count);
    							$serial_id = $product->save('product',$product_obj);
    							$count++;
    					}
    				}
    			}
    			if($formData['section_id']){
    				$arr_success = array('serial'=>$formData['section_id']);
    			}
    			else
    			{
    				$arr_success = array('serial'=>'saved');
    			}
    		}

    		echo json_encode($arr_success);
    		$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success saved order')));
    	}
    }
        
    /**
     * Deletes an existent product
     */
    public function deleteAction()
    {
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action
    	$this->_helper->viewRenderer->setNoRender();
    
    	//translate library
    	$lang = Zend_Registry::get('Zend_Translate');
    
    	//product_id passed in URL
    	$product_id = $this->_getParam('id');
    	
    	//section_id of product passed in URL
    	$section_id = $this->_getParam('section_id');
    	 
    	//session
    	$session = new Zend_Session_Namespace('id');

    	/* Delete module area according section of product */    	
    	//Get module_id by module_name    		
    	$module_obj = new Core_Model_Module();
    	$module = $module_obj->find('wc_module',array('name'=>'products'));
    	$module_id = $module[0]->id;
    	
    	//Get module description id by module and product_id
    	$module_description_obj = new Core_Model_ModuleDescription();
    	$module_description = $module_description_obj->find('wc_module_description',array('module_id'=>$module_id,'row_id'=>$product_id));
    	$module_description_id = $module_description[0]->id;
  	    	
    	//Delete module area by module description id and section id
		$section_module_area_aux = new Core_Model_SectionModuleArea();
    	$delete_product= $section_module_area_aux->delete('wc_section_module_area',array('module_description_id'=>$module_description_id,'section_id'=>$section_id));

    	//succes or error messages displayed on screen
    	if($delete_product)
    	{
    		$this->_helper->flashMessenger->addMessage(array('success'=>$lang->translate('Success deleted')));
    		$arr_success = array('serial'=>$section_id);
    		echo json_encode($arr_success);
    	}
    	else
    	{
    		$this->_helper->flashMessenger->addMessage(array('error'=>$lang->translate('Errors in deleting data')));
    	}
    }
        
    /**
     * Validate that the entered product name is not repeated
     */
    public function validateproductnameAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();
    	 
    	if ($this->getRequest()->isPost())
    	{
    		$data = $this->_request->getPost();

    		$productname = mb_strtolower($data['name'], 'UTF-8');
    		$id = -1;
    		if(isset($data['id']))
    			$id= $data['id'];
    
    		$product = new Products_Model_Products();
    		if(isset($id) && $id>0)
    			$product_array = $product->personalized_find('product', array(array('name', '=', $productname),array('id', '!=', $id)));
    		else
    			$product_array = $product->personalized_find('product', array(array('name', '=', $productname)));
    
    		if($product_array && count($product_array)>0)
    			echo json_encode(false);
    		else
    			echo json_encode(true);    
    	}
    }
    
    /**
     * Uploads a product picture
     */
    public function uploadfileAction()
    {
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action only:
    	$this->_helper->viewRenderer->setNoRender();
    
    	$formData  = $this->_request->getPost();
    
    	$directory = $formData['directory'];
    	$maxSize = $formData['maxSize'];
    
    	$directory = APPLICATION_PATH. '/../'. $directory;
    	if ($_FILES["product_photos"]["size"] <= $maxSize) {//DETERMINING IF THE SIZE OF THE FILE UPLOADED IS VALID
    		$path_parts = pathinfo($_FILES["product_photos"]["name"]);
    		$extensions = array(0 => 'jpg', 1 => 'jpeg', 2 => 'png', 3 => 'gif', 4 => 'JPG', 5 => 'JPEG', 6 => 'PNG', 7 => 'GIF', 8 => 'swf');
    
    		if (in_array($path_parts['extension'], $extensions)) {//DETERMINING IF THE EXTENSION OF THE FILE UPLOADED IS VALID
    			if (is_dir($directory)) {
    				do {
    					$tempName = 'pic_' . time() . '.' . $path_parts['extension'];
    				} while (file_exists($directory . $tempName));
    				move_uploaded_file($_FILES["product_photos"]["tmp_name"], $directory . $tempName);
    				echo $tempName;
    			} else {//ITS NOT A DIRECTORY
    				echo 3;
    			}
    		} else {//INCORRECT EXTENSION
    			echo 2;
    		}
    	} else {//INCORRECT SIZE
    		echo 1;
    	}
    }
    
    public function uploadfichaAction()
    {
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action only:
    	$this->_helper->viewRenderer->setNoRender();
    
    	$formData  = $this->_request->getPost();
    
    	$directory = $formData['directory'];
    	$maxSize = $formData['maxSize'];
    
    	$directory = APPLICATION_PATH. '/../'. $directory;
    	if ($_FILES["product_ficha"]["size"] <= $maxSize) {//DETERMINING IF THE SIZE OF THE FILE UPLOADED IS VALID
    		$path_parts = pathinfo($_FILES["product_ficha"]["name"]);
    		$extensions = array(0 => 'pdf', 1 => 'PDF', 2 => 'doc', 3 => 'DOC', 4 => 'docx', 5 => 'DOCX', 6 => 'xls',
                                    7 => 'XLS', 8 => 'xlsx', 9 => 'XLSX', 10 => 'jpg', 11 => 'JPG', 12 => 'jpeg', 13 => 'JPEG', 14 => 'png', 13 => 'PNG');
    
    		if (in_array($path_parts['extension'], $extensions)) {//DETERMINING IF THE EXTENSION OF THE FILE UPLOADED IS VALID
    			if (is_dir($directory)) {
    				do {
    					$tempName = 'ficha_' . time() . '.' . $path_parts['extension'];
    				} while (file_exists($directory . $tempName));
    				move_uploaded_file($_FILES["product_ficha"]["tmp_name"], $directory . $tempName);
    				echo $tempName;
    			} else {//ITS NOT A DIRECTORY
    				echo 3;
    			}
    		} else {//INCORRECT EXTENSION
    			echo 2;
    		}
    	} else {//INCORRECT SIZE
    		echo 1;
    	}
    }
    
    /**
     * Deletes the product temp picture
     */
    public function deletetemppictureAction()
    {
    	$this->_helper->layout->disableLayout ();
    	// disable autorendering for this action only:
    	$this->_helper->viewRenderer->setNoRender();
    
    	$formData  = $this->_request->getPost();
    	$temp_file = $formData['file_tmp'];
    
    	if ($temp_file)
    	{
    		if (file_exists(APPLICATION_PATH. '/../'. 'public/uploads/tmp/' . $temp_file))
    		{
    			unlink(APPLICATION_PATH. '/../'. 'public/uploads/tmp/'. $temp_file);
    		}
    	}
    }

    public function viewcatalogAction(){
   	
    	//Disable layout for this form
    	$this->_helper->layout->disableLayout ();   	
    	 
    	$data = $this->_request->getParams();
    	//Get product_id
    	$product_id = $data['product_id'];
//     	$location = $data['location'];
    	 
    	$catalog = new Products_Model_ProductCatalog();
    	$products_catalog = $catalog->find('product_catalog', array('product_id'=>$product_id));
    	
    	$product_obj = new Products_Model_Products();
    	$product = $product_obj->find('product', array('id'=>$product_id));
    	
    	$this->view->catalog = $products_catalog;
    	$this->view->product = $product[0];
//     	$this->view->location = $location;
 	
    }
    
}
