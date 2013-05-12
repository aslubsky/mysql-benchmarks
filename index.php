<?php

define('SITE_DIR', realpath(dirname(__FILE__)));

require_once SITE_DIR.'/mongo.php';

if(isset($_GET['benchmark'])) {
    $benchmark = $_GET['benchmark'];
    MongoAdapter::setDb($benchmark);
    
    // $run = MongoAdapter::createRun();
    
    if(isset($_GET['new_run'])) {
        $run = MongoAdapter::createRun();
    }
    if(isset($_GET['run_id'])) {
        $items = MongoAdapter::getCollection('items')->find(array(
            'run_id' => $_GET['run_id']
        ));
        
        foreach($items as $item) {
            print_r($item);
        }
    } else {
        $runs = MongoAdapter::getCollection('runs')->find();
        foreach($runs as $run) {
            echo '<a href="?benchmark='.$benchmark.'&run_id='.$run['_id'].'">'.$run['date'].'</a><br>'."\n";
        }
        
        echo '<br><a href="?benchmark='.$benchmark.'&new_run=true">New</a><br>'."\n";
    }
} else {
    $benchmarks = MongoAdapter::listDBs();
    foreach($benchmarks['databases'] as $benchmark) {
        echo '<a href="?benchmark='.$benchmark['name'].'">'.$benchmark['name'].'</a><br>'."\n";
    }
}