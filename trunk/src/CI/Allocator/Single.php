<?php

/**
 * CI_Allocator_Single
 * 
 * Implements the single allocation policy. This means an object is only
 * created once and reused for every reference.
 * 
 * @author Martin Kuckert
 * @copyright Copyright (c) 2009 Martin Kuckert
 * @license New BSD License
 * @package CI.Allocator
 * @since 30.08.2009
 */
class CI_Allocator_Single extends CI_Allocator_Base {
	
	/**
	 * @var array Already allocated instances.
	 */
	private $_createdInstances=array();
	
	/**
	 * Creates an object for the configurations of the given id.
	 * 
	 * @throws CI_Allocator_Exception
	 * @return object
	 * @param string Configuration id
	 */
	public function allocate($id) {
		if(!isset($this->_createdInstances[$id])) {
			$config=$this->ctx->getObjectConfigs($id);
			$instance=$this->createObject($config['ctr'][0], $config['ctr'][1]);
			$this->_createdInstances[$id]=$instance;
			$this->initializeProperties($instance, $config['properties']);
		}
		
		return $this->_createdInstances[$id];
	}
	
}
