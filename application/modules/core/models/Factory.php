<?php
/**
 *	Factory model simplifies code used to recognize database tables and attributes
 *
 * @category   WicaWeb
 * @package    Core_Model
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.1
 * @author      Jose Luis Landazuri - Jorge Tenorio - David Rosales
 */

class Core_Model_Factory {

	/**
	 * Obtains default database adapter 
	 */
	public  function getAdapter()
	{	
		$this->dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	}	
	
	/**
	 * Return empty object with the table attributes
	 * @param string $tableName
	 * @return Object
	 */
	public function getNewRow($tableName)
	{
	
		$this->getAdapter();
	
		$description = $this->dbAdapter->describeTable($tableName);
	
		$attributes = array();
	
		foreach($description as $field => $detail)
		{
			$attributes[$field]= NULL;
		}
	
		$obj = (object)$attributes;
	
		return $obj;
	}
		
	/**
	 * Returns an object array with results
	 * @param string $tableName
	 * @param array $criteria
	 * @return array
	 */
	public function find($tableName,$criteria = array(), $order_params = array())
	{
		//get criteria
		$where = '1=1 ';
		if($criteria)
		{	
			foreach($criteria as $field => $value){
				if(!$value)
					$where .= 'and '.$field.' IS NULL ';
				else
					$where .= 'and '.$field.' = "'.$value.'" ';
			}
		}	
		$order = NULL;
                if ($order_params) {
                    foreach ($order_params as $field_order => $way) {
                        if ( empty( $order ) ) {
                            $order = array($field_order . ' ' . $way);
                        }else{
                        array_push($order, $field_order . ' ' . $way);
                        }
                    }
                }

		$table = new Zend_Db_Table($tableName);	
		$result = $table->fetchAll($where, $order);

		$return = array();
		foreach($result as $row)
		{
			$temp = array();
			foreach($row as $col=>$value)
			{
				$temp[$col] = utf8_encode($value);
			}
	
			//convert into object
			$obj = (object)$temp;
			array_push($return, $obj);
		}
	
		return $return;
	}
	
        /**
	 * Returns an object array with results
	 * @param string $tableName
	 * @param array $criteria
	 * @return array
	 */
	public function find_union($tableName, $tableName2, $field1, $field2, $union_type, $rowFields,  $criteria = array(), $order_params = array())
	{
            
		//get criteria
		$where = '1=1 ';
		if($criteria)
		{	
			foreach($criteria as $field => $value){
				if(!$value)
					$where .= 'and '.$field.' IS NULL ';
				else
					$where .= 'and '.$field.'= "'.$value.'" ';
			}
		}	
		$order = NULL;
                if ($order_params) {
                    foreach ($order_params as $field_order => $way) {
                        if ( empty( $order ) ) {
                            $order = array($field_order . ' ' . $way);
                        }else{
                        array_push($order, $field_order . ' ' . $way);
                        }
                    }
                }
                                
                               
                $table = new Zend_Db_Table($tableName);	
                                
                $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                        ->setIntegrityCheck(false);
                
                $temp1 = $tableName.'.'.$field1.' = '.$tableName2.'.'.$field2;
                switch ($union_type) {
                    case 'normal':
                        //$select->join($tableName2, $tableName2.'.idPerfil = '.$tableName.'.id', $rowFields);                        
                        //$select->join('rs_local_perfiles', 'rs_locales.id = rs_local_perfiles.idLocal', array('idPerfil'=>'rs_local_perfiles.idPerfil') );   
                        $select->join($tableName2, $temp1, $rowFields );                        
                        break;
                    case 'left':
                        $select->joinLeft($tableName2, $temp1, $rowFields);
                        break;
                    case 'right':
                        $select->joinRight($tableName2, $temp1, $rowFields);
                        break;
                }

                $select->where($where);
                $select->order($order);
                 
              
		$result = $table->fetchAll($select);
                //print_r($result); 
                //Zend_Debug::dump($select);
               


		$return1 = array();
		foreach($result as $row)
		{
			$temp = array();
			foreach($row as $col=>$value)
			{
				$temp[$col] = utf8_encode($value);
			}
	
			//convert into object
			$obj = (object)$temp;
			array_push($return1, $obj);
		}
	
		return $return1;
	} 
        /**
	 * Returns an object array with results
	 * @param string $tableName
	 * @param array $criteria
	 * @return array
	 */
	public function findMultipleParent($tableName,$criteria = array(), $order_params = array())
	{
		//get criteria
		$where = '1=1 ';
		if($criteria)
		{	
                        $count = 0;
			foreach($criteria as $field => $value){
                        $count++;
				if($count == 1)
					$where .= 'and section_parent_id = "'.$value.'" ';
				else
					$where .= 'or section_parent_id = "'.$value.'" ';
			}
                        $where .= ' and article = "yes" ';
		}	
		$order = NULL;
                if ($order_params) {
                    foreach ($order_params as $field_order => $way) {
                        if ( empty( $order ) ) {
                            $order = array($field_order . ' ' . $way);
                        }else{
                        array_push($order, $field_order . ' ' . $way);
                        }
                    }
                }

		$table = new Zend_Db_Table($tableName);	
		$result = $table->fetchAll($where, $order);

		$return = array();
		foreach($result as $row)
		{
			$temp = array();
			foreach($row as $col=>$value)
			{
				$temp[$col] = utf8_encode($value);
			}
	
			//convert into object
			$obj = (object)$temp;
			array_push($return, $obj);
		}
	
		return $return;
	}
        
	/**
	 * Returns an object array with results with LIMIT
	 * @param string $tableName
	 * @param array $criteria
         * @param array $limit1
         * @param array $limit2
	 * @return array
	 */
	public function limit_find($tableName,$criteria = array(), $order_params = array(), $limit1, $limit2)
	{
		//get criteria
		$where = '1=1 ';
		if($criteria)
		{	
			foreach($criteria as $field => $value){
				if(!$value)
					$where .= 'and '.$field.' IS NULL ';
				else
					$where .= 'and '.$field.' = "'.$value.'" ';
			}
		}	
		$order = NULL;
                if ($order_params) {
                    foreach ($order_params as $field_order => $way) {
                        if ( empty( $order ) ) {
                            $order = array($field_order . ' ' . $way);
                        }else{
                        array_push($order, $field_order . ' ' . $way);
                        }
                    }
                }
		$table = new Zend_Db_Table($tableName);	
		$result = $table->fetchAll($where, $order, $limit1, $limit2);
		$return = array();
		foreach($result as $row)
		{
			$temp = array();
			foreach($row as $col=>$value)
			{
				$temp[$col] = utf8_encode($value);
			}
			//convert into object
			$obj = (object)$temp;
			array_push($return, $obj);
		}
		return $return;
	}
	/**
	 * Save object into table
	 * @param string $tableName
	 * @param Object $object
	 * @return mixed|null
	 */
	public function save($tableName,$object,$action=NULL)
	{
		$update = FALSE;
		$updateCriteria = array();
		$data = array();
		$count_keys = 0;
		
		if (is_object($object)) 
		{
			// Get the properties of the given object
			// with get_object_vars function
			$attributes = get_object_vars($object);
	
			$table = new Zend_Db_Table($tableName);
	
			$info = $table->info();
			
			//get the primary keys		
			if(is_array($info['primary']))
			{
				foreach($info['primary'] as $k => $field)
				{
					$primary[] = $field;
				}
			}
			else 
			{
				$primary[] = $info['primary'];
			}
	
			foreach($attributes as $field => $value)
			{
				//check for primary key
				if(in_array($field,$primary) && $value!= '')
				{
					$count_keys++;				
					array_push($updateCriteria, "$field = $value");	
					$primary_row[$field] = $value;
				}
				
				if($value=='')
				{
					$value = NULL;								
				}

				$data[$field]=$value;							
			}

			//insert or update
			if($count_keys==count($primary) && $action == NULL)
			{
				//is update				
				$result = $table->update($data, $updateCriteria);
				$result = $primary_row;
			}
			else 
			{
				$res = $table->insert($data);
				if(count($primary)==1)
					$result[$primary[0]] = $res;
				else
					$result = $res;
			}
	
			return $result;
	
		}else{
			return NULL;
		}
	}
	
	/**
	 * Delete a row from database
	 * @param string $tableName
	 * @param array $criteria
	 * @return array
	 */
	public function delete($tableName,$criteria = array())
	{
		//get criteria
		$where = '1=1 ';
		if($criteria)
		{
			foreach($criteria as $field => $value){
				if(!$value)
					$where .= 'and '.$field.' IS NULL ';
				else
					$where .= 'and '.$field.' = "'.$value.'" ';
			}
		}
	
		$table = new Zend_Db_Table($tableName);
		$result = $table->delete($where);
			
		return $result;
	}
	
	/**
	 * Function to create personalized find querys to search for other fields than the id
	 * @param string $tableName
	 * @param array $criteria
	 * criteria array must have an array with 3 positions for each field that we want to include in the where clause to find records
	 * Example:  personalized_find($tableName,array(array('field','operator', 'value')))
	 * @param string $order_by
	 * order_by field name 			
	 * @return array
	 */
	public function personalized_find($tableName,$criteria = array(),$order_by = null)
	{

		//get criteria
		$where = '1=1 ';
		if($criteria)
		{
			foreach($criteria as $value){
				if($value & count($value)==3)
				{
					switch($value[1]){
						case 'LIKE':
							$where .= 'and '.'LOWER('.$value[0].') LIKE _utf8 "%'.$value[2].'%" COLLATE utf8_bin';
							break;
						case '==':
							$where .= 'and '.'LOWER('.$value[0].') = _utf8 "'.$value[2].'" COLLATE utf8_bin';
							break;
                                                case '<':
                                                        $where .=" and ". $value[0] ." < '" . $value[2] . "'";
                                                        break;
							
						default:
							if($value[1] == '=' && !$value[2])
								$where .= ' and '.$value[0].' IS NULL ';
							else
								$where .= ' and '.$value[0].' '.$value[1].' "'.$value[2].'" ';
							break;
					}
				}
				else {
					return NULL;
				}	
			}
		}
		else{
			return NULL;
		}
		//Zend_Debug::dump($where);		
		$table = new Zend_Db_Table($tableName);
		$result = $table->fetchAll($where,$order_by);
		
		$return = array();
		foreach($result as $row)
		{
			$temp = array();
			foreach($row as $col=>$value)
			{
				$temp[$col] = utf8_encode($value);
			}
	
			//convert into object
			$obj = (object)$temp;
			array_push($return, $obj);
		}
			
		return $return;
	}
        
        public function find_between($tableName,$criteria = array(),$order_by = null) {
            //get criteria
		$where = '1=1 ';
		if($criteria)
		{
			foreach($criteria as $value){
                           
                           
				if($value)
				{     if(count($value)==3){
					switch($value[1]){
						case 'LIKE':
							$where .= 'and '.'LOWER('.$value[0].') LIKE _utf8 "%'.$value[2].'%" COLLATE utf8_bin';
							break;
						case '==':
							$where .= 'and '.'LOWER('.$value[0].') = _utf8 "'.$value[2].'" COLLATE utf8_bin';
							break;
							
						default:
							if($value[1] == '=' && !$value[2])
								$where .= ' and '.$value[0].' IS NULL ';
							else
								$where .= ' and '.$value[0].' '.$value[1].' "'.$value[2].'" ';
							break;
					}
                                    }elseif(count($value)==4 && $value[1]=='BETWEEN') {
                                        $where .=" and '".$value[0]."' BETWEEN ".$value[2]." AND ".$value[3];
                                     }else{
                                         return NULL;
                                     }
				}
				else {
					return NULL;
				}	
			}
                        
		}
		else{
			return NULL;
		}
                //Zend_Debug::dump($where); 
                
		$table = new Zend_Db_Table($tableName);
		$result = $table->fetchAll($where,$order_by);
		
		$return = array();
		foreach($result as $row)
		{
			$temp = array();
			foreach($row as $col=>$value)
			{
				$temp[$col] = utf8_encode($value);
			}
	
			//convert into object
			$obj = (object)$temp;
			array_push($return, $obj);
		}
			
		return $return;
        }
	
}