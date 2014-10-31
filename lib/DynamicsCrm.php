<?php
use Symfony\Component\HttpFoundation\HeaderBag;
/*
 * ! \mainpage DynamicsCrm
 *
 * \section Installation
 * if by any mean you dont get the bundle from packagist add to you composer.json under require\\n
 *
 * "dynamicscrm/connector" : "dev-master"
 *
 * to you composer.json\n
 * and then : \n
 *
 * composer install
 *
 * or
 *
 * composer update
 *
 * enjoy the bundle\n\n
 *
 * Example:\n\n
 *
 * $DynamicsCrm=new DynamicsCrm($serv_adress, $user, $password);
 * $result=$DynamicsCrm->Retrieve($Table, $Id, $Columns);
 *
 */
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

/*
 * ! \brief Connector and basic operation from PHP to CRM using NTLM and curl.
 *
 * This connector load from a simple login/password/url (/web) \n
 * it has the update/create/retrieve functionnality\n
 * AND RetrieveMultiple based on FetchXML wich allow to make some powerfull request\n
 * without the need to make many code\n
 * For this part better check the documentation to understand the nesting of parameters.\n
 *
 * @todo make a Execute function to run anything with parameter.
 */
class DynamicsCrm {
	// @formatter:off
	var $serv_adress;
	var $user;
	var $password;
	private $StateCheck=array(
			'account'=>array('0'=>array('1'),'1'=>array('2')),
			'activitypointer'=>array('0'=>array('1'),'1'=>array('2'),'2'=>array('3'),'3'=>array('4')),
			'appointment'=>array('0'=>array('1','2','3','4'),'1'=>array('1','2','3','4'),'2'=>array('1','2','3','4'),'3'=>array('1','2','3','4')),
			'kbarticle'=>array('1'=>array('1'),'2'=>array('2'),'2'=>array('3')),
			'campaign'=>array('0'=>array('1','2','3','4','5')),
			'campaignactivity'=>array('0'=>array('0','0','4','5','6'),'1'=>array('2'),'2'=>array('3')),
			'campaignresponse'=>array('0'=>array('1'),'1'=>array('2'),'2'=>array('3')),
			'incident'=>array('0'=>array('1','2','3','4'),'1'=>array('5'),'2'=>array('6')),
			'incidentresolution'=>array('0'=>array('1'),'1'=>array('2'),'2'=>array('3')),
			'notcustomizable'=>array('0'=>array('1'),'1'=>array('2'),'2'=>array('3')),
			'contact'=>array('0'=>array('1'),'1'=>array('2')),
			'contract'=>array('0'=>array('1'),'1'=>array('2'),'2'=>array('3'),'3'=>array('4'),'4'=>array('5'),'5'=>array('6')),
			'contractdetail'=>array('0'=>array('1'),'1'=>array('2'),'2'=>array('3'),'3'=>array('4')),
			'transactioncurrency'=>array('0'=>array('0'),'1'=>array('1')),
			'discounttype'=>array('0'=>array('100001'),'1'=>array('100002')),
			'email'=>array('0'=>array('1','8'),'1'=>array('2','3','4','6','7'),'2'=>array('5')),
			'fax'=>array('0'=>array('1'),'1'=>array('2','3','4'),'2'=>array('5')),
			'invoice'=>array('0'=>array('1','2','4','5','6'),'1'=>array('3','7'),'2'=>array('100001','100002'),'2'=>array('100003')),
			'lead'=>array('0'=>array('1','2'),'1'=>array('3'),'2'=>array('4','5','6','7')),
			'letter'=>array('0'=>array('1','2'),'1'=>array('3','4'),'2'=>array('5')),
			'list'=>array('0'=>array('0'),'1'=>array('1')),
			'opportunity'=>array('0'=>array('1','2'),'1'=>array('3'),'2'=>array('4','5')),
			'salesorder'=>array('0'=>array('1','2'),'1'=>array('3'),'2'=>array('4'),'3'=>array('100001','100002'),'4'=>array('100003')),
			'phonecall'=>array('0'=>array('1'),'1'=>array('2','4'),'2'=>array('3')),
			'pricelevel'=>array('0'=>array('100001'),'1'=>array('100002')),
			'product'=>array('0'=>array('1'),'1'=>array('2')),
			'quote'=>array('0'=>array('1'),'1'=>array('2','2'),'2'=>array('4','5'),'3'=>array('5','6','7')),
			'serviceappointment'=>array('0'=>array('1','2'),'1'=>array('8'),'2'=>array('9','10'),'3'=>array('3','4','6','7')),
			'task'=>array('0'=>array('2','3','4','7'),'1'=>array('5'),'2'=>array('6'))
	);
	private $Aggregate = array ('avg','max','min','sum','count','countcolumn','countdistinct');
	private $Operator= array(
			'0'=>array('eq-userid','ne-userid','eq-bysinessid','ne-bysinessid',
					'eq-userteams','last-seven-days','last-fiscal-period','last-fiscal-year','last-month',
					'last-week','last-year','next-seven-days','next-fiscal-period','next-fiscal-year','next-month','next-week',
					'next-year','null','this-fiscal-period','this-fiscal-year','this-month','this-week','this-year','today','not-null','tomorrow','yesterday') ,
			'1'=>array('like','not-like','eq','ge','gt','le','lt','ne','on','on-or-after','on-or-before',
					'in-fiscal-period','in-fiscal-year','last-x-days','last-x-fiscal-periods','last-x-fiscal-years','last-x-hours',
					'last-x-months','last-x-weeks','last-x-years','next-x-days','next-x-fiscal-periods','next-x-fiscal-years',
					'next-x-hours','next-x-months','next-x-weeks','next-x-years','olderthan-x-months') ,
			'2'=> array('not-in','in'),
			'3'=> array('between','not-between','in-fiscal-period-and-year','in-or-after-fiscal-period-and-year','in-or-before-fiscal-period-and-year')
	);

	/**
	 * Constructor
	 *
	 * @param string $serv_adress
	 *        	Url to service
	 * @param string $user
	 *        	Dynamics CRM connection's User <p>
	 *        	(LDAP) Domain/user </p>
	 * @param string $password
	 *        	Dynamics CRM connection's password
	 */
	function __construct($serv_adress, $user, $password) {
		$this->setServAdress ( $serv_adress );
		$this->setUser ( $user );
		$this->setPassword ( $password );
	}
	
	/**
	 *
	 * @return Dynamics CRM connection's Url to service
	 */
	public function getServAdress() {
		return $this->serv_adress;
	}
	
	/**
	 *
	 * @param string $serv_adress
	 *        	Dynamics CRM connection's Url to service
	 */
	public function setServAdress($serv_adress) {
		$this->serv_adress = $serv_adress;
		return $this;
	}
	
	/**
	 *
	 * @return Dynamics CRM connection's user
	 */
	public function getUser() {
		return $this->user;
	}
	
	/**
	 *
	 * @param string $user
	 *        	Dynamics CRM connection's User <p>
	 *        	(LDAP) Domain/user </p>
	 */
	public function setUser($user) {
		$this->user = $user;
		return $this;
	}
	
	/**
	 *
	 * @return Dynamics CRM connection's password
	 */
	public function getPassword() {
		return $this->password;
	}
	
	/**
	 *
	 * @param string $password
	 *        	Dynamics CRM connection's password
	 */
	public function setPassword($password) {
		$this->password = $password;
		return $this;
	}
	
	/**
	 * Retrieve ONE and ONLY ONE Line in CRM By ID
	 *
	 * @see DynamicsCrm::FormatFilter() For a better understanding of where clause
	 * @param string $Id
	 *        	mostly a GUID from the id column (key)
	 * @param string $Table
	 *        	Table name
	 * @param array $Columns
	 *        	list of columns to be shown , false to see all <p>
	 *        	array ('col1','col2',...)
	 * @return Object CrmResponse
	 *         * Bool $Error,\n
	 *         * String $ErrorCode,\n
	 *         * String $ErrorMessage,\n
	 *         * $Result => false or std object with key parameter\n
	 *         </p>
	 */
	function Retrieve($Table, $Id, $Columns) {
		$this->TestGuid( $Id);
				
		// build Saop Enveloppe
		$SoapEnvelope = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://schemas.microsoft.com/xrm/2011/Contracts/Services" xmlns:con="http://schemas.microsoft.com/xrm/2011/Contracts" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
  						 <soap:Body>
      						<ser:Retrieve>
         							<ser:entityName>' . $Table . '</ser:entityName>
         							<ser:id>' . $Id . '</ser:id>';
		$SoapEnvelope .= $this->FormatColumn ( $Columns );
		$SoapEnvelope .= '</ser:Retrieve>
						   </soap:Body>
						</soap:Envelope>';
		
		// call
		Return $this->call ( 'Retrieve', $SoapEnvelope );
	}
	
	/**
	 * Retrieve Multiple Lines in CRM
	 *
	 * @see FormatFilter For a better understanding of where clause
	 *     
	 * @param array $Where
	 *        	[detail of $Where attribute](@ref FormatFetchFilter)
	 * @param array $Columns
	 *        	[detail of $Columns attribute](@ref FormatFetchAttribute)
	 *        	
	 * @param array $Order
	 *        	will change order of results
	 *        	<p>
	 *        	array(\n
	 *        	1=>('Column'=> 'Colname','Order'=>'Asc'),\n
	 *        	2=>('Column'=> 'Colname2','Order'=>'Desc'),\n
	 *        	...);\n\n
	 *        	</p>
	 * @return object CrmResponse.
	 *         * Bool $Error,\n
	 *         * String $ErrorCode,\n
	 *         * String $ErrorMessage,\n
	 *         * $Result => false or array of std object with key parameter,\n
	 *         </p>
	 */
	public function RetrieveMultiple($Table, $Where, $Columns=false, $Join = false, $Order = false) {
		// check if there is some agregate inside parameter
		if (is_array ( $Columns )) {
			if ($this->array_key_exists_r ( 'aggregate', $Columns ) || $this->array_key_exists_r ( 'groupby', $Columns )) {
				$Aggregate = 'true';
			} else
				$Aggregate = 'false';
		} else
			$Aggregate = 'false';
			// build Soap Enveloppe
		try {
				$Entity = $this->FormatFetchEntity ( $Table );
				$Attributes = $this->FormatFetchAttribute ( $Columns );
				$Order = $this->FormatFetchOrder ( $Order );
				$Filter = $this->formatFetchFilter ( $Where );
				if ($Join !== false){
					$Join = $this->FormatFetchJoin ( $Join );
				}else $Join='';
			} catch ( Exception $e ) {
				return $this->GetErrorObject ( $e->getMessage () );
			}
		
		$SoapEnvelope = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
				 			 <s:Body>
				    			<RetrieveMultiple xmlns="http://schemas.microsoft.com/xrm/2011/Contracts/Services" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
				      				<query i:type="a:FetchExpression" xmlns:a="http://schemas.microsoft.com/xrm/2011/Contracts">
				       					 <a:Query>
												&lt;fetch version=\'1.0\' output-format=\'xml-platform\' mapping=\'logical\' aggregate=\'' . $Aggregate . '\' distinct=\'false\'&gt;';
		$SoapEnvelope.=$Entity.$Attributes.$Order.$Filter.$Join;
	
		// add footer
		$SoapEnvelope .= '&lt;/entity&gt;
				                               &lt;/fetch&gt;
				</a:Query>
				   	                </query>
				   		        </RetrieveMultiple>
					  	      </s:Body>
						</s:Envelope>
						';
		
		// call
		Return $this->call ( 'RetrieveMultiple', $SoapEnvelope );
	}
	
	/**
	 * Delete a row
	 *
	 * @param string $Table
	 *        	<p>
	 *        	the table where you want to delete a row
	 *        	</p>
	 * @param string $Id
	 *        	mostly a GUID from the id column (key)
	 *        	...
	 * @return obj CrmResponse. <p>
	 *         * Bool $Error,\n
	 *         * String $ErrorCode,\n
	 *         * String $ErrorMessage,\n
	 *         * $Result => Guid or false depending if query worked or not,\n
	 *         </p>
	 */
	public function Delete($Table, $Id) {
		$this->TestGuid( $Id);
		// build soap Enveloppe
		$SoapEnvelope = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://schemas.microsoft.com/xrm/2011/Contracts/Services">
							<soap:Header/>
								<soap:Body>
									<ser:Delete>
         								<ser:entityName>' . $Table . '</ser:entityName>
         								<ser:id>' . $Id . '</ser:id>
									</ser:Delete>
								</soap:Body>
						</soap:Envelope>';
		// call
		Return $this->call ( 'Delete', $SoapEnvelope );
	}
	
	/**
	 * Update a row in CRM
	 *
	 * @param string $Table
	 *        	<p>
	 *        	the table name where you want to insert a row
	 *        	</p>
	 * @param string $Id
	 *        	mostly a GUID from the id column (key)
	 * @link http://msdn.microsoft.com/en-us/library/microsoft.xrm.sdk.query.conditionoperator.aspx
	 *       too see the dif operator
	 *       </p>
	 * @param array $Params
	 *        	<p>
	 *        	'ColumnName' => Value
	 *        	</p>
	 * @return obj CrmResponse. <p>
	 *         * Bool $Error,\n
	 *         * String $ErrorCode,\n
	 *         * String $ErrorMessage,\n
	 *         * $Result => true or false...\n
	 *         </p>
	 */
	function Update($Table, $Params, $Id) {
		// check the array parameter first
		try {
			$Param = $this->FormatAttributes ( $Params );
		} catch ( Exception $e ) {
			return $this->GetErrorObject ( $e->getMessage () );
		}
		$this->TestGuid( $Id);
		// build soap Enveloppe
		$SoapEnvelope = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
							<soap:Body><Update xmlns="http://schemas.microsoft.com/xrm/2011/Contracts/Services">
            <entity xmlns:b="http://schemas.microsoft.com/xrm/2011/Contracts" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                    <b:Attributes xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic">
                       ' . $Param . '
                    </b:Attributes>
                    <b:EntityState i:nil="true"/>
                    <b:FormattedValues xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic"/>
                    <b:Id>' . $Id . '</b:Id>
                    <b:LogicalName>' . $Table . '</b:LogicalName>
                    <b:RelatedEntities xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic"/>
                </entity></Update>
            </soap:Body>
        </soap:Envelope>';
		// call
		return $this->call ( 'Update', $SoapEnvelope );
	}
	
	/**
	 * Create a new row
	 *
	 * @param string $Table
	 *        	<p>
	 *        	the table where you want to create a new row
	 *        	</p>
	 * @param array $Params
	 *        	<p>
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
	 *        	* ) , \n
	 *        	*)\n
	 *        	
	 *        	</p>
	 * @return objet CrmResponse. <p>
	 *         * Bool $Error,\n
	 *         * String $ErrorCode,\n
	 *         * String $ErrorMessage,\n
	 *         * $Result => Guid or false depending if query worked or not,\n
	 *         </p>
	 */
	public function Create($Table, $Params) {
		try {
			$Param = $this->FormatAttributes ( $Params );
		} catch ( Exception $e ) {
			return $this->GetErrorObject ( $e->getMessage () );
		}
		$SoapEnvelope = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
							<soap:Body>
				<Create xmlns="http://schemas.microsoft.com/xrm/2011/Contracts/Services">
            <entity xmlns:b="http://schemas.microsoft.com/xrm/2011/Contracts" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                    <b:Attributes xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic">
                       ' . $Param . '
                    </b:Attributes>
  						<b:EntityState i:nil="true"/>
                        <b:FormattedValues />
                        <b:Id>00000000-0000-0000-0000-000000000000</b:Id>
                        <b:LogicalName>' . $Table . '</b:LogicalName>
                        <b:RelatedEntities />
                </entity></Create></soap:Body>
        </soap:Envelope>';
		return $this->call ( 'Create', $SoapEnvelope );
	}
	
	
	/**
	 * Set state for a newly created object (can't be done in create)
	 * 
	 * @param string $Id	<p>
	 *        	the GUID of the entity you want to update
	 *        	</p>
	 * @param string $Table 	<p>
	 *        	the table where you want to create a new row
	 *        	</p>
	 * @param string $StateCode 	State Code depending on Table
	 * @param string $StatusCode 	Status Code depending on State Code
	 * @return result|CrmResponse
	 */
	public function SetState($Id, $Table, $StateCode, $StatusCode) {
		if (isset ( $this->StateCheck [$Table] [$StateCode] ) && in_array ( $StatusCode, $this->StateCheck [$Table] [$StateCode] )) {
			$this->TestGuid( $Id);
			$Request = '<request i:type="b:SetStateRequest" xmlns:a="http://schemas.microsoft.com/xrm/2011/Contracts" xmlns:b="http://schemas.microsoft.com/crm/2011/Contracts">
       <a:Parameters xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic">
         <a:KeyValuePairOfstringanyType>
           <c:key>EntityMoniker</c:key>
           <c:value i:type="a:EntityReference">
             <a:Id>' . $Id . '</a:Id>
             <a:LogicalName>' . $Table . '</a:LogicalName>
             <a:Name i:nil="true" />
           </c:value>
         </a:KeyValuePairOfstringanyType>
       <a:KeyValuePairOfstringanyType>
           <c:key>State</c:key>
           <c:value i:type="a:OptionSetValue">
             <a:Value>' . $StateCode . '</a:Value>
           </c:value>
         </a:KeyValuePairOfstringanyType>
		<a:KeyValuePairOfstringanyType>
           <c:key>Status</c:key>
           <c:value i:type="a:OptionSetValue">
             <a:Value>' . $StatusCode . '</a:Value>
           </c:value>
         </a:KeyValuePairOfstringanyType></a:Parameters>
       <a:RequestId i:nil="true" />
       <a:RequestName>SetState</a:RequestName>
     </request>';
			return $this->ExecuteBody ( $Request );
		} else {
			$strPair = print_r ( $this->StateCheck [$Table], true );
			return $this->GetErrorObject ( 'Pair State and status not avaible for ' . $Table . ' should be in  State => array( avaible status) :  ' . $strPair );
		}
	}
	
	
	/**
	 * This is more of a test function return EVERY SINGLE ENTITY OF CRM
	 * never ever run that in production...
	 * @return result
	 */
	public function GetAllEntities() {
		$Request = '<request i:type="a:RetrieveAllEntitiesRequest" xmlns:a="http://schemas.microsoft.com/xrm/2011/Contracts">
                   <a:Parameters xmlns:b="http://schemas.datacontract.org/2004/07/System.Collections.Generic">
                     <a:KeyValuePairOfstringanyType>
                       <b:key>EntityFilters</b:key>
                       <b:value i:type="c:EntityFilters" xmlns:c="http://schemas.microsoft.com/xrm/2011/Metadata">Privileges</b:value>
                     </a:KeyValuePairOfstringanyType>
                     <a:KeyValuePairOfstringanyType>
                       <b:key>RetrieveAsIfPublished</b:key>
                       <b:value i:type="c:boolean" xmlns:c="http://www.w3.org/2001/XMLSchema">true</b:value>
                     </a:KeyValuePairOfstringanyType>
                   </a:Parameters>
                   <a:RequestId i:nil="true" />
                   <a:RequestName>RetrieveAllEntities</a:RequestName>
                 </request>
              ';
		$Return = $this->ExecuteBody ( $Request );
		return $Return;
	}
	
	/**
	 * Will run a workflow accordingly to the parameters
	 * 
	 * @param string $EntityId <p>
	 *        	the GUID of the entity you want to run
	 *        	</p>
	 * @param string $WorkflowId <p>
	 *        	the GUID of the Workflow you want to run 
	 *        	</p>
	 * @return result
	 */
	public function Workflow($EntityId, $WorkflowId) {
		$this->TestGuid( $EntityId);
		$this->TestGuid( $WorkflowId);
		$Request = '<request i:type="b:ExecuteWorkflowRequest" xmlns:a="http://schemas.microsoft.com/xrm/2011/Contracts" xmlns:b="http://schemas.microsoft.com/crm/2011/Contracts">
					<a:Parameters xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic">
					<a:KeyValuePairOfstringanyType>
					<c:key>EntityId</c:key>
	<c:value i:type="d:guid" xmlns:d="http://schemas.microsoft.com/2003/10/Serialization/">$EntityId</c:value>
	</a:KeyValuePairOfstringanyType>
	<a:KeyValuePairOfstringanyType>
	<c:key>WorkflowId</c:key>
	<c:value i:type="d:guid" xmlns:d="http://schemas.microsoft.com/2003/10/Serialization/">$WorkflowId</c:value>
	</a:KeyValuePairOfstringanyType>
	</a:Parameters>
	<a:RequestId i:nil="true" />
	<a:RequestName>ExecuteWorkflow</a:RequestName>
	</request>';
		$Return = $this->ExecuteBody ( $Request );
		$Return = $this->ParseDatas ( $Return->Result->Body->ExecuteResponse->ExecuteResult->Results, $Return );
		return $this->ExecuteBody ( $Request );
	}
	
	/**
	 * @todo
	 * @return result
	 */
	public function Associate() {
		$SoapEnvelope = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
							<soap:Body>
			<Associate xmlns="http://schemas.microsoft.com/xrm/2011/Contracts/Services">
			<subType>
			</subType>
			<objectType>
			10069</objectType>
			<parentObjectType>10095</parentObjectType>
			<objectId>568B31F9-8AEE-E211-8830-005056B74E4F</objectId>
			<parentId>A5DF8846-3BF0-E211-8830-005056B74E4F</parentId>
			<associationName>classification_occupation</associationName>
			</Associate></soap:Body>
        </soap:Envelope>';
		return $this->call ( 'Associate', $SoapEnvelope );
	}
	
	
	/**
	 * Will assign a new owner to an Crm object
	 * @param string $Table <p>
	 *        	the Table of the entity you want to update
	 *        	</p>
	 * @param string $Id <p>
	 *        	the GUID of the entity you want to update
	 *        	</p>
	 * @param string $IdAssign 
	 * 			<p>
	 *        	the GUID that will be used as new owner
	 *        	</p>
	 * @param string $TypeAssign <p>
	 *        	Not required set by default as systemuser,<br>
	 *        someday could be of some use to set anything else
	 *        	</p>
	 */
	public function Assign($Table,$Id,$IdAssign,$TypeAssign='systemuser'){
		$this->TestGuid( $Id);
		$this->TestGuid( $IdAssign);
		$Request = '<request i:type="b:AssignRequest" xmlns:a="http://schemas.microsoft.com/xrm/2011/Contracts" xmlns:b="http://schemas.microsoft.com/crm/2011/Contracts">
				<a:Parameters xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic">
					<a:KeyValuePairOfstringanyType>
						<c:key>Target</c:key>
						<c:value i:type="a:EntityReference">
							<a:Id>'.$Id.'</a:Id>
							<a:LogicalName>'.$Table.'</a:LogicalName>
							<a:Name i:nil="true" />
						</c:value>
					</a:KeyValuePairOfstringanyType>
					<a:KeyValuePairOfstringanyType>
						<c:key>Assignee</c:key>
						<c:value i:type="a:EntityReference">
							<a:Id>'.$IdAssign.'</a:Id>
							<a:LogicalName>'.$TypeAssign.'</a:LogicalName>
							<a:Name i:nil="true" />
						</c:value>
					</a:KeyValuePairOfstringanyType>
				</a:Parameters>
				<a:RequestId i:nil="true" />
				<a:RequestName>Assign</a:RequestName>
			</request>';
		
		$Response= $this->ExecuteBody ( $Request );
		if(isset( $Response->Error) && $Response->Error===true){
			return $Response;
		}else{
			$Return = new CrmResponse ();
			$Return->Error = false;
			$Return->ErrorCode = false;
			$Return->ErrorMessage = '';
			$Return->NbResult =1 ;
			$Return->Result=true;
		return  $Return;
		}
	}

	/**
	 * As It sounds will return the Logged in CRM user (login pwd wise)
	 * @return object
	 */
	public function GetCurrentUserInfo() {
		$Request = '<request i:type="b:WhoAmIRequest" xmlns:a="http://schemas.microsoft.com/xrm/2011/Contracts" xmlns:b="http://schemas.microsoft.com/crm/2011/Contracts">
                            <a:Parameters xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic" />
                            <a:RequestId i:nil="true" />
                            <a:RequestName>WhoAmI</a:RequestName>
                          </request>';
		
		$Return = $this->ExecuteBody ( $Request );
		$Return = $this->ParseDatas ( $Return->Result->Body->ExecuteResponse->ExecuteResult->Results, $Return );
		return $Return;
	}
	
	/**
	 * Format Entity Header
	 *
	 * @param string $Request
	 *        	Request Body for Execute
	 * @return result parsed and in an CrmResponse Object according to action
	 */
	private function ExecuteBody($Request) {
		$SoapEnvelope = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
				 			 <s:Body>
				   				 <Execute xmlns="http://schemas.microsoft.com/xrm/2011/Contracts/Services" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
             			 			' . $Request . '
              					</Execute>
							</s:Body>
						</s:Envelope>';
		return $this->call ( 'Execute', $SoapEnvelope );
	}
	
	/**
	 * Format Entity Header
	 *
	 * @param string $operation
	 *        	Name of the action to be done
	 * @param string $soapBody
	 *        	Soap body request according to the Action
	 * @return result parsed and in an CrmResponse Object according to action
	 */
	private function call($operation, $soapBody) {
		$headers = $this->GenerateSoapHeader ( $operation );
		
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $this->serv_adress );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt ( $ch, CURLOPT_UNRESTRICTED_AUTH,true);
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION,true);
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $soapBody );
		curl_setopt ( $ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM );
		curl_setopt ( $ch, CURLOPT_USERPWD, $this->user . ':' . $this->password );
		$response = curl_exec ( $ch );

		
		
		$Return = new CrmResponse ();
		if ($response === false) {
			$Return->Error = True;
			$Return->ErrorCode = 0;
			$Return->ErrorMessage = curl_error ( $ch );
			$Return->Result = False;
		} else {
			
			$response = str_replace ( '<a:', '<', $response );
			$response = str_replace ( '<b:', '<', $response );
			$response = str_replace ( '<c:', '<', $response );
			$response = str_replace ( '<s:', '<', $response );
			$response = str_replace ( '</a:', '</', $response );
			$response = str_replace ( '</b:', '</', $response );
			$response = str_replace ( '</c:', '</', $response );
			$response = str_replace ( '</s:', '</', $response );
			$xml = simplexml_load_string ( $response );
						if (isset ( $xml->Body->Fault )) {
				$Return->Error = True;
				$Return->ErrorCode = ( string ) $xml->Body->Fault->faultcode;
				
				$Return->ErrorMessage = (string) $xml->Body->Fault->faultstring;
				if (isset($xml->Body->Fault->detail->OrganizationServiceFault)){
				$Return->ErrorMessage .='<br>Détail : '.$this->getErrorMessage($xml->Body->Fault->detail->OrganizationServiceFault);
				};
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
						$Return->Result = ( string ) $xml->Body->CreateResponse->CreateResult;
						Break;
					case 'Update' :
						if (isset ( $xml->Body->UpdateResponse )) {
							$Return->NbResult = 1;
							$Return->Result = true;
						} else {
							$Return->Error = True;
							$Return->ErrorCode = 'Unknown Error';
							$Return->ErrorMessage = 'Unknown Error';
						}
						Break;
					case 'Execute' :
						$Return->NbResult = 1;
						$Return->Result = $xml;
						break;
					case 'Associate' :
						die ( print_r ( $xml ) );
						break;
					case 'Delete' :
						if (isset ( $xml->Body->DeleteResponse )) {
							$Return->NbResult = 1;
							$Return->Result = true;
						} else {
							$Return->Error = True;
							$Return->ErrorCode = 'Unknown Error';
							$Return->ErrorMessage = 'Unknown Error';
						}
						Break;
				}
			}
		}
		
		return $Return;
	}
	
	private function getErrorMessage($Xml){
		if (isset($Xml->InnerFault->Message) && isset($Xml->InnerFault->InnerFault) ) return $this-> getErrorMessage($Xml->InnerFault);
		else{
			if (isset($Xml->Message)) return (string) $Xml->Message;
		}
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
	
	private function TestGuid($Guid,$Param=false){
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
	 * @param unknown $message
	 * @return CrmResponse
	 */
	private function GetErrorObject($message) {
		$Return = new CrmResponse ();
		$Return->Error = True;
		$Return->ErrorCode = 0;
		$Return->ErrorMessage = $message;
		$Return->Result = False;
		return $Return;
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
	
	/**
	 * Parse the result of a RetrieveMultiple Query
	 *
	 * @param $xml SimpleXml
	 *        	to be parsed
	 * @param
	 *        	Object CrmResponse
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
	 * @param $xml SimpleXml
	 *        	to be parsed
	 * @param
	 *        	Object CrmResponse
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
	 * @param $Data SimpleXml
	 *        	Entity to be parsed
	 * @return stdClass with correct Key/value for the resul line
	 */
	private function ParseResultLine($Data) {
		$ResultLine = new \stdClass ();
		if (isset ( $Data->Attributes )) {
			foreach ( $Data->Attributes as $keypair ) {
				foreach ( $keypair->KeyValuePairOfstringanyType as $data ) {
					$key = str_ireplace ( '.', '_', $data->key );
					$ResultLine->$key = $this->ParseKeyPair ( $data );
				}
			}
		} else {
			
			foreach ( $Data as $data ) {
				if (isset ( $data->key )) {
					$key = str_ireplace ( '.', '_', $data->key );
					$ResultLine->$key = $this->ParseKeyPair ( $data );
				}
			}
		}
		return $ResultLine;
	}
	
	/**
	 * @param unknown $data
	 * @return number|boolean|Ambigous <string, multitype:string , multitype:stdClass >|Ambigous <multitype:string , string, multitype:stdClass >
	 */
	private function ParseKeyPair($data) {
		// liason simple
		if (isset ( $data->value->Value )) {
			$value = ( string ) $data->value->Value;
			// entité logique
		} else if (isset ( $data->value->LogicalName )) {
			$value = array (
					'id' => ( string ) $data->value->Id,
					'logicalname' => ( string ) $data->value->LogicalName,
					'name' => ( string ) $data->value->Name 
			);
		} else if (isset ( $data->value->Entities )) {
			$Entities = array ();
			foreach ( $data->value->Entities as $entity ) {
				if (isset ( $entity->Entity->Attributes )) {
					$Entities [] = $this->ParseResultLine ( $entity->Entity );
				}
			}
			
			$value = $Entities;
		} else
			$value = ( string ) $data->value;
		
		if (! is_array ( $value )) {
			if (ctype_digit ( $value )) {
				return ( int ) $value;
			} else if (is_float ( $value )) {
				return ( float ) $value;
			} else if (is_bool ( $value )) {
				return ( bool ) $value;
			} else
				return $value;
		} else
			return $value;
	}
	
	/**
	 * Parse the result of a Retrieve Query
	 *
	 * @param $xml SimpleXml
	 *        	to be parsed
	 * @param
	 *        	Object CrmResponse
	 * @return Object CrmResponse
	 */
	private function ParseRetrieve($xml, $Return) {
		$Datas = $xml->Body->RetrieveResponse->RetrieveResult;
		$Return = $this->ParseDatas ( $Datas, $Return );
		$Return->Result = $Return->Result [0];
		return $Return;
	}
	
	/**
	 * Generate Header accordingly to the operation chosen
	 *
	 * @param string $operation
	 *        	Operation name (wdsl wise)
	 * @return array formated Header
	 */
	private function GenerateSoapHeader($operation) {
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
	 * @param $needle what
	 *        	you want to find
	 * @param $haystack where
	 *        	you want to find it
	 * @return boolean
	 */
	private function array_key_exists_r($needle, $haystack) {
		$result = array_key_exists ( $needle, $haystack );
		if ($result)
			return $result;
		foreach ( $haystack as $v ) {
			if (is_array ( $v ) || is_object ( $v ))
				$result = $this->array_key_exists_r ( $needle, $v );
			if ($result)
				return $result;
		}
		return $result;
	}
}