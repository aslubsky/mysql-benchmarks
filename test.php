<?php

define('SITE_DIR', realpath(dirname(__FILE__)));

require_once SITE_DIR.'/mongo.php';
require_once SITE_DIR.'/db.php';


// $_REQUEST['run_id'] = '518fe746aeecf93002000003';
MongoAdapter::setDb(isset($_REQUEST['benchmark']) ? $_REQUEST['benchmark'] : 'default');

class Benchmark {
    private $_starTime = null;
    private $_stopTime = null;
    private $_query = null;
    
    public function start()
    {
        $rand = mt_rand();
        // echo $rand;
        $query = 
            MongoAdapter::getCollection('queries')->findOne(array(
                'run_id' => new MongoId($_REQUEST['run_id']),
                'random' => array(
                    '$gte' => $rand
                )
            ));
        if(!$query) {
            $query = 
                MongoAdapter::getCollection('queries')->findOne(array(
                    'run_id' => new MongoId($_REQUEST['run_id']),
                    'random' => array(
                        '$lte' => $rand
                    )
                ));
        }
        // if($query['_id'].'' != '51913824aeecf9c80d000003') {
            // print_r($query['_id']);exit('O_o');
        // }
        // exit(' |');
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