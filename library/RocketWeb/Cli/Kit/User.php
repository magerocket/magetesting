<?php

class RocketWeb_Cli_Kit_User
    extends RocketWeb_Cli_Query
{
    protected $_scripts_dir = NULL;
    protected function _getScriptsDir()
    {
        if(!$this->_scripts_dir) {
            $this->_scripts_dir = APPLICATION_PATH . '/../scripts/worker/';
        }
        return $this->_scripts_dir;
    }
    public function create($login, $password, $salt, $homeDir)
    {
        $this->_runScript('create_user.sh');
        $this->append(':login :password :salt :homeDir');
        $this->bindAssoc(':login', $login);
        $this->bindAssoc(':password', $password);
        $this->bindAssoc(':salt', $salt);
        $this->bindAssoc(':homeDir', $homeDir);
        return $this;
    }
    public function delete($login)
    {
        $this->_runScript('remove_user.sh');
        return $this->append('?', $login);
    }

    public function rebuildPhpMyAdmin($denyList)
    {
        $this->_runScript('phpmyadmin-user-rebuild.sh');
        $this->append('":denylist"');
        $this->bindAssoc(':denylist', $denyList, false);
        return $this;
    }
    protected function _runScript($script)
    {
        $this->append('sh :script')
             ->bindAssoc(':script', $this->_getScriptsDir() . $script);
        return $this;
    }
}