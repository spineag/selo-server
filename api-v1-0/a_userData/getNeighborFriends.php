<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $userId = filter_var($_POST['userId']);
    if (isset($_POST['channelId'])) {
        $channelId = (int)$_POST['channelId'];
    } else $channelId = 2; // VK
    $shardDb = $app->getShardDb($userId, $channelId);
    $mainDb = $app->getMainDb($channelId);

    try {
        $result = $shardDb->query("SELECT * FROM user_friends WHERE user_id =" . $userId);
        if ($result) {
            $res = $result->fetch();
            if (!$res) {
                $result = $shardDb->queryWithAnswerId('INSERT INTO user_friends SET user_id=' . $userId . ', friend_1=0, friend_2=0, friend_3=0, friend_4=0, friend_5=0');
            } else {
                $resD = [];
                for ($i = 1; $i < 6; $i++) {
                    $name = 'friend_' . $i;
                    if ($res[$name]) {
                        $result2 = $mainDb->query('SELECT * FROM users WHERE id =' . $res[$name]);
                        $result3 = $result2->fetch();
                        $resp = [];
                        $resp['user_id'] = $result3['id'];
                        $resp['name'] = $result3['name'];
                        $resp['last_name'] = $result3['last_name'];
                        $resp['level'] = $result3['level'];
                        $resp['xp'] = $result3['xp'];
                        $resp['social_id'] = $result3['social_id'];
                        $resD[] = $resp;
                    }
                }
            }
            $json_data['message'] = $resD;
            echo json_encode($json_data);
        }
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