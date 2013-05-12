<?php

define('SITE_DIR', realpath(dirname(__FILE__)));

require_once SITE_DIR.'/memcache.php';
// require_once SITE_DIR.'/db.php';

// $test = (int)MemcacheAdapter::get('test');
// MemcacheAdapter::set('test', ++$test);
// echo MemcacheAdapter::get('test');

class Benchmark {
    private $_starTime = null;
    private $_stopTime = null;
    
    public function start()
    {
        $this->_starTime = microtime(TRUE);
        sleep(20);
    }
    
    public function stop()
    {
        $this->_stopTime = microtime(TRUE);
    }
    
    public function save()
    {
        echo number_format($this->_stopTime - $this->_starTime, 6);
    }
}

$b = new Benchmark();
$b->start();
$b->stop();
$b->save();