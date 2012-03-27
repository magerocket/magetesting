<?php
/**
 * Retrieves and saves data from plan table
 * @package Application_Model_Plan
 * @author Grzegorz (golaod)
 */
class Application_Model_Plan {

    protected $_id;

    protected $_name;

    protected $_instances;

    protected $_price;

    protected $_mapper;

    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function setOptions(array $options)
    {
        $filter = new Zend_Filter_Word_UnderscoreToCamelCase();
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . $filter->filter($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function setId($id)
    {
        $this->_id = (int)$id;
        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setInstances($instances)
    {
        $this->_instances = (int)$instances;
        return $this;
    }

    public function getInstances()
    {
        return $this->_instances;
    }

    
    public function setPrice($price)
    {
        $this->_price = (float)$price;
        return $this;
    }
    
    public function getPrice()
    {
        return $this->_price;
    }
    
    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        if (null === $this->_mapper) {
            $this->setMapper(new Application_Model_PlanMapper());
        }
        return $this->_mapper;
    }

    public function save()
    {
        $this->getMapper()->save($this);
    }

    public function delete($id)
    {
        $this->getMapper()->delete($id);
    }

    public function find($id)
    {
        $this->getMapper()->find($id, $this);
        return $this;
    }

    public function fetchAll()
    {
        return $this->getMapper()->fetchAll();
    }

    public function __toArray()
    {
        return array(
                'id'        => $this->getId(),
                'name'      => $this->getName(),
                'instances' => $this->getInstances(),
                'price'     => $this->getPrice()
        );
    }

    public function getAll()
    {
        return $this->getMapper()->fetchAll();
    }

}