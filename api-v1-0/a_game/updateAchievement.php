<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $userId = filter_var($_POST['userId']);
    $channelId = (int)$_POST['channelId'];
    $shardDb = $app->getShardDb($userId, $channelId);
    try {
        $result = $shardDb->query('SELECT * FROM user_achievement WHERE user_id =' . $userId . ' AND achievement_id =' . $_POST['achievementId']);
        if ($result) {
            $res = $result->fetch();
            if (!$res) {
                $result = $shardDb->query('INSERT INTO user_achievement SET user_id=' . $userId . ', achievement_id=' . $_POST['achievementId'] . ', resource_count=' . $_POST['resourceCount']);
            } else {
                $result = $shardDb->query('UPDATE user_achievement SET resource_count=' . $_POST['resourceCount'] . ', show_panel=' . $_POST['showPanel'] . ', took_gift = "' . $_POST['tookGift'] . '" WHERE user_id =' . $userId . ' AND achievement_id =' . $_POST['achievementId']);
            }
        } else {
            $json_data['id'] = 2;
            $json_data['status'] = 's307';
            throw new Exception("Bad request to DB!");
        }

        $json_data['message'] = $result;
        echo json_encode($json_data);
    } catch (Exception $e) {
        $json_data['status'] = 's098';
        $json_data['message'] = $e->getMessage();
        echo json_encode($json_data);
    }
} else {
    $json_data['id'] = 1;
    $json_data['status'] = 's023';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}