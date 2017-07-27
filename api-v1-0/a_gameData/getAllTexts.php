<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

//if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
//    $userId = filter_var($_POST['userId']);
    if (isset($_POST['channelId'])) {
        $channelId = (int)$_POST['channelId'];
    } else $channelId = 2; // VK
//    $shardDb = $app->getShardDb($userId, $channelId);
    $mainDb = $app->getMainDb($channelId);
    try {
        $result = $mainDb->query("SELECT * FROM all_texts");
        if ($result) {
            $res = $result->fetchAll();
            $resp = [];
            if (!empty($res)) {
                foreach ($res as $key => $dict) {
                    $texts = [];
                    $texts['id'] = $dict['id'];
                    if ($_POST['languageId'] == 1) $texts['text'] = $dict['text_1'];
                    else $texts['text'] = $dict['text_2'];
                    $resp[] = $texts;
                }
            }
        } else {
            $json_data['id'] = 2;
            $json_data['status'] = 's307';
            throw new Exception("Bad request to DB!");
        }

        $json_data['message'] = $resp;
        echo json_encode($json_data);
    }
    catch (Exception $e)
    {
        $json_data['status'] = 's098';
        $json_data['message'] = $e->getMessage();
        echo json_encode($json_data);
    }
//}
//else
//{
//    $json_data['id'] = 1;
//    $json_data['status'] = 's023';
//    $json_data['message'] = 'bad POST[userId]';
//    echo json_encode($json_data);
//}