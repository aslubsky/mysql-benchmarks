<?php

define('SITE_DIR', realpath(dirname(__FILE__)));

require_once SITE_DIR.'/mongo.php';
require_once SITE_DIR.'/db.php';


$_REQUEST['run_id'] = '518fe746aeecf93002000003';
MongoAdapter::setDb(isset($_REQUEST['benchmark']) ? $_REQUEST['benchmark'] : 'default');

class Benchmark {
    private $_starTime = null;
    private $_stopTime = null;
    private $_query = null;
    
    public function start()
    {
        $randomNumber = mt_rand(0, MongoAdapter::getCollection('queries')->count()+1);
        //findOne({random_field: {$gte: rand()}}) 
        //http://stackoverflow.com/questions/2824157/random-record-from-mongodb
        $query = 
            MongoAdapter::getCollection('queries')->find(array(
                'run_id' => $_REQUEST['run_id']
            ))
            ->limit(1);
            // ->skip($randomNumber)
            // ->next();
        print_r($query->current());exit('O_o');
        $this->_starTime = microtime(TRUE);
        $this->_query = $query['query'];
        $isSelect = stripos($this->_query, 'sel') !== false;
        if($isSelect) {
            DB::fetchAll($this->_query);
        } else {
            DB::exec($this->_query);
        }
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
            'query' => $this->_query
        ));
    }
}

$b = new Benchmark();
$b->start();
$b->stop();
$b->save();