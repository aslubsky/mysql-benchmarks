<?php

define('SITE_DIR', realpath(dirname(__FILE__)));

require_once SITE_DIR.'/mongo.php';
require_once SITE_DIR.'/db.php';

MongoAdapter::setDb(isset($_REQUEST['benchmark']) ? $_REQUEST['benchmark'] : 'default');

class Benchmark {
    private $_starTime = null;
    private $_stopTime = null;
    
    public function start()
    {
        $query = MongoAdapter::getCollection('queries')->find(array(
            $_REQUEST['run_id']
        ));
        $this->_starTime = microtime(TRUE);
        sleep(5);
    }
    
    public function stop()
    {
        $this->_stopTime = microtime(TRUE);
    }
    
    public function save()
    {
        MongoAdapter::add(array(
            'date' => date('Y-m-d H:i:s'),
            'exec_time' => number_format($this->_stopTime - $this->_starTime, 6),
            'run_id' => $_REQUEST['run_id'],
            'query' => 'test'
        ));
    }
}

$b = new Benchmark();
$b->start();
$b->stop();
$b->save();