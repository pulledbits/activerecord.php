<?php
namespace ActiveRecord;

class PrototypeTest extends \PHPUnit_Framework_TestCase
{
	public function testPrototypeWritesToStream()
	{
		$class = new PHPClass('PersonRecord');
		$prototype = new Prototype($class);
		$prototype->addProperty('name');
		
		$stream = fopen('php://memory', 'wb');
		$prototype->writeOut($stream);
		fseek($stream, 0);
		
		$this->assertEquals($class->generate(), fread($stream, 512));
	}
	
}