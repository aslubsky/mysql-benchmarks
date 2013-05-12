<?php

class MongoAdapter {

    private static $conn;
    
    private static $db;
   
    public static function init()
    {
        if (!extension_loaded('mongo')) {
            throw new Exception('Extension mongodb is not loaded');
        }
        self::$conn = new Mongo();
    }
    
    public static function setDb($db = 'default')
    {
        self::$db = self::$conn->{$db};
    }
    
    public static function add($data)
    { 
        // access collection
        $collection = self::$db->items;
        $collection->insert($data);
    }

    public static function clear()
    {
        // self::$memcache->flush();
    }

    public static function getCollection($collName)
    {
        return self::$db->{$collName};
    }

    public static function createRun()
    {
        $collection = self::$db->runs;
        return $collection->insert(array(
            'date' => date('Y-m-d H:i:s')
        ));
    }

    public static function listDBs()
    {
        return self::$conn->listDBs();
    }
}

MongoAdapter::init();