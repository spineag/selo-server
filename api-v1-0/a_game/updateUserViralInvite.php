<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {  // only FB yet
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];
    try {
        $shardDb = $app->getShardDb($_POST['userId'], $channelId);
        $result = $shardDb->query('UPDATE user_info SET next_time_invite=' . $_POST['nextTime'] . ' WHERE user_id=' . $_POST['userId']);
        if (!$result) {
            $json_data['id'] = 2;
            $json_data['status'] = 's...';
            throw new Exception("Bad request to DB!");
        }
    } catch (Exception $e) {
        $json_data['status'] = 's...';
        $json_data['message'] = $e->getMessage();
        echo json_encode($json_data);
    }

    $json_data['message'] = '';
    echo json_encode($json_data);
}
else
{
    $json_data['id'] = 1;
    $json_data['status'] = 's203';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
