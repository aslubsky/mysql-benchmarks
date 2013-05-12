<?php

class MemcacheAdapter {
    private static $memcache;
   
    public static function init()
    {
        if (!extension_loaded('memcache')) {
            throw new Exception('Extension memcache is not loaded');
        }
        $host = 'localhost';
        $port = 11211;
        self::$memcache = new Memcache();
        if (self::$memcache->addServer($host, $port) === false || @self::$memcache->getStats() === false) {
            throw new Exception('Could not connect to Memcache server ' . $host . ':' . $port);
        }
    }
   
    public static function exec($q)
    {
        return self::$conn->exec($q);
    }
   
    public static function set($id, $data, $lifeTime = false)
    {
        if (!($result = self::$memcache->replace($id, $data, false, $lifeTime))) {
            $result = self::$memcache->set($id, $data, false, $lifeTime);
        }
        return $result;
    }

    public static function get($id)
    {
        $res = self::$memcache->get($id);
//        var_dump($res);
        return $res;
    }

    public static function clear()
    {
        self::$memcache->flush();
    }
}

MemcacheAdapter::init();