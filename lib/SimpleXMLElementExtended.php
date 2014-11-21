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
 * ! \brief Extend SimpleXMLELEMT
 *
 * Add support for CDATA
 */
   Class SimpleXMLElementExtended extends SimpleXMLElement {

  /**
   * Adds a child with $value inside CDATA
   * @param unknown $name
   * @param unknown $value
   */
  public function addChildWithCDATA($name, $value = NULL) {
    $new_child = $this->addChild($name);

    if ($new_child !== NULL) {
      $node = dom_import_simplexml($new_child);
      $no   = $node->ownerDocument;
      $node->appendChild($no->createCDATASection($value));
    }

    return $new_child;
  }
}
?>