<?php

class TestObj1 {
	public $ctrArgs;
	public $propArg1;
	public $prop2;
	public $propArg3;
	
	public function __construct() {
		$this->ctrArgs=func_get_args();
	}
	public function setProp1($value) {
		$this->propArg1=$value;
	}
	public function setProp3($value) {
		$this->propArg3=$value;
	}
}
class TestObj2 {
	private $refObj=NULL;
	public function getRefObj() {
		return $this->refObj;
	}
	public function setRefObj(TestObj1 $o) {
		$this->refObj=$o;
	}
}
class TestObj3 {
	private $refObj=NULL;
	public function getRefObj() {
		return $this->refObj;
	}
	public function setRefObj(TestObj3 $o) {
		$this->refObj=$o;
	}
}
class TestObj4 {
	static public $createdInstance=NULL;
	public function __construct() {
		self::$createdInstance=$this;
	}
}