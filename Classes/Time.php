<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2014 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This class provides time-related constants.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class tx_oelib_Time {
	/**
	 * the number of seconds per minute
	 *
	 * @var integer
	 */
	const SECONDS_PER_MINUTE = 60;

	/**
	 * the number of seconds per hour
	 *
	 * @var integer
	 */
	const SECONDS_PER_HOUR = 3600;

	/**
	 * the number of seconds per day
	 *
	 * @var integer
	 */
	const SECONDS_PER_DAY = 86400;

	/**
	 * the number of seconds per week
	 *
	 * @var integer
	 */
	const SECONDS_PER_WEEK = 604800;

	/**
	 * the number of seconds per year (only for non-leap years),
	 * use with caution
	 *
	 * @var integer
	 */
	const SECONDS_PER_YEAR = 220752000;
}