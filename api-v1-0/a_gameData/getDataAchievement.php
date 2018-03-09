<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];
    $mainDb = $app->getMainDb($channelId);
    try {
        $result = $mainDb->query("SELECT * FROM data_achievement");
        if ($result) {
            $arr = $result->fetchAll();
        } else {
            $json_data['id'] = 2;
            $json_data['status'] = 's301';
            throw new Exception("Bad request to DB!");
        }

        $json_data['message'] = $arr;
        echo json_encode($json_data);
    }
    catch (Exception $e)
    {
        $json_data['status'] = 's098';
        $json_data['message'] = $e->getMessage();
        echo json_encode($json_data);
    }
