<?php

define('SITE_DIR', realpath(dirname(__FILE__)));

require_once SITE_DIR.'/mongo.php';
// require_once SITE_DIR.'/db.php';

MongoAdapter::setDb(isset($_REQUEST['suite']) ? $_REQUEST['suite'] : 'default');

class Benchmark {
    private $_starTime = null;
    private $_stopTime = null;
    
    public function start()
    {
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
            'query' => 'test',
        ));
    }
}

$b = new Benchmark();
$b->start();
$b->stop();
$b->save();