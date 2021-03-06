<?php

class ExtensionController extends Integration_Controller_Action {

    protected $_tempDir;
    protected $_gridController;
    protected $_controllerUser;

    public function init() {
        $this->_tempDir = rtrim(APPLICATION_PATH, '/').'/../public/assets/img/temp/';
        /* Initialize action controller here */
        $this->_helper->sslSwitch();
        $page_type = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
        $this->_gridController = ('my-extensions' == $page_type ? $page_type : 'extension');
        $this->_controllerUser = 'admin';
    }

    public function indexAction() {
        # display grid of all supported extensions
        /* index action is only alias for list extensions action */
        $this->listAction();
    }

    public function listAction() {
        $extensionModel = new Application_Model_Extension();

        $request = $this->getRequest();
        $filter = $request->getParam('filter', array());
        if(!is_array($filter)) {
            $filter = array();
        }
        $order = $request->getParam('order', array());
        if(!is_array($order)) {
            $order = array();
        }
        $offset = $request->getParam('offset', 0);
        $offset = !is_numeric($offset) ? 0 : (int)$offset;
        $limit = 50;

        $this->view->extensions = $extensionModel->fetchFullListOfExtensions($filter, $order, $offset, $limit);
        $this->view->extensions_counter = $extensionModel->getMapper()->fetchFullListOfExtensions($filter, $order, $offset, $limit, true);

        $extensionCategoryModel = new Application_Model_ExtensionCategory();
        $this->view->categories = $extensionCategoryModel->fetchAll();

        if($request->isPost() && $request->isXmlHttpRequest()) {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $this->getResponse()->setBody(
                json_encode(
                    array(
                        'count' => $this->view->extensions_counter,
                        'tiles' => $this->view->render('extension/tiles.phtml')
                    )
                )
            );
        } else {
            $this->renderScript('extension/list.phtml');
        }
    }

    public function addAction()
    {
        /* add and edit actions should have the same logic */
        $this->editAction('Add');
    }
    
    /**
     * Render extension form
     * 
     * @param string $action (Add or Edit)
     * @return mixed
     */
    public function editAction($action = 'Edit')
    {
        $id = (int) $this->_getParam('id', 0);



        if(($cancel = (int)$this->_getParam('cancel', 0)) AND $cancel) {
            return $this->_helper->redirector->gotoRoute(array(
                'module'     => 'default',
                'controller' => $this->_gridController,
                'action'     => 'index',
            ), 'default', true);
        }
        
        $extension_data = array(
            'title'           => $this->_getParam('title', ''),
            'extension_key'   => $this->_getParam('extension_key', ''),
            'version'         => $this->_getParam('version', ''),
            'edition'         => $this->_getParam('edition', ''),
            'from_version'    => $this->_getParam('from_version', ''),
            'to_version'      => $this->_getParam('to_version') == '' ? null : $this->_getParam('to_version', ''),
            'description'     => $this->_getParam('description', ''),
            'price'           => $this->_getParam('price', ''),
            'logo'            => $this->_getParam('logo', ''),
            'screenshots'     => $this->_getParam('screenshots', array()),
            'directory_hash'  => $this->_getParam('directory_hash', time().'-'.uniqid()),
            'category_id'     => $this->_getParam('category_id', ''),
            'is_visible'      => $this->_getParam('is_visible', ''),
            'extension_owner' => $this->_getParam('extension_owner',0),
            'author'          => $this->_getParam('author', ''),
            'sort'            => $this->_getParam('sort', ''),
            'extension_detail'        => $this->_getParam('extension_detail', ''),
            'extension_documentation' => $this->_getParam('extension_documentation', ''),
        );

        $name = 'Application_Form_Extension'.$action;
        $form = new $name;
        $success_message = 'Extension has been added properly.';
        
        $extension = new Application_Model_Extension();
        $extension_entity_data = array();
        $screenshots = array();
        $screenshots_ids = array();

        $cat_model = new Application_Model_ExtensionCategory();
        $extension_categories = array();
        foreach($cat_model->fetchAll() as $category) {
            $extension_categories[$category->getId()] = $category->getName();
        }
        $form->category_id->addValidator(
            new Zend_Validate_InArray(array_keys($extension_categories))
        );

        $form->category_id->addMultiOptions($extension_categories);

        $extension_owners = array();
        $owners = new Application_Model_User();
        foreach($owners->fetchAll() as $owner){
            $extension_owners[$owner->getId()] = $owner->getLogin();
        }
        $form->extension_owner->addValidator(
            new Zend_Validate_InArray(array_keys($extension_owners))
        );
        $form->extension_owner->addMultiOptions($extension_owners);


        $verModel = new Application_Model_Version();
        
        $this->view->versionCe = $verModel->getAllForEdition('CE');
        $this->view->versionPe = $verModel->getAllForEdition('PE');
        $this->view->versionEe = $verModel->getAllForEdition('EE');
        
        $versions = array();
        foreach($verModel->fetchAll() as $version) {
            $versions[$version->getVersion()] = $version->getVersion();
        }
        
        $form->from_version->addMultiOptions($versions);
        $form->from_version->addValidator(
            new Zend_Validate_InArray(array_keys($versions))
        );
        
        if ($extension_data['to_version'] !== null) {
            $form->to_version->addMultiOptions($versions);
            $form->to_version->addValidator(
                new Zend_Validate_InArray(array_keys($versions))
            );
        }
        $editionModel = new Application_Model_Edition();
        $editions = array();
        foreach($editionModel->fetchAll() as $edition) {
            $editions[$edition->getKey()] = $edition->getKey();
        }
        
        $form->edition->addMultiOptions($editions);
        $form->edition->addValidator(
            new Zend_Validate_InArray(array_keys($editions))
        );
        
        /* $id > 0 AND extension found in database */
        $noExtension = false;
        if($id) {
            $extension = $extension->find($id);
            if($extension->getId()) {
                $this->view->extension_file = $extension->getExtension();
                $this->view->extension_encoded_file = $extension->getExtensionEncoded();
                
                foreach($extension->fetchScreenshots() as $row) {
                    $screenshots_ids[] = $row->getId();
                    $screenshots[] = $row->getImage();
                }
                $extension_entity_data = array(
                    'title'           => $extension->getName(),
                    'extension_key'   => $extension->getExtensionKey(),
                    'description'     => $extension->getDescription(),
                    'version'         => $extension->getVersion(),
                    'edition'         => $extension->getEdition(),
                    'from_version'    => $extension->getFromVersion(),
                    'to_version'      => $extension->getToVersion(),
                    'price'           => $extension->getPrice(),
                    'logo'            => $extension->getLogo(),
                    'screenshots'     => $screenshots,
                    'author'          => $extension->getAuthor(),
                    'is_visible'      => $extension->getIsVisible(),
                    'extension_owner' => $extension->getExtensionOwner(),
                    'category_id'     => $extension->getCategoryId(),
                    'sort'            => $extension->getSort(),
                    'extension_detail'        => $extension->getExtensionDetail(),
                    'extension_documentation' => $extension->getExtensionDocumentation(),
                );

                $success_message = 'Extension has been changed properly.';
            } else {
                $noExtension = true;
            }
        } elseif(!$action == 'Add') {
            $noExtension = true;
        }

        $this->view->extension = $extension;
        $this->view->tempDir = $this->_tempDir;
        $this->view->directoryHash = $extension_data['directory_hash'];

        if($noExtension) {
            $this->_helper->FlashMessenger(array('type' => 'error', 'message' => 'Extension with given id, does not exist.'));
            return $this->_helper->redirector->gotoRoute(array(
                    'module'     => 'default',
                    'controller' => $this->_gridController,
                    'action'     => 'index',
            ), 'default', true);
        }

        if ($this->_request->isPost()) {

            $extension_data['screenshots'] = $this->_getParam('screenshots', array());
            $extension_data['screenshots_ids'] = $this->_getParam('screenshots_ids', array());

            $this->view->logo = $this->_getParam('logo', '');

            $formData = $this->_request->getPost();
            $formData['to_version'] = $this->_getParam('to_version') == '' ? null : $this->_getParam('to_version', '');
            if($form->isValid($formData)) {
                $old_logo = $extension->getLogo();
                $new_logo = $this->_getParam('logo', '');

                $formData['name'] = $formData['title'];
                if($extension->getId()) {
                    unset($formData['logo']);
                }

                $extension_new_name = (isset($_FILES["extension_file"]) && $_FILES["extension_file"]["name"] ? $_FILES["extension_file"]["name"] : '');

                $errors = false;

                $adapter = new Zend_File_Transfer_Adapter_Http();
                
                if($extension_new_name) {

                    /*
                     * First we check if an extension with the same name already exists and the extension owner is the same!
                     * */
                    $extension_owner_model = new Application_Model_Extension();
                    $extension_new_name_model = $extension_owner_model->findByExtensionFileName($extension_new_name);
                    if((
                            Zend_Auth::getInstance()->getIdentity()->group != 'admin' &&
                            $extension_new_name_model != null &&
                            $extension_new_name_model->getExtensionOwner() != Zend_Auth::getInstance()->getIdentity()->id
                        ) ||
                        (
                            Zend_Auth::getInstance()->getIdentity()->group == 'admin' &&
                            $extension_new_name_model != null &&
                            $extension_new_name_model->getId() != $id 
                        )
                    ){
                        if($extension_entity_data['id'] != $extension_new_name_model->getId()){
                            $this->_helper->FlashMessenger(
                                array(
                                    'type' => 'error',
                                    'message' => 'A file with the same name already exists under a different extension!'
                                )
                            );
                            $this->_redirectDefault();
                        }
                    }



                    $dir = APPLICATION_PATH.'/../data/extensions/'.$formData['edition'].'/open/';
                    if(!file_exists($dir)) {
                        @mkdir($dir, 0777, true);
                    }

                    try {
                        $adapter->setDestination($dir);
                    } catch (Zend_File_Transfer_Exception $e) {
                        $this->_helper->FlashMessenger(
                            array(
                                'type' => 'error',
                                'message' => $e->getMessage() . ' ' . $dir
                            )
                        );
                        $errors = true;
                    }

                    $adapter->receive('extension_file');

                    if($extension->getExtension() AND $extension->getExtension() != $extension_new_name) {
                        $file_to_delete = APPLICATION_PATH.'/../data/extensions/'.$extension->getEdition().'/open/'.$extension->getExtension();
                        if(file_exists($file_to_delete)) {
                            @unlink($file_to_delete);
                        }
                    }
                    $extension->setExtension($extension_new_name);

                    // encode extension file using ioncube
                    if ($extension->getPrice() > 0) {
                        $file_to_delete = APPLICATION_PATH.'/../data/extensions/'.$extension->getEdition().'/encoded/'.$extension->getExtensionEncoded();
                        if(file_exists($file_to_delete)) {
                            @unlink($file_to_delete);
                        }

                        try {
                            $config = Zend_Registry::get('config');

                            $ioncube = new Application_Model_Ioncube_Encode_Extension();
                            $ioncube->setup(
                                $extension,
                                $config
                                /*,$this->cli()->getLogger()*/
                            );
                            $extensionEncodedNewName = $ioncube->process();

                            $extension->setExtensionEncoded($extensionEncodedNewName);

                        } catch (Application_Model_Ioncube_Encode_Extension_Exception $e) {

                            $this->_helper->FlashMessenger(
                                array(
                                    'type' => 'error',
                                    'message' => $e->getMessage()
                                )
                            );
                            $errors = true;
                        } catch (Exception $e) {
                            $this->getLog()->log('Encoding extension failed.', Zend_Log::ERR, $e);

                            $this->_helper->FlashMessenger(
                                array(
                                    'type' => 'error',
                                    'message' => 'Encoding extension failed. Please contact with administrator.'
                                )
                            );
                            $errors = true;
                        }
                    }
                }

                if(!$errors) {
                    try {
                        $extension->setOptions($formData);
                        $extension->save();
                        $extension_id = $extension->getId();

                        if($extension_entity_data['extension_owner'] != $formData['extension_owner']) {
                            $extensionModel = new Application_Model_Extension();
                            $extensionsWithSameKey = $extensionModel->findByExtensionKeyAndEdition($formData['extension_key'],$formData['edition']);
                            foreach($extensionsWithSameKey as $ewsn) {
                                try{
                                    $ewsn->setExtensionOwner($formData['extension_owner']);
                                    $ewsn->save();
                                }catch(Exception $e){

                                }
                            }
                        }
                        
                        if($this->_getParam('remove_logo', null)) {
                            @unlink($this->view->ImagePath($old_logo, 'extension/logo'));
                            @unlink(
                                $this
                                    ->view
                                    ->getHelper('Thumbnail')
                                    ->getThumbnailPath(
                                        $old_logo,
                                        $this->view->ImagePath($old_logo, 'extension/logo', false, false),
                                        null,
                                        110
                                    )
                            );
                            @unlink(
                                $this
                                    ->view
                                    ->getHelper('Thumbnail')
                                    ->getThumbnailPath(
                                        $old_logo,
                                        $this->view->ImagePath($old_logo, 'extension/logo', false, false),
                                        null,
                                        38
                                    )
                            );
                            $extension->setLogo(NULL);
                            $oldLogoRemoved = true;
                        }
                        
                        if($old_logo != $new_logo) {
                            if($old_logo && !$oldLogoRemoved) {
                                @unlink($this->view->ImagePath($old_logo, 'extension/logo'));
                            }
                            if($new_logo) {
                                $new_file_name = $this->view->NiceString(substr_replace($new_logo, '-'.$extension_id, strrpos($new_logo, '.'), 0));
                                $new_path = $this->view->ImagePath($new_file_name, 'extension/logo', true, false);
                                if(!file_exists($new_path)) {
                                    @mkdir($new_path, 0777, true);
                                }
                                @copy($this->_tempDir.$this->view->directoryHash.'/'.$new_logo, $new_path.$new_file_name);
                                $extension->setLogo($new_file_name);
                            }
                        }
                        $this->_saveImages($extension_id);
                        $extension->save();

                        $this->_helper->FlashMessenger($success_message);
                        return $this->_helper->redirector->gotoRoute(array(
                                'module'     => 'default',
                                'controller' => $this->_gridController,
                                'action'     => 'index',
                        ), 'default', true);
                    } catch(Zend_Db_Exception $e) {
                        if(stristr($e->getMessage(), 'extension_release')) {
                            $form->version->addErrors(array('Version "' . $formData['version'] . '" for that extension already exists.'))
                                 ->markAsError();
                        } else {
                            $this->_helper->FlashMessenger(array('type' => 'error', 'message' => 'Unknown error: '.$e->getMessage()));
                            return $this->_helper->redirector->gotoRoute(array(
                                    'module'     => 'default',
                                    'controller' => $this->_gridController,
                                    'action'     => 'index',
                            ), 'default', true);
                        }
                    }
                } else {
                    return $this->_helper->redirector->gotoRoute(array(
                            'module'     => 'default',
                            'controller' => $this->_gridController,
                            'action'     => 'index',
                    ), 'default', true);
                }
            }
        } else {
            $extension_data = array_merge($extension_data, $extension_entity_data);
            $this->view->old_logo = $extension->getLogo();
            $this->view->logo = $this->view->old_logo;
        }

        $form->populate($extension_data);

        $this->view->screenshots = $extension_data['screenshots'];
        $this->view->screenshots_ids = $screenshots_ids;
        $this->view->form = $form;
        $this->view->headScript()->appendFile('/public/js/extension-edit.js', 'text/javascript');
    
    }
    
    public function deleteAction()
    {
        // array with redirect to grid page
        $redirect = array(
                'module'      => 'default',
                'controller' => $this->_gridController,
                'action'      => 'index'
        );

        // init form object
        $form = new Application_Form_ExtensionDelete();

        // shorten request
        $request = $this->getRequest();

        // if request is without proper id param
        // redirect to grid with information message 
        if(((int)$request->getParam('id', 0)) == 0) {
            // set message
            $this->_helper->FlashMessenger(
                array(
                    'type' => 'error',
                    'message' => 'You cannot delete extension with specified id.'
                )
            );
            // redirect to grid
            return $this->_helper->redirector->gotoRoute(
                    $redirect, 'default', true
            );
        }

        if($request->isPost()) {
            // has post data and sent data is valid
            if($form->isValid($request->getParams())) {
                // someone agreed deletion 
                if($request->getParam('confirm') == '1') {
                    $flash_message = array(
                        'type' => 'success',
                        'message' => 'You have deleted extension successfully.'
                    );
                    $extension = new Application_Model_Extension();
                    // set news id to the one passed by get param
                    try {
                        $extension->find($request->getParam('id'));
                        if($extension->getName()) {
                            $duplicates = $extension->fetchDuplicatedFilesCount((string)$extension->getExtension(), (string)$extension->getExtensionEncoded());
                            $extensions_path = APPLICATION_PATH . '/data/extensions/' . strtoupper($extension->getEdition());
                            if(1 === (int)$duplicates['open']) {
                                $file_path = $extensions_path . '/open/' . $extension->getExtension();
                                if(file_exists($file_path)) {
                                    unlink($file_path);
                                }
                            }
                            if($extension->getExtensionEncoded() && 1 === (int)$duplicates['encoded']) {
                            $file_path = $extensions_path . '/encoded/' . $extension->getExtensionEncoded();
                                if(file_exists($file_path)) {
                                    unlink($file_path);
                                }
                            }
                            $extension->delete($request->getParam('id'));
                        }
                    } catch(Exception $e) {
                        $this->getLog()->log('Admin - extension delete', Zend_Log::ERR, $e->getMessage());
                        $flash_message = array(
                            'type' => 'error',
                            'from_scratch' => 1,
                            'message' => 'Extension couldn’t be deleted as it is currently installed in one of the stores.<span class="hidden">'.$e->getMessage().'</span>'
                        );
                    }
                    // set message
                    $this->_helper->FlashMessenger(
                        $flash_message
                    );
                } else {
                    // deletion cancelled
                    // set message
                    $this->_helper->FlashMessenger(
                        array(
                            'type' => 'notice',
                            'message' => 'Extension deletion cancelled.'
                        )
                    );
                }
                // redirect to grid if request is withou ajax
                return $this->_helper->redirector->gotoRoute(
                    $redirect, 'default', true
                );
            }
        }

        $this->view->form = $form;
    }

    public function listVersionsAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $response = new stdClass();
        $response->status = 'error';
        $response->message = '';

        $id = (int)$this->_getParam('id', 0);

        if($id) {
            $extension = new Application_Model_Extension();
            $extension = $extension->find($id);
            if($extension->getName()) {
                $response->status = 'ok';
                $versions = $extension->findByExtensionKeyAndEdition($extension->getExtensionKey(), $extension->getEdition());
                foreach($versions as $version) {
                    $actions = '<a href="' . $this->view->url(array('controller' => $this->_gridController, 'action' => 'edit', 'id' => $version->getId()), 'default', true) . '" class="btn btn-success"><i class="icon-white icon-pencil"></i>&nbsp;Edit</a>';
                    $actions .= '<a href="#extension-deletion" data-toggle="modal" class="extension-delete btn btn-danger" data-version-id="' . $version->getId() . '" data-dismiss="modal"><i class="icon-white icon-trash"></i>&nbsp;Delete</a>';
                    $files = '';
                    if($version->getExtension()) {
                        $files .= '<div class="label label-success">' . $version->getExtension() . '</div><br />';
                    }
                    if($version->getExtensionEncoded()) {
                        $files .= '<div class="label label-important">' . $version->getExtensionEncoded() . '</div><br />';
                    }
                    $response->message .= '<tr><td>' . $version->getVersion() . '</td><td>' . $files . '</td><td class="nowrap">' . $actions . '</td></tr>';
                }
            } else {
                $response->message = 'Specified extension does not exist.';
            }
        } else {
            $response->message = 'Wrong specified extension id.';
        }

        $this->getResponse()->setBody(json_encode($response));
    }
    
    public function addVersionToExtensionAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $redirect = array(
                'module'      => 'default',
                'controller' => $this->_gridController,
                'action'      => 'index'
        );
        $request = $this->getRequest();
        $extension_id = (int)$request->getParam('id', 0);
        $extension_version = $request->getParam('version', '');
        if($request->isPost()) {
            if($extension_id && $extension_version) {
                $extension = new Application_Model_Extension();
                if($extension->addVersionToExtension($extension_id, $extension_version)) {
                    if($request->getParam('edit_release')) {
                        $redirect['action'] = 'edit';
                        $redirect['id'] = $extension->getId();
                    }
                    $this->_helper->FlashMessenger('Succesfully added version to extension.');
                }
            }
        }
        return $this->_helper->redirector->gotoRoute(
                $redirect, 'default', true
        );
    }

    public function syncAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $request = $this->getRequest();
        $response = new stdClass();
        $response->status = 'error';
        $response->message = '';

        $extension_id = (int)$request->getParam('extension_id', 0);

        if($request->isPost()) {
            if($extension_id) {
                $sync = new Application_Model_Extension();
                $sync_message = $sync->synchronizeReleases($extension_id);
                if(
                    $sync_message === Application_Model_ExtensionVersionSynchronizer::EXTENSION_DOES_NOT_EXIST ||
                    $sync_message === FALSE
                ) {
                    $response->message = 'Extension does not exist.';
                } elseif($sync_message === Application_Model_ExtensionVersionSynchronizer::EXTENSION_IS_UP_TO_DATE) {
                    $response->message = 'Extension is up to date.';
                } else {
                    $response->message = $sync_message;
                    $response->status = 'ok';
                }
            } else {
                $response->message = 'No parameters.';
            }
        }

        $this->getResponse()->setBody(json_encode($response));
    }

    public function uploadAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $directoryHash = $this->_getParam('directory_hash', '');
        if($directoryHash AND preg_match('/^\d{10}\-[a-z0-9]{13}$/i', $directoryHash)) {
            $as_logo = (int)$this->_getParam('checked', 0);
            $chmod = 0777;
            $uploader_options = array(
                    'upload_dir' => $this->_tempDir.$directoryHash.'/',
                    'upload_url'  => $this->view->baseUrl('public/assets/img/temp/'.$directoryHash.'/'),
                    'image_versions' => array(), // do not create thumbnails
                    'accept_file_types' => '/\.(gif|jpe?g|png)$/i',
                    'mkdir_mode' => $chmod
            );
            
            umask(0);
            
            if(!file_exists($this->_tempDir)) {
                @mkdir($this->_tempDir, $chmod, true);
            }
            
            /* delete old temp files */
            if(!file_exists($this->_tempDir)) {
                $dir = array();
            } else {
                $dir = new DirectoryIterator($this->_tempDir);
            }
            foreach($dir as $fileinfo) {
                if(!$fileinfo->isDot()) {
                    if($fileinfo->isDir()) {
                        if(preg_match('/^(\d{10})\-[a-z0-9]{13}$/i', $directoryHash, $match) AND time()-(int)$match[1] > 3600) {
                            $del_dir = new DirectoryIterator($this->_tempDir);
                            foreach($del_dir as $del_fileinfo) {
                                @unlink($this->_tempDir.$key.'/'.$del_fileinfo->getFilename());
                            }
                            @rmdir($this->_tempDir.$fileinfo->getFilename());
                        }
                    }
                }
            }
            $uploader = new Integration_Uploader($uploader_options, false);
            $result = $uploader->post(false);
            if(isset($result[0]) AND is_object($result[0])) {
                $result[0]->as_logo = $as_logo;
            }
            
            $this->_response->setBody(json_encode($result));
        } else {
            // empty response
            $this->_response->setBody('{}');
        }
    }

    protected function _saveImages($extension_id) {
        umask(0);

        $this->_deleteRemovedImages($extension_id);

        $directory = $this->_getParam('directory_hash');
        $ids = $this->_getParam('screenshots_ids');
        $screenshots = $this->_getParam('screenshots');
        if(!$ids OR !is_array($ids)) {
            $ids = array();
        }
        if(!$screenshots OR !is_array($screenshots)) {
            $screenshots = array();
        }
        foreach($screenshots as $key => $image) {
            if(!isset($ids[$key]) OR !(int)$ids[$key]) {
                $old_path = $this->_tempDir.$directory.'/'.$image;
                $new_file_name = $this->view->NiceString(substr_replace($image, '-'.$extension_id, strrpos($image, '.'), 0));
                $new_path = $this->view->ImagePath($new_file_name, 'extension/screenshots', true, false);
                if(!file_exists($new_path)) {
                    @mkdir($new_path, 0777, true);
                }
                @copy($old_path, $new_path.$new_file_name);
                if(file_exists($old_path)) {
                    $screenshotModel = new Application_Model_ExtensionScreenshot();
                    $screenshotModel
                        ->setExtensionId($extension_id)
                        ->setImage($new_file_name)
                        ->save();
                }
            }
        }
    }

    protected function _deleteRemovedImages($extension_id) {
        umask(0);

        /* delete extension screenshots removed from form */
        $form_screenshots = $this->_getParam('screenshots_ids', array());
        $form_screenshots = (is_array($form_screenshots) ? $form_screenshots: array());
        $extension_screenshots = new Application_Model_ExtensionScreenshot();
        foreach($extension_screenshots->fetchByExtensionId($extension_id) as $screenshot) {
            if(!in_array($screenshot->getId(), $form_screenshots)) {
                $file_to_delete = $this->view->ImagePath($screenshot->getImage(), 'extension/screenshots');
                if(file_exists($file_to_delete)) {
                    unlink($file_to_delete);
                }
                $screenshot->delete();
            }
        }
    }

    protected function _redirectDefault($redirect_options = array())
    {
        $redirect_options = array_merge(array(
                                            'module'     => 'default',
                                            'controller' => $this->_gridController,
                                            'action'     => 'index',
                                        ),$redirect_options);
        return $this->_helper->redirector->gotoRoute($redirect_options, 'default', true);
    }
}
