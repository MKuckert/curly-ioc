<?php
require_once dirname(__FILE__).'/init.php';
require_once 'tests/Parser/FactoryTest.php';
require_once 'tests/Parser/XmlParserTest.php';
require_once 'tests/ContainerTest.php';

/**
 * Static test suite.
 */
class AllTests extends PHPUnit_Framework_TestSuite {
	
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		$this->setName ( 'AllTests' );
		
		$this->addTestSuite ( 'CI_Parser_FactoryTest' );
		$this->addTestSuite ( 'CI_Parser_XmlParserTest' );
		$this->addTestSuite ( 'CI_ContainerTest' );
	
	}
	
	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ( );
	}
}

