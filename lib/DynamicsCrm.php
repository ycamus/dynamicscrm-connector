<?php
use Symfony\Component\HttpFoundation\HeaderBag;
/*! \mainpage DynamicsCrm
 *
 * \section Installation
 *  if by any mean you dont get the bundle from packagist add to you composer.json under require\\n  
 
  *      "dynamicscrm/connector" : "dev-master"
  
  * to you composer.json\n
  * and then : \n
 *
 *      composer install
 *
 *or 
 *
 *      composer update
 *
 *enjoy the bundle\n\n
 *
 *Example:\n\n
 
 *      $DynamicsCrm=new DynamicsCrm($serv_adress, $user, $password);
 *      $result=$DynamicsCrm->Retrieve($Table, $Id, $Columns);
 *
 **/
/**
 * DynamicsCrm
 *
 * Copyright (c) 2014 DynamicsCrm
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * @category DynamicsCrm
 * @package DynamicsCrm
 * @copyright Copyright (c) 2014 DynamicsCrm (http://DynamicsCrm.codeplex.com/)
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version 0.0.1, 2014-10-06
 *         
 *      
 */


/*! \brief Connector and basic operation from PHP to CRM using NTLM and curl.
 *         
 *This connector load from a simple login/password/url (/web) \n
 *it has the update/create/retrieve functionnality\n
 *AND RetrieveMultiple based on FetchXML wich allow to make some powerfull request\n
 *without the need to make many code\n
 *For this part better check the documentation to understand the nesting of parameters.\n
 *  
 *@todo make a Execute function to run anything with parameter.
 */
class DynamicsCrm {
	var $serv_adress;
	var $user;
	var $password;
	
	/**
	 * Constructor
	 *
	 * @param string $serv_adress Url to service
	 * @param string $user Dynamics CRM connection's User <p>
	 * (LDAP) Domain/user </p>
	 * @param string $password Dynamics CRM connection's password 
	 */
	function __construct($serv_adress, $user, $password) {
		$this->setServAdress($serv_adress);
		$this->setUser($user);
		$this->setPassword($password);
	}
	
	/**
	 * @return  Dynamics CRM connection's Url to service
	 */
	public function getServAdress() {
		return $this->serv_adress;
	}
	
	/**
	 * @param string $serv_adress Dynamics CRM connection's Url to service
	 */
	public function setServAdress($serv_adress) {
		$this->serv_adress = $serv_adress;
		return $this;
	}
	
	/**
	 * @return  Dynamics CRM connection's user
	 */	
	public function getUser() {
		return $this->user;
	}
	
	/**
	 * @param  string $user Dynamics CRM connection's User <p>
	 * (LDAP) Domain/user </p>
	 */
	public function setUser($user) {
		$this->user = $user;
		return $this;
	}
	
	/**
	* @return Dynamics CRM connection's password
	*/
	public function getPassword() {
		return $this->password;
	}
	
	/**
	 * @param string $password Dynamics CRM connection's password 
	 */
	public function setPassword($password) {
		$this->password = $password;
		return $this;
	}
	
	/**
	 * Retrieve ONE and ONLY ONE Line in CRM By ID
	 *
	 * @see DynamicsCrm::FormatFilter() For a better understanding of where clause
	 * @param string $Id  mostly a GUID from the id column (key)      	
	 * @param string $Table Table name
	 * @param array $Columns list of columns to be shown , false to see all <p>
	 * 				array ('col1','col2',...)
	 * @return Object CrmResponse
	 **        Bool $Error,\n
	 **        String $ErrorCode,\n
	 **        String $ErrorMessage,\n
	 **        $Result => false or std object with key parameter\n
	 *</p>
	 */
	function Retrieve($Table, $Id, $Columns) {
		$SoapEnvelope = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://schemas.microsoft.com/xrm/2011/Contracts/Services" xmlns:con="http://schemas.microsoft.com/xrm/2011/Contracts" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
  						 <soap:Body>
      						<ser:Retrieve>
         							<ser:entityName>' . $Table . '</ser:entityName>
         							<ser:id>' . $Id . '</ser:id>';
		$SoapEnvelope .= $this->FormatColumn ( $Columns );
		$SoapEnvelope .= '</ser:Retrieve>
						   </soap:Body>
						</soap:Envelope>';
		$Result = $this->call ( "Retrieve", $SoapEnvelope );
		Return $Result;
	}
	
	/**
	 * Retrieve Multiple Lines in CRM
	 *
	 * @see FormatFilter For a better understanding of where clause
	 * 
	 * @param array $Where    [detail of $Where attribute](@ref FormatFetchFilter) 
	  @param array $Columns [detail of $Columns attribute](@ref FormatFetchAttribute) 
	 * 
	 * @param array $Order will change order of results
	 *        	<p>
     *    array(\n
	 *     1=>('Column'=> 'Colname','Order'=>'Asc'),\n
	 *     2=>('Column'=> 'Colname2','Order'=>'Desc'),\n
	 *     ...);\n\n
	 *        	</p>
	 * @return object CrmResponse.
 	 **        Bool $Error,\n
	 **        String $ErrorCode,\n
	 **        String $ErrorMessage,\n
	 **        $Result => false or array of std object with key parameter,\n
	 *</p>
	 */
	public function RetrieveMultiple($Table, $Where, $Columns, $Join=false ,$Order = false) {
		$Aggregate=($this->array_key_exists_r('aggregate', $Columns))?'true':'false';
		$SoapEnvelope = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
				 			 <s:Body>
				    			<RetrieveMultiple xmlns="http://schemas.microsoft.com/xrm/2011/Contracts/Services" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
				      				<query i:type="a:FetchExpression" xmlns:a="http://schemas.microsoft.com/xrm/2011/Contracts">
				       					 <a:Query>
												&lt;fetch version=\'1.0\' output-format=\'xml-platform\' mapping=\'logical\' aggregate=\''.$Aggregate.'\' distinct=\'false\'&gt;';
		$SoapEnvelope .= $this->FormatFetchEntity ( $Table );
		$SoapEnvelope .= $this->FormatFetchAttribute ( $Columns );
		$SoapEnvelope .= $this->FormatFetchOrder ( $Order );
		$SoapEnvelope .= $this->formatFetchFilter ( $Where );
		if ($Join!==false) $SoapEnvelope .= $this->formatFetchJoin($Join);
		$SoapEnvelope .= '&lt;/entity&gt;
				                               &lt;/fetch&gt;
				</a:Query>
				   	                </query>
				   		        </RetrieveMultiple>
					  	      </s:Body>
						</s:Envelope>
						';
		$Result = $this->call ( "RetrieveMultiple", $SoapEnvelope );
		Return $Result;
	}
	
	/**
	 * Delete a row
	 *
	 * @param string $Table <p>
	 *        	the table where you want to delete a row
	 *        	</p>
	 * @param string $Id  mostly a GUID from the id column (key)     
	 *        	...
	 * @return  obj CrmResponse. <p>
	  **        Bool $Error,\n
	 **        String $ErrorCode,\n
	 **        String $ErrorMessage,\n
	 **        $Result => Guid or false depending if query worked or not,\n
	 *</p>
	 */
	public function Delete($Table, $Id) {
		$SoapEnvelope = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://schemas.microsoft.com/xrm/2011/Contracts/Services">
							<soap:Header/>
								<soap:Body>
									<ser:Delete>
         								<ser:entityName>' . $Table . '</ser:entityName>
         								<ser:id>' . $Id . '</ser:id>
									</ser:Delete>
								</soap:Body>
						</soap:Envelope>';
		$Result = $this->call ( "Delete", $SoapEnvelope );
		Return $Result;
	}
	
	/**
	 * Update a row in CRM
	 *
	 * @param string $Table <p>
	 *        	the table name where you want to insert a row
	 *        	</p>
	 * @param string $Id  mostly a GUID from the id column (key)     
	 * @link http://msdn.microsoft.com/en-us/library/microsoft.xrm.sdk.query.conditionoperator.aspx
	 *       too see the dif operator
	 *       </p>
	 * @param array $Params <p>
	 *        	'ColumnName' => Value
	 *        	</p>
	 * @return obj CrmResponse. <p>
	 **        Bool $Error,\n
	 **        String $ErrorCode,\n
	 **        String $ErrorMessage,\n
	 **        $Result => true or false...\n
	 *</p>
	 */
	function Update($Table, $Params, $Id) {
		$SoapEnvelope = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
							<soap:Body><Update xmlns="http://schemas.microsoft.com/xrm/2011/Contracts/Services">
            <entity xmlns:b="http://schemas.microsoft.com/xrm/2011/Contracts" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                    <b:Attributes xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic">
                       ' . $this->FormatAttribute ( $Params ) . '
                    </b:Attributes>
                    <b:EntityState i:nil="true"/>
                    <b:FormattedValues xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic"/>
                    <b:Id>' . $Id . '</b:Id>
                    <b:LogicalName>' . $Table . '</b:LogicalName>
                    <b:RelatedEntities xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic"/>
                </entity></Update>
            </soap:Body>
        </soap:Envelope>';
		return $this->call ( 'Update', $SoapEnvelope );
	}
	
	/**
	 * Create a new row
	 *
	 * @param string $Table <p>
	 *        	the table where you want to create a new row
	 *        	</p>
	 * @param array $Params <p>
	 * 
	 **   array(\n
	 *   *    1=>array( \n
	 *        *        'field'=>'fieldname',\n
	 *        *        'type'=>'Field type',\n
	 *        *        'value'=>'value'\n
	 *   *    ) ,\n
	 *   *    2=>array( \n
	 *        *        'field'=>'fieldname',\n
	 *        *        'type'=>'EntityReference',\n
	 *        *        'id'=>'guidforentity',\n
	 *        *        'name'=>'entityname(table)'\n
	 *   *    ) , \n       	
	 **)\n
	 * 
	 * </p>
	 * @return objet CrmResponse. <p>
	 **        Bool $Error,\n
	 **        String $ErrorCode,\n
	 **        String $ErrorMessage,\n
	 **        $Result => Guid or false depending if query worked or not,\n
	 *</p>
	 */
	public function Create($Table, $Params) {
		
		$SoapEnvelope = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
							<soap:Body>
				<Create xmlns="http://schemas.microsoft.com/xrm/2011/Contracts/Services">
            <entity xmlns:b="http://schemas.microsoft.com/xrm/2011/Contracts" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                    <b:Attributes xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic">
                       ' . $this->FormatAttribute ( $Params ) . '
                    </b:Attributes>
  						<b:EntityState i:nil="true"/>
                        <b:FormattedValues xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic"/>
                        <b:Id>00000000-0000-0000-0000-000000000000</b:Id>
                        <b:LogicalName>' . $Table . '</b:LogicalName>
                        <b:RelatedEntities xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic"/>
               			<b:RelatedEntities xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic"/>
                </entity></Create></soap:Body>
        </soap:Envelope>';
		return $this->call ( 'Create', $SoapEnvelope );
	}
	
	
	/*
	 * Not Working ATM to be checked
	 */
	public function getCurrentUserInfo() {
		$SoapEnvelope = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
								<soap:Body>
									<Execute xmlns=http://schemas.microsoft.com/crm/2007/WebServices>
         			<request i:type="b:WhoAmIRequest" xmlns:a="http://schemas.microsoft.com/xrm/2011/Contracts" xmlns:b="http://schemas.microsoft.com/crm/2011/Contracts">
                            <a:Parameters xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic" />
                            <a:RequestId i:nil="true" />
                            <a:RequestName>WhoAmI</a:RequestName>
                          </request>			
									</Execute>
								</soap:Body>
						</soap:Envelope>';
		
		return $this->call ( 'Execute', $SoapEnvelope );
	}
	
	/**
	 * Format Entity Header
	 *
	 * @param string $operation Name of the action to be done
	 * @param string $soapBody Soap body request according to the Action      	
	 * @return result parsed and in an CrmResponse Object according to action
	 */
	private function call($operation, $soapBody) {
		
		$headers = $this->generateSoapHeader ( $operation );
		
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $this->serv_adress );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $soapBody );
		curl_setopt ( $ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM );
		curl_setopt ( $ch, CURLOPT_USERPWD, $this->user . ':' . $this->password );
		
		$response = curl_exec ( $ch );
		$Return = new CrmResponse ();
		if (curl_exec ( $ch ) === false) {
			$Return->Error = True;
			$Return->ErrorCode = 0;
			$Return->ErrorMessage = curl_error ( $ch );
			$Return->Result = False;
		} else {
			
			$response = str_replace ( '<a:', '<', $response );
			$response = str_replace ( '<b:', '<', $response );
			$response = str_replace ( '<s:', '<', $response );
			$response = str_replace ( '</a:', '</', $response );
			$response = str_replace ( '</b:', '</', $response );
			$response = str_replace ( '</s:', '</', $response );
			$xml = simplexml_load_string ( $response );
			
			if (isset ( $xml->Body->Fault )) {
				$Return->Error = True;
				$Return->ErrorCode = ( string ) $xml->Body->Fault->faultcode;
				$Return->ErrorMessage = ( string ) $xml->Body->Fault->faultstring;
				$Return->Result = False;
			} else {
				switch ($operation) {
					case 'RetrieveMultiple' :
						$Return = $this->ParseRetrieveMultiple ( $xml, $Return );
						Break;
					case 'Retrieve' :
						$Return = $this->ParseRetrieve ( $xml, $Return );
						Break;
					case 'Create' :
						$Return->NbResult = 1;
						$Return->Result = ( string )  $xml->Body->CreateResponse->CreateResult;
						Break;
					case 'Update' :
						if(isset($xml->Body->UpdateResponse)){
							$Return->NbResult = 1;
							$Return->Result = true;
						}else{
							$Return->Error = True;
							$Return->ErrorCode = "Unknown Error";
							$Return->ErrorMessage = "Unknown Error";
						}
						Break;
					case 'Delete' :
						if(isset($xml->Body->DeleteResponse)){
							$Return->NbResult = 1;
							$Return->Result = true;
						}else{
							$Return->Error = True;
							$Return->ErrorCode = "Unknown Error";
							$Return->ErrorMessage = "Unknown Error";
						}
						Break;
				}
			}
		}
		
		return $Return;
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
	 * Format Fetch Attributes <p>
	 * (Column being resolved by RetrieveMultiple)
	 *
	 * @param array $Columns <p>
	 **    simple request\n
	 *   *    array(\n
	 *       *    'Colname1',\n
	 *       *    'Colname2'...\n
	 *   *    )\n\n
	 *        
	 **aggregate request \n
	 *   *    array(\n
	 *       *    '1'=>array(\n
	 *           *    'name'=>'colname',\n
	 *           *    'alias'=>	'alias',(false,notset => will keep colname as alias)\n
	 *           *    'aggregate' =>'aggregate function name' ,if aggregate function needed else false or unset (avg , max, min, sum, count => SQL : (COUNT(*)) ,countcolumn => SQL :  (COUNT(name)) ,countdistinct => SQL :  (COUNT(DISTINCT name))  )\n	
	 *       *    ),\n  
	 *       *    '2'=> array(\n
	 *           *    'name'=>'colname',\n
	 *           *    'alias'=>	'alias',(false,notset => will keep colname as alias)\n
	 *           *    'groupby' =>true or year,quarter,month,week,day for date grouping\n
	 *       *    )\n
	 *   *    )   \n\n  
	 * note that you cant mix simple column with agregates
	 * @return correct XML for Columns
	 */
	private function FormatFetchAttribute($Columns) {
		$Attributes = "";
		if (is_array ( $Columns )) {
			foreach ( $Columns as $column ) {
				//aggregate cases
				if (isset($column['name'])){
					if (empty($column['alias'])){
						$column['alias']=$column['name'];
					}
					//basic aggregate function avg , max, min, sum, count (COUNT(*)) ,countcolumn (COUNT(name)) ,countdistinct (COUNT(DISTINCT name)) 
					if (!empty($column['aggregate'])){
						if ($column['aggregate']=='countdistinct'){
							$Attributes .= "&lt;attribute name='".$column['name']."' alias='".$column['alias']."' aggregate='countcolumn' distinct='true' /&gt;";
						}else {
							$Attributes .= "&lt;attribute name='".$column['name']."' alias='".$column['alias']."' aggregate='".$column['aggregate']."' /&gt;";
						}
					//so is that a groupby?
					}else if (isset($column['groupby'])) {
						
					 if($column['groupby']!==true){
						$Attributes .= "&lt;attribute name='".$column['name']."' alias='".$column['alias']."' dategrouping='".$column['groupby']."' groupby='true' /&gt;";
						}else{
						$Attributes .= "&lt;attribute name='".$column['name']."' alias='".$column['alias']."' groupby='true' /&gt;";
							
						}
					 }
				//normal cases	
				}else $Attributes .= "&lt;attribute name='" . $column . "' /&gt;";
			}
		}
		return $Attributes;
	}
	
	/**
	 * Format Attributes for insert and updates<p>
	 *
	 * @param $Params Attributes parameter <p>
	 * 
	 **   array(\n
	 *   *    1=>array( \n
	 *        *        'field'=>'fieldname',\n
	 *        *        'type'=>'Field type',\n
	 *        *        'value'=>'value'\n
	 *   *    ) ,\n
	 *   *    2=>array( \n
	 *        *        'field'=>'fieldname',\n
	 *        *        'type'=>'EntityReference',\n
	 *        *        'id'=>'guidforentity',\n
	 *        *        'name'=>'entityname(table)'\n
	 *   *    ) , \n       	
	 **)\n
	 * 
	 * @return correct Attributes XML for insert/update cases
	 */
	private function FormatAttribute($Params) {
		$TxtAttribute = "";

		if (is_array ( $Params )) {
			
			foreach ( $Params as $Param ) {
				if (isset ( $Param ['field'] ) && isset ( $Param ['type'] )) {
					$TxtAttribute .= '<b:KeyValuePairOfstringanyType>
                            <c:key>' . $Param ['field'] . '</c:key>';
					switch ($Param ['type']) {
						case 'OptionSetValue' :
						case 'optionsetvalue' :
						case 'option' :
						case 'Option' :
							$TxtAttribute .= '<c:value i:type="b:OptionSetValue"><b:value>' . $Param ['value'] . '</b:value></c:value>';
							break;
						case 'Money' :
						case 'money' :
							$TxtAttribute .= '<c:value i:type="b:Money">><b:value>' . $Param ['value'] . '</b:value></c:value>';
							break;
						case 'Boolean' :
						case 'boolean' :
						case 'bool' :
						case 'Bool' :
							$TxtAttribute .= '<c:value i:type="d:boolean" xmlns:d="http://www.w3.org/2001/XMLSchema">' . $Param ['value'] . '</c:value>';
							break;
						case 'Integer' :
						case 'integer' :
						case 'Int' :
						case 'int' :
							$TxtAttribute .= '<c:value i:type="d:int" xmlns:d="http://www.w3.org/2001/XMLSchema">' . $Param ['value'] . '</c:value>';
							break;
						case 'DateTime' :
						case 'datetime' :
							$TxtAttribute .= '<c:value i:type="d:dateTime" xmlns:d="http://www.w3.org/2001/XMLSchema">' . $Param ['value'] . '</c:value>';
							break;
						
						case 'EntityReference' :
						case 'entityreference' :
						case 'Entity' :
						case 'entity' :
							$TxtAttribute .= '<c:value i:type="b:EntityReference">
												<b:id>' . $Param ["id"] . '</b:id>
												<b:logicalname>' . $Param ["name"] . '</b:logicalname>
												<b:name i:nil="true">
												</b:name></c:value>';
							break;
						case 'String' :
						case 'string' :
						default :
							$TxtAttribute .= '<c:value i:type="d:string" xmlns:d="http://www.w3.org/2001/XMLSchema">' . $Param ['value'] . '</c:value>';
							break;
					}
					$TxtAttribute .= '</b:KeyValuePairOfstringanyType>';
				}
			}
		}
		return $TxtAttribute;
	}
	
	/**
	 * Format Columns for REtrieveMultiple<p>
	 *
	 * @param array $Columns array with colnames or false for all col\n <p>
	 **    array(\n
	 *    *    'colname1',\n
	 *    *    'colname2'...\n
	 **    )\n
	 * notice it will always return GUID Except with agregate function</p>
	 * @return correct Columns XML for REtrieveMultiple
	 */
	private function FormatColumn($Columns) {
		$AllCol = ($Columns == false) ? "true" : "false";
		
		$TxtColumn = '<ser:columnSet> <con:AllColumns>' . $AllCol . '</con:AllColumns>';
		if ($Columns != false) {
			foreach ( $Columns as $Column ) {
				$TxtColumn .= '<arr:string>' . $Column . '</arr:string>';
			}
		}
		$TxtColumn .= '</ser:columnSet>';
		return $TxtColumn;
	}
	
	/**
	 * Format Order by for the request
	 *
	 * @param array $Order Clause order by
	 **    array(\n
	 *    *    1=>array(\n
	 *        *    'Column'=> 'ColumName',\n
	 *        *    'Order'=>'Asc'\n
	 *    *    ),\n
	 *    *    2=>array(\n
	 *        *    'Column'=> 'ColumName2',\n
	 *        *    'Order'=>'Desc'\n
	 *    *    )..\n
	 **    )\n
	 * @return correct XML for this Order by
	 */
	private function FormatFetchOrder($Orders) {
		$TxtOrder = "";
		if (is_array ( $Orders )) {
			foreach($Orders as $Order ){
			$TxtOrder .= "&lt;order attribute='" . $Order ['Column'] . "' descending='";
			$TxtOrder .= ($Order ['Order'] == 'Asc') ? "false" : "true";
			$TxtOrder .= "' /&gt;";
			}
			
		}
		return $TxtOrder;
	}
	
	/**
	 * Format Filters for request
	 * will return a Filter is Type is set
	 * else will retun a condition
	 *
	 * @param array $Filter <p>
	 *- basic and req  where condition1 and condition2\n\n
	 * 	array(\n
	 *    *		Condition1=>array\n
	 *    *		Condition2=>array\n
	 *			)\n
	 *- basic or req  where condition1 or condition2\n\n
	 * 			array(\n
	 *    * 		'Type'=>'or'\n
	 *    *			Condition1=>array\n
	 *    *			Condition2=>array\n
	 * 				)\n
	 *- multiple occurence and/or can be tricky\n
	 *  where ((condition1 and condition2) or ((condition5 and condition6)or condition4))\n\n
	 *  
	 **   array (\n
	 *   *    Type=>'or'\n
	 *   *    1=>array(\n
	 *        *        'Type'=>'and'\n
	 *        *        Condition1=>array\n
	 *        *       Condition2=>array\n
	 *        *       )\n
	 *   *    2=>array(\n
	 *        *        'Type'=>'or'\n
	 *        *         Condition3=>array(\n
	 *            *                       'Type'=>'and'\n
	 *            *                        Condition5=>array\n
	 *            *                        Condition6=>array\n
	 *            *                       )\n
	 *        *         Condition4=>array\n
	 *        *    	)\n
	 **    )\n
	 * [Check FormatCondition for more information about each operator](@ref FormatCondition) 
	 *        
	 * @return correct XML for thoose filters
	 */
	private function FormatFetchFilter($Filters) {
		$TxtFilter = "";
		if (is_array ( $Filters )) {
			if (isset ( $Filters ['Type'] )) {
				$TxtFilter .= "&lt;filter type='" . $Filters ['Type'] . "' &gt;";
			} else {
				$TxtFilter .= '&lt;filter &gt;';
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
			$TxtFilter .= '&lt;/filter &gt;';
		} else
			return "";
		
		return $TxtFilter;
	}
	
	
	
	/**
	 * Format Join for RetriveMultiple
	 *
	 * @param array $Joins <p>
	 **    array(   \n
	 *   *    1=>array(	\n
	 *        *    	'name' => (String) tablename\n
	 *        *    	'from' => join name On 'from'=to \n
	 *        *    	'to' => join name On from='to' \n
	 *        *    	'alias' =>not necessary can be false but aliases will be used when you try to get back ur data meaning $result->alias.data\n
	 *        *    	'type' => Operator\n
	 *        *    	'colums' => array [see FormatColumn for detail](@ref FormatColumn)  \n
	 *        *    	'where' => array [see FormatFetchFilter for detail](@ref FormatFetchFilter) \n
	 *        *    	'nested'=> (reproduce the same pattern and will nest a join false or unset if not required)\n
	 *   *    ),\n
	 *   *    2=>array('name'=>...)\n
	 **    )\n
	 *        if only one join is required u can go directly with no nested array 
	 *        
	 *        
	 *        	</p>
	 * @return correct XML for request this condition
	 */
	private function formatFetchJoin($Joins) {
		//so we dont have to make a special statement for only 1 join
		$TxtJoin="";
		if (isset($Joins['name'])){
			$Joins=array('1'=>$Joins);
		}
		foreach ($Joins as $Join){
			
			$TxtJoin.="&lt;link-entity name='".$Join['name']."' from='".$Join['from']."' to='".$Join['to']."' ";
			if (!empty($Join['alias']) ) $TxtJoin.=" alias='".$Join['alias']."' ";
			$TxtJoin.=" link-type='".$Join['type']."' &gt;";
			$TxtJoin.= $this->FormatFetchAttribute ( $Join['columns'] );
			if (!empty($Join['nested'])) $TxtJoin.=formatFetchJoin($Join);
			$TxtJoin .= $this->formatFetchFilter ( $Join['where'] );
			$TxtJoin.="&lt;/link-entity&gt;";
			
		}
		return $TxtJoin;
	}
	
	/**
	 * Format condition Depending on Operator
	 *
	 * @param array $Filter<p>
	 **array(\n
	 *    *'Column' => (String) $ColumnName\n
     *    *'Op' => Operator [link to all operator](http://msdynamicscrmblog.wordpress.com/2013/05/10/fetch-xml-and-conditionexpression-operators-using-c-in-dynamics-crm-2011/)\n   
	 *    *'Value' => $Value (if necessary depending on operator, data or could be an array)\n
	 *    *'Value2' => $Value2 (if necessary mostly in between case)\n
     **)
	 *        	</p>
	 * @return correct XML for request this condition
	 */
	private function FormatCondition($Filter) {
		switch ($Filter ['Op']) {
			/*
			 * The operators wich dont need a value
			 * so no need for value here
			 */
			case 'eq-userid' :
			case 'ne-userid' :				
			case 'eq-bysinessid' :
			case 'ne-bysinessid' :				
			case 'eq-userteams' :
			case 'last-seven-days' :
			case 'last-fiscal-period' :
			case 'last-fiscal-year' :
			case 'last-month' :
			case 'last-week' :
			case 'last-year' :
			case 'next-seven-days' :
			case 'next-fiscal-period' :
			case 'next-fiscal-year' :
			case 'next-month' :
			case 'next-week' :
			case 'next-year' :
			case 'null' :
			case 'this-fiscal-period' :
			case 'this-fiscal-year' :
			case 'this-month' :
			case 'this-week' :
			case 'this-year' :
			case 'today' :
			case 'not-null' :
			case 'tomorrow' :
			case 'yesterday' :
				$Condition = "&lt;condition attribute='" . $Filter ['Column'] . "' operator='" . $Filter ['Op'] . "' /&gt;";
				break;
			/*
			* The basic operators
			* Value to be set, nothing much
			*/
			case 'like' :
			case 'not-like' :
			case 'eq' :
			case 'ge' :
			case 'gt' :
			case 'le' :
			case 'lt' :
			case 'ne' :
			case 'on' :
			case 'on-or-after' :
			case 'on-or-before' :
			case 'in-fiscal-period' :
			case 'in-fiscal-year' :
			case 'last-x-days' :
			case 'last-x-fiscal-periods' :
			case 'last-x-fiscal-years' :
			case 'last-x-hours' :
			case 'last-x-months' :
			case 'last-x-weeks' :
			case 'last-x-years' :
			case 'next-x-days' :
			case 'next-x-fiscal-periods' :
			case 'next-x-fiscal-years' :
			case 'next-x-hours' :
			case 'next-x-months' :
			case 'next-x-weeks' :
			case 'next-x-years' :
			case 'olderthan-x-months' :
			default :
				$Condition = "&lt;condition attribute='" . $Filter ['Column'] . "' operator='" . $Filter ['Op'] . "' value='" . $Filter ['Value'] . "'  /&gt;";
				break;
			/*
			* The in operators
			* Value is now an array with data to be tested array('1','2',...)
			*/
			case 'not-in' :
			case 'in' :
				$Condition = "&lt;condition attribute='" . $Filter ['Column'] . "' operator='" . $Filter ['Op'] . "' &gt;";
				foreach ($Filter ['Value'] as $Value){
				$Condition .= '&lt;value&gt;' . $Value . '&lt;/value&gt;';
				}
				$Condition .= '&lt;/condition &gt;';
				break;
			/*
			 * The 2 values operator
			 * Value and Value2 in array
			 */	
			case 'between' :
			case 'not-between' :
			case 'in-fiscal-period-and-year' :
			case 'in-or-after-fiscal-period-and-year' :
			case 'in-or-before-fiscal-period-and-year' :
				$Condition = "&lt;condition attribute='" . $Filter ['Column'] . "' operator='" . $Filter ['Op'] . "' &gt;";
				$Condition .= '&lt;value&gt;' . $Filter ['Value'] . '&lt;/value&gt; &lt;value&gt;'.['Value2'].'&lt;/value&gt;';
				$Condition .= '&lt;/condition &gt;';
				break;
		}
		
		return $Condition;
	}
	
	/**
	 * Parse the result of a RetrieveMultiple Query
	 *
	 * @param $xml SimpleXml to be parsed
	 * @param  Object CrmResponse
	 * @return Object CrmResponse
	 */
	private function ParseRetrieveMultiple($xml, $Return) {
		$Datas = $xml->Body->RetrieveMultipleResponse->RetrieveMultipleResult->Entities->Entity; // ->retrievemultipleresponse;//->retrievemultipleresult->entities->entity;
		$Return = $this->ParseDatas ( $Datas, $Return );
		return $Return;
	}
	
	/**
	 * Parse the datas and update the object accordingly
	 *
	 * @param $xml SimpleXml to be parsed
	 * @param  Object CrmResponse
	 * @return Object CrmResponse
	 */
	private function ParseDatas($Datas, $Return) {
		$NbResults = 0;
		$Results = array ();
		foreach ( $Datas as $Data ) {
			$ResultLine = $this->ParseResultLine ( $Data );
			$Results [$NbResults] = $ResultLine;
			$NbResults ++;
		}
		$Return->NbResult = $NbResults;
		$Return->Result = $Results;
		return $Return;
	}
	
	/**
	 * Parse the result of a ResultLine
	 * (will be used for both Retrieve and RetrieveMultiple)
	 *
	 * @param $Data SimpleXml Entity to be parsed
	 * @return stdClass with correct Key/value for the resul line
	 */
	private function ParseResultLine($Data) {
		$ResultLine = new \stdClass ();
		foreach ( $Data->Attributes as $keypair ) {
			foreach ( $keypair->KeyValuePairOfstringanyType as $data ) {
				$key = $data->key;
				if(isset($data->value->Value))$value = ( string ) $data->value->Value;
				else $value = ( string ) $data->value;
				if (ctype_digit ( $value )) {
					$ResultLine->$key = ( int ) $value;
				} else if (is_float ( $value )) {
					$ResultLine->$key = ( float ) $value;
				} else if (is_bool ( $value )) {
					$ResultLine->$key = ( bool ) $value;
				} else
					$ResultLine->$key = $value;
			}
			
		}
		return $ResultLine;
	}
	
	/**
	 * Parse the result of a Retrieve Query
	 *
	 * @param $xml SimpleXml to be parsed
	 * @param  	Object CrmResponse
	 * @return Object CrmResponse
	 */
	private function ParseRetrieve($xml, $Return) {
		$Datas = $xml->Body->RetrieveResponse->RetrieveResult;
		$Return = $this->ParseDatas ( $Datas, $Return );
		$Return->Result=$Return->Result[0];
		return $Return;
	}
	
	/**
	 * Generate Header accordingly to the operation chosen
	 *
	 * @param string $operation Operation name (wdsl wise)
	 * @return array formated Header
	 */
	private function generateSoapHeader($operation) {
		$soap_action = 'http://schemas.microsoft.com/xrm/2011/Contracts/Services/IOrganizationService/' . $operation;
		
		$headers = array (
				'Method: POST',
				'Connection: Keep-Alive',
				'User-Agent: PHP-SOAP-CURL',
				'Content-Type: text/xml; charset=utf-8',
				'SOAPAction: "' . $soap_action . '"' 
		);
		return $headers;
	}
	
	
	/**
	 * Recursive array_key_exist
	 *
	 *@param $needle what you want to find
	 *@param $haystack where you want to find it
	 * @return boolean
	 */
	private function array_key_exists_r($needle, $haystack)
	{
		$result = array_key_exists($needle, $haystack);
		if ($result)
			return $result;
		foreach ($haystack as $v)
		{
			if (is_array($v) || is_object($v))
				$result = $this->array_key_exists_r($needle, $v);
			if ($result)
				return $result;
		}
		return $result;
	}
	

	
}