<?php

/**
 * CI_Parser_Interface
 * 
 * @author Martin Kuckert
 * @copyright Copyright (c) 2009 Martin Kuckert
 * @license New BSD License
 * @package CI.Parser
 * @since 28.08.2009
 */
interface CI_Parser_Interface {
	
	/**
	 * @throws CI_Parser_Exception
	 * @param CI_Container
	 */
	public function __construct(CI_Container $container);
	
	/**
	 * @throws CI_Parser_Exception
	 * @return array Contextdefinitions
	 * @param string Path to the file to parse
	 */
	public function parseFile($file);
	
	/**
	 * @throws CI_Parser_Exception
	 * @return array Contextdefinitions
	 * @param string String to parse
	 */
	public function parseString($string);
	
}