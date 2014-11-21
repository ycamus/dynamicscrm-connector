<?php
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
 */

/*
 * ! \brief Result object for DynamicCRM
 *
 * Any request Update evrything will be stocked there as a result
 * Error (bool),ErrorCode,ErrorMessage
 * Result(s)
 * NbResult
 */
class CrmResponse {
	public $Error;
	public $ErrorCode;
	public $ErrorMessage;
	public $Result;
	public $NbResult;
	public $MoreRecords;
	
	/**
	 * Basic Constructor
	 */
	public function __construct() {
		$this->Error = false;
		$this->ErrorCode = false;
		$this->ErrorMessage = "";
		$this->NbResult = 0;
		$this->Result = false;
		$this->MoreRecords = false;
	}
	public function toXml() {
		$XmlResponse = new SimpleXMLElementExtended ( "<Response></Response>" );
		if ($this->Error) {
			$Error = $XmlResponse->addChild ( 'Error' );
			$Error->addChild ( 'Code', $this->ErrorCode );
			$Error->addChildWithCDATA ( 'Message', utf8_encode ( $this->ErrorMessage ) );
		} else {
			if (is_array ( $this->Result )) {
			$XmlResponse->addChild ( 'Nbresult', $this->NbResult );
			$Results = $XmlResponse->addChild ( 'Entities' );
			
				foreach ( $this->Result as $result ) {
					$Row = $Results->addChild ( 'Entity' );
					$Row = $this->RowToXml ( $Row, $result );
				}
			}else{
					$Results = $XmlResponse->addChild ( 'Entity' );
					$Results = $this->ColToXml ( $Results, array('Id'=>$this->Result) );
				}
		}
		return  $XmlResponse->asXML () ;
	}
	
	private function RowToXml($Row, $result) {
		if (! is_array ( $result ))
			$Reference = $Row;
		else {
			if (! isset ( $result ['logicalname'] ) || isset ( $result->activitypartyid )) {
				$Reference = $Row;
				foreach ( $result as $line ) {
					
					if (isset ( $line->activitypartyid )) {
						$entity = $Reference->addChild ( 'Activityparty' );
						$entity = $this->ColToXml ( $entity, $line );
					}
				}
			} else {
				$Reference = $Row->addChild ( $result ['logicalname'] );
			}
		}
		$this->ColToXml ( $Reference, $result );
		
		return $Row;
	}
	private function ColToXml($Reference, $result) {
		foreach ( $result as $key => $value ) {
			if ($key != "logicalname") {
				if (! is_array ( $value ) && ! is_object ( $value )) {
					if ($key == 'activitypartyid')
						$key = 'id';
					if (is_numeric ( $value ) || $this->isGuid ( $value ) || $value == "true" || $value == "false" || $value == "")
						$Reference->addChild ( $key, $value );
					else
						$Reference->addChildWithCDATA ( $key, $value );
				} else {
					if ($key != 1) {
						$entity = $Reference->addChild ( $key );
						$entity = $this->RowToXml ( $entity, $value );
					}
				}
			}
		}
		return $Reference;
	}
	private function isGuid($Guid) {
		return preg_match ( "/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i", $Guid );
	}
}