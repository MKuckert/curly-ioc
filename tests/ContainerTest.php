<?php
require_once dirname(__FILE__).'/init.php';
require_once dirname(__FILE__).'/testobjects.php';
class CI_ContainerTest extends PHPUnit_Framework_TestCase {
	
	public function testParseAndCreate() {
		$container=new CI_Container();
		$container->parseFile(dirname(__FILE__).'/test/xmlparse.xml');
		$ctx=$container->getContext('ctx');
		$this->assertTrue($ctx instanceof CI_Context);
		
		$obj=$ctx->getObject('obj');
		$this->assertTrue($obj instanceof TestObj1);
		
		$this->assertEquals($obj->ctrArgs, array(
			'ctrParam',
			'ctrParam2',
			123,
			array(
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
				'x' => 'y',
				'array' => array(
					'foo' => 0.123
				)
			)
		));
		$this->assertEquals($obj->propArg1, 1235);
		$this->assertEquals($obj->prop2, 'valueOfProp2');
		$this->assertEquals($obj->propArg3, array(
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
		));
	}
	
	public function testDontHasContext() {
		$container=new CI_Container();
		$this->assertNull($container->getContext('ctx'));
	}
	
	public function testDontHasObject() {
		$container=new CI_Container();
		$container->parseFile(dirname(__FILE__).'/test/xmlparse.xml');
		$ctx=$container->getContext('ctx');
		
		try {
			$ctx->getObject('notDefined');
			$this->fail('Expected exception');
		}
		catch(CI_Exception $ex) {
			$this->assertContains('Unable to find an object with id', $ex->getMessage());
		}
	}
	
	public function testObjectReference() {
		$xml=<<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<configuration>
	<context name="ctx">
		<object id="obj1" class="TestObj1">
			<property name="prop1" type="integer">1235</property>
		</object>
		<object id="obj2" class="TestObj2">
			<property name="refObj">
				<ref>obj1</ref>
			</property>
		</object>
	</context>
</configuration>
XML;
		
		$container=new CI_Container();
		$ctx=$container
			->parseString($xml)
			->getContext('ctx');
		
		$this->assertTrue($ctx->hasObject('obj1'));
		$this->assertTrue($ctx->hasObject('obj2'));
		
		$obj1=$ctx->getObject('obj1');
		$this->assertTrue($obj1 instanceof TestObj1);
		
		$obj2=$ctx->getObject('obj2');
		$this->assertTrue($obj2 instanceof TestObj2);
		$this->assertTrue($obj2->getRefObj() instanceof TestObj1);
		$this->assertFalse($obj2->getRefObj()===$obj1);
	}
	
	public function testObjectReferenceWithSingletonRef() {
		$xml=<<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<configuration>
	<context name="ctx">
		<object id="obj1" class="TestObj1" allocation-policy="single">
			<property name="prop1" type="integer">1235</property>
		</object>
		<object id="obj2" class="TestObj2">
			<property name="refObj">
				<ref>obj1</ref>
			</property>
		</object>
	</context>
</configuration>
XML;
		
		$container=new CI_Container();
		$ctx=$container
			->parseString($xml)
			->getContext('ctx');
		
		$this->assertTrue($ctx->hasObject('obj1'));
		$this->assertTrue($ctx->hasObject('obj2'));
		
		$obj1=$ctx->getObject('obj1');
		$this->assertTrue($obj1 instanceof TestObj1);
		
		$obj2=$ctx->getObject('obj2');
		$this->assertTrue($obj2 instanceof TestObj2);
		$this->assertTrue($obj2->getRefObj() instanceof TestObj1);
		$this->assertTrue($obj2->getRefObj()===$obj1);
	}
	
	public function testRecursiveObjectReference() {
		$xml=<<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<configuration>
	<context name="ctx">
		<object id="obj" class="TestObj3">
			<property name="refObj">
				<ref>obj</ref>
			</property>
		</object>
	</context>
</configuration>
XML;
		
		$container=new CI_Container();
		$ctx=$container
			->parseString($xml)
			->getContext('ctx');
		
		$this->assertTrue($ctx->hasObject('obj'));
		
		try {
			$ctx->getObject('obj');
			$this->fail('Expected exception');
		}
		catch(CI_Allocator_Exception $ex) {
			$this->assertContains('Found circular object reference', $ex->getMessage());
		}
	}
	
	public function testRecursiveObjectReferenceWithSingletonObj() {
		$xml=<<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<configuration>
	<context name="ctx">
		<object id="obj" class="TestObj3" allocation-policy="single">
			<property name="refObj">
				<ref>obj</ref>
			</property>
		</object>
	</context>
</configuration>
XML;
		
		$container=new CI_Container();
		$ctx=$container
			->parseString($xml)
			->getContext('ctx');
		
		$this->assertTrue($ctx->hasObject('obj'));
		$obj=$ctx->getObject('obj');
		$this->assertEquals($obj, $obj->getRefObj());
	}
	
	public function testImmediateInitialization() {
		$xml=<<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<configuration>
	<context name="ctx">
		<object id="obj" class="TestObj4" initialization-policy="immediate" />
	</context>
</configuration>
XML;
		
		$container=new CI_Container();
		$container
			->parseString($xml)
			->getContext('ctx');
		
		$this->assertNotNull(TestObj4::$createdInstance);
	}
	
}