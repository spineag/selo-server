<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

$app = Application::getInstance();
$userId = filter_var($_POST['userId']);
$channelId = (int)$_POST['channelId'];
$shardDb = $app->getShardDb($userId, $channelId);

if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
    $m = md5($_POST['userId'].$_POST['houseDbId'].$_POST['petId'].$app->md5Secret());
    if ($m != $_POST['hash']) {
        $json_data['id'] = 6;
        $json_data['status'] = 's...';
        $json_data['message'] = 'wrong hash';
        echo json_encode($json_data);
    } else {
        try {
            $result = $shardDb->queryWithAnswerId('INSERT INTO user_pet SET user_id='.$userId.', house_db_id='.$_POST['houseDbId'].', pet_id='.$_POST['petId'].', time_eat=0, has_new_eat=0');
            if ($result) {
                $json_data['message'] = $result[1];
                echo json_encode($json_data);
            } else {
                $json_data['id'] = 2;
                $json_data['status'] = 's...';
                $json_data['message'] = 'bad query';
            }
        } catch (Exception $e) {
            $json_data['status'] = 's009';
            $json_data['message'] = $e->getMessage();
            echo json_encode($json_data);
        }
    }
} else {
    $json_data['id'] = 13;
    $json_data['status'] = 's221';
    $json_data['message'] = 'bad sessionKey';
    echo json_encode($json_data);
}