<?php
/**
 * Created by IntelliJ IDEA.
 * User: andy
 * Date: 8/11/16
 * Time: 17:00
 */
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');

$app = Application::getInstance();
$memcache = $app->getMemcache();

try {
    $memcache->flush();
    echo '1';
}catch (Exception $e) {
    echo $e;
}