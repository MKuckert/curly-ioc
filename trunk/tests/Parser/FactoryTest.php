<?php
require_once dirname(__FILE__).'/../init.php';
class CI_Parser_FactoryTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @var CI_Container
	 */
	private $container=NULL;
	
	/**
	 * @var CI_Parser_Factory
	 */
	private $factory=NULL;
	
	protected function setUp() {
		$this->factory=new CI_Parser_Factory();
		$this->container=new CI_Container();
	}
	
	public function testContainerSetter() {
		$this->container->setParserFactory($this->factory);
	}
	
	public function testFabricateXmlParserByFile() {
		$file=dirname(__FILE__).'/../test/xmlparse.xml';
		$parser=$this->factory->fabricateFileParser($this->container, $file);
		$this->assertType('CI_Parser_Xml', $parser);
	}
	
	public function testFabricateXmlParserByString() {
		$file=dirname(__FILE__).'/../test/xmlparse.xml';
		$parser=$this->factory->fabricateStringParser($this->container, file_get_contents($file));
		$this->assertType('CI_Parser_Xml', $parser);
	}
	
	public function testFabricateXmlParserByFileWithTypehint() {
		$file=dirname(__FILE__).'/../test/xmlparse.xml';
		$parser=$this->factory->fabricateFileParser($this->container, $file, array('typeHint'=>'xml'));
		$this->assertType('CI_Parser_Xml', $parser);
	}
	
	public function testFabricateXmlParserByStringWithTypehint() {
		$file=dirname(__FILE__).'/../test/xmlparse.xml';
		$parser=$this->factory->fabricateStringParser($this->container, file_get_contents($file), array('typeHint'=>'xml'));
		$this->assertType('CI_Parser_Xml', $parser);
	}
	
	public function testFabricateXmlParserByFileWithUnknownExtension() {
		$file=dirname(__FILE__).'/../test/xmlparse.xml';
		$copy=$file.'.'.rand(1000, 9999);
		copy($file, $copy);
		$parser=$this->factory->fabricateFileParser($this->container, $copy);
		$this->assertType('CI_Parser_Xml', $parser);
		unlink($copy);
	}
	
	public function testFabricateParserByStringWithUnknownType() {
		try {
			$this->factory->fabricateStringParser($this->container, 'THIS SHOULD BE REALLY UNKNOWN!');
			$this->fail('Expected exception');
		}
		catch(CI_Parser_Exception $ex) {
			$this->assertContains('Unable to create a string parser for type unknown. Either type determination failed or the typehint was not really helpful', $ex->getMessage());
		}
	}
	
	public function testFabricateParserByFileWithUnknownType() {
		$tmpFile=dirname(__FILE__).'/../test/'.rand(1000, 9999);
		file_put_contents($tmpFile, 'THIS SHOULD BE REALLY UNKNOWN!');
		
		try {
			$this->factory->fabricateFileParser($this->container, $tmpFile);
			unlink($tmpFile);
			$this->fail('Expected exception');
		}
		catch(CI_Parser_Exception $ex) {
			$this->assertContains('Unable to create a string parser for type unknown. Either type determination failed or the typehint was not really helpful', $ex->getMessage());
		}
		
		unlink($tmpFile);
	}
	
	public function testFabricateParserByFileWithUnknownTypeWithTypehint() {
		$tmpFile=dirname(__FILE__).'/../test/'.rand(1000, 9999);
		file_put_contents($tmpFile, 'THIS SHOULD BE REALLY UNKNOWN!');
		
		$parser=$this->factory->fabricateFileParser($this->container, $tmpFile, array('typeHint'=>'xml'));
		$this->assertType('CI_Parser_Xml', $parser);
		unlink($tmpFile);
	}
	
	public function testFabricateParserByNotExistingFile() {
		$tmpFile='/YouReallyShouldntHaveSuchAFile';
		
		try {
			$this->factory->fabricateFileParser($this->container, $tmpFile);
			$this->fail('Expected exception');
		}
		catch(CI_Parser_Exception $ex) {
			$this->assertContains('Unable to open file', $ex->getMessage());
		}
	}
	
}
