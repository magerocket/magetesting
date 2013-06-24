<?php

include 'init.console.php';

$select = new Zend_Db_Select($db);
$sql = $select
    ->from('user')
    ->joinLeft('store','user.id = store.user_id', 'domain')
    ->joinLeft('server','user.server_id = server.id', array('server_domain' => 'domain'))
    ->joinLeft('payment', 'user.braintree_transaction_id = payment.braintree_transaction_id AND user.id = payment.user_id', '')
    ->where('store.status = ?', 'ready')
    ->where('TIMESTAMPDIFF(SECOND,user.plan_active_to, \''.date("Y-m-d H:i:s").'\') > ?', 3*60*60*24)
    ->orwhere('braintree_transaction_confirmed = 0 AND date(CURRENT_TIMESTAMP)-date(payment.date) > 3')
    ->where('(user.group IN (?)', array('awaiting-user', 'commercial-user'))
    ->orwhere('user.downgraded = ?)', 2);

$result = $db->fetchAll($sql);

if($result) {
    $downgrade_by_id = array();
    foreach($result as $store) {
        if(!isset($downgrade_by_id[$store['id']])) {
            $downgrade_by_id[$store['id']] = null;
        }
               
        /* disable user vhost */
        exec('sudo a2dissite '.$store['login'].'.'.$store['server_domain']);
    }
    
    if($downgrade_by_id) {
        $set = array(
                'group' => 'free-user',
                'downgraded' => 1
        );
        
        $user_ids = array_keys($downgrade_by_id);
        
        $where = array('id IN (?)' => $user_ids);
        echo 'Update: '.$db->update('user', $set, $where).PHP_EOL;
        
        foreach ($user_ids as $user_id){
            $modelUser = new Application_Model_User();
            $modelUser->find($user_id);
            
            $modelUser->disableFtp();
            $modelUser->disablePhpmyadmin();
        }
        exec('sudo /etc/init.d/apache2 reload');
    }
    $log->log('Downgraded '.count($downgrade_by_id).' users', Zend_Log::INFO);
} else {
    //$log->log('There is no user to downgrade.', Zend_Log::INFO);
}