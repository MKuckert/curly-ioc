<?php

/**
 * CI_Parser_Factory
 * 
 * Creates parser instances from a given input.
 * 
 * @author Martin Kuckert
 * @copyright Copyright (c) 2009 Martin Kuckert
 * @license New BSD License
 * @package CI.Parser
 * @since 09.09.2009
 */
class CI_Parser_Factory {
	
	/**#@+
	 * @desc Known parser types
	 */
	const TYPE_XML='xml';
	const TYPE_UNKNOWN='unknown';
	/**#@-*/
	
	/**
	 * @var array List of known parser types for file extensions.
	 */
	private $_fileExtensionTypes=array(
		'xml'	=> self::TYPE_XML
	);
	
	/**
	 * Tries to create a parser for the given string.
	 * 
	 * @throws CI_Parser_Exception If no parser can be found.
	 * @return CI_Parser_Interface
	 * @param CI_Container
	 * @param string
	 * @param array Additional options
	 */
	public function fabricateStringParser(CI_Container $container, $string, array $options=array()) {
		// No typehint given
		if(!isset($options['typeHint'])) {
			$type=$this->determineTypeByString($string);
		}
		else {
			$type=$options['typeHint'];
		}
		
		return $this->fabricateParserByType($container, $type);
	}
	
	/**
	 * Tries to create a parser for the given filepath.
	 * 
	 * @throws CI_Parser_Exception If no parser can be found.
	 * @return CI_Parser_Interface
	 * @param CI_Container
	 * @param string
	 * @param array Additional options
	 */
	public function fabricateFileParser(CI_Container $container, $filepath, array $options=array()) {
		// No typehint given
		if(!isset($options['typeHint'])) {
			$type=$this->determineTypeByFile($filepath);
		}
		else {
			$type=$options['typeHint'];
		}
		
		return $this->fabricateParserByType($container, $type);
	}
	
	/**
	 * Tries to create a parser for the given type.
	 * 
	 * @throws CI_Parser_Exception If no parser can be found.
	 * @return CI_Parser_Interface
	 * @param CI_Container
	 * @param string
	 */
	public function fabricateParserByType(CI_Container $container, $type) {
		switch($type) {
			case self::TYPE_XML:
				return new CI_Parser_Xml($container);
			case self::TYPE_UNKNOWN:
			default:
				throw new CI_Parser_Exception('Unable to create a string parser for type '.$type.'. Either type determination failed or the typehint was not really helpful');
		}
	}
	
	/**
	 * Tries to determine the type of the given string.
	 * 
	 * @throws CI_Parser_Exception
	 * @return string
	 * @param string
	 */
	protected function determineTypeByString($string) {
		if(substr($string, 0, 5)==='<?xml') {
			return self::TYPE_XML;
		}
		else {
			return self::TYPE_UNKNOWN;
		}
	}
	
	/**
	 * Tries to determine the type of the given filepath.
	 * 
	 * @throws CI_Parser_Exception
	 * @return string
	 * @param string
	 */
	protected function determineTypeByFile($filepath) {
		// Determine by file extension
		$extension=pathinfo($filepath, PATHINFO_EXTENSION);
		if(isset($this->_fileExtensionTypes[$extension])) {
			return $this->_fileExtensionTypes[$extension];
		}
		
		// We have to look into the file
		$handle=fopen($filepath, 'rb');
		if($handle===false) {
			throw new CI_Parser_Exception('Unable to open file '.$filepath.' for reading to determine it´s type');
		}
		
		$read=fread($handle, 5); // Adjust the 5 bytes, if new parser types comes available and requires more to detect
		if($read===false) {
			throw new CI_Parser_Exception('Failed to read from the file '.$filepath);
		}
		
		return $this->determineTypeByString($read);
	}
	
}
