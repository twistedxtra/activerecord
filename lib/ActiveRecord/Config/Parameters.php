<?php

namespace ActiveRecord\Config;

/**
 * Description of Parameters
 *
 * @author maguirre
 */
class Parameters
{

    protected $id;
    protected $username;
    protected $password;
    protected $port;
    protected $dbName;
    protected $host = 'localhost';
    protected $type;
    protected $charset;

    function __construct($id, array $config = array())
    {
        $this->id = $id;
        isset($config['username']) && $this->username = $config['username'];
        isset($config['password']) && $this->password = $config['password'];
        isset($config['name']) && $this->dbName = $config['name'];
        isset($config['host']) && $this->host = $config['host'];
        isset($config['type']) && $this->type = $config['type'];
        isset($config['port']) && $this->port = $config['port'];
        isset($config['charset']) && $this->charset = $config['charset'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    public function getDbName()
    {
        return $this->dbName;
    }

    public function setDbName($dbName)
    {
        $this->dbName = $dbName;
        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getCharset()
    {
        return $this->charset;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }

}
