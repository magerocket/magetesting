<?php

class Integration_Mail_UserResetPassword
{

    protected $user;
    protected $view;
    protected $mail;

    public function __construct($config, $user)
    {
        $this->user = $user;

        $from = $config->from;
        //headers set up here
        $mail = $this->mail = new Zend_Mail('utf-8');
        $mail->setFrom( $from->email, $from->desc );
        $mail->addTo($user->getEmail());
        $mail->setSubject($config->subject);
        $mail->setReplyTo( $from->email, $from->desc );
        $mail->setReturnPath($from->email);

        $this->view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
    }

    public function send()
    {
        $resetUrl = $this->view->url(
            array(
                'controller' => 'user',
                'action'     => 'set-new-password',
                'id'         => $this->user->getId(),
                'key'        => sha1($this->user->getAddedDate().$this->user->getPassword()),
            )
        );
        $this->view->resetLink = $this->view->serverUrl().$resetUrl;
        $this->view->login  = $this->user->getLogin();
        //body setup here
        $msg = $this->view->render('_emails/user-reset-password.phtml');

        $this->mail->setBodyHtml($msg);
        $this->mail->setBodyText(strip_tags($msg));

        $result =  $this->mail->send();
        return $result;
    }


}
