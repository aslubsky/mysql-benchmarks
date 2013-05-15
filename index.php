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
            echo 'ab -n 1000 -c 1000 http://local.mysql-benchmarks.ua/test.php?benchmark='.$benchmark.'&run_id='.$_GET['run_id']."<br\n>";
        }
        
        // $res = MongoAdapter::getCollection('items')->group(
            // array(
                // 'query' => true
            // ),
            // array(
                // 'runs_count' => 0
            // ),
            // "function (obj, prev) { prev.runs_count++; }",
            // array('condition' => array('query' => array( '$gt' => 1)))
        // );
        // print_r($res);exit;
        // $items = MongoAdapter::getCollection('items')->find(array(
            // 'run_id' => $_GET['run_id']
        // ));
        // db.users.count( { user_id: { $exists: true } } )
        
        
        $items = MongoAdapter::getCollection('items')->find(array(
            'run_id' => $_GET['run_id']
        ));
        
        $chartDataSet = array();
        foreach($items as $item) {
            // print_r($item);exit;
            $chartDataSet []= array(strtotime($item['date']).'000', $item['exec_time']);
        }
        
        include 'chart.php';
        
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