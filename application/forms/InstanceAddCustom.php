<?php

class Application_Form_InstanceAddCustom extends Integration_Form{

    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');
        $this->setAttrib('class', 'form-stacked');
        $this->setAttrib('id', 'custom-instance-form');
        //TODO: move model usage to controller

        
        $this->addElement('text', 'instance_name', array(
                'label'      => 'Name',
                'required'   => false,
                'filters'    => array('StripTags', 'StringTrim'),
                'class'      => 'span4'
        ));

        $this->addElement('text', 'description', array(
                'label'      => 'Description',
                'required'   => false,
                'filters'    => array('StripTags', 'StringTrim'),
                'validators' => array(
                    array('validator' => 'StringLength', 'options' => array('max' => 300))
                ),
                'class'      => 'span4'
        ));

        $editionModel = new Application_Model_Edition();
        $this->addElement('select', 'edition', array(
                'label'      => 'Used Edition',
                'required'   => true,
                'filters'    => array('StripTags', 'StringTrim'),
                'validators' => array(
                        new Zend_Validate_InArray(array_keys($editionModel->getKeys()))
                ),
                'class'      => 'span4'
        ));
        $emptyVersion = array('' => 'Choose...');
        $versions = array_merge($emptyVersion,$editionModel->getOptions());

        $this->edition->addMultiOptions($versions);

        $versionModel = new Application_Model_Version();
        $this->addElement('select', 'version', array(
                'label'      => 'Used Version',
                'required'   => true,
                'filters'    => array('StripTags', 'StringTrim'),
                'validators' => array(
                        new Zend_Validate_InArray($versionModel->getKeys())
                ),
                'class'      => 'span4'
        ));
        
        $this->addElement('select', 'custom_protocol', array(
                'label'      => 'Protocol',
                'required'   => true,
                'filters'    => array('StripTags', 'StringTrim'),
                'validators' => array(
                        new Zend_Validate_InArray(array('ftp'))
                ),
                'class'      => 'span4'
        ));
        $this->custom_protocol->addMultiOptions(array(
                'ftp' => 'FTP',
        ));

        $this->addElement('text', 'custom_host', array(
                'label'       => 'Host',
                'required'    => true,
                'label_class' => 'radio inline',
                'placeholder' => 'ie. ftp.my-company.com',
                'class'      => 'span4'
        ));
//        $this->custom_host->setValue('ie. ftp.my-company.com');
        
        $this->addElement('text', 'custom_remote_path', array(
                'label'       => 'Remote Path to Magento Root',
                'required'    => true,
                'label_class' => 'radio inline',
                'placeholder' => 'ie. /website/html',
                'class'      => 'span8'
        ));
        
//        $this->custom_remote_path->setValue('ie. /website/html');
        
        $this->addElement('text', 'custom_sql', array(
                'label'       => 'Remote Path to SQL dump',
                'required'    => true,
                'label_class' => 'radio inline',
                'placeholder' => 'ie. /website/html/dump.sql',
                'class'      => 'span8'
        ));
//        $this->custom_sql->setValue('ie. /website/html/dump.sql');
        
        $this->addElement('text', 'custom_login', array(
                'label'       => 'Login',
                'required'    => true,
                'label_class' => 'radio inline',
                'class'      => 'span4'
        ));
        
        $this->addElement('password', 'custom_pass', array(
                'label'       => 'Password',
                'required'    => true,
                'label_class' => 'radio inline',
                'class'      => 'span4'
        ));
        
        // Add the submit button
        $this->addElement('submit', 'instanceAdd', array(
                'ignore'   => true,
                'label'    => 'Install',
        ));
        
        $this->addElement('text', 'custom_file', array(
                'label'       => 'Remote Path to .zip or .tar.gz package containing all store files',
                'required'    => false,
                'label_class' => 'radio inline',
                'placeholder' => 'ie. /website/html/store_backup.tar.gz',
                'class'      => 'span8'
        ));
        
//        $this->custom_file->setValue('ie. /website/html/store_backup.tar.gz');

        $this->_setDecorators();

        $this->instanceAdd->removeDecorator('HtmlTag');
        $this->instanceAdd->removeDecorator('overall');
        $this->instanceAdd->setAttrib('class','btn btn-primary');

        $this->custom_protocol->removeDecorator('HtmlTag');
        $this->custom_protocol->removeDecorator('overall');

        $this->custom_host->removeDecorator('HtmlTag');
        $this->custom_host->removeDecorator('overall');
        
        $this->custom_remote_path->removeDecorator('HtmlTag');
        $this->custom_remote_path->removeDecorator('overall');
        
        $this->custom_sql->removeDecorator('HtmlTag');
        $this->custom_sql->removeDecorator('overall');
        
        $this->custom_login->removeDecorator('HtmlTag');
        $this->custom_login->removeDecorator('overall');
        
        $this->custom_pass->removeDecorator('HtmlTag');
        $this->custom_pass->removeDecorator('overall');
        
        $this->custom_file->removeDecorator('HtmlTag');
        $this->custom_file->removeDecorator('overall');
    }

}

