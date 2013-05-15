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

    public static function getCollection($collName)
    {
        return self::$db->{$collName};
    }

    public static function listDBs()
    {
        return self::$conn->listDBs();
    }

    public static function createDefaultBenchmark()
    {
        self::setDb('default');
        self::$db->createCollection('tmp_collection');
        self::$db->dropCollection('tmp_collection');
    }
}

MongoAdapter::init();