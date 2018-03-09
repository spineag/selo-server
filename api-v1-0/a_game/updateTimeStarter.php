<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];
    $shardDb = $app->getShardDb($_POST['userId'], $channelId);

    try {
        $result = $shardDb->query('UPDATE user_info SET time_starter_pack=' . time() . ' WHERE user_id=' . $_POST['userId']);
        if (!$result) {
            $json_data['id'] = 2;
            $json_data['status'] = 's335';
            throw new Exception("Bad request to DB!");
        }

        $json_data['message'] = '';
        echo json_encode($json_data);
    } catch (Exception $e) {
        $json_data['status'] = 's182';
        $json_data['message'] = $e->getMessage();
        echo json_encode($json_data);
    }
}
else
{
    $json_data['id'] = 1;
    $json_data['status'] = 's183';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
