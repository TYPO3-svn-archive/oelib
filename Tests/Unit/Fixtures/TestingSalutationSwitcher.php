<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2014 Oliver Klee (typo3-coding@oliverklee.de)
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
 * This is mere a class used for unit tests. Don't use it for any other purpose.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class Tx_Oelib_TestingSalutationSwitcher extends Tx_Oelib_SalutationSwitcher {
	/**
	 * @var string
	 */
	public $scriptRelPath = 'Tests/Unit/Fixtures/TestingSalutationSwitcher.php';

	/**
	 * @var string
	 */
	public $extKey = 'oelib';

	/**
	 * The constructor.
	 *
	 * @param array $configuration
	 *        TS setup configuration, may be empty
	 */
	public function __construct(array $configuration) {
		// Calls the base class' constructor manually as this isn't done
		// automatically.
		parent::__construct();

		$this->conf = $configuration;

		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
	}

	/**
	 * Sets the current language for this plugin and loads the language files.
	 *
	 * @param string $language
	 *        two-letter lowercase language like "en" or "de" or "default"
	 *        (which is an alias for "en")
	 *
	 * @return void
	 */
	public function setLanguage($language) {
		if ($this->getLanguage() != $language) {
			// Make sure the language file are reloaded.
			$this->LOCAL_LANG_loaded = FALSE;
			$this->LLkey = $language;
		}

		$this->pi_loadLL();
	}

	/**
	 * Gets the current language.
	 *
	 * @return string the two-letter key of the current language like "en",
	 *                "de" or "default" (which is the only non-two-letter
	 *                code and an alias for "en"), will return an empty
	 *                string if no language key has been set yet
	 */
	public function getLanguage() {
		return $this->LLkey;
	}

	/**
	 * Sets the salutation mode.
	 *
	 * @param string $salutation
	 *        the salutation mode to use ("formal" or "informal")
	 *
	 * @return void
	 */
	public function setSalutationMode($salutation) {
		$this->conf['salutation'] = $salutation;
	}

	/**
	 * Gets the salutation mode.
	 *
	 * @return string the current salutation mode to use: "formal", "informal"
	 *                or an empty string
	 */
	public function getSalutationMode() {
		return $this->conf['salutation'];
	}
}