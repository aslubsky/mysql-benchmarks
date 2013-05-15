<?php
ini_set('memory_limit', '1024M');
set_time_limit(1800);

define('SITE_DIR', realpath(dirname(__FILE__)));

require_once SITE_DIR.'/mongo.php';

if(isset($_GET['benchmark'])) {
    $benchmark = $_GET['benchmark'];
    MongoAdapter::setDb($benchmark);

    if(isset($_GET['new_run'])) {
        $run = array(
            'set_id' => $_GET['set_id'],
            'date' => date('Y-m-d H:i:s')
        );
        MongoAdapter::getCollection('runs')->insert($run);
//        echo '/usr/bin/ab -n 100 -c 100 "http://local.mysql-benchmarks.ua/test.php?benchmark='.$benchmark.'&run_id='.$run['_id'].'"';exit;
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
    if(isset($_GET['run_id'])) {
        $run = MongoAdapter::getCollection('runs')->findOne(array(
            'run_id' => $_GET['run_id']
        ));
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

        $queriesCountPerTime = array();
        $queriesPerTime = array();
        foreach($items as $item) {
            // print_r($item);exit;
            if(!isset($queriesCountPerTime[$item['date']])) {
                $queriesCountPerTime[$item['date']] = 0;
                $queriesPerTime[$item['date']] = array();
            }
            $queriesCountPerTime[$item['date']]++;
            $queriesPerTime[$item['date']] []= $item['exec_time'];
        }

        $speedDataSet = array();
        foreach($queriesCountPerTime as $date => $cnt) {
            // print_r($item);exit;
            $speedDataSet []= array(strtotime($date).'000', $cnt);
        }

        $avgTimeDataSet = array();
        foreach($queriesPerTime as $date => $times) {
//            print_r($times);exit;
            $count = count($times);
            $sum = 0;
            foreach($times as $time) {
                $sum += $time;
            }
            // print_r($item);exit;
            $avgTimeDataSet []= array(strtotime($date).'000', $sum/$count);
        }
//        print_r($avgTimeDataSet);exit;
        
        include 'chart.php';
        makeChart('Queries per sec', 'speed', $speedDataSet);
        makeChart('Average execution time (in sec)', 'avgTime', $avgTimeDataSet);

    }
    if(isset($_GET['set_id'])) {
        $runs = MongoAdapter::getCollection('runs')->find(array(
            'set_id' => $_GET['set_id']
        ));
        foreach($runs as $run) {
            echo '<a href="?benchmark='.$benchmark.'&set_id='.$_GET['set_id'].'&run_id='.$run['_id'].'">'.$run['date'].'</a><br>'."\n";
        }

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
}