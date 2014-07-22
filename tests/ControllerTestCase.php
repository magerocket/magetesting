<?php
require_once 'Zend/Test/PHPUnit/ControllerTestCase.php';
require_once 'Braintree/Braintree.php';

require_once 'Zend/Db.php';
require_once 'Zend/Config/Ini.php';
require_once 'Zend/Test/PHPUnit/Db/Connection.php';
require_once 'Zend/Test/PHPUnit/Db/SimpleTester.php';
/**
 * Abstract Class for Test Controllers
 * Define setUp method
 */
abstract class ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase
{
    public $bootstrap = null;
    protected $_disposables = array();
    protected $_db;

    public function setUp()
    {   
        //$this->setupDatabase();
        $this->bootstrap = new Zend_Application(
            'testing',
            APPLICATION_PATH . '/configs/application.ini'
        );

        parent::setUp();
        $this->_db = $this->bootstrap->getBootstrap()->getResource('db');
        $this->_db->beginTransaction();
    }


    public function createFakeUser()
    {
        $userData = array(
            'login' => 'standard-user',
            'password' => 'standard-user',
            'email' => 'no-replay@rocketweb.com',
            'firstname' => 'Standard',
            'lastname' => 'User',
            'status' => 'active',
            'group' => 'admin'

        );

        $user = new Application_Model_User();
        $user->setOptions($userData);
        $user->save();
    }

    public function loginUser($user, $password)
    {
        $this->request
             ->setMethod('POST')
             ->setPost(array(
                'login'    => $user,
                'password' => $password,
            )
        );
        $this->dispatch('/user/login');


        $this->assertRedirectTo('/user/dashboard');
        
        $this->resetRequest()->resetResponse();
        
        $layout = Zend_Controller_Action_HelperBroker::getStaticHelper('layout');
        $layout->enableLayout();
    }


    protected function tearDown()


    {
        parent::tearDown();
        if($this->_db != null) $this->_db->rollback();
    }
//    public function setupDatabase()
//    {
//        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'testing');
//        
//        $db = Zend_Db::factory('PDO_MYSQL', $config->resources->db->params);
//        $connection = new Zend_Test_PHPUnit_Db_Connection($db, 'magetesting');
//        $databaseTester = new Zend_Test_PHPUnit_Db_SimpleTester($connection);
//
//        $databaseFixture =
//            new PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet(
//                dirname(__FILE__) . '/_files/initialUserFixture.xml'
//        );
//
//        $databaseTester->setupDatabase($databaseFixture);
//    }
}
