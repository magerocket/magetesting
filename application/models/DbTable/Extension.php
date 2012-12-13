<?php

class Application_Model_DbTable_Extension extends Zend_Db_Table_Abstract
{

    protected $_name = 'extension';

    /**
     * 
     * @param array $instance
     * @return array
     */
    public function findMatching($instance)
    {
	  //get already installed extensions
	  $installed = $this->findInstalled($instance);
	  
	  $exclude = array();
	  foreach($installed as $ins){
	   $exclude[] = $ins->extension_id;
	  }
    
        $select = $this->select()
                ->from($this->_name)
                ->where('edition = ?', $instance['edition'])
                ->where(' ? BETWEEN REPLACE(from_version,\'.\',\'\') AND REPLACE(to_version,\'.\',\'\')',(int)str_replace('.','',$instance['version']));
                
                if (count($exclude)>0){
                    $select->where('id NOT IN (?) ',$exclude);
                }
                
                //get also developr extensions for admins
                if (Zend_Auth::getInstance()->getIdentity()->group == 'admin') {
                    $select->where('is_dev IN (?)',array(0,1));
                } else {
                    $select->where('is_dev  = ? ',0);
                }
                
//                var_dump($select->__toString());
               
        return $this->fetchAll($select);
    }
    
    public function fetchInstanceExtensions($instance) {
        $select = $this->select()
                        ->from(array('e' => $this->_name))
                        ->setIntegrityCheck(false)
                        ->where('ie.instance_id = ? OR ie.instance_id IS NULL', $instance->id)
                        ->where('e.edition = ?', $instance['edition'])
                        ->where(' ? 
                                 BETWEEN REPLACE(e.from_version,\'.\',\'\')
                                 AND REPLACE(e.to_version,\'.\',\'\')',
                            (int)str_replace('.','',$instance['version'])
                        );
        $select->joinLeft(
            array('ie' => 'instance_extension'),
            'e.id = ie.extension_id',
            'ie.instance_id'
        );
        $select->joinLeft(
            array('q' => 'queue'),
            'q.instance_id = ie.instance_id AND q.extension_id = e.id',
            'q.id as q_id'
        );
        $select->joinLeft(
            array('ec' => 'extension_category'),
            'ec.id = e.category_id',
            'ec.class as category_class'
        );
        $select->order(array('ie.instance_id DESC', 'q_id ASC', 'price DESC'));
        $select->group(new Zend_Db_Expr('e.id DESC'));
        //get also developr extensions for admins
        if (Zend_Auth::getInstance()->getIdentity()->group == 'admin') {
            $select->where('e.is_dev IN (?)',array(0,1));
        } else {
            $select->where('e.is_dev  = ? ',0);
        }
        #echo $select->__toString();die;
        return $this->fetchAll($select);
    }

    public function findInstalled($instance)
    {
        $select = $this->select()
        ->setIntegrityCheck(false)
                ->from($this->_name)
                ->join('instance_extension', $this->_name.'.id = instance_extension.extension_id')
                ->where('instance_id = ?', $instance['id'])
                ;
               
               //var_dump($select->__toString());
        return $this->fetchAll($select);
    }
    
     public function findByFilters(array $filters){
        
         $allowed_keys = array(
             'name',
             'namespace_module',
             'from_version',
             'to_version',
             'edition',
             'is_dev',
         );
         
        $select = $this->select()
            ->from($this->_name);
            
            foreach (array_keys($filters) as $key){
                if (!in_array($key,$allowed_keys)){
                    return null;
                }
            }
        
            foreach($filters as $key => $value){
	      $select->where($key.' = ?', $value);
            }
                
	return $this->fetchRow($select);
    }
}
