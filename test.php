<?php

define('SITE_DIR', realpath(dirname(__FILE__)));

require_once SITE_DIR.'/mongo.php';
require_once SITE_DIR.'/db.php';


MongoAdapter::setDb(isset($_REQUEST['benchmark']) ? $_REQUEST['benchmark'] : 'default');

class Benchmark {
    private $_starTime = null;
    private $_stopTime = null;
    private $_query = null;
    
    public function run($setId, $runId)
    {
        $queries = MongoAdapter::getCollection('queries')->find(array(
            'set_id' => new MongoId($setId)
        ));
        foreach($queries as $query) {
            $this->_query = $query;
            $this->start();
            $this->stop();
            $this->save($setId);
        }
    }
    
    public function start()
    {
        $this->_starTime = microtime(TRUE);
        $isSelect = stripos($this->_query['query'], 'sel') !== false;
        if($isSelect) {
            DB::fetchAll($this->_query['query']);
        } else {
            DB::exec($this->_query['query']);
        }
    }
    
    public function stop()
    {
        $this->_stopTime = microtime(TRUE);
    }
    
    public function save($setId)
    {
        $type = 0;
        if(stripos($this->_query['query'], 'sel') !== false) {
            $type = 1;
        }
        if(stripos($this->_query['query'], 'upd') !== false) {
            $type = 2;
        }
        if(stripos($this->_query['query'], 'ins') !== false) {
            $type = 3;
        }
        if(stripos($this->_query['query'], 'del') !== false) {
            $type = 4;
        }
        MongoAdapter::getCollection('items')->insert(array(
            'date' => date('Y-m-d H:i:s'),
            'exec_time' => number_format($this->_stopTime - $this->_starTime, 6),
            'set_id' => $setId,
            'query' => $this->_query['_id'],
            'type' => $type
        ));
    }
}

$b = new Benchmark();
$b->run($_REQUEST['set_id']);