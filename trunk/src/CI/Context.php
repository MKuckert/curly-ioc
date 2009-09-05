<?php

/**
 * CI_Container
 * 
 * @author Martin Kuckert
 * @copyright Copyright (c) 2009 Martin Kuckert
 * @license New BSD License
 * @package CI
 * @since 28.08.2009
 */
class CI_Context {
	
	/**
	 * @var array Configurations for objects
	 */
	private $_objConf=array();
	
	/**
	 * @var array Already created objects
	 */
	private $_obj=array();
	
	/**
	 * @var array Instances used for object allocation
	 */
	private $_allocator=array();
	
	/**
	 * @var array Property for recognition of circular object references
	 */
	protected $circleRef=array();
	
	/**
	 * @throws CI_Exception
	 * @param array
	 */
	public function __construct(array $init) {
		$this->_objConf=$init['objects'];
		$this->initImmediateObjects();
	}
	
	/**
	 * Initializes the objects with initialization policy immediate.
	 * 
	 * @throws CI_Exception
	 * @return void
	 */
	protected function initImmediateObjects() {
		try {
			foreach($this->_objConf as $id=>$conf) {
				if($conf['policies']['initialization']==CI_Container::INIT_IMMEDIATE) {
					$this->getObjectInstance($id);
				}
			}
		}
		catch(CI_Exception $ex) {
			throw new CI_Exception('The initialization of objects with immediate initialization policy failed because of the following error: '.$ex->getMessage());
		}
	}
	
	/**
	 * @return boolean
	 * @param string Object id
	 */
	public function hasObject($id) {
		return isset($this->_objConf[$id]);
	}
	
	/**
	 * @return array
	 * @param string Object id
	 */
	public function getObjectConfigs($id) {
		return $this->_objConf[$id];
	}
	
	/**
	 * @throws CI_Exception
	 * @return object
	 * @param string Object id
	 */
	public function getObject($id) {
		if(isset($this->_objConf[$id])) {
			return $this->getObjectInstance($id);
		}
		else {
			throw new CI_Exception('Unable to find an object with id '.$id);
		}
	}
	
	/**
	 * @throws CI_Exception
	 * @return object
	 * @param string Object id
	 */
	protected function getObjectInstance($id) {
		$count=array_count_values($this->circleRef);
		if(isset($count[$id]) and $count[$id]>2) {
			throw new CI_Allocator_Exception('Found circular object reference in resolve chain '.implode(', ', $this->circleRef).', id '.$id.' referenced more than twice');
		}
		
		// Let's see how to create an instance for this configuration 
		$alloc=$this->_objConf[$id]['policies']['allocation'];
		if(!isset($this->_allocator[$alloc])) {
			$inst=CI_Allocator_Factory::fabricate($alloc, $this);
			if($inst===NULL) {
				throw new CI_Exception('Failed to create an object allocator for allocation policy '.$alloc);
			}
			$this->_allocator[$alloc]=$inst;
		}
		
		try {
			$this->circleRef[]=$id;
			$instance=$this->_allocator[$alloc]->allocate($id);
			array_pop($this->circleRef);
			return $instance;
		}
		catch(CI_Exception $ex) {
			array_pop($this->circleRef);
			throw $ex;
		}
	}
	
}