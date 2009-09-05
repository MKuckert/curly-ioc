<?php

/**
 * CI_Allocator_Factory
 * 
 * @author Martin Kuckert
 * @copyright Copyright (c) 2009 Martin Kuckert
 * @license New BSD License
 * @package CI.Allocator
 * @since 30.08.2009
 */
class CI_Allocator_Factory {
	
	/**
	 * Create an allocator instance for the given allocation policy.
	 * 
	 * @return CI_Allocator_Interface or NULL
	 * @param string Allocation policy
	 * @param CI_Context Context for this allocator
	 */
	static public function fabricate($allocPolicy, CI_Context $ctx) {
		switch($allocPolicy) {
			case CI_Container::ALLOC_CLONE:
				return new CI_Allocator_Clone($ctx);
			case CI_Container::ALLOC_SINGLE:
				return new CI_Allocator_Single($ctx);
			default:
				return NULL;
		}
	}
	
}