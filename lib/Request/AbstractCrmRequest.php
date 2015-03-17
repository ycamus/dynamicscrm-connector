<?php
use \DOMElement;

/**
 * Class AbstractCrmRequest
 *
 * @package connector\lib\Request
 */
class AbstractCrmRequest
{
        public function __construct(RequestBuilder $requestBuilder)
    {
      
    }
   /**
     * Format Attributes for insert updates and execute<p>
     *
     * @param array $Param
     *        	Attribute
     *        	parameter <p>
     *
     *        	*
     *        	* array( \n
     *        	* 'field'=>'fieldname',\n
     *        	* 'type'=>'Field type',\n
     *        	* 'value'=>'value'\n
     *        	* )
     *        	* array( \n
     *        	* 'field'=>'fieldname',\n
     *        	* 'type'=>'EntityReference',\n
     *        	* 'id'=>'guidforentity',\n
     *        	* 'name'=>'entityname(table)'\n
     *        	* )
     *
     * @return correct Attribute XML for insert/update/execute cases
     */
    private function FormatAttribute($Param) {
    	$TxtAttribute = '';
    	$this->TestParameters ( $Param, array('field','type') );
    	if (isset ( $Param ['field'] ) && isset ( $Param ['type'] )) {
    		$TxtAttribute .= '<b:KeyValuePairOfstringanyType>
                            <c:key>' . $Param ['field'] . '</c:key>';
    		switch ($Param ['type']) {
    			case 'OptionSetValue' :
    			case 'optionsetvalue' :
    			case 'option' :
    			case 'Option' :
    				$this->TestParameter ( $Param, 'value' );
    				$TxtAttribute .= '<c:value i:type="b:OptionSetValue"><b:Value>' . $Param ['value'] . '</b:Value></c:value>';
    				break;
    			case 'Money' :
    			case 'money' :
    				$this->TestParameter ( $Param, 'value' );
    				$TxtAttribute .= '<c:value i:type="b:Money"><b:value>' . $Param ['value'] . '</b:value></c:value>';
    				break;
    			case 'double' :
    			case 'Double' :
    			case 'Float' :
    			case 'float' :
    				$this->TestParameter ( $Param, 'value' );
    				$TxtAttribute .= '<c:value i:type="d:double" xmlns:d="http://www.w3.org/2001/XMLSchema">' . $Param ['value'] . '</c:value>';
    				break;
    			case 'decimal':
    			case 'Decimal':
    				$this->TestParameter ( $Param, 'value' );
    				$TxtAttribute .= '<c:value i:type="d:decimal" xmlns:d="http://www.w3.org/2001/XMLSchema">' . $Param ['value'] . '</c:value>';
    				break;
    			case 'long':
    			case 'Long':
    				$this->TestParameter ( $Param, 'value' );
    				$TxtAttribute .= '<c:value i:type="d:long" xmlns:d="http://www.w3.org/2001/XMLSchema">' . $Param ['value'] . '</c:value>';
    				break;
    			case 'Boolean' :
    			case 'boolean' :
    			case 'bool' :
    			case 'Bool' :
    				$this->TestParameter ( $Param, 'value' );
    				$TxtAttribute .= '<c:value i:type="d:boolean" xmlns:d="http://www.w3.org/2001/XMLSchema">' . $Param ['value'] . '</c:value>';
    				break;
    			case 'Integer' :
    			case 'integer' :
    			case 'Int' :
    			case 'int' :
    				$this->TestParameter ( $Param, 'value' );
    				$TxtAttribute .= '<c:value i:type="d:int" xmlns:d="http://www.w3.org/2001/XMLSchema">' . $Param ['value'] . '</c:value>';
    				break;
    			case 'DateTime' :
    			case 'datetime' :
    				$this->TestParameter ( $Param, 'value' );
    				$TxtAttribute .= '<c:value i:type="d:dateTime" xmlns:d="http://www.w3.org/2001/XMLSchema">' . $Param ['value'] . '</c:value>';
    				break;
    			case 'String' :
    			case 'string' :
    			default :
    				$this->TestParameter ( $Param, 'value' );
    				$TxtAttribute .= '<c:value i:type="d:string" xmlns:d="http://www.w3.org/2001/XMLSchema">' . $Param ['value'] . '</c:value>';
    				break;
    			case 'EntityReference' :
    			case 'entityreference' :
    			case 'Entity' :
    			case 'entity' :
    				$this->TestParameters ( $Param, array('id','name') );
    				$this->TestGuid($Param ['id'],$Param);
    				$TxtAttribute .= '<c:value i:type="b:EntityReference">
												<b:Id>' . $Param ['id'] . '</b:Id>
												<b:LogicalName>' . $Param ['name'] . '</b:LogicalName>
												<b:Name i:nil="true" />
												</c:value>';
    				break;
    			case 'ActivityParty' :
    			case 'Activity' :
    			case 'activityparty' :
    			case 'activity' :
    			case 'ArrayOfEntity' :
    			case 'arrayofentity' :
    			case 'Array' :
    			case 'array' :
    				$TxtAttribute .= '<c:value i:type="b:ArrayOfEntity">';
    				$this->TestParameter ( $Param, 'value' );
    				foreach ( $Param ['value'] as $entity ) {
    					$TxtAttribute .= ' <b:Entity>
									<b:Attributes>';
    					$TxtAttribute .= $this->FormatAttribute ( $entity );
    					$TxtAttribute .= '</b:Attributes><b:Entitystate i:nil="true" />
							<b:FormattedValues />
								<b:Id>00000000-0000-0000-0000-000000000000</b:Id>
							<b:LogicalName>activityparty</b:LogicalName>
							<b:RelatedEntities />
							</b:Entity>';
    				}
    				$TxtAttribute .= '
  								</c:value>';
    					
    				break;
    		}
    		$TxtAttribute .= '</b:KeyValuePairOfstringanyType>';
    	}
    
    	return $TxtAttribute;
    }
    
    /**
     * Format Attributes for insert,updates and execute<p>
     *
     * @param array $Params
     *        	Attributes
     *        	parameter <p>
     *
     *        	* array(\n
     *        	* 1=>array( \n
     *        	* 'field'=>'fieldname',\n
     *        	* 'type'=>'Field type',\n
     *        	* 'value'=>'value'\n
     *        	* ) ,\n
     *        	* 2=>array( \n
     *        	* 'field'=>'fieldname',\n
     *        	* 'type'=>'EntityReference',\n
     *        	* 'id'=>'guidforentity',\n
     *        	* 'name'=>'entityname(table)'\n
     *        	* )
     *        	3=>array( \n
     *        	* 'field'=>'fieldname',\n
     *        	* 'type'=>'ActivityParty',\n
     *        	* 'value'=>array(\n
     *        	"entity1"=>array(\n
     *        	* 1=>array( \n
     *        	* 'field'=>'fieldname',\n
     *        	* 'type'=>'Field type',\n
     *        	* 'value'=>'value'\n
     *        	* ) ,\n
     *        	* 2=>array( \n
     *        	* 'field'=>'fieldname',\n
     *        	* 'type'=>'EntityReference',\n
     *        	* 'id'=>'guidforentity',\n
     *        	* 'name'=>'entityname(table)'\n
     *        	* ) \n
     *        	* )\n
     *        	* )\n
     *        	, \n
     *        	*)\n
     *
     * @return correct Attributes XML for insert/update/execute cases
     */
    private function FormatAttributes($Params) {
    	$TxtAttribute = '';
    
    	if (is_array ( $Params )) {
    		foreach ( $Params as $Param ) {
    			$TxtAttribute .= $this->FormatAttribute ( $Param );
    		}
    	}
    	return $TxtAttribute;
    }
    /**
     * Format Fetch Attributes <p>
     * (Column being resolved by RetrieveMultiple)
     *
     * @param array $Columns
     *        	<p>
     *        	* simple request\n
     *        	* array(\n
     *        	* 'Colname1',\n
     *        	* 'Colname2'...\n
     *        	* )\n\n
     *
     *        	*aggregate request \n
     *        	* array(\n
     *        	* '1'=>array(\n
     *        	* 'name'=>'colname',\n
     *        	* 'alias'=>	'alias',(false,notset => will keep colname as alias)\n
     *        	* 'aggregate' =>'aggregate function name' ,if aggregate function needed else false or unset (avg , max, min, sum, count => SQL : (COUNT(*)) ,countcolumn => SQL : (COUNT(name)) ,countdistinct => SQL : (COUNT(DISTINCT name)) )\n
     *        	* ),\n
     *        	* '2'=> array(\n
     *        	* 'name'=>'colname',\n
     *        	* 'alias'=>	'alias',(false,notset => will keep colname as alias)\n
     *        	* 'groupby' =>true or year,quarter,month,week,day for date grouping\n
     *        	* )\n
     *        	* ) \n\n
     *        	note that you cant mix simple column with agregates
     * @return correct XML for Columns
     */
    private function FormatFetchAttribute($Columns) {
    	$Attributes = '';
    	if (is_array ( $Columns )) {
    		foreach ( $Columns as $column ) {
    			if (is_array ( $column )) {
    				$this->TestParameter ( $column, 'name' );
    				// aggregate cases
    				if (isset ( $column ['name'] )) {
    					if (empty ( $column ['alias'] )) {
    						$column ['alias'] = $column ['name'];
    					}
    					// basic aggregate function avg , max, min, sum, count (COUNT(*)) ,countcolumn (COUNT(name)) ,countdistinct (COUNT(DISTINCT name))
    					if (! empty ( $column ['aggregate'] )) {
    						$this->TestAgreggate ( $column ['aggregate'] );
    						if ($column ['aggregate'] == 'countdistinct') {
    							$Attributes .= "&lt;attribute name='" . $column ['name'] . "' alias='" . $column ['alias'] . "' aggregate='countcolumn' distinct='true' /&gt;";
    						} else {
    							$Attributes .= "&lt;attribute name='" . $column ['name'] . "' alias='" . $column ['alias'] . "' aggregate='" . $column ['aggregate'] . "' /&gt;";
    						}
    						// so is that a groupby?
    					} else if (isset ( $column ['groupby'] )) {
    						$this->TestParameter ( $column, 'alias' );
    						if (isset ( $column ['alias'] )) {
    							if ($column ['groupby'] !== true) {
    								$Attributes .= "&lt;attribute name='" . $column ['name'] . "' alias='" . $column ['alias'] . "' dategrouping='" . $column ['groupby'] . "' groupby='true' /&gt;";
    							} else {
    								$Attributes .= "&lt;attribute name='" . $column ['name'] . "' alias='" . $column ['alias'] . "' groupby='true' /&gt;";
    							}
    						}
    					}else{
    						$Attributes .= "&lt;attribute name='" . $column ['name'] . "' alias='" . $column ['alias'] . "' /&gt;";
    
    					}
    					// normal cases
    				}
    			} else
    				$Attributes .= "&lt;attribute name='" . $column . "' /&gt;";
    		}
    	}
    	return $Attributes;
    }
    
    /**
     * Format Entity Header
     *
     * @param $EntityName String
     * @return correct XML for Entity header
     */
    private function FormatFetchEntity($EntityName) {
    	return " &lt;entity name='" . $EntityName . "'&gt;";
    }
    
    /**
     * @param unknown $Param
     * @param unknown $Fields
     */
    private function TestParameters($Param, $Fields) {
    	foreach ( $Fields as $Field ) {
    		$this->TestParameter ( $Param, $Field );
    	}
    }
    
    /**
     * @param unknown $Param
     * @param unknown $field
     * @throws \InvalidArgumentException
     */
    private function TestParameter($Param, $field) {
    	if (! isset ( $Param [$field] )) {
    		$strArray = print_r ( $Param, true );
    		throw new \InvalidArgumentException ( sprintf ( "Missing Parameter $field for array : $strArray" ) );
    	}
    }
    
    public function TestGuid($Guid,$Param=false){
    	if(!preg_match("/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i", $Guid)){
    		if ($Param){
    			$strArray = print_r ( $Param, true );
    			throw new \InvalidArgumentException ( sprintf ( "'$Guid' is not a Valid Guid in array : $strArray" ) );
    		}else{
    			throw new \InvalidArgumentException ( sprintf ( "'$Guid' is not a Valid Guid" ) );
    		}
    			
    	}
    }
    
    /**
     * @param unknown $Param
     * @throws \InvalidArgumentException
     */
    private function TestAgreggate($Param) {
    	if (! in_array ( $Param, $this->Aggregate )) {
    		$AggregateValue = print_r ( $this->Aggregate, true );
    		throw new \InvalidArgumentException ( sprintf ( "Aggregate Invalid should be in : $AggregateValue" ) );
    	}
    }
    
    
    /**
     * Format Columns for REtrieveMultiple<p>
     *
     * @param array $Columns
     *        	array with colnames or false for all col\n <p>
     *        	* array(\n
     *        	* 'colname1',\n
     *        	* 'colname2'...\n
     *        	* )\n
     *        	notice it will always return GUID Except with agregate function</p>
     * @return correct Columns XML for REtrieveMultiple
     */
    private function FormatColumn($Columns) {
    	if ($Columns !== false && ! is_array ( $Columns )) {
    		throw new \InvalidArgumentException ( sprintf ( "Column Parameter  invalid : $Columns" ) );
    	}
    	$AllCol = ($Columns == false) ? 'true' : 'false';
    	$TxtColumn = '<ser:columnSet><con:AllColumns>' . $AllCol . '</con:AllColumns><con:Columns>';
    	if ($Columns != false) {
    		foreach ( $Columns as $Column ) {
    			$TxtColumn .= '<arr:string>' . $Column . '</arr:string>';
    		}
    	}
    	$TxtColumn .= '</con:Columns></ser:columnSet>';
    	return $TxtColumn;
    }
    
    /**
     * Format Order by for the request
     *
     * @param array $Order
     *        	Clause order by
     *        	* array(\n
     *        	* 1=>array(\n
     *        	* 'Column'=> 'ColumName',\n
     *        	* 'Order'=>'Asc'\n
     *        	* ),\n
     *        	* 2=>array(\n
     *        	* 'Column'=> 'ColumName2',\n
     *        	* 'Order'=>'Desc'\n
     *        	* )..\n
     *        	* )\n
     * @return correct XML for this Order by
     */
    private function FormatFetchOrder($Orders) {
    	$TxtOrder = '';
    	if (is_array ( $Orders )) {
    		foreach ( $Orders as $Order ) {
    			$this->TestParameters ( $Order, array('Column','Order'));
    			if (isset ( $Order ['Column'] ) && isset ( $Order ['Order'] )) {
    				$TxtOrder .= "&lt;order attribute='" . $Order ['Column'] . "' descending='";
    				$TxtOrder .= ($Order ['Order'] == 'Asc') ? 'false' : 'true';
    				$TxtOrder .= "' /&gt;";
    			}
    		}
    	}
    	return $TxtOrder;
    }
    
    /**
     * Format Filters for request
     * will return a Filter is Type is set
     * else will retun a condition
     *
     * @param array $Filter
     *        	<p>
     *        	- basic and req where condition1 and condition2\n\n
     *        	array(\n
     *        	*		Condition1=>array\n
     *        	*		Condition2=>array\n
     *        	)\n
     *        	- basic or req where condition1 or condition2\n\n
     *        	array(\n
     *        	* 'Type'=>'or'\n
     *        	*			Condition1=>array\n
     *        	*			Condition2=>array\n
     *        	)\n
     *        	- multiple occurence and/or can be tricky\n
     *        	where ((condition1 and condition2) or ((condition5 and condition6)or condition4))\n\n
     *
     *        	* array (\n
     *        	* Type=>'or'\n
     *        	* 1=>array(\n
     *        	* 'Type'=>'and'\n
     *        	* Condition1=>array\n
     *        	* Condition2=>array\n
     *        	* )\n
     *        	* 2=>array(\n
     *        	* 'Type'=>'or'\n
     *        	* Condition3=>array(\n
     *        	* 'Type'=>'and'\n
     *        	* Condition5=>array\n
     *        	* Condition6=>array\n
     *        	* )\n
     *        	* Condition4=>array\n
     *        	* )\n
     *        	* )\n
     *        	[Check FormatCondition for more information about each operator](@ref FormatCondition)
     *
     * @return correct XML for thoose filters
     */
    private function FormatFetchFilter($Filters) {
    	$TxtFilter = '';
    	if (is_array ( $Filters )) {
    		if (isset ( $Filters ['Type'] )) {
    			$TxtHead = "&lt;filter type='" . $Filters ['Type'] . "' &gt;";
    		} else {
    			$TxtHead = '&lt;filter &gt;';
    		}
    		foreach ( $Filters as $Filter ) {
    			if (isset ( $Filter ['Type'] )) {
    				$TxtFilter .= $this->FormatFetchFilter ( $Filter );
    			} else {
    				if (isset ( $Filter ['Op'] )) {
    					$TxtFilter .= $this->FormatCondition ( $Filter );
    				}
    			}
    		}
    		if($TxtFilter!=''){
    			$TxtFilter=$TxtHead.$TxtFilter.'&lt;/filter &gt;';
    			return $TxtFilter;
    		}else return '';
    	} else	return '';
    
    
    }
    
    /**
     * Format Join for RetriveMultiple
     *
     * @param array $Joins
     *        	<p>
     *        	* array( \n
     *        	* 1=>array(	\n
     *        	* 'name' => (String) tablename\n
     *        	* 'from' => join name On 'from'=to \n
     *        	* 'to' => join name On from='to' \n
     *        	* 'alias' =>not necessary can be false but aliases will be used when you try to get back ur data meaning $result->alias.data\n
     *        	* 'type' => Operator\n
     *        	* 'colums' => array [see FormatColumn for detail](@ref FormatColumn) \n
     *        	* 'where' => array [see FormatFetchFilter for detail](@ref FormatFetchFilter) \n
     *        	* 'nested'=> (reproduce the same pattern and will nest a join false or unset if not required)\n
     *        	* ),\n
     *        	* 2=>array('name'=>...)\n
     *        	* )\n
     *        	if only one join is required u can go directly with no nested array
     *
     *
     *        	</p>
     * @return correct XML for request this condition
     */
    private function FormatFetchJoin($Joins) {
    	// so we dont have to make a special statement for only 1 join
    	$TxtJoin = '';
    	if (isset ( $Joins ['name'] )) {
    		$Joins = array (
    				'1' => $Joins
    		);
    	}
    	foreach ( $Joins as $Join ) {
    		$this->TestParameters($Join,array('name','from','to','type','columns','where'));
    		$TxtJoin .= "&lt;link-entity name='" . $Join ['name'] . "' from='" . $Join ['from'] . "' to='" . $Join ['to'] . "' ";
    		if (! empty ( $Join ['alias'] )){
    			$TxtJoin .= " alias='" . $Join ['alias'] . "' ";
    		}
    		$TxtJoin .= " link-type='" . $Join ['type'] . "' &gt;";
    		$TxtJoin .= $this->FormatFetchAttribute ( $Join ['columns'] );
    		if (! empty ( $Join ['nested'] ))
    			$TxtJoin .= FormatFetchJoin ( $Join );
    		$TxtJoin .= $this->formatFetchFilter ( $Join ['where'] );
    		$TxtJoin .= "&lt;/link-entity&gt;";
    	}
    	return $TxtJoin;
    }
    
    /**
     * Format condition Depending on Operator
     *
     * @param array $Filter<p>
     *        	*array(\n
     *        	*'Column' => (String) $ColumnName\n
     *        	*'Op' => Operator [link to all operator](http://msdynamicscrmblog.wordpress.com/2013/05/10/fetch-xml-and-conditionexpression-operators-using-c-in-dynamics-crm-2011/)\n
     *        	*'Value' => $Value (if necessary depending on operator, data or could be an array)\n
     *        	*'Value2' => $Value2 (if necessary mostly in between case)\n
     *        	*)
     *        	</p>
     * @return correct XML for request this condition
     */
    private function FormatCondition($Filter) {
    
    	/*
    	 * The operators wich dont need a value
    	 * so no need for value here
    	 */
    	if (in_array ( $Filter ['Op'], $this->Operator ['0'] )) {
    		$Condition = "&lt;condition attribute='" . $Filter ['Column'] . "' operator='" . $Filter ['Op'] . "' /&gt;";
    		/*
    		 * The basic operators
    		 * Value to be set, nothing much
    		 */
    	} else if (in_array ( $Filter ['Op'], $this->Operator ['1'] )) {
    		$this->TestParameters ( $Filter, array (
    				'Column',
    				'Value'
    		) );
    		$Condition = "&lt;condition attribute='" . $Filter ['Column'] . "' operator='" . $Filter ['Op'] . "' value='" . $Filter ['Value'] . "'  /&gt;";
    		/*
    		 * The in operators
    		 * Value is now an array with data to be tested array('1','2',...)
    		 */
    	} else if (in_array ( $Filter ['Op'], $this->Operator ['2'] )) {
    		$this->TestParameters ( $Filter, array (
    				'Column',
    				'Value'
    		) );
    		$Condition = "&lt;condition attribute='" . $Filter ['Column'] . "' operator='" . $Filter ['Op'] . "' &gt;";
    		if (! is_array ( $Filter ['Value'] )) {
    			$TxtFilter = print_r ( $Filter, true );
    			throw new \InvalidArgumentException ( sprintf ( "Filter Incorrect [Value] for this kind of operator should be an array  : $TxtFilter" ) );
    		}
    		foreach ( $Filter ['Value'] as $Value ) {
    			$Condition .= '&lt;value&gt;' . $Value . '&lt;/value&gt;';
    		}
    		$Condition .= '&lt;/condition &gt;';
    		/*
    		 * The 2 values operator
    		 * Value and Value2 in array
    		 */
    	} else if (in_array ( $Filter ['Op'], $this->Operator ['3'] )) {
    		$this->TestParameters ( $Filter, array (
    				'Column',
    				'Value','Value2'
    		) );
    		$Condition = "&lt;condition attribute='" . $Filter ['Column'] . "' operator='" . $Filter ['Op'] . "' &gt;";
    		$Condition .= '&lt;value&gt;' . $Filter ['Value'] . '&lt;/value&gt; &lt;value&gt;' . $Filter[
    				'Value2'
    		] . '&lt;/value&gt;';
    		$Condition .= '&lt;/condition &gt;';
    	} else {
    		$ListOperator = print_r ( $this->Operator, true );
    		throw new \InvalidArgumentException ( sprintf ( "Condition '" . $Filter ['Op'] . "' Invalid should be in : $ListOperator" ) );
    	}
    
    	return $Condition;
    }
 
}