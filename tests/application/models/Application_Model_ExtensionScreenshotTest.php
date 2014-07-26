<?php
require_once realpath(dirname(__FILE__) . '/../../ModelTestCase.php');

class Application_Model_ExtensionScreenshotTest extends ModelTestCase
{

    protected $model;

    protected $_extensionScreenshotData = array(
        'extension_id' => 13,
        'image' => '01_custom_stock_display_new_rule-13.png'
    );


    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->model = new Application_Model_ExtensionScreenshot();
        $this->assertInstanceOf('Application_Model_ExtensionScreenshot', $this->model);

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

    protected function setExtension()
    {
        $extensionModel = new Application_Model_Extension();
        $extension = $extensionModel->findByFilters(array('edition' => 'CE'));
        if($extension == null)
        {
            $this->markTestIncomplete('No stores found to test LogReindexer model');
            return false;
        }
        $this->_extensionScreenshotData['extension_id'] = $extension->getId();
    }

    private function savedObject(Application_Model_ExtensionScreenshot $extensionScreenshot){
        $allExtensionScreenshots = $extensionScreenshot->fetchByExtensionId($this->_extensionScreenshotData['extension_id']);
        $lastExtensionScreenshot = null;
        foreach($allExtensionScreenshots as $p){
            if($lastExtensionScreenshot == null) $lastExtensionScreenshot = $p;
            if($p->getId() > $lastExtensionScreenshot->getId()) $lastExtensionScreenshot = $p;
        }

        return $lastExtensionScreenshot;
    }

    public function testSave()
    {
        if($this->setExtension() === false) return ;

        $extensionScreenshot = new Application_Model_ExtensionScreenshot();
        $extensionScreenshot->setOptions($this->_extensionScreenshotData);

        try{
            $extensionScreenshot->save();
            $extensionScreenshot = $this->savedObject($extensionScreenshot);
            $this->assertGreaterThan(0, (int)$extensionScreenshot->getId(), 'Application_Model_ExtensionScreenshot::save() failed. ID not set after trying to save!');
        }catch(DatabseException $e){
            $this->markTestIncomplete('Database error when trying to save model Application_Model_ExtensionScreenshot::save(): '.$e->getMessage());
        }
    }

    /**
     * @depends testSave
     */
    public function testUpdate()
    {
        if($this->setExtension() === false) return ;

        $extensionScreenshot = new Application_Model_ExtensionScreenshot();
        $extensionScreenshot->setOptions($this->_extensionScreenshotData);
        $extensionScreenshot->save();
        $extensionScreenshot = $this->savedObject($extensionScreenshot);

        $extensionScreenshot->setImage('01_custom_stock_display_new_rule-14.png');
        try{
            $extensionScreenshot->save();
        }catch(DatabseException $e){
            $this->markTestIncomplete('Database error when trying to update model Application_Model_ExtensionScreenshot::save(): '.$e->getMessage());
        }
    }


    public function testSetOptions()
    {
        $data = $this->_extensionScreenshotData;

        $extensionScreenshot = new Application_Model_ExtensionScreenshot();
        $extensionScreenshot->setOptions($data);

        $filter = new Zend_Filter_Word_UnderscoreToCamelCase();
        $methods = get_class_methods($extensionScreenshot);

        foreach($data as $key => $value){
            $method = 'get' . $filter->filter($key);
            if (in_array($method, $methods)) {
                $this->assertEquals($value,$extensionScreenshot->$method());
            }
        }
        unset($extensionScreenshot);
    }

    /**
     * @depends testSetOptions
     */
    public function testToArray()
    {
        $data = $this->_extensionScreenshotData;

        $extensionScreenshot = new Application_Model_ExtensionScreenshot();
        $extensionScreenshot->setOptions($data);

        $exportData = $extensionScreenshot->__toArray();

        unset($exportData['id']);

        $this->assertModelArray($data,$exportData);
        unset($extensionScreenshot);
    }

    /**
     * @depends testSave
     */
    public function testDelete()
    {
        if($this->setExtension() === false) return ;

        $extensionScreenshot = new Application_Model_ExtensionScreenshot();
        $extensionScreenshot->setOptions($this->_extensionScreenshotData);
        $extensionScreenshot->save();
        $extensionScreenshot = $this->savedObject($extensionScreenshot);

        $extensionScreenshotId = $extensionScreenshot->getId();

        $extensionScreenshot->delete($extensionScreenshotId);

        $find =  new Application_Model_ExtensionScreenshot();
        $find = $find->fetchByExtensionId($this->_extensionScreenshotData['extension_id']);
        foreach($find as $f){
            $this->assertNotEquals($f->getId(),$extensionScreenshotId,'Application_Model_ExtensionScreenshot::delete(\'`id` = '.$extensionScreenshotId.'\') failed.');
        }
    }
}
