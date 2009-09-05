<?php

/**
 * CI_Allocator_Base
 * 
 * @author Martin Kuckert
 * @copyright Copyright (c) 2009 Martin Kuckert
 * @license New BSD License
 * @package CI.Allocator
 * @since 30.08.2009
 */
abstract class CI_Allocator_Base implements CI_Allocator_Interface {
	
	/**
	 * @var CI_Context
	 */
	protected $ctx=NULL;
	
	/**
	 * @param CI_Context
	 */
	public function __construct(CI_Context $ctx) {
		$this->ctx=$ctx;
	}
	
	/**
	 * Creates an object for the configurations of the given id.
	 * 
	 * @throws CI_Allocator_Exception
	 * @return object
	 * @param string Configuration id
	 */
	public function allocate($id) {
		$config=$this->ctx->getObjectConfigs($id);
		$instance=$this->createObject($config['ctr'][0], $config['ctr'][1]);
		$this->initializeProperties($instance, $config['properties']);
		return $instance;
	}
	
	/**
	 * Creates the object.
	 * 
	 * @throws CI_Allocator_Exception
	 * @return object
	 * @param array Creation method
	 * @param array Creation arguments
	 */
	protected function createObject(array $method, array $args) {
		// Resolve object references
		$this->resolveObjectReferences($args);
		
		if(isset($method['type'])) {
			// Initialization by factory
			if($method['type']==='factory') {
				return $this->createObjectByFactory($method, $args);
			}
			else {
				throw new CI_Allocator_Exception('Unknown creation type '.$method['type']);
			}
		}
		// Default initialization by class
		else if(isset($method['class'])) {
			return $this->createInstance($method['class'], $args);
		}
		// This configuration sucks!
		else {
			throw new CI_Allocator_Exception('The creation configuration is not really usable!');
		}
	}
	
	/**
	 * Creates the concrete object.
	 * 
	 * @return object
	 * @param string Classname
	 * @param array Creation arguments
	 */
	protected function createInstance($class, array $args) {
		// The ReflectionClass is not quite fast, so we'll use a simple
		// constructor call for most of the time
		switch(count($args)) {
			case 0:
				return new $class();
			case 1:
				return new $class($args[0]);
			case 2:
				return new $class($args[0], $args[1]);
			case 3:
				return new $class($args[0], $args[1], $args[2]);
			case 4:
				return new $class($args[0], $args[1], $args[2], $args[3]);
			default:
				$ref=new ReflectionClass($class);
				return $ref->newInstanceArgs($args);
		}
	}
	
	/**
	 * Creates the object with a factory
	 * 
	 * @throws CI_Allocator_Exception
	 * @return object
	 * @param array Creation method
	 * @param array Creation arguments
	 */
	protected function createObjectByFactory(array $method, array $args) {
		// The method setting ist mandatory for the factory type
		if(!isset($method['method'])) {
			throw new CI_Allocator_Exception('The method setting is required for a creation of type factory');
		}
		
		if(isset($method['class'])) {
			$factory=$method['class'];
			if(!class_exists($factory)) {
				throw new CI_Allocator_Exception('Unable to find factory class '.$factory);
			}
		}
		else if(isset($method['object'])) {
			if(!$this->ctx->hasObject($method['object'])) {
				throw new CI_Allocator_Exception('Unable to create the object because the factory object '.$method['object'].' can\'t be found');
			}
			
			$factory=$this->ctx->getObject($method['object']);
		}
		else {
			throw new CI_Allocator_Exception('Invalid factory creation definition. Either the object or class setting is required.');
		}
		
		return call_user_func_array(
			array($factory, $method['method']),
			$args
		);
	}
	
	/**
	 * Initializes all properties for the given object.
	 * 
	 * @throws CI_Allocator_Exception
	 * @return void
	 * @param object
	 * @param array Property configurations
	 */
	protected function initializeProperties($instance, array $propCfg) {
		// Resolve object references
		$this->resolveObjectReferences($propCfg);
		
		foreach($propCfg as $prop=>$value) {
			// Simple property
			if(property_exists($instance, $prop)) {
				$instance->$prop=$value;
				continue;
			}
			// Setter method
			$method='set'.$prop;
			if(method_exists($instance, $method)) {
				$instance->$method($value);
				continue;
			}
			
			// So, we found no property and no method, what now?
			// Simply throw this stuff back to the developer!
			throw new CI_Allocator_Exception('Unable to initialize property '.$prop.' neither as a property nor as a setter method');
		}
	}
	
	/**
	 * Resolve object references in the given value.
	 * 
	 * @return void
	 * @param array|Traversable&
	 */
	protected function resolveObjectReferences(&$values) {
		foreach($values as $key=>$value) {
			// Resolve an object reference
			if($value instanceof CI_ObjectReference) {
				$values[$key]=$this->ctx->getObject($value->getRefName());
			}
			// Recurse for sub values
			else if(is_array($value) or $value instanceof Traversable) {
				$this->resolveObjectReferences($values[$key]);
			}
		}
	}
	
}
