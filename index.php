<?php
ini_set('memory_limit', '1024M');
set_time_limit(1800);

define('SITE_DIR', realpath(dirname(__FILE__)));

require_once SITE_DIR.'/mongo.php';

function calc($items) {
    $queriesCountPerTime = array();
    $queriesPerTime = array();
    foreach($items as $item) {
        // print_r($item);exit;
        $ts = strtotime(date('Y-m-d H:i', strtotime($item['date'])));
        if(!isset($queriesCountPerTime[$ts])) {
            $queriesCountPerTime[$ts] = 0;
            $queriesPerTime[$ts] = array();
        }
        $queriesCountPerTime[$ts]++;
        $queriesPerTime[$ts] []= $item['exec_time'];
    }

    // $speedDataSet = array();
    // foreach($queriesCountPerTime as $date => $cnt) {
        // print_r($item);exit;
        // $speedDataSet []= array(strtotime($date).'000', $cnt);
    // }
    ksort($queriesPerTime);
    
    $tmp = array();
    $avgTimeDataSet = array();
    foreach($queriesPerTime as $ts => $times) {
//            print_r($times);exit;
        $count = count($times);
        $sum = 0;
        foreach($times as $time) {
            $sum += $time;
        }
        // print_r($item);exit;
        $avgTimeDataSet []= array($ts.'000', $sum/$count);
        // $tmp [$queriesCountPerTime[$date]] = $sum/$count;
    }
    return $avgTimeDataSet;
}

if(isset($_GET['benchmark'])) {
    $benchmark = $_GET['benchmark'];
    MongoAdapter::setDb($benchmark);

    if(isset($_GET['new_run'])) {
        echo 'Z:\usr\local\apache\bin\ab.exe -n 1000 -c 100 "http://local.mysql-benchmarks.ua/test.php?benchmark='.$benchmark.'&set_id='.$_GET['set_id'].'"';exit;
        //echo '<pre>'.
            shell_exec('/usr/bin/ab -n 100000 -c 500 "http://local.mysql-benchmarks.ua/test.php?benchmark='.$benchmark.'&set_id='.$_GET['set_id'].'&run_id='.$run['_id'].'"');//.'</pre>';exit;
        header('Location: index.php?benchmark='.$benchmark.'&set_id='.$_GET['set_id'].'&run_id='.$run['_id']);
        exit;
    }

    if(isset($_GET['new_set']) && isset($_POST['set_name'])) {
//        print_r($_FILES);exit;
        $file = current($_FILES);
        if(file_exists($file['tmp_name'])) {
            $set = array(
                'name' => $_POST['set_name'],
                'date' => date('Y-m-d H:i:s')
            );
            MongoAdapter::getCollection('sets')->insert($set);

            $cont = file_get_contents($file['tmp_name']);
            $queries = explode(';', str_replace("\r", "", trim(trim($cont), ';')));
            foreach($queries as $query) {
                if($query) {
                    MongoAdapter::getCollection('queries')->insert(array(
                        'query' => $query,
                        'random' => mt_rand(),
                        'set_id' => $set['_id']
                    ));

                    MongoAdapter::getCollection('queries')->ensureIndex(array(
                        'random' => true
                    ));
                }
            }
        }
    }
    if(isset($_GET['new_set'])) {
        echo '<form enctype="multipart/form-data" name="new_set" method="POST">Set name: <input type="text" name="set_name"/> <br/>
        SQL file: <input type="file" name="queries_file"/> <br/><button type="submit">Save</button></form>';
    }
    if(isset($_GET['set_id'])) {
        $items = MongoAdapter::getCollection('items')->find(array(
            'set_id' => $_GET['set_id'],
            'type' => 1
        ));
        $selectSet = calc($items);
        
        $items = MongoAdapter::getCollection('items')->find(array(
            'set_id' => $_GET['set_id'],
            'type' => 2
        ));
        $updateSet = calc($items);
        
        $items = MongoAdapter::getCollection('items')->find(array(
            'set_id' => $_GET['set_id'],
            'type' => 3
        ));
        $insertSet = calc($items);
        
        $items = MongoAdapter::getCollection('items')->find(array(
            'set_id' => $_GET['set_id'],
            'type' => 4
        ));
        $deleteSet = calc($items);
        
//        print_r($avgTimeDataSet);exit;
        
        include 'chart.php';
        // makeChart('Test', 'Test', $tmpDataSet);
        // makeChart('Queries per sec', 'speed', $speedDataSet);
        makeChart('Average execution time (in sec)', 'avgTime', array(
            array(
                'label' => 'SELECT',
                'data' => $selectSet
            ),
             array(
                 'label' => 'UPDATE',
                 'data' => $updateSet
            ),          
            array(
                'label' => 'INSERT',
                'data' => $insertSet
            ),           
            array(
                'label' => 'DELETE',
                'data' => $deleteSet
            )
        ));

        echo '<a href="?benchmark='.$benchmark.'&set_id='.$_GET['set_id'].'&new_run=true">Run</a><br>'."\n";
    } else {
        $sets = MongoAdapter::getCollection('sets')->find();
        foreach($sets as $set) {
            echo '<a href="?benchmark='.$benchmark.'&set_id='.$set['_id'].'">'.$set['name'].'</a><br>'."\n";
        }

        echo '<br><a href="?benchmark='.$benchmark.'&new_set=true">New</a><br>'."\n";
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
};