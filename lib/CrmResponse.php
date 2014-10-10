<?php
namespace dynamicscrm\connector\lib;
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

/*! \brief Result object for DynamicCRM
 *
 *Any request Update evrything will be stocked there as a result
 *Error (bool),ErrorCode,ErrorMessage
 *Result(s)
 *NbResult
 */
class CrmResponse {
	
	public $Error  ;
	public $ErrorCode ;
	public $ErrorMessage ;
	public $Result;
	public $NbResult;
	
	/**
	 * Basic Constructor
	 *
	 */
	public function __construct(){
		$this->Error= false;
		$this->ErrorCode= false;
		$this->ErrorMessage= "";
		$this->NbResult= 0;
		$this->Result = false;
		
	}
}