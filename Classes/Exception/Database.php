<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2014 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This class represents an exception that should be thrown when a database
 * error has occurred.
 *
 * The exception automatically will use an error message, the error message
 * from the DB and the last query.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Exception_Database extends Exception {
	/**
	 * The constructor.
	 */
	public function __construct() {
		$message = 'There was an error with the database query.' . LF .
			$GLOBALS['TYPO3_DB']->sql_error();

		if ($GLOBALS['TYPO3_DB']->store_lastBuiltQuery
			|| $GLOBALS['TYPO3_DB']->debugOutput
		) {
			$message .= LF . 'The last built query:' . LF .
				$GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;
		}

		parent::__construct($message);
	}
}