<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    if (isset($_POST['channelId'])) {
        $channelId = (int)$_POST['channelId'];
    } else $channelId = 2; // VK
    $shardDb = $app->getShardDb($_POST['userId'], $channelId);
        try {
            $result = $shardDb->query('UPDATE user_friends SET friend_1 =' . $_POST['friend1'] . ', friend_2 ='. $_POST['friend2'] . ', friend_3 ='. $_POST['friend3'] . ', friend_4 ='. $_POST['friend4'] . ', friend_5 ='. $_POST['friend5'] . ' WHERE user_id=' . $_POST['userId']);
            if (!$result) {
                $json_data['id'] = 2;
                $json_data['status'] = 's340';
                throw new Exception("Bad request to DB!");
            }

            $json_data['message'] = '';
            echo json_encode($json_data);
        } catch (Exception $e) {
            $json_data['status'] = 's208';
            $json_data['message'] = $e->getMessage();
            echo json_encode($json_data);
        }
}
else
{
    $json_data['id'] = 1;
    $json_data['status'] = 's208';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
