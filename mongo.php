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

    public static function getAll()
    {
        $collection = self::$db->items;
        return $collection->find();
    }
}

MongoAdapter::init();