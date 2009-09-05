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
class CI_Container {
	
	const ALLOC_SINGLE='single';
	const ALLOC_CLONE='clone';
	const INIT_LAZY='lazy';
	const INIT_IMMEDIATE='immediate';
	const CTR_CLASS='class';
	const CTR_FACTORY='factory';
	
	/**
	 * @var array
	 */
	private $_ctxs=array();
	
	/**
	 * @return CI_Context or NULL
	 * @param string Context name
	 */
	public function getContext($name) {
		if(isset($this->_ctxs[$name])) {
			return $this->_ctxs[$name];
		}
		else {
			return NULL;
		}
	}
	
	/**
	 * Parse given string.
	 * 
	 * @throws CI_Parser_Exception
	 * @return CI_Container
	 * @param string
	 */
	public function parseString($string) {
		// TODO: Determine Filetype with factory.
		$parser=new CI_Parser_Xml($this);
		$this->initCtx($parser->parseString($string));
		return $this;
	}
	
	/**
	 * Parse given file.
	 * 
	 * @throws CI_Parser_Exception
	 * @return CI_Container
	 * @param string
	 */
	public function parseFile($file) {
		// TODO: Determine Filetype with factory.
		$parser=new CI_Parser_Xml($this);
		$this->initCtx($parser->parseFile($file));
		return $this;
	}
	
	/**
	 * Initializes the given context definitions.
	 *
	 * @return void
	 * @param array
	 */
	protected function initCtx(array $ctxs) {
		foreach($ctxs as $name=>$ctx) {
			$this->_ctxs[$name]=new CI_Context($ctx);
		}
	}
	
	public function normalizeClassname($name) {
		return str_replace('.', '_', $name);
	}
	
}