<?php

/**
 * CI_Parser_Xml
 * 
 * @author Martin Kuckert
 * @copyright Copyright (c) 2009 Martin Kuckert
 * @license New BSD License
 * @package CI.Parser
 * @since 28.08.2009
 */
class CI_Parser_Xml implements CI_Parser_Interface {
	
	/**
	 * @var SimpleXMLElement
	 */
	private $xml=NULL;
	
	/**
	 * @var CI_Container
	 */
	private $container=NULL;
	
	/**
	 * @var array The current working path
	 */
	private $path=array();
	
	/**
	 * Returns the current working path as a string.
	 * 
	 * @return string
	 */
	private function p() {
		return implode(' - ', $this->path);
	}
	
	/**
	 * @throws CI_Parser_Exception
	 * @param CI_Container
	 */
	public function __construct(CI_Container $container) {
		$this->container=$container;
	}
	
	/**
	 * @throws CI_Parser_Exception
	 * @return array Contextdefinitions
	 * @param string Path to the file to parse
	 */
	public function parseFile($file) {
		try {
			$this->xml=new SimpleXMLElement($file, LIBXML_NOERROR|LIBXML_NOWARNING, true);
		}
		catch(Exception $ex) {
			throw new CI_Parser_Exception('Failed to parse file '.$file.' as xml: '.$ex->getMessage());
		}
		
		return $this->parse();
	}
	
	/**
	 * @throws CI_Parser_Exception
	 * @return array Contextdefinitions
	 * @param string String to parse
	 */
	public function parseString($string) {
		try {
			$this->xml=new SimpleXMLElement($string, LIBXML_NOERROR|LIBXML_NOWARNING);
		}
		catch(Exception $ex) {
			throw new CI_Parser_Exception('Failed to parse given string as xml: '.$ex->getMessage());
		}
		
		return $this->parse();
	}
	
	/**
	 * @throws CI_Parser_Exception
	 * @return array Contextdefinitions
	 */
	protected function parse() {
		$contexts=array();
		$this->path=array();
		foreach($this->xml->context as $ctx) {
			$name=$ctx['name'];
			if($name===NULL) {
				throw new CI_Parser_Exception('Every context node requires a name attribute');
			}
			
			$name=(string)$name;
			if(isset($contexts[$name])) {
				throw new CI_Parser_Exception('Every context node has to have a unique name attribute, '.$name.' found at least twice in '.$this->p());
			}
			
			$this->path[]=$name;
			$contexts[$name]=$this->parseContext($ctx);
			array_pop($this->path);
		}
		return $contexts;
	}
	
	/**
	 * @throws CI_Parser_Exception
	 * @return array Objectdefinitions
	 * @param SimpleXMLElement
	 */
	protected function parseContext(SimpleXMLElement $el) {
		$objects=array();
		foreach($el->object as $obj) {
			$id=$obj['id'];
			if($id===NULL) {
				throw new CI_Parser_Exception('Every object node requires an id attribute in '.$this->p());
			}
			
			$id=(string)$id;
			if(isset($objects[$id])) {
				throw new CI_Parser_Exception('Every object node has to have a unique id attribute, '.$id.' found at least twice in '.$this->p());
			}
			
			$this->path[]=$id;
			$objects[$id]=$this->parseObject($obj);
			array_pop($this->path);
		}
		return array(
			'objects'	=> $objects
		);
	}
	
	/**
	 * @throws CI_Parser_Exception
	 * @return array Objectdefinition
	 * @param SimpleXMLElement
	 */
	protected function parseObject(SimpleXMLElement $el) {
		$obj=array();
		
		$obj['ctr']=array(
			$this->parseConstruction($el),
			$this->parseConstructionArguments($el)
		);
		$obj['policies']=$this->parsePolicies($el);
		$obj['properties']=$this->parseProperties($el);
		
		return $obj;
	}
	
	/**
	 * @throws CI_Parser_Exception
	 * @return array Construction definition
	 * @param SimpleXMLElement
	 */
	protected function parseConstruction(SimpleXMLElement $el) {
		// classname given
		if(isset($el['class'])) {
			return array(
				'class' => $this->container->normalizeClassname((string)$el['class'])
			);
		}
		// factory class given
		else if(isset($el['factory-class'])) {
			$method=$el['factory-method'];
			if($method===NULL) {
				throw new CI_Parser_Exception('Failed to parse factory initialization in '.$this->p());
			}
			
			return array(
				'type' => CI_Container::CTR_FACTORY,
				'class' => $this->container->normalizeClassname((string)$el['factory-class']),
				'method' => (string)$method
			);
		}
		// factory object given
		else if(isset($el['factory'])) {
			$method=$el['factory-method'];
			if($method===NULL) {
				throw new CI_Parser_Exception('Failed to parse factory initialization in '.$this->p());
			}
			
			return array(
				'type' => CI_Container::CTR_FACTORY,
				'object' => (string)$el['factory'],
				'method' => (string)$method
			);
		}
		// nothing given
		else {
			throw new CI_Parser_Exception('No object initialization found in '.$this->p());
		}
	}
	
	/**
	 * @throws CI_Parser_Exception
	 * @return array Construction arguments
	 * @param SimpleXMLElement
	 */
	protected function parseConstructionArguments(SimpleXMLElement $el) {
		// No subnodes available
		if(!isset($el->ctr)) {
			return array();
		}
		// More than one subnode
		else if(count($el->ctr)>1) {
			throw new CI_Parser_Exception('Found more than one ctr element in '.$this->p());
		}
		
		$this->path[]='ctr';
		
		$args=array();
		foreach($el->ctr->param as $param) {
			$args[]=$this->parseParameter($param);
		}
		
		array_pop($this->path);
		
		return $args;
	}
	
	/**
	 * @throws CI_Parser_Exception
	 * @return array Policy definitions
	 * @param SimpleXMLElement
	 */
	protected function parsePolicies(SimpleXMLElement $el) {
		// Allocation
		if(isset($el['allocation-policy'])) {
			$ap=(string)$el['allocation-policy'];
			$c='CI_Container::ALLOC_'.strtoupper($ap);
			if(!defined($c)) {
				throw new CI_Parser_Exception('Invalid allocation policy '.$ap.' found in '.$this->p());
			}
			$allocation=constant($c);
		}
		else {
			$allocation=CI_Container::ALLOC_CLONE;
		}
	
		// Initialization
		if(isset($el['initialization-policy'])) {
			$in=(string)$el['initialization-policy'];
			$c='CI_Container::INIT_'.strtoupper($in);
			if(!defined($c)) {
				throw new CI_Parser_Exception('Invalid initialization policy '.$in.' found in '.$this->p());
			}
			$initialization=constant($c);
		}
		else if($allocation===CI_Container::ALLOC_CLONE) {
			$initialization=CI_Container::INIT_LAZY;
		}
		else {
			$initialization=CI_Container::INIT_IMMEDIATE;
		}
		
		return array(
			'allocation'		=> $allocation,
			'initialization'	=> $initialization
		);
	}
	
	/**
	 * @throws CI_Parser_Exception
	 * @return array Policy definitions
	 * @param SimpleXMLElement
	 */
	protected function parseProperties(SimpleXMLElement $el) {
		$retval=array();
		$this->path[]='properties';
		foreach($el->property as $prop) {
			if(!isset($prop['name'])) {
				throw new CI_Parser_Exception('Every property node requires a name attribute in '.$this->p());
			}
			
			$name=(string)$prop['name'];
			if(isset($retval[$name])) {
				throw new CI_Parser_Exception('A property name has to be unique, '.$name.' found at least twice in '.$this->p());
			}
			
			$value=$this->parseParameter($prop);
			$retval[$name]=$value;
		}
		array_pop($this->path);
		return $retval;
	}
	
	/**
	 * @throws CI_Parser_Exception
	 * @return mixed Parameter definition
	 * @param SimpleXMLElement
	 */
	protected function parseParameter(SimpleXMLElement $el) {
		// Array given
		if(isset($el->array)) {
			if(count($el->children())>1) {
				throw new CI_Parser_Exception('An value of type array may only include the array tag in '.$this->p());
			}
			return $this->parseArray($el->array);
		}
		// NULL given
		else if(isset($el->null)) {
			if(count($el->children())>1) {
				throw new CI_Parser_Exception('An null value may only include the null tag in '.$this->p());
			}
			return NULL;
		}
		// Reference to other object
		else if(isset($el->ref)) {
			if(count($el->children())>1) {
				throw new CI_Parser_Exception('An ref value may only include the ref tag in '.$this->p());
			}
			return new CI_ObjectReference((string)$el->ref);
		}
		// Use direct value
		else {
			// As attribute given
			if(isset($el['value'])) {
				$value=(string)$el['value'];
			}
			else {
				$value=trim((string)$el);
			}
			if(isset($el['type'])) {
				$value=$this->convertType($value, $el['type']);
			}
			return $value;
		}
	}
	
	/**
	 * @throws CI_Parser_Exception
	 * @return mixed Typed parameter
	 * @param string Parameter definition
	 * @param string Type
	 */
	protected function convertType($value, $type) {
		$type=trim(strtolower($type));
		switch($type) {
			case 'boolean':
			case 'bool':
				// TODO: Improve this
				return strcasecmp($value, 'false')!==0;
			case 'integer':
			case 'int':
				return (int)$value;
			case 'double':
				return (double)$value;
			case 'string':
				return $value;
			case 'constant':
				if(!defined($value)) {
					throw new CI_Parser_Exception('The constant '.$value.' is not defined in '.$this->p());
				}
				return constant($value);
			default:
				throw new CI_Parser_Exception('Unknown type '.$type.' found in '.$this->p());
		}
	}
	
	/**
	 * @throws CI_Parser_Exception
	 * @return array Interpreted array definition
	 * @param SimpleXMLElement
	 */
	protected function parseArray(SimpleXMLElement $el) {
		$retval=array();
		if(count($el->children())!=count($el->entry)) {
			throw new CI_Parser_Exception('An array node may only include entry tags in '.$this->p());
		}
		foreach($el->entry as $entry) {
			$key=$this->parseArrayEntryKey($entry);
			$value=$this->parseArrayEntryValue($entry);
			
			// No direct key value specified
			if($key===NULL) {
				$retval[]=$value;
			}
			// key is not unique
			else if(isset($retval[$key])) {
				throw new CI_Parser_Exception('An array entry with key '.$key.' is already set in array in '.$this->p());
			}
			else {
				$retval[$key]=$value;
			}
		}
		return $retval;
	}
	
	/**
	 * @throws CI_Parser_Exception
	 * @return string|integer Parsed array key
	 * @param SimpleXMLElement
	 */
	protected function parseArrayEntryKey(SimpleXMLElement $entry) {
		if(isset($entry['key'])) {
			$key=(string)$entry['key'];
		}
		else if(isset($entry->key)) {
			$key=trim((string)$entry->key);
			if(isset($entry->key['type'])) {
				$key=$this->convertType($key, $entry->key['type']);
			}
		}
		else {
			$key=NULL;
		}
		
		return $key;
	}
	
	/**
	 * @throws CI_Parser_Exception
	 * @return string|integer Parsed array value
	 * @param SimpleXMLElement
	 */
	protected function parseArrayEntryValue(SimpleXMLElement $entry) {
		if(isset($entry['value'])) {
			$value=(string)$entry['value'];
		}
		else if(isset($entry->value)) {
			$value=$this->parseParameter($entry->value);
		}
		else {
			$value=trim((string)$entry);
		}
		
		return $value;
	}
	
}