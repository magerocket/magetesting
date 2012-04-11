<?php
/**
 * Retrieves and saves data from payment table
 * @package Application_Model_Payment
 * @author Grzegorz (golaod)
 */
class Application_Model_Payment {

    protected $_id;

    protected $_price;

    protected $_first_name;

    protected $_last_name;

    protected $_street;

    protected $_postal_code;

    protected $_city;

    protected $_state;

    protected $_country;

    protected $_date;

    protected $_plan_id;

    protected $_user_id;

    protected $_subscr_id;

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

    public function setPrice($price)
    {
        $this->_price = (float)$price;
        return $this;
    }

    public function getPrice()
    {
        return $this->_price;
    }

    public function setFirstName($firstname)
    {
        $this->_first_name = $firstname;
        return $this;
    }

    public function getFirstName()
    {
        return $this->_first_name;
    }

    public function setLastName($lastname)
    {
        $this->_last_name = $lastname;
        return $this;
    }

    public function getLastName()
    {
        return $this->_last_name;
    }

    public function setStreet($street)
    {
        $this->_street = $street;
        return $this;
    }

    public function getStreet()
    {
        return $this->_street;
    }

    public function setPostalCode($postal_code)
    {
        $this->_postal_code = $postal_code;
        return $this;
    }

    public function getPostalCode()
    {
        return $this->_postal_code;
    }

    public function setCity($city)
    {
        $this->_city = $city;
        return $this;
    }
    
    public function getCity()
    {
        return $this->_city;
    }

    public function setState($state)
    {
        $this->_state = $state;
        return $this;
    }

    public function getState()
    {
        return $this->_state;
    }

    public function setCountry($country)
    {
        $this->_country = $country;
        return $this;
    }

    public function getCountry()
    {
        return $this->_country;
    }

    public function setDate($Date)
    {
        $this->_date = $Date;
        return $this;
    }

    public function getDate()
    {
        return $this->_date;
    }

    public function setPlanId($plan_id)
    {
        $this->_plan_id = (int)$plan_id;
        return $this;
    }

    public function getPlanId()
    {
        return $this->_plan_id;
    }

    public function setUserId($user_id)
    {
        $this->_user_id = (int)$user_id;
        return $this;
    }

    public function getUserId()
    {
        return $this->_user_id;
    }

    public function setSubscrId($subscr_id)
    {
        $this->_subscr_id = $subscr_id;
        return $this;
    }

    public function getSubscrId()
    {
        return $this->_subscr_id;
    }

    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        if (null === $this->_mapper) {
            $this->setMapper(new Application_Model_PaymentMapper());
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
            'id'          => $this->getId(),
            'price'       => $this->getPrice(),
            'first_name'  => $this->getFirstName(),
            'last_name'   => $this->getLastName(),
            'street'      => $this->getStreet(),
            'postal_code' => $this->getPostalCode(),
            'city'        => $this->getCity(),
            'state'       => $this->getState(),
            'country'     => $this->getCountry(),
            'date'        => $this->getDate(),
            'plan_id'     => $this->getPlanId(),
            'user_id'     => $this->getUserId(),
            'subscr_id'   => $this->getSubscrId()
        );
    }

    public function fetchUserPayments($id)
    {
        return $this->getMapper()->fetchPaymentsByUser($id);
    }

    public function getPlan()
    {
        $plan = new Application_Model_Plan();
        return $plan->find($this->getPlanId());
    }
}