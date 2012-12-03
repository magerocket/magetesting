<?php

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-11-15 at 09:39:08.
 */
class Application_Model_EditionTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Application_Model_Edition
     */
    protected $model;
    
    /**
     * @var int 
     */
    public $id = 1;
    
    /**
     * @var string 
     */
    public $key = '123';
    
    /**
     * @var string 
     */
    public $name = 'test_name';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->model = new Application_Model_Edition;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
         unset($this->model);
    }
    
    public function testEditionModel() {
        $this->assertType('Application_Model_Edition', $this->model);
    }

    /**
     * @covers Application_Model_Edition::setOptions
     */
    public function testSetOptions() {
        $this->model->setOptions(array(
            'id'   => $this->id,
            'key'  => $this->key,
            'name' => $this->name
        ));
        
        $this->assertEquals($this->id, $this->model->getId());
        $this->assertEquals($this->key, $this->model->getKey());
        $this->assertEquals($this->name, $this->model->getName());
    }
    
    /**
     * @covers Application_Model_Edition::__toArray
     */
    public function testToArray() {
        $this->model->setOptions(array(
            'id'   => $this->id,
            'key'  => $this->key,
            'name' => $this->name
        ));
        
        $array = $this->model->__toArray();
        
        $this->assertType('array', $array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('key', $array);
        $this->assertArrayHasKey('name', $array);
    }

}