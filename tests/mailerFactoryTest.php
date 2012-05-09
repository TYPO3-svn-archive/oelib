<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2011 Saskia Metzler <saskia@merlin.owl.de> All rights reserved
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');
if (!class_exists('mail_mime', FALSE)) {
	require_once(t3lib_extMgm::extPath('oelib') . 'contrib/PEAR/Mail/mime.php');
}

/**
 * Testcase for the tx_oelib_mailerFactory class and the tx_oelib_emailCollector
 * class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_mailerFactoryTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_emailCollector
	 */
	private $fixture;

	private static $email = array(
		'recipient' => 'any-recipient@email-address.org',
		'subject' => 'any subject',
		'message' => 'any message',
		'headers' => ''
	);
	private static $otherEmail = array(
		'recipient' => 'any-other-recipient@email-address.org',
		'subject' => 'any other subject',
		'message' => 'any other message',
		'headers' => ''
	);

	protected function setUp() {
		// Only the instance with an enabled test mode can be tested as in the
		// non-test mode e-mails are sent.
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		$this->fixture = tx_oelib_mailerFactory::getInstance()->getMailer();

		$this->addHeadersToTestEmail();
	}

	protected function tearDown() {
		tx_oelib_mailerFactory::purgeInstance();
		unset($this->fixture);
	}


	/////////////////////
	// Utility functions
	/////////////////////

	/**
	 * Adds the headers to the static test e-mail as LF cannot be used when it
	 * is defined.
	 */
	private function addHeadersToTestEmail() {
		self::$email['headers'] = 'From: any-sender@email-address.org' . LF .
			'CC: "another recipient" <another-recipient@email-address.org>' . LF .
			'Reply-To: any-sender@email-address.org';
	}

	/**
	 * Gets the current character set in TYPO3, e.g., "utf-8".
	 *
	 * @return string the current character set, will not be empty
	 */
	private function getCharacterSet() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4007000) {
			return 'utf-8';
		}

		return ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] != '') ?
			$GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] : 'ISO-8859-1';
	}


	/////////////////////////////////////////////
	// Tests concerning the basic functionality
	/////////////////////////////////////////////

	public function testGetMailerInTestMode() {
		$this->assertEquals(
			'tx_oelib_emailCollector',
			get_class($this->fixture)
		);
	}

	public function testGetMailerInNonTestMode() {
		// initially, the test mode is disabled
		tx_oelib_mailerFactory::purgeInstance();

		$this->assertEquals(
			'tx_oelib_realMailer',
			get_class(tx_oelib_mailerFactory::getInstance()->getMailer())
		);
	}

	public function testGetMailerReturnsTheSameObjectWhenTheInstanceWasNotDiscarded() {
		$this->assertSame(
			$this->fixture,
			tx_oelib_mailerFactory::getInstance()->getMailer()
		);
	}

	public function testGetMailerAfterPurgeInstanceReturnsNewObject() {
		tx_oelib_mailerFactory::purgeInstance();

		$this->assertNotSame(
			$this->fixture,
			tx_oelib_mailerFactory::getInstance()->getMailer()
		);
	}

	public function testStoreAnEmailAndGetIt() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message'],
			self::$email['headers']
		);

		$this->assertEquals(
			self::$email,
			$this->fixture->getLastEmail()
		);
	}

	public function testStoreTwoEmailsAndGetTheLastEmail() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
		);
		$this->fixture->sendEmail(
			self::$otherEmail['recipient'],
			self::$otherEmail['subject'],
			self::$otherEmail['message']
		);

		$this->assertEquals(
			self::$otherEmail,
			$this->fixture->getLastEmail()
		);
	}

	public function testStoreNoEmailAndTryToGetTheLastEmail() {
		$this->assertEquals(
			array(),
			$this->fixture->getLastEmail()
		);
	}

	public function testStoreTwoEmailsAndGetBothEmails() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message'],
			self::$email['headers']
		);
		$this->fixture->sendEmail(
			self::$otherEmail['recipient'],
			self::$otherEmail['subject'],
			self::$otherEmail['message']
		);

		$this->assertEquals(
			array(
				self::$email,
				self::$otherEmail
			),
			$this->fixture->getAllEmail()
		);
	}

	public function testSendEmailReturnsTrueIfTheReturnValueIsSetToTrue() {
		$this->fixture->setFakedReturnValue(TRUE);

		$this->assertTrue(
			$this->fixture->sendEmail('', '', '')
		);
	}

	public function testSendEmailReturnsFalseIfTheReturnValueIsSetToFalse() {
		$this->fixture->setFakedReturnValue(FALSE);

		$this->assertFalse(
			$this->fixture->sendEmail('', '', '')
		);
	}

	public function testGetLastRecipientReturnsTheRecipientOfTheLastEmail() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
		);

		$this->assertEquals(
			self::$email['recipient'],
			$this->fixture->getLastRecipient()
		);
	}

	public function testGetLastRecipientReturnsAnEmptyStringIfThereWasNoEmail() {
		$this->assertEquals(
			'',
			$this->fixture->getLastRecipient()
		);
	}

	public function testGetLastSubjectReturnsTheSubjectOfTheLastEmail() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
		);

		$this->assertEquals(
			self::$email['subject'],
			$this->fixture->getLastSubject()
		);
	}

	public function testGetLastSubjectReturnsAnEmptyStringIfThereWasNoEmail() {
		$this->assertEquals(
			'',
			$this->fixture->getLastSubject()
		);
	}

	public function testGetLastBodyReturnsTheBodyOfTheLastEmail() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
		);

		$this->assertEquals(
			self::$email['message'],
			$this->fixture->getLastBody()
		);
	}

	public function testGetLastBodyReturnsAnEmptyStringIfThereWasNoEmail() {
		$this->assertEquals(
			'',
			$this->fixture->getLastBody()
		);
	}

	public function testGetLastHeadersIfTheEmailDoesNotHaveAny() {
		$this->fixture->sendEmail(
			self::$otherEmail['recipient'],
			self::$otherEmail['subject'],
			self::$otherEmail['message']
		);

		$this->assertEquals(
			'',
			$this->fixture->getLastHeaders()
		);
	}

	public function testGetLastHeadersReturnsTheLastHeaders() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message'],
			self::$email['headers']
		);

		$this->assertEquals(
			self::$email['headers'],
			$this->fixture->getLastHeaders()
		);
	}

	/**
	 * @test
	 */
	public function sendWithAnEMailAndGetIt() {
		$sender = new tx_oelib_tests_fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new tx_oelib_tests_fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(self::$email['message']);

		$this->fixture->send($eMail);

		$characterSet = $this->getCharacterSet();
		$buildParameter = array(
			'text_encoding' => 'quoted-printable',
			'head_charset' => $characterSet,
			'text_charset' => $characterSet,
			'html_charset' => $characterSet,
		);

		$mimeEMail = new Mail_mime(array('eol' => LF));
		$mimeEMail->setFrom($sender->getEmailAddress());
		$mimeEMail->setTXTBody(self::$email['message']);

		$this->assertEquals(
			array(
				'recipient' => self::$email['recipient'],
				'subject' => self::$email['subject'],
				'message' => $mimeEMail->get($buildParameter),
				'headers' => $mimeEMail->txtHeaders(),
			),
			$this->fixture->getLastEmail()
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
	}

	/**
	 * @test
	 */
	public function sendingPlainTextMailRemovesAnyCarriageReturnFromBody() {
		$sender = new tx_oelib_tests_fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new tx_oelib_tests_fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(
			'one long line ...........................................' . CRLF .
			'now a blank line:' . LF . LF .
			'another long line .........................................' . LF .
			'and a line with umlauts: Hörbär saß früh.'
		);

		$this->fixture->send($eMail);

		$this->assertNotContains(
			CR,
			$this->fixture->getLastBody()
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
	}

	/**
	 * @test
	 */
	public function sendWithTwoEMailsAndGetTheLastEMail() {
		$sender = new tx_oelib_tests_fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new tx_oelib_tests_fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(self::$email['message']);

		$otherRecipient = new tx_oelib_tests_fixtures_TestingMailRole(
			'', self::$otherEmail['recipient']
		);

		$otherEMail = new tx_oelib_Mail();
		$otherEMail->setSender($sender);
		$otherEMail->addRecipient($otherRecipient);
		$otherEMail->setSubject(self::$otherEmail['subject']);
		$otherEMail->setMessage(self::$otherEmail['message']);

		$this->fixture->send($eMail);
		$this->fixture->send($otherEMail);

		$characterSet = $this->getCharacterSet();
		$buildParameter = array(
			'text_encoding' => 'quoted-printable',
			'head_charset' => $characterSet,
			'text_charset' => $characterSet,
			'html_charset' => $characterSet,
		);

		$mimeEMail = new Mail_mime(array('eol' => LF));
		$mimeEMail->setFrom($sender->getEmailAddress());
		$mimeEMail->setTXTBody(self::$otherEmail['message']);

		$this->assertEquals(
			array(
				'recipient' => self::$otherEmail['recipient'],
				'subject' => self::$otherEmail['subject'],
				'message' => $mimeEMail->get($buildParameter),
				'headers' => $mimeEMail->txtHeaders(),
			),
			$this->fixture->getLastEmail()
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
		$otherRecipient->__destruct();
		$otherEMail->__destruct();
	}

	/**
	 * @test
	 */
	public function sendWithTwoEMailsAndGetBothEMails() {
		$sender = new tx_oelib_tests_fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new tx_oelib_tests_fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(self::$email['message']);

		$otherRecipient =new tx_oelib_tests_fixtures_TestingMailRole(
			'', self::$otherEmail['recipient']
		);

		$otherEMail = new tx_oelib_Mail();
		$otherEMail->setSender($sender);
		$otherEMail->addRecipient($otherRecipient);
		$otherEMail->setSubject(self::$otherEmail['subject']);
		$otherEMail->setMessage(self::$otherEmail['message']);

		$this->fixture->send($eMail);
		$this->fixture->send($otherEMail);

		$characterSet = $this->getCharacterSet();
		$buildParameter = array(
			'text_encoding' => 'quoted-printable',
			'head_charset' => $characterSet,
			'text_charset' => $characterSet,
			'html_charset' => $characterSet,
		);

		$mimeEMail = new Mail_mime(array('eol' => LF));
		$mimeEMail->setFrom($sender->getEmailAddress());
		$mimeEMail->setTXTBody(self::$email['message']);

		$otherMimeEMail = new Mail_mime(array('eol' => LF));
		$otherMimeEMail->setFrom($sender->getEmailAddress());
		$otherMimeEMail->setTXTBody(self::$otherEmail['message']);

		$this->assertEquals(
			array(
				array(
					'recipient' => self::$email['recipient'],
					'subject' => self::$email['subject'],
					'message' => $mimeEMail->get($buildParameter),
					'headers' => $mimeEMail->txtHeaders(),
				),
				array(
					'recipient' => self::$otherEmail['recipient'],
					'subject' => self::$otherEmail['subject'],
					'message' => $otherMimeEMail->get($buildParameter),
					'headers' => $otherMimeEMail->txtHeaders(),
				),
			),
			$this->fixture->getAllEmail()
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
		$otherRecipient->__destruct();
		$otherEMail->__destruct();
	}

	/**
	 * @test
	 */
	public function sendWithoutSenderThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$email must have a sender set.'
		);

		$eMail = new tx_oelib_Mail();

		$this->fixture->send($eMail);
	}

	/**
	 * @test
	 */
	public function sendWithHTMLMessage() {
		$htmlMessage = '<h1>Very cool HTML message</h1>' . LF .
			'<p>Great to have HTML e-mails in oelib.</p>';

		$sender = new tx_oelib_tests_fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new tx_oelib_tests_fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setHTMLMessage($htmlMessage);

		$this->fixture->send($eMail);

		$characterSet = $this->getCharacterSet();
		$this->assertEquals(
			array(
				'recipient' => self::$email['recipient'],
				'subject' => self::$email['subject'],
				'message' => $htmlMessage,
				'headers' => 'MIME-Version: 1.0' . LF .
					'Content-Type: text/html;' . LF . ' charset=' . $characterSet . LF .
					'Content-Transfer-Encoding: quoted-printable' . LF .
					'From: any-sender@email-address.org' . LF,
			),
			$this->fixture->getLastEmail()
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
	}

	/**
	 * @test
	 */
	public function mailWithEmptySenderThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$emailAddress must not be empty.'
		);

		$this->fixture->mail('', 'subject', 'message');
	}

	/**
	 * @test
	 */
	public function mailWithEmptySubjectThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$subject must not be empty.'
		);

		$this->fixture->mail('john@doe.com', '', 'message');
	}

	/**
	 * @test
	 */
	public function mailWithEmptyMessageThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$message must not be empty.'
		);

		$this->fixture->mail('john@doe.com', 'subject', '');
	}

	public function test_sendForSubjectWithAsciiCharactersOnly_DoesNotEncodeIt() {
		$sender = new tx_oelib_tests_fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new tx_oelib_tests_fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(self::$email['message']);

		$this->fixture->send($eMail);

		$this->assertEquals(
			self::$email['subject'],
			$this->fixture->getLastSubject()
		);
	}

	public function test_sendForSubjectWithNonAsciiCharacters_EncodesItWithUtf8CharsetInformation() {
		if ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] == '') {
			$this->markTestSkipped(
				'This test applies to installations with forceCharset only.'
			);
		}

		$sender = new tx_oelib_tests_fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new tx_oelib_tests_fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('föö');
		$eMail->setMessage(self::$email['message']);

		$this->fixture->send($eMail);

		$this->assertContains(
			'utf-8',
			$this->fixture->getLastSubject()
		);
	}

	public function test_sendForSubjectWithNonAsciiCharacters_EncodesItWithIso88591CharsetInformation() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4007000) {
			$this->markTestSkipped('This test is only applicable in TYPO3 < 4.7.');
		}
		if ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] != '') {
			$this->markTestSkipped('This test applies to installations without forceCharset only.');
		}

		$sender = new tx_oelib_tests_fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new tx_oelib_tests_fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('föö');
		$eMail->setMessage(self::$email['message']);

		$this->fixture->send($eMail);

		$this->assertContains(
			'ISO-8859-1',
			$this->fixture->getLastSubject()
		);
	}


	/////////////////////////////////////////////////
	// Tests concerning formatting the e-mail body.
	/////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function oneLineFeedIsKeptIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo' . LF . 'bar');

		$this->assertEquals(
			'foo' . LF . 'bar',
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function oneCarriageReturnIsReplacedByLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo' . CR . 'bar');

		$this->assertEquals(
			'foo' . LF . 'bar',
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function twoLineFeedsAreKeptIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo' . LF . LF . 'bar');

		$this->assertEquals(
			'foo' . LF . LF . 'bar',
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function twoCarriageReturnsAreReplacedByTwoLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo' . CR . CR . 'bar');

		$this->assertEquals(
			'foo' . LF . LF . 'bar',
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function severalLineFeedsAreReplacedByTwoLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo' . LF . LF . LF . LF . LF . 'bar');

		$this->assertEquals(
			'foo' . LF . LF . 'bar',
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function severalCarriageReturnsAreReplacedByTwoLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo' . CR . CR . CR . CR . CR . 'bar');

		$this->assertEquals(
			'foo' . LF . LF . 'bar',
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function emailBodyIsNotChangesWhenFormattingIsDisabled() {
		$this->fixture->sendFormattedEmails(FALSE);
		$this->fixture->sendEmail('', '', 'foo' . CR . CR . CR . CR . CR . 'bar');

		$this->assertEquals(
			'foo' . CR . CR . CR . CR . CR . 'bar',
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function oneCrLfPairIsReplacedByLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo' . CRLF . 'bar');

		$this->assertEquals(
			'foo' . LF . 'bar',
			$this->fixture->getLastBody()
		);
	}


	/////////////////////////////////
	// Tests concerning the headers
	/////////////////////////////////

	/**
	 * @test
	 */
	public function fromWithoutUmlautsInTheirNameStaysUnencoded() {
		$sender = new tx_oelib_tests_fixtures_TestingMailRole(
			'John Doe', 'any-sender@email-address.org'
		);

		$recipient = new tx_oelib_tests_fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('Hello world');
		$eMail->setMessage('Summertime...');

		$this->fixture->send($eMail);

		$rawMail = $this->fixture->getLastEmail();
		$this->assertContains(
			'From: "John Doe" <any-sender@email-address.org>',
			$rawMail['headers']
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
	}

	/**
	 * @test
	 */
	public function fromWithUmlautsInTheirNameGetsEncoded() {
		$sender = new tx_oelib_tests_fixtures_TestingMailRole(
			'Jöhn Döe', 'any-sender@email-address.org'
		);

		$recipient = new tx_oelib_tests_fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('Hello world');
		$eMail->setMessage('Summertime...');

		$this->fixture->send($eMail);

		$characterSet = $this->getCharacterSet();
		$encodedName = t3lib_div::encodeHeader(
			'Jöhn Döe', 'quoted-printable', $characterSet
		);

		$rawMail = $this->fixture->getLastEmail();
		$this->assertContains(
			'From: "' . $encodedName . '" <any-sender@email-address.org>',
			$rawMail['headers']
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
	}

	/**
	 * @test
	 */
	public function longFromWithoutUmlautsInTheirNameStaysUnchanged() {
		$senderName = 'Pfefferminzia Lakritzia Kunigunde Canneloria ' .
			'Coffeinia Hydrogenoxidia Pizzeria Ambrosia Antagonia Antipasti';
		$sender = new tx_oelib_tests_fixtures_TestingMailRole(
			$senderName, 'any-sender@email-address.org'
		);

		$recipient = new tx_oelib_tests_fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('Hello world');
		$eMail->setMessage('Summertime...');

		$this->fixture->send($eMail);

		$rawMail = $this->fixture->getLastEmail();
		$this->assertContains(
			'From: "' . $senderName . '" <any-sender@email-address.org>',
			$rawMail['headers']
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
	}

	/**
	 * @test
	 */
	public function longFromWithUmlautsInTheirNameIsEncodedButStaysOtherwiseUnchanged() {
		$senderName = 'Pfefferminzia Lakritzia Kunigunde Canneloria Bäria ' .
			'Coffeinia Hydrogenoxidia Pizzeria Ambrosia Antagonia Antipasti';
		$sender = new tx_oelib_tests_fixtures_TestingMailRole(
			$senderName, 'any-sender@email-address.org'
		);

		$recipient = new tx_oelib_tests_fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('Hello world');
		$eMail->setMessage('Summertime...');

		$this->fixture->send($eMail);

		$characterSet = $this->getCharacterSet();
		$encodedName = t3lib_div::encodeHeader(
			$senderName, 'quoted-printable', $characterSet
		);
		$this->assertNotContains(
			LF,
			$encodedName
		);

		$rawMail = $this->fixture->getLastEmail();
		$this->assertContains(
			'From: "' . $encodedName . '" <any-sender@email-address.org>',
			$rawMail['headers']
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
	}


	///////////////////////////////////////////////////////////
	// Tests concerning the additional headers in the e-mails
	///////////////////////////////////////////////////////////

	public function test_send_ForEmailWithAdditionalHeader_AddsThisHeaderToSentMail() {
		$sender = new tx_oelib_tests_fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new tx_oelib_tests_fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(self::$email['message']);
		$eMail->setReturnPath('mail@foobar.com');

		$this->fixture->send($eMail);

		$sentMail = $this->fixture->getLastEmail();

		$this->assertContains(
			'Return-Path: <mail@foobar.com>',
			$sentMail['headers']
		);
	}
}
?>