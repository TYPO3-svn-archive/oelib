<?php
/***************************************************************
* Copyright notice
*
* (c) 2011-2013 Oliver Klee <typo3-coding@oliverklee.de>
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * This interface represents something that has an identity, i.e., a UID.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface tx_oelib_Interface_Identity {
	/**
	 * Gets this object's UID.
	 *
	 * @return integer
	 *         this object's UID, will be zero if this object does not have a UID yet
	 */
	public function getUid();

	/**
	 * Checks whether this object has a UID.
	 *
	 * @return boolean TRUE if this object has a non-zero UID, FALSE otherwise
	 */
	public function hasUid();

	/**
	 * Sets this object's UID.
	 *
	 * This function may only be called on objects that do not have a UID yet.
	 *
	 * @param integer $uid the UID to set, must be > 0
	 *
	 * @return void
	 */
	public function setUid($uid);
}