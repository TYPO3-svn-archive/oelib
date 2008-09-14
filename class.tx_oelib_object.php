<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Class 'tx_oelib_object' for the 'oelib' extension.
 *
 * This class represents an object that allows getting and setting its data.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class tx_oelib_object {
	/**
	 * Gets the value of the data item for the key $key.
	 *
	 * @param	string		the key of the data item to get, must not be empty
	 *
	 * @return	mixed		the data for the key $key, will be an empty string
	 * 						if the key has not been set yet
	 */
	abstract protected function get($key);

	/**
	 * Sets the value of the data item for the key $key.
	 *
	 * @param	string		the key of the data item to get, must not be empty
	 * @param	mixed		the data for the key $key
	 */
	abstract protected function set($key, $value);

	/**
	 * Checks that $key is not empty.
	 *
	 * @throws	Exception	if $key is empty
	 *
	 * @param	string		a key to check
	 */
	public function checkForNonEmptyKey($key) {
		if ($key == '') {
			throw new Exception('$key must not be empty.');
		}
	}

	/**
	 * Gets the value stored in under the key $key, converted to a string.
	 *
	 * @param	string		the key of the element to retrieve, must not be
	 * 						empty
	 *
	 * @return	string		the string value of the given key, may be empty
	 */
	public function getAsString($key) {
		$this->checkForNonEmptyKey($key);

		return trim((string) $this->get($key));
	}

	/**
	 * Sets a value for the key $key (and converts it to a string).
	 *
	 * @param	string		the key of the element to set, must not be empty
	 * @param	mixed		the value to set, may be empty
	 */
	public function setAsString($key, $value) {
		$this->checkForNonEmptyKey($key);

		$this->set($key, (string) $value);
	}

	/**
	 * Gets the value stored in under the key $key, converted to an integer.
	 *
	 * @param	string		the key of the element to retrieve, must not be
	 * 						empty
	 *
	 * @return	integer		the integer value of the given key, may be positive,
	 * 						negative or zero
	 */
	public function getAsInteger($key) {
		$this->checkForNonEmptyKey($key);

		return intval($this->get($key));
	}

	/**
	 * Sets a value for the key $key (and converts it to an integer).
	 *
	 * @param	string		the key of the element to set, must not be empty
	 * @param	mixed		the value to set, may be empty
	 */
	public function setAsInteger($key, $value) {
		$this->checkForNonEmptyKey($key);

		$this->set($key, intval($value));
	}

	/**
	 * Gets the value stored in under the key $key, converted to an array of
	 * trimmed strings.
	 *
	 * @param	string		the key of the element to retrieve, must not be
	 * 						empty
	 *
	 * @return	array		the array value of the given key, may be empty
	 */
	public function getAsTrimmedArray($key) {
		$stringValue = $this->getAsString($key);

		if ($stringValue == '') {
			return array();
		}

		return t3lib_div::trimExplode(',', $stringValue);
	}

	/**
	 * Gets the value stored under the key $key, converted to an array of
	 * integers.
	 *
	 * @param	string		the key of the element to retrieve, must not be
	 * 						empty
	 *
	 * @return	array		the array value of the given key, may be empty
	 */
	public function getAsIntegerArray($key) {
		$stringValue = $this->getAsString($key);

		if ($stringValue == '') {
			return array();
		}

		return t3lib_div::intExplode(',', $stringValue);
	}

	/**
	 * Sets an array value for the key $key.
	 *
	 * Note: This function is intended for data that does not contain any
	 * commas. Commas in the array elements cause getAsTrimmedArray and
	 * getAsIntegerArray to split that element at the comma. This is a known
	 * limitation.
	 *
	 * @param	string		the key of the element to set, must not be empty
	 * @param	array		the value to set, may be empty
	 *
	 * @see	getAsTrimmedArray
	 * @see	getAsIntegerArray
	 */
	public function setAsArray($key, array $value) {
		$this->setAsString($key, implode(',', $value));
	}

	/**
	 * Gets the value stored in under the key $key, converted to a boolean.
	 *
	 * @param	string		the key of the element to retrieve, must not be
	 * 						empty
	 *
	 * @return	boolean		the boolean value of the given key
	 */
	public function getAsBoolean($key) {
		$this->checkForNonEmptyKey($key);

		return (boolean) $this->get($key);
	}

	/**
	 * Sets a value for the key $key (and converts it to a boolean).
	 *
	 * @param	string		the key of the element to set, must not be empty
	 * @param	mixed		the value to set, may be empty
	 */
	public function setAsBoolean($key, $value) {
		$this->checkForNonEmptyKey($key);

		$this->set($key, (boolean) $value);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_object.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_object.php']);
}
?>