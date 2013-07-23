<?php

class RocketWeb_Cli_Type_Ssh
    extends RocketWeb_Cli_Query
{
    public function connect($user, $host, $port)
    {
        $this->append('ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no');
        $this->append(':user@:host -p :port');
        $this->bindAssoc(':user', $user)
             ->bindAssoc(':host', $host, false)
             ->bindAssoc(':port', $port, false);
        return $this;
    }
    public function passPassword($password)
    {
        $this->append('sshpass -p ?', $password);
        return $this;
    }
}