<?php

include 'init.console.php';

$select = new Zend_Db_Select($db);
$sql = $select
    ->from('user')
    ->joinLeft('store','user.id = store.user_id', 'domain')
    ->joinLeft('server','user.server_id = server.id', array('server_domain' => 'domain'))
    ->joinLeft('payment', 'user.braintree_transaction_id = payment.braintree_transaction_id AND user.id = payment.user_id', '')
    ->where('(store.status = ?', 'ready')
    ->where('user.server_id = ?', $config->magento->currentServerId)
    ->where('TIMESTAMPDIFF(SECOND,user.plan_active_to, \''.date("Y-m-d H:i:s").'\') > ?', 3*60*60*24)
    ->where('user.downgraded != ?', Application_Model_User::DOWNGRADED_EXPIRED_SYMLINKS_DELETED)
    ->where('user.group = ?)', 'commercial-user')
    ->orwhere('braintree_transaction_confirmed = 0 AND date(CURRENT_TIMESTAMP)-date(payment.date) > 3 AND user.downgraded != ?', Application_Model_User::DOWNGRADED_EXPIRED_SYMLINKS_DELETED)
    ->orwhere('user.downgraded = ?', Application_Model_User::DOWNGRADED_EXPIRED_SYMLINKS_NOT_DELETED);

$apache = new RocketWeb_Cli_Kit_Apache();
$service = new RocketWeb_Cli_Kit_Service();

$result = $db->fetchAll($sql);
if($result) {
    $downgrade_by_id = array();
    foreach($result as $store) {
        if(!isset($downgrade_by_id[$store['id']])) {
            $downgrade_by_id[$store['id']] = null;
        }
               
        /* disable user vhost */
        $apache->clear()->disableSite($store['login'].'.'.$store['server_domain'])->call();
    }
    
    if($downgrade_by_id) {
        $set = array(
            'group' => 'free-user',
            'downgraded' => Application_Model_User::DOWNGRADED_EXPIRED_SYMLINKS_DELETED
        );
        
        $user_ids = array_keys($downgrade_by_id);
        
        $where = array('id IN (?)' => $user_ids);
        $db->update('user', $set, $where);

        $dbPrivileged = Zend_Db::factory('PDO_MYSQL', $config->dbPrivileged->params);
        $DbManager = new Application_Model_DbTable_Privilege($dbPrivileged,$config);
        
        foreach ($user_ids as $user_id){
            $modelUser = new Application_Model_User();
            $modelUser->find($user_id);
            $log->log('Downgraded '.json_encode($modelUser->__toArray()), Zend_Log::INFO);

            $DbManager->disableFtp($modelUser->getLogin());

            $modelUser->disablePhpmyadmin();
        }
        $service->clear()->reload('apache2')->call();
    }
    #$log->log('Downgraded '.count($downgrade_by_id).' users', Zend_Log::INFO);
} else {
    //$log->log('There is no user to downgrade.', Zend_Log::INFO);
}