<?php
require_once dirname(__FILE__).'/../init.php';
class CI_Parser_XmlParserTest extends PHPUnit_Framework_TestCase {
	
	const FOO='bar';
	
	protected $assertion=array(
		'ctx' => array(
			'objects' => array(
				'obj' => array(
					'ctr' => array(
						array(
							'class' => 'TestObj1'
						),
						array(
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
						)
					),
					'policies' => array(
						'allocation' => CI_Container::ALLOC_CLONE,
						'initialization' => CI_Container::INIT_LAZY
					),
					'properties' => array(
						'prop1' => 1235,
						'prop2' => 'valueOfProp2',
						'prop3' => array(
							PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
						)
					)
				)
			)
		)
	);
	
	protected function xml($obj) {
		return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
	<context name="ctx">
		$obj
	</context>
</configuration>
XML;
	}
	
	public function testParseFile() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$file=dirname(__FILE__).'/../test/xmlparse.xml';
		
		try {
			$result=$parser->parseFile($file);
		}
		catch(CI_Parser_Exception $ex) {
			$this->fail('Failed to parse xml file');
		}
		
		$this->assertEquals($result, $this->assertion);
	}
	
	public function testParseString() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$file=dirname(__FILE__).'/../test/xmlparse.xml';
		$string=file_get_contents($file);
		
		try {
			$result=$parser->parseString($string);
		}
		catch(CI_Parser_Exception $ex) {
			$this->fail('Failed to parse xml string');
		}
		
		$this->assertEquals($result, $this->assertion);
	}
	
	public function testParseFileFails() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$file=__FILE__;
		
		try {
			$result=$parser->parseFile($file);
			$this->fail('Expected exception');
		}
		catch(CI_Parser_Exception $ex) {
			// success
		}
	}
	
	public function testParseStringFails() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$string=file_get_contents(__FILE__);
		
		try {
			$result=$parser->parseString($string);
			$this->fail('Expected exception');
		}
		catch(CI_Parser_Exception $ex) {
			// success
		}
	}
	
	public function testParseEmpty() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<configuration />
XML;
		$result=$parser->parseString($str);
		
		$this->assertEquals($result, array());
	}
	
	public function testParseContexts() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
	<context name="ctx1" />
	<context name="ctx2" />
</configuration>
XML;
		$result=$parser->parseString($str);
		
		$this->assertEquals($result, array(
			'ctx1' => array('objects'=>array()),
			'ctx2' => array('objects'=>array())
		));
	}
	
	public function testParseContextWithoutName() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
	<context />
</configuration>
XML;
		try {
			$parser->parseString($str);
			$this->fail('Exception expected');
		}
		catch(CI_Parser_Exception $ex) {
			$this->assertContains('Every context node requires a name attribute', $ex->getMessage());
		}
	}
	
	public function testParseContextDublicateName() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
	<context name="ctx" />
	<context name="ctx" />
</configuration>
XML;
		try {
			$parser->parseString($str);
			$this->fail('Exception expected');
		}
		catch(CI_Parser_Exception $ex) {
			$this->assertContains('Every context node has to have a unique name attribute,', $ex->getMessage());
		}
	}
	
	public function testParseObjects() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml('<object id="obj" class="Test" />');
		$result=$parser->parseString($str);
		
		$this->assertEquals($result, array(
			'ctx' => array('objects'=>array(
				'obj' => array(
					'ctr' => array(
						array('class' => 'Test'),
						array()
					),
					'policies' => array(
						'allocation' => CI_Container::ALLOC_CLONE,
						'initialization' => CI_Container::INIT_LAZY
					),
					'properties' => array()
				)
			))
		));
	}
	
	public function testParseObjectWithoutID() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml('<object class="Test" />');
		
		try {
			$parser->parseString($str);
			$this->fail('Excepted exception');
		}
		catch(CI_Parser_Exception $ex) {
			$this->assertContains('Every object node requires an id attribute', $ex->getMessage());
		}
	}
	
	public function testParseObjectDuplicateID() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml('<object id="obj" class="Test" /><object id="obj" class="Test" />');
		
		try {
			$parser->parseString($str);
			$this->fail('Excepted exception');
		}
		catch(CI_Parser_Exception $ex) {
			$this->assertContains('Every object node has to have a unique id attribute', $ex->getMessage());
		}
	}
	
	public function testParseObjectWithoutClass() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml('<object id="obj" />');
		
		try {
			$parser->parseString($str);
			$this->fail('Excepted exception');
		}
		catch(CI_Parser_Exception $ex) {
			$this->assertContains('No object initialization found', $ex->getMessage());
		}
	}
	
	public function testParseObjectWithFactoryClass() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml('<object id="obj" factory-class="Factory" factory-method="FactoryMethod" />');
		
		$result=$parser->parseString($str);
		$this->assertEquals($result, array(
			'ctx' => array('objects'=>array(
				'obj' => array(
					'ctr' => array(
						array(
							'type' => 'factory',
							'class' => 'Factory',
							'method' => 'FactoryMethod'
						),
						array()
					),
					'policies' => array(
						'allocation' => CI_Container::ALLOC_CLONE,
						'initialization' => CI_Container::INIT_LAZY
					),
					'properties' => array()
				)
			))
		));
	}
	
	public function testParseObjectWithFactoryObject() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml('<object id="obj" factory="factoryObj" factory-method="FactoryMethod" />');
		
		$result=$parser->parseString($str);
		$this->assertEquals($result, array(
			'ctx' => array('objects'=>array(
				'obj' => array(
					'ctr' => array(
						array(
							'type' => 'factory',
							'object' => 'factoryObj',
							'method' => 'FactoryMethod'
						),
						array()
					),
					'policies' => array(
						'allocation' => CI_Container::ALLOC_CLONE,
						'initialization' => CI_Container::INIT_LAZY
					),
					'properties' => array()
				)
			))
		));
	}
	
	public function testParseObjectWithFactoryClassWithoutMethod() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml('<object id="obj" factory-class="Factory" />');
		
		try {
			$parser->parseString($str);
			$this->fail('Expected exception');
		}
		catch(CI_Parser_Exception $ex) {
			$this->assertContains('Failed to parse factory initialization', $ex->getMessage());
		}
	}
	
	public function testParseObjectWithFactoryObjectWithoutMethod() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml('<object id="obj" factory="factoryObj" />');
		
		try {
			$parser->parseString($str);
			$this->fail('Expected exception');
		}
		catch(CI_Parser_Exception $ex) {
			$this->assertContains('Failed to parse factory initialization', $ex->getMessage());
		}
	}
	
	public function testParseObjectMoreThanOneCtxElement() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml(
			'<object id="obj" class="Test">
				<ctr />
				<ctr />
			</object>'
		);
		try {
			$parser->parseString($str);
			$this->fail('Expected exception');
		}
		catch(CI_Parser_Exception $ex) {
			$this->assertContains('Found more than one ctr element', $ex->getMessage());
		}
	}
	
	public function testParseObjectPolicies() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		
		foreach(array('single', 'clone') as $alloc) {
			foreach(array('lazy', 'immediate') as $init) {
				$str=$this->xml('<object id="obj" class="Test" allocation-policy="'.$alloc.'" initialization-policy="'.$init.'" />');

				$result=$parser->parseString($str);
				$this->assertEquals($result, array(
					'ctx' => array('objects'=>array(
						'obj' => array(
							'ctr' => array(
								array(
									'class' => 'Test'
								),
								array()
							),
							'policies' => array(
								'allocation' => $alloc,
								'initialization' => $init
							),
							'properties' => array()
						)
					))
				));
			}
		}
	}
	
	public function testParseObjectInvalidPolicies() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		
		foreach(array('allocation', 'initialization') as $policy) {
			$str=$this->xml('<object id="obj" class="Test" '.$policy.'-policy="invalid" />');
			
			try {
				$parser->parseString($str);
				$this->fail('Expected exception');
			}
			catch(CI_Parser_Exception $ex) {
				$this->assertContains('Invalid '.$policy.' policy', $ex->getMessage());
			}
		}
	}
	
	public function testParseValueString() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml(
			'<object id="obj" class="Test">
				<ctr>
					<param>FooBar</param>
					<param>
						FooBar
					</param>
					<param value="FooBar" />
					<param type="string">FooBar</param>
					<param type="string" value="FooBar" />
				</ctr>
			</object>'
		);
		
		$result=$parser->parseString($str);
		$this->assertEquals($result, array(
			'ctx' => array('objects'=>array(
				'obj' => array(
					'ctr' => array(
						array('class' => 'Test'),
						array(
							'FooBar',
							'FooBar',
							'FooBar',
							'FooBar',
							'FooBar'
						)
					),
					'policies' => array(
						'allocation' => 'clone',
						'initialization' => 'lazy'
					),
					'properties' => array()
				)
			))
		));
	}
	
	public function testParseValueInteger() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml(
			'<object id="obj" class="Test">
				<ctr>
					<param type="integer">1234</param>
					<param type="integer">
						1234
					</param>
					<param type="integer" value="1234" />
				</ctr>
			</object>'
		);
		
		$result=$parser->parseString($str);
		$this->assertEquals($result, array(
			'ctx' => array('objects'=>array(
				'obj' => array(
					'ctr' => array(
						array('class' => 'Test'),
						array(
							1234, 1234, 1234
						)
					),
					'policies' => array(
						'allocation' => 'clone',
						'initialization' => 'lazy'
					),
					'properties' => array()
				)
			))
		));
	}
	
	public function testParseValueBoolean() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml(
			'<object id="obj" class="Test">
				<ctr>
					<param type="boolean">false</param>
					<param type="boolean">
						false
					</param>
					<param type="boolean" value="false" />
					<param type="boolean">true</param>
					<param type="boolean">
						true
					</param>
					<param type="boolean" value="true" />
				</ctr>
			</object>'
		);
		
		$result=$parser->parseString($str);
		$this->assertEquals($result, array(
			'ctx' => array('objects'=>array(
				'obj' => array(
					'ctr' => array(
						array('class' => 'Test'),
						array(
							false, false, false, true, true, true
						)
					),
					'policies' => array(
						'allocation' => 'clone',
						'initialization' => 'lazy'
					),
					'properties' => array()
				)
			))
		));
	}
	
	public function testParseValueDouble() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml(
			'<object id="obj" class="Test">
				<ctr>
					<param type="double">0.1234</param>
					<param type="double">
						0.1234
					</param>
					<param type="double" value="0.1234" />
				</ctr>
			</object>'
		);
		
		$result=$parser->parseString($str);
		$this->assertEquals($result, array(
			'ctx' => array('objects'=>array(
				'obj' => array(
					'ctr' => array(
						array('class' => 'Test'),
						array(
							0.1234, 0.1234, 0.1234
						)
					),
					'policies' => array(
						'allocation' => 'clone',
						'initialization' => 'lazy'
					),
					'properties' => array()
				)
			))
		));
	}
	
	public function testParseValueConstant() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml(
			'<object id="obj" class="Test">
				<ctr>
					<param type="constant">CI_Parser_XmlParserTest::FOO</param>
					<param type="constant">
						CI_Parser_XmlParserTest::FOO
					</param>
					<param type="constant" value="CI_Parser_XmlParserTest::FOO" />
				</ctr>
			</object>'
		);
		
		$result=$parser->parseString($str);
		$this->assertEquals($result, array(
			'ctx' => array('objects'=>array(
				'obj' => array(
					'ctr' => array(
						array('class' => 'Test'),
						array(
							CI_Parser_XmlParserTest::FOO,
							CI_Parser_XmlParserTest::FOO,
							CI_Parser_XmlParserTest::FOO
						)
					),
					'policies' => array(
						'allocation' => 'clone',
						'initialization' => 'lazy'
					),
					'properties' => array()
				)
			))
		));
	}
	
	public function testParseValueOfInvalidType() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml(
			'<object id="obj" class="Test">
				<ctr>
					<param type="invalid">123</param>
				</ctr>
			</object>'
		);
		
		try {
			$parser->parseString($str);
			$this->fail('Expected exception');
		}
		catch(CI_Parser_Exception $ex) {
			$this->assertContains('Unknown type invalid found', $ex->getMessage());
		}
	}
	
	public function testParseValueArray() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml(
			'<object id="obj" class="Test">
				<ctr>
					<param>
						<array>
							<entry key="x" value="y" />
							<entry type="double">0.1234</entry>
						</array>
					</param>
				</ctr>
			</object>'
		);
		
		$result=$parser->parseString($str);
		$this->assertEquals($result, array(
			'ctx' => array('objects'=>array(
				'obj' => array(
					'ctr' => array(
						array('class' => 'Test'),
						array(
							array(
								'x' => 'y',
								0.1234
							)
						)
					),
					'policies' => array(
						'allocation' => 'clone',
						'initialization' => 'lazy'
					),
					'properties' => array()
				)
			))
		));
	}
	
	public function testParseValueVeryDeepArray() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml(
			'<object id="obj" class="Test">
				<ctr>
					<param>
						<array><!-- 1 -->
							<entry><value>
								<array><!-- 2 -->
									<entry><value>
										<array><!-- 3 -->
											<entry><value>
												<array><!-- 4 -->
													<entry><value>
														<array><!-- 5 -->
															<entry><value>
																<array><!-- 6 -->
																	<entry><value>
																		<array><!-- 7 -->
																			<entry key="x" value="y" />
																		</array>
																	</value></entry>
																</array>
															</value></entry>
														</array>
													</value></entry>
												</array>
											</value></entry>
										</array>
									</value></entry>
								</array>
							</value></entry>
						</array>
					</param>
				</ctr>
			</object>'
		);
		
		$result=$parser->parseString($str);
		$this->assertEquals($result, array(
			'ctx' => array('objects'=>array(
				'obj' => array(
					'ctr' => array(
						array('class' => 'Test'),
						array(
							array(	// 1
								array(	// 2
									array(	// 3
										array(	// 4
											array(	// 5
												array(	// 6
													array(	// 7
														'x' => 'y'
													)
												)
											)
										)
									)
								)
							)
						)
					),
					'policies' => array(
						'allocation' => 'clone',
						'initialization' => 'lazy'
					),
					'properties' => array()
				)
			))
		));
	}
	
	public function testParseValueArrayWithKey() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml(
			'<object id="obj" class="Test">
				<ctr>
					<param>
						<array>
							<entry>
								<key type="constant">
									CI_Parser_XmlParserTest::FOO
								</key>
								<value type="integer">123</value>
							</entry>
						</array>
					</param>
				</ctr>
			</object>'
		);
		
		$result=$parser->parseString($str);
		$this->assertEquals($result, array(
			'ctx' => array('objects'=>array(
				'obj' => array(
					'ctr' => array(
						array('class' => 'Test'),
						array(
							array(
								CI_Parser_XmlParserTest::FOO => 123
							)
						)
					),
					'policies' => array(
						'allocation' => 'clone',
						'initialization' => 'lazy'
					),
					'properties' => array()
				)
			))
		));
	}
	
	public function testParseProperty() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml(
			'<object id="obj" class="Test">
				<property name="x" value="y" />
			</object>'
		);
		
		$result=$parser->parseString($str);
		$this->assertEquals($result, array(
			'ctx' => array('objects'=>array(
				'obj' => array(
					'ctr' => array(
						array('class' => 'Test'),
						array()
					),
					'policies' => array(
						'allocation' => 'clone',
						'initialization' => 'lazy'
					),
					'properties' => array(
						'x' => 'y'
					)
				)
			))
		));
	}
	
	public function testParsePropertyWithoutName() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml(
			'<object id="obj" class="Test">
				<property value="y" />
			</object>'
		);
		
		try {
			$parser->parseString($str);
		}
		catch(CI_Parser_Exception $ex) {
			$this->assertContains('Every property node requires a name attribute', $ex->getMessage());
		}
	}
	
	public function testParsePropertyWithDuplicateName() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml(
			'<object id="obj" class="Test">
				<property name="x" value="y" />
				<property name="x" value="z" />
			</object>'
		);
		
		try {
			$parser->parseString($str);
		}
		catch(CI_Parser_Exception $ex) {
			$this->assertContains('A property name has to be unique', $ex->getMessage());
		}
	}
	
	public function testParsePropertyValues() {
		$container=new CI_Container();
		$parser=new CI_Parser_Xml($container);
		$str=$this->xml(
			'<object id="obj" class="Test">
				<property name="str" type="string">
					foo
				</property>
				<property name="int" type="int">
					123
				</property>
				<property name="dbl" type="double">
					123.456
				</property>
				<property name="boolTrue" type="boolean">
					true
				</property>
				<property name="boolFalse" type="boolean">
					false
				</property>
			</object>'
		);
		
		$parser->parseString($str);
	}
	
}
