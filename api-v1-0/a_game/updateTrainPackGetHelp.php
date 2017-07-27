<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    if (isset($_POST['channelId'])) {
        $channelId = (int)$_POST['channelId'];
    } else $channelId = 2; // VK

            $userId = filter_var($_POST['userId']);
            $shardDb = $app->getShardDb($userId, $channelId);
            try {
                $result = $shardDb->query('UPDATE user_train_pack_item SET help_id="' . $_POST['helpId'] .'",want_help=0, is_full=1  WHERE id=' . $_POST['id']);
                if (!$result) {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's342';
                    throw new Exception("Bad request to DB!");
                }

                $json_data['message'] = '';
                echo json_encode($json_data);
            } catch (Exception $e) {
                $json_data['status'] = 's190';
                $json_data['message'] = $e->getMessage();
                echo json_encode($json_data);
            }
}
else
{
    $json_data['id'] = 1;
    $json_data['status'] = 's191';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
