<?php
require_once 'init.console.php';

$debug = !(sizeOf($argv) == 2 && $argv[1] == 'run');

$file = new RocketWeb_Cli_Kit_File();
$query = new RocketWeb_Cli_Query();

$baseFolder = $config->magento->systemHomeFolder;
$userPrefix = $config->magento->userprefix;

$command = $query->append("find '".$baseFolder."' -maxdepth 1 -type d -name '".$userPrefix."*' -print");
$output = $command->call()->getLastOutput();

$allFileSystemStores = array();
$allStoreUsers = array();

foreach($output as $userPath){
    $user = explode('/',$userPath);
    $user = $user[sizeOf($user)-1];

    $baseStoresFolder = $userPath.'/public_html';
    $command = $query->clear()->append("find '".$baseStoresFolder."' -maxdepth 1 -mindepth 1 -type d -print");
    $storesOutput = $command->call()->getLastOutput();

    if(sizeOf($storesOutput) == 0) continue;

    $allFileSystemStores[$user] = array();

    foreach($storesOutput as $storePath){
        if(strpos($storePath,'No such file or directory') !== false) continue;
        $store = explode('/',$storePath);
        $store =  $store[sizeOf($store)-1];
        $allFileSystemStores[$user][] = $store;
        $allStoreUsers[$store] = $user;
    }
}

$storeModel = new Application_Model_Store();
$stores = $storeModel->fetchAll();

foreach($stores as $model){
    $domain = $model->getDomain();
    if(!isset($allStoreUsers[$domain])){
        $log->log('Store ('.$domain.') doesn\'t exists on FS! ('.$baseFolder.'/(user_id:'.$model->getUserId().')/public_html/'.$domain.')', Zend_Log::ALERT);
        continue;
    }
    $user = $allStoreUsers[$domain];
    if(($key = array_search($domain, $allFileSystemStores[$user])) !== false) {
        unset($allFileSystemStores[$user][$key]);
    }
    if(sizeOf($allFileSystemStores[$user]) == 0){
        unset($allFileSystemStores[$user]);
    }
}

$dbPrivileged = Zend_Db::factory('PDO_MYSQL', $config->dbPrivileged->params);
$DbManager = new Application_Model_DbTable_Privilege($dbPrivileged,$config);
if($debug === true){
    echo 'DRYRUN MODE'."\n";
}
foreach($allFileSystemStores as $user => $stores){
    foreach($stores as $domain){
        $path = $baseFolder.'/'.$user.'/public_html/'.$domain;
        $removeCommand = $file->clear()->remove($path);
        if($debug === false){
            try{
                $removeOutput = $removeCommand->call()->getLastOutput();
                $removeOutput = var_export($removeOutput,true);
                $log->log("\n" . $removeCommand->toString() . "\n" . $removeOutput, Zend_Log::INFO);
            }catch(Exception $e){
                echo 'Catch: '.$e->getMessage()."\n";
            }
        }else{
            echo $removeCommand->toString()."\n";
        }

        $login = str_replace($userPrefix,'',$user);

        $dbname = $login.'_'.$domain;

        if($debug === false){
            try{
                $DbManager->dropDatabase($dbname);
                $log->log("MYSQL query: DROP DATABASE `".$config->magento->storeprefix.$dbname."`"."\n", Zend_Log::INFO);
            }catch(PDOException $e){
                echo 'Catch: '.$e->getMessage()."\n";
            }
        }else{
            echo "DROP DATABASE `".$config->magento->storeprefix.$dbname."`"."\n\n";
        }
    }
}