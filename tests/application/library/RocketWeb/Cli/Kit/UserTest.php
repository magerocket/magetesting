<?php

class RocketWeg_Cli_Kit_UserTest extends PHPUnit_Framework_TestCase
{
    protected $_kit;
    public function setUp()
    {
        $cli = new RocketWeb_Cli();
        $this->_kit = $cli->kit('user');
    }

    public function tearDown()
    {
        unset($this->_kit);
    }

    protected function _scriptPath()
    {
        return APPLICATION_PATH."/../scripts/worker";
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('RocketWeb_Cli_Kit_User', $this->_kit);
    }

    public function testInit()
    {
        $this->assertEquals(
            "sh '".$this->_scriptPath()."/create_user.sh' 'login' 'pass' 'salt_hash' '/home/login_dir' 2>&1",
            $this->_kit->_prepareCall($this->_kit->create('login', 'pass', 'salt_hash', '/home/login_dir'))
        );
    }

    public function testDelete()
    {
        $this->assertEquals(
            "sh '".$this->_scriptPath()."/remove_user.sh' 'login' 2>&1",
            $this->_kit->_prepareCall($this->_kit->delete('login'))
        );
    }

    public function testRebuildPhpMyAdmin()
    {
        $this->assertEquals(
            "sh '".$this->_scriptPath()."/phpmyadmin-user-rebuild.sh' \"whole denied list of users\" 2>&1",
            $this->_kit->_prepareCall($this->_kit->rebuildPhpMyAdmin('whole denied list of users'))
        );
    }
}