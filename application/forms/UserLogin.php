<?php

class Application_Form_UserLogin extends Integration_Form
{

    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');
        $this->setAttrib('class', 'form-stacked');

        // Add id field
        $this->addElement('hidden', 'id');

        // Add a login element
        $this->addElement('text', 'login', array(
                'label'      => 'Username:',
                'class'      => 'span6',
                'required'   => true,
                'filters'    => array('StripTags', 'StringTrim'),
                'validators' => array(
                        array('validator' => 'StringLength', 'options' => array(3, 255))
                )
        ));

        // Add a password element
        $this->addElement('password', 'password', array(
                'label'      => 'Password:',
                'class'      => 'span6',
                'required'   => true,
                'filters'    => array('StripTags', 'StringTrim'),
                'validators' => array(
                        array('validator' => 'StringLength', 'options' => array(3, 45))
                )
        ));

        // Add the submit button
        $this->addElement('submit', 'submit', array(
                'ignore'   => true,
                'label'    => 'Log in',
        ));

        $this->addDisplayGroup(array( 'login', 'password', 'submit'),
                'accountLogin', array('legend' => 'Your Login Details')
        );

        $this->_setDecorators();

        $this->submit->removeDecorator('HtmlTag');
        $this->submit->removeDecorator('overall');
    }


}

