<?php
require_once realpath(dirname(__FILE__) . '/../../ModelTestCase.php');

class Application_Model_QueueTest extends ModelTestCase
{

    protected $model;

    protected $_queueData = array(
        'store_id' => 0,
        'user_id' => 0,
        'status' => 'processing',
        'extension_id' => 0,
        'parent_id' => 0,
        'server_id' => 1,
        'task' => 'RevisionInit',
        'task_params' => NULL,
        'retry_count' => 1,
        //'added_date' => '2114-01-01 00:00:00',
        'next_execution_time' => '2019-01-01 00:00:00'
    );

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->model = new Application_Model_Queue();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->model);
        parent::tearDown();
    }

    private function setStoreAndExtension()
    {
        $storeModel = new Application_Model_Store();
        $stores = $storeModel->fetchAll();
        if(sizeOf($stores) == 0)
        {
            $this->markTestIncomplete('No stores found to test StoreExtension model');
            return false;
        }
        $store = $stores[array_rand($stores)];
        $this->_queueData['store_id'] = $store->getId();

        $extensionModel = new Application_Model_Extension();
        $extension = $extensionModel->findByFilters(array('edition' => 'CE'));
        if($extension == null)
        {
            $this->markTestIncomplete('No extensions found to test StoreExtension model');
            return false;
        }
        $this->_queueData['extension_id'] = $extension->getId();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Application_Model_Queue', $this->model);
    }

    public function testSave()
    {
        $queue = new Application_Model_Queue();
        if($this->setStoreAndExtension() === false) return ;
        $queue->setOptions($this->_queueData);

        try{
            $queue->save();
            $this->assertGreaterThan(0, (int)$queue->getId(), 'Application_Model_Queue::save() failed. ID not set after trying to save!');
        }catch(DatabseException $e){
            $this->markTestIncomplete('Database error when trying to save model Application_Model_Queue::save(): '.$e->getMessage());
        }
    }

    /**
     * @depends testSave
     */
    public function testUpdate()
    {
        $queue = new Application_Model_Queue();
        if($this->setStoreAndExtension() === false) return ;
        $queue->setOptions($this->_queueData);
        $queue->save();

        $queue->setRetryCount('99');
        try{
            $queue->save();
        }catch(DatabseException $e){
            $this->markTestIncomplete('Database error when trying to update model Application_Model_Queue::save(): '.$e->getMessage());
        }
    }


    public function testSetOptions()
    {
        $data = $this->_queueData;

        $queue = new Application_Model_Queue();
        $queue->setOptions($data);

        $filter = new Zend_Filter_Word_UnderscoreToCamelCase();
        $methods = get_class_methods($queue);

        foreach($data as $key => $value){
            $method = 'get' . $filter->filter($key);
            if (in_array($method, $methods)) {
                $this->assertEquals($value,$queue->$method());
            }
        }
        unset($queue);
    }

    /**
     * @depends testSetOptions
     */
    public function testToArray()
    {
        $data = $this->_queueData;

        $queue = new Application_Model_Queue();
        $queue->setOptions($data);

        $exportData = $queue->__toArray();

        unset($exportData['id']);

        $this->assertModelArray($data,$exportData);
        unset($queue);
    }

    public function testFetchAll()
    {
        $queue = new Application_Model_Queue();
        if($this->setStoreAndExtension() === false) return ;
        $queue->setOptions($this->_queueData);
        $queue->save();
        $queues = $queue->fetchAll();

        $this->assertGreaterThan(0,sizeof($queues),'Application_Model_Queue::fetchAll() failed. Returned size is 0');

        $counter = 0;
        foreach($queues as $queue){
            if($counter > $this->_fetchAllBreaker) break;
            $counter++;

            $this->assertInstanceOf('Application_Model_Queue', $queue);
        }
    }

    /**
     * @depends testSave
     */
    public function testFind()
    {
        $queue = new Application_Model_Queue();
        if($this->setStoreAndExtension() === false) return ;
        $queue->setOptions($this->_queueData);
        $queue->save();

        $queueId = $queue->getId();

        $find =  new Application_Model_Queue();
        $find = $find->find($queueId);
        $this->assertNotNull($find->getId(),'Application_Model_Queue::find('.$queueId.') failed.');
    }
    
    /**
     * @depends testSave
     */
    public function testDelete()
    {
        $queue = new Application_Model_Queue();
        if($this->setStoreAndExtension() === false) return ;
        $queue->setOptions($this->_queueData);
        $queue->save();

        $queueId = $queue->getId();

        $queue->delete($queueId);

        $find =  new Application_Model_Queue();
        $find = $find->find($queueId);
        $this->assertNull($find->getId(),'Application_Model_Queue::delete(\'`id` = '.$queueId.'\') failed.');
    }
}
