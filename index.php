<?php

define('SITE_DIR', realpath(dirname(__FILE__)));

require_once SITE_DIR.'/mongo.php';

if(isset($_GET['benchmark'])) {
    $benchmark = $_GET['benchmark'];
    MongoAdapter::setDb($benchmark);
    
    if(isset($_GET['new_run']) && isset($_POST['queries'])) {
        $run = MongoAdapter::createRun();
        $queries = explode(';', str_replace("\r", "", trim($_POST['queries'])));
        foreach($queries as $query) {
            if($query) {
                MongoAdapter::getCollection('queries')->insert(array(
                    'query' => $query,
                    'random' => mt_rand(),
                    'run_id' => $run['_id']
                ));
                
                MongoAdapter::getCollection('queries')->ensureIndex(array(
                    'random' => true
                ));
            }
        }
    }
    if(isset($_GET['new_run'])) {
        echo '<form name="new_run" method="POST"><textarea name="queries"></textarea><button type="submit">Save</button></form>';
    }
    if(isset($_GET['run_id'])) {
        $run = MongoAdapter::getCollection('runs')->findOne(array(
            'run_id' => $_GET['run_id']
        ));
        if($run->state == 0) {
            echo 'ab http://local.mysql-benchmarks.ua/test.php?benchmark='.$benchmark.'&run_id='.$_GET['run_id']."<br\n>";
        }
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

    $hasDefault = false;


    foreach($benchmarks['databases'] as $benchmark) {
        echo '<a href="?benchmark='.$benchmark['name'].'">'.$benchmark['name'].'</a><br>'."\n";
        if(!$hasDefault) {
            $hasDefault = $benchmark['name'] == 'default';
        }
    }

    if(!$hasDefault) {
        MongoAdapter::createDefaultBenchmark();
    }
}