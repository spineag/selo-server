<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

$app = Application::getInstance();
$channelId = (int)$_POST['channelId'];
$mainDb = $app->getMainDb($channelId);

$result = $mainDb->query("SELECT * FROM version");

if ($result) {
    $v = $result->fetchAll();
} else {
    $json_data['id'] = 1;
    $json_data['status'] = 's421';
    throw new Exception("Bad request to DB!");
}

try
{
    $resp = [];
    if (!empty($v)) {
        foreach ($v as $key => $dict) {
            $item = [];
            $item['id'] = $dict['id'];
            $item['name'] = $dict['name'];
            $item['version'] = $dict['version'];
            $resp[] = $item;
        }
    } else {
        $json_data['id'] = 1;
        $json_data['status'] = 's422';
        throw new Exception("EMPTY ANSWER");
    }

    $json_data['message'] = $resp;
    echo json_encode($json_data);
}
catch (Exception $e)
{
    $json_data['status'] = 's423';
    $json_data['message'] = $e;
    echo json_encode($json_data);
}


