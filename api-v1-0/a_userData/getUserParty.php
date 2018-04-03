<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $userId = filter_var($_POST['userId']);
    $channelId = (int)$_POST['channelId'];
    $shardDb = $app->getShardDb($userId, $channelId);
    try {
        $result = $shardDb->query("SELECT * FROM user_party WHERE user_id =" . $userId);
        if ($result) {
            $partyALL = $result->fetchAll();
            if (!empty($partyALL)) {
                $res = [];
                foreach ($partyALL as $key => $party) {
                    $res[] = $party;
                }
            } else {
                $result = $shardDb->queryWithAnswerId('INSERT INTO user_party SET user_id=' . $userId);
                $res = [];
                $resp = [];
                $resp['id_party'] = -1;
                $resp['count_resource'] = 0;
                $resp['took_gift'] = "0&0&0&0&0";
                $resp['show_window'] = 0;
                $res[] = $resp;
            }
        } else {
            $json_data['id'] = 2;
            $json_data['status'] = 's307';
            throw new Exception("Bad request to DB!");
        }
    
        $json_data['message'] = $res;
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