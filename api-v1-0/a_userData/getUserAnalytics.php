<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's413';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            $mainDb = $app->getMainDb($channelId);
            $userId = filter_var($_POST['userId']);
            $shardDb = $app->getShardDb($userId, $channelId);
            try {
                $result = $shardDb->query("SELECT * FROM user_analytics WHERE user_id =" . $_POST['userId']);
                $uS = $result->fetch();
                $user['buy_paper'] = $uS['buy_paper'];
                $user['done_order'] = $uS['done_order'];
                $user['done_nyashuk'] = $uS['done_nyashuk'];
                $user['count_session'] = $uS['count_session'];

                $json_data['message'] = $user;
                echo json_encode($json_data);
            } catch (Exception $e) {
                $json_data['status'] = 's092';
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
    $json_data['status'] = 's093';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
