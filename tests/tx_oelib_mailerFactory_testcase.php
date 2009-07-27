<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Saskia Metzler <saskia@merlin.owl.de> All rights reserved
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
require_once(t3lib_extMgm::extPath('oelib') . 'contrib/PEAR/Mail/mime.php');

/**
 * Testcase for the mailer factory class and the e-mail collector class in the
 * 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class tx_oelib_mailerFactory_testcase extends tx_phpunit_testcase {
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
		$this->fixture->setFakedReturnValue(true);

		$this->assertTrue(
			$this->fixture->sendEmail('', '', '')
		);
	}

	public function testSendEmailReturnsFalseIfTheReturnValueIsSetToFalse() {
		$this->fixture->setFakedReturnValue(false);

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

		$characterSet = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] ?
			$GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] : 'ISO-8859-1';

		$buildParameter = array(
			'text_encoding' => 'base64',
			'head_charset' => $characterSet,
			'text_charset' => $characterSet,
			'html_charset' => $characterSet,
		);

		$mimeEMail = new Mail_mime();
		$mimeEMail->setFrom($sender->getEMailAddress());
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
		unset($sender, $recipient, $eMail);
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

		$characterSet = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] ?
			$GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] : 'ISO-8859-1';

		$buildParameter = array(
			'text_encoding' => 'base64',
			'head_charset' => $characterSet,
			'text_charset' => $characterSet,
			'html_charset' => $characterSet,
		);

		$mimeEMail = new Mail_mime();
		$mimeEMail->setFrom($sender->getEMailAddress());
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
		unset($sender, $recipient, $eMail, $otherRecipient, $otherEMail);
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

		$characterSet = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] ?
			$GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] : 'ISO-8859-1';

		$buildParameter = array(
			'text_encoding' => 'base64',
			'head_charset' => $characterSet,
			'text_charset' => $characterSet,
			'html_charset' => $characterSet,
		);

		$mimeEMail = new Mail_mime();
		$mimeEMail->setFrom($sender->getEMailAddress());
		$mimeEMail->setTXTBody(self::$email['message']);

		$otherMimeEMail = new Mail_mime();
		$otherMimeEMail->setFrom($sender->getEMailAddress());
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
		unset($sender, $recipient, $eMail, $otherRecipient, $otherEMail);
	}

	/**
	 * @test
	 */
	public function sendWithoutSenderThrowsException() {
		$this->setExpectedException('Exception', '$email must have a sender set.');

		$eMail = new tx_oelib_Mail();

		$this->fixture->send($eMail);
	}

	/**
	 * @test
	 */
	public function sendWithHTMLMessage() {
		$htmlMessage = '<h1>Very cool HTML message</h1>' . CRLF .
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

		$characterSet = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] ?
			$GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] : 'ISO-8859-1';

		$this->assertEquals(
			array(
				'recipient' => self::$email['recipient'],
				'subject' => self::$email['subject'],
				'message' => $htmlMessage,
				'headers' => 'MIME-Version: 1.0' . CRLF .
					'From: any-sender@email-address.org' . CRLF .
					'Content-Transfer-Encoding: quoted-printable' . CRLF .
					'Content-Type: text/html; charset="' . $characterSet . '"' . CRLF,
			),
			$this->fixture->getLastEmail()
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
		unset($sender, $recipient, $eMail);
	}

	/**
	 * @test
	 */
	public function mailWithEmptySenderThrowsException() {
		$this->setExpectedException('Exception', '$emailAddress must not be empty.');

		$this->fixture->mail('', 'subject', 'message');
	}

	/**
	 * @test
	 */
	public function mailWithEmptySubjectThrowsException() {
		$this->setExpectedException('Exception', '$subject must not be empty.');

		$this->fixture->mail('john@doe.com', '', 'message');
	}

	/**
	 * @test
	 */
	public function mailWithEmptyMessageThrowsException() {
		$this->setExpectedException('Exception', '$message must not be empty.');

		$this->fixture->mail('john@doe.com', 'subject', '');
	}

	public function test_sendForSubjectWithAsciiCharactersOnly_DoesNotBase64EncodeSubject() {
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

	public function test_sendForSubjectWithNonAsciiCharacters_Base64EncodesSubject() {
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
			base64_encode('föö'),
			$this->fixture->getLastSubject()
		);
	}


	/////////////////////////////////////////////////
	// Tests concerning formatting the e-mail body.
	/////////////////////////////////////////////////

	public function testOneLineFeedIsReplacedByCrLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo'.LF.'bar');

		$this->assertEquals(
			'foo'.CRLF.'bar',
			$this->fixture->getLastBody()
		);
	}

	public function testOneCarriageReturnIsReplacedByCrLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo'.CR.'bar');

		$this->assertEquals(
			'foo'.CRLF.'bar',
			$this->fixture->getLastBody()
		);
	}

	public function testTwoLineFeedsAreReplacedByTwoCrLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo'.LF.LF.'bar');

		$this->assertEquals(
			'foo'.CRLF.CRLF.'bar',
			$this->fixture->getLastBody()
		);
	}

	public function testTwoCarriageReturnsAreReplacedByTwoCrLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo'.CR.CR.'bar');

		$this->assertEquals(
			'foo'.CRLF.CRLF.'bar',
			$this->fixture->getLastBody()
		);
	}

	public function testSeveralLineFeedsAreReplacedByTwoCrLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo'.LF.LF.LF.LF.LF.'bar');

		$this->assertEquals(
			'foo'.CRLF.CRLF.'bar',
			$this->fixture->getLastBody()
		);
	}

	public function testSeveralCarriageReturnsAreReplacedByTwoCrLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo'.CR.CR.CR.CR.CR.'bar');

		$this->assertEquals(
			'foo'.CRLF.CRLF.'bar',
			$this->fixture->getLastBody()
		);
	}

	public function testEmailBodyIsNotFormattedWhenFormattingIsDisabled() {
		$this->fixture->sendFormattedEmails(false);
		$this->fixture->sendEmail('', '', 'foo'.CR.CR.CR.CR.CR.'bar');

		$this->assertEquals(
			'foo'.CR.CR.CR.CR.CR.'bar',
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function emailBodyWithOneCrlfStillContainsOneCrlf() {
		$this->fixture->sendEmail('', '', 'foo' . CRLF . 'bar');

		$this->assertEquals(
			'foo' . CRLF . 'bar',
			$this->fixture->getLastBody()
		);
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
			'Return-Path: mail@foobar.com',
			$sentMail['headers']
		);
	}
}
?>