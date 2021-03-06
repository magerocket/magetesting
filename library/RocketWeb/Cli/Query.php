<?php

class RocketWeb_Cli_Query
{
    protected $_query = '';
    protected $_super_user = false;
    protected $_cli_object = false;

    public function __construct($string = '', $values = null, $cli_object = false)
    {
        if(is_object($cli_object) && $cli_object instanceof RocketWeb_Cli) {
            $this->_cli_object = $cli_object;
        } else {
            $this->_cli_object = new RocketWeb_Cli();
        }
        $this->pipe($string, $values);
    }

    /**
     * @return RocketWeb_Cli
     */
    public function call()
    {
        return $this->_cli_object->exec($this->_prepareCall((string) $this));
    }

    public function _prepareCall($query)
    {
        return $this->_redirectStdErr($query);
    }
    /**
     *
     * @param string $name - name of the kit to load
     * @param boolean $clean - whether append current query to the returned kit
     * @return RocketWeb_Cli_Query
     */
    public function kit($name, $clean = true)
    {
        $kit = $this->_cli_object->kit($name);
        if(!$clean) {
            $kit->append($this);
        }
        return $kit;
    }

    public function sortNatural()
    {
        return $this->pipe('sort -u');
    }
    public function force()
    {
        return $this->append('-f');
    }
    /**
     * method clones and clears current object
     * @param string $string
     * @param string $values
     */
    public function newQuery($string = '', $values = null)
    {
        $query = $this->cloneObject()->clear();
        if($string) {
            $query->append($string, $values);
        }
        return $query;
    }
    public function cloneObject()
    {
        return clone $this;
    }
    /**
     * appends query to the end of existing one
     * @param string $string
     * @param string $values
     * @param bool $prependSpace - whether add space before appended string
     * @return RocketWeb_Cli_Query
     */
    final public function append($string = '', $values = null, $prependSpace = true, $suppressErrors = true)
    {
        $this->_append($string, $values, false, $prependSpace, $suppressErrors);
        return $this;
    }

    /**
     * 
     * @param mixed $string - string | RocketWeb_Cli_Query
     * @param string $values
     * @param string $pipe - whether append using ' ' or ' | '
     * @throws RocketWeb_Cli_Exception
     * @return RocketWeb_Cli_Query
     */
    final protected function _append($query = '', $values = null, $pipe = false, $prependSpace = true, $suppressErrors = true)
    {
        $query = (string)$query;
        if(null !== $values && !is_array($values)) {
            $values = array($values);
        }
        $append = trim($this->_query);
        if(null === $values) {
            $this->_query = trim($query);
        } else {
            $this->_query = $this->_bindValues(trim($query), $values);
        }

        if($append) {
            if($pipe) {
                // it's to pipe left side stderr to /dev/null without breaking
                // right side stdout
                $this->_query = $append . ($suppressErrors ? ' 2>/dev/null | ' : ' 2>&1 | '). $this->_query;
            } else {
                $this->_query = $append . ($prependSpace ? ' ' : ''). $this->_query;
            }
        }
    }

    /**
     * pipes new query to the existing one
     * @param string $string
     * @param string $values
     * @return RocketWeb_Cli_Query
     */
    final public function pipe($string = '', $values = null, $suppressErrors = true)
    {
        $this->_append($string, $values, true, false, $suppressErrors);

        return $this;
    }
    /**
     * clears query string
     */
    public function clear()
    {
        $this->_query = '';
        return $this;
    }

    /**
     * Binds value to the given name already after method call,<br />
     * so if name does not exist in current query string,<br />
     * value will not be binded in the future
     * @param string|array $name - name to be find or array of names => values
     * @param mixed $value - value to pass in place of name
     * @param boolean $escape - whether escapeshellarg or not
     * @return RocketWeb_Cli_Query
     */
    final public function bindAssoc($name, $value = '', $escape = true)
    {
        if(!is_string($name) && !is_array($name)) {
            throw new RocketWeb_Cli_Exception('Wrong argument name.');
        }

        $arguments = $name;
        if(is_string($name)) {
            $arguments = array($name => $value);
        }

        foreach($arguments as $key => $val) {
            $this->_query =
                str_replace(
                    $key,
                    ($escape ? $this->escape($val) : $val),
                    $this->_query
                );
        }
        return $this;
    }

    final protected function _bindValues($string, $values)
    {
        if(!is_array($values)) {
            $values = array($values);
        }

        foreach($values as $value) {
            $position = strpos($string, '?');
            if(false !== $position) {
                $string = substr_replace($string, $this->escape($value), $position, 1);
            }
        }

        return $string;
    }

    /**
     * @return RocketWeb_Cli_Query
     */
    final public function asSuperUser($value = true)
    {
        $this->_super_user = ((int)$value ? true : false);
        return $this;
    }

    public function toString()
    {
        if($this->_super_user) {
            $replaced = preg_replace('/^sudo\s*/i', '', $this->_query);
            if(NULL === $replaced) {
                throw new RocketWeb_Cli_Exception('PREG Replace Error');
            }
            $this->_query = 'sudo '.$replaced;
        }

        return $this->_query;
    }

    final public function __toString()
    {
        return $this->toString();
    }

    final protected function _redirectStdErr($string)
    {
        $replaced = preg_replace('/\s*2\s*>\s*&1\s*$/i', '', $string);
        if(NULL === $replaced) {
            throw new RocketWeb_Cli_Exception('PREG Replace Error');
        }

        return $replaced . ' 2>&1';
    }
    public function escape($string)
    {
        return escapeshellarg($string);
    }
}