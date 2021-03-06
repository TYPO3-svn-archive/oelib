<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Niels Pardon <mail@niels-pardon.de>
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
 * This class represents a domain model for testing purposes.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_Fixtures_TestingChildModel extends Tx_Oelib_Model implements tx_oelib_Interface_Sortable {
	/**
	 * Sets the "title" data item for this model.
	 *
	 * @param string $value
	 *        the value to set, may be empty
	 *
	 * @return void
	 */
	public function setTitle($value) {
		$this->setAsString('title', $value);
	}

	/**
	 * Gets the "title" data item.
	 *
	 * @return string the value of the "title" data item, may be empty
	 */
	public function getTitle() {
		return $this->getAsString('title');
	}

	/**
	 * Gets the "parent" data item.
	 *
	 * @return Tx_Oelib_Tests_Unit_Fixtures_TestingModel the "parent" data item,
	 *                                              will be NULL if this model
	 *                                              has no parent
	 */
	public function getParent() {
		return $this->getAsModel('parent');
	}

	/**
	 * Gets the "tx_oelib_parent2" data item.
	 *
	 * @return Tx_Oelib_Tests_Unit_Fixtures_TestingModel the "tx_oelib_parent2" data
	 *                                              item, will be NULL if this
	 *                                              model has no parent2
	 */
	public function getParent2() {
		return $this->getAsModel('tx_oelib_parent2');
	}

	/**
	 * Sets the "parent" data item.
	 *
	 * @param Tx_Oelib_Tests_Unit_Fixtures_TestingModel $parent
	 *        the "parent" data item to set
	 *
	 * @return void
	 */
	public function setParent(Tx_Oelib_Tests_Unit_Fixtures_TestingModel $parent) {
		$this->set('parent', $parent);
	}

	/**
	 * Sets the "tx_oelib_parent2" data item.
	 *
	 * @param Tx_Oelib_Tests_Unit_Fixtures_TestingModel $parent
	 *        the "tx_oelib_parent2" data item to set
	 *
	 * @return void
	 */
	public function setParent2(Tx_Oelib_Tests_Unit_Fixtures_TestingModel $parent) {
		$this->set('tx_oelib_parent2', $parent);
	}

	/**
	 * Sets the dummy column to TRUE.
	 *
	 * @return void
	 */
	public function markAsDummyModel() {
		$this->set('is_dummy_record', TRUE);
	}

	/**
	 * Returns the sorting value for this object.
	 *
	 * This is the sorting as used in the back end.
	 *
	 * @return integer the sorting value of this object, will be >= 0
	 */
	public function getSorting() {
		return $this->getAsInteger('sorting');
	}

	/**
	 * Sets the sorting value for this object.
	 *
	 * This is the sorting as used in the back end.
	 *
	 * @param integer $sorting the sorting value of this object, must be >= 0
	 *
	 * @return void
	 */
	public function setSorting($sorting) {
		$this->setAsInteger('sorting', $sorting);
	}
}