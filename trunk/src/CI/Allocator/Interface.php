<?php

/**
 * CI_Allocator_Interface
 * 
 * @author Martin Kuckert
 * @copyright Copyright (c) 2009 Martin Kuckert
 * @license New BSD License
 * @package CI.Allocator
 * @since 30.08.2009
 */
interface CI_Allocator_Interface {
	
	/**
	 * @param CI_Context
	 */
	public function __construct(CI_Context $ctx);
	
	/**
	 * Creates an object for the configurations of the given id.
	 * 
	 * @throws CI_Allocator_Exception
	 * @return object
	 * @param string Configuration id
	 */
	public function allocate($id);
	
}
