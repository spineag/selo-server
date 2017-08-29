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

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'].$_POST['ids'].$_POST['counts'].$_POST['xp'].$_POST['coins'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's358';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            try {
                if ($_POST['catId']) $catId = $_POST['catId'];
                else $catId = 0;
                $result = $shardDb->queryWithAnswerId('INSERT INTO user_order SET user_id='.$userId.', ids="'.$_POST['ids'].'", counts="'.$_POST['counts'].'", xp='.$_POST['xp'].', coins='.$_POST['coins'].
                    ', add_coupone='.$_POST['addCoupone'].', start_time='.(time() + (int)$_POST['delay']).', place='.$_POST['place'].', faster_buyer='.$_POST['fasterBuyer'].', cat_id='.$catId);

                if ($result) {
                    $json_data['message'] = $result[1];
                    echo json_encode($json_data);
                } else {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's018';
                    $json_data['message'] = 'bad query';
                }

            } catch (Exception $e) {
                $json_data['status'] = 's019';
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
    $json_data['status'] = 's020';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
