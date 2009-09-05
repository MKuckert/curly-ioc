<?php

/**
 * CI_ObjectReference
 * 
 * @author Martin Kuckert
 * @copyright Copyright (c) 2009 Martin Kuckert
 * @license New BSD License
 * @package CI
 * @since 30.08.2009
 */
class CI_ObjectReference {
	
	/**
	 * @var string Name of the referenced object
	 */
	private $_refName;
	
	/**
	 * Constructor
	 *
	 * @param string Name of the referenced object
	 */
	public function __construct($name) {
		$this->_refName=$name;
	}
	
	/**
	 * @return string
	 */
	public function getRefName() {
		return $this->_refName;
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_refName;
	}
	
}