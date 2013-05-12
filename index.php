<?php

define('SITE_DIR', realpath(dirname(__FILE__)));

require_once SITE_DIR.'/mongo.php';

$cursor = MongoAdapter::getAll();
foreach($cursor as $item) {
    print_r($item);
}