<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $userId = filter_var($_POST['userId']);
    $channelId = (int)$_POST['channelId'];
    $shardDb = $app->getShardDb($userId, $channelId);

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'].$_POST['dbId'].$_POST['count'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's350';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            try {
                $result = $shardDb->query('UPDATE user_building SET count_cell = '.$_POST['count'].' WHERE id='.$_POST['dbId'].' AND user_id = '.$_POST['userId']);
                if (!$result) {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's231';
                    throw new Exception("Bad request to DB!");
                }
                $json_data['message'] = '';
                echo json_encode($json_data);
            } catch (Exception $e) {
                $json_data['status'] = 's001';
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
}
else
{
    $json_data['id'] = 1;
    $json_data['status'] = 's002';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
