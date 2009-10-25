<?php

/**
 * CI_Container
 * 
 * The concrete container for the inversion of control. Parsing and context
 * retrieval is made with an instance of this class.
 * 
 * The {@link parseString} and {@link parseFile} methods take an options array.
 * Possible options are the following:
 *  - typeHint (string):	Gives the parser factory a hint, what type of input
 * 							the given parameter consists of. This can improve
 * 							performance, because the type doesn´t have to be
 * 							determined.
 *  - cachePath (string):	Directory path where the container can cache parsed
 * 							context configurations.
 *  - disableCache (bool):  This flag is only useful in conjunction with the
 * 							cachePath option. You can disable caching even if
 * 							the path is set. Is just useful for debugging to
 * 							easily disable caching without the need to remove
 * 							the cachePath.
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
	 * @var array List of all parsed contexts.
	 */
	private $_ctxs=array();
	
	/**
	 * @var CI_Parser_Factory
	 */
	private $_parserFactory=NULL;
	
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
	 * @return CI_Parser_Factory
	 */
	public function getParserFactory() {
		if($this->_parserFactory===NULL) {
			$this->_parserFactory=new CI_Parser_Factory();
		}
		return $this->_parserFactory;
	}
	
	/**
	 * @return CI_Container
	 * @param CI_Parser_Factory
	 */
	public function setParserFactory(CI_Parser_Factory $factory) {
		$this->_parserFactory=$factory;
		return $this;
	}
	
	/**
	 * Trys to load an entry from cache
	 * 
	 * @return boolean true if successfully loaded
	 * @param string Parsestring or -file
	 * @param array Additional options
	 */
	protected function tryLoadFromCache($stringOrFile, array $options) {
		if(!isset($options['cachePath']) or isset($options['disableCache'])) {
			return false;
		}
		
		$cacheKey=md5($stringOrFile);
		$file=$options['cachePath'].DIRECTORY_SEPARATOR.$cacheKey;
		if(!is_file($file)) {
			return false;
		}
		else if(is_file($stringOrFile) and filemtime($stringOrFile)>$file) {
			return false;
		}
		
		$entry=unserialize(
			file_get_contents($options['cachePath'].DIRECTORY_SEPARATOR.$cacheKey)
		);
		
		if(!is_array($entry)) {
			return false;
		}
		
		$this->initCtx($entry);
		return true;
	}
	
	/**
	 * Trys to store an entry to cache
	 * 
	 * @return void
	 * @param string Parsestring or -file
	 * @param array Additional options
	 * @param array Parsed context data
	 */
	protected function storeToCache($stringOrFile, array $options, array $entry) {
		if(!isset($options['cachePath']) or isset($options['disableCache'])) {
			return;
		}
		
		$cacheKey=md5($stringOrFile);
		file_put_contents($options['cachePath'].DIRECTORY_SEPARATOR.$cacheKey, serialize($entry));
	}
	
	/**
	 * Parse given string.
	 * 
	 * @throws CI_Parser_Exception
	 * @return CI_Container
	 * @param string
	 * @param array Additional options
	 */
	public function parseString($string, array $options=array()) {
		if($this->tryLoadFromCache($string, $options)) {
			return $this;
		}
		
		$parser=$this->getParserFactory()
			->fabricateStringParser($this, $string, $options);
		$entry=$parser->parseString($string);
		
		$this->storeToCache($string, $options, $entry);
		
		$this->initCtx($entry);
		return $this;
	}
	
	/**
	 * Parse given file.
	 * 
	 * @throws CI_Parser_Exception
	 * @return CI_Container
	 * @param string
	 * @param array Additional options
	 */
	public function parseFile($file, array $options=array()) {
		if($this->tryLoadFromCache($file, $options)) {
			return $this;
		}
		
		$parser=$this->getParserFactory()
			->fabricateFileParser($this, $file, $options);
		$entry=$parser->parseFile($file);
		
		$this->storeToCache($file, $options, $entry);
		
		$this->initCtx($entry);
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