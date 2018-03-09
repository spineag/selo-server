<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];
    $shardDb = $app->getShardDb($_POST['userId'], $channelId);
    $mainDb = $app->getMainDb($channelId);

    $result = $shardDb->query("SELECT * FROM user_miss WHERE user_id =" . $_POST['userId'] .' AND user_id_miss = ' .$_POST["userMissId"]);
    if ($result) {
        $arr = $result->fetchAll();
        if (!$arr) {
            $result = $shardDb->query('INSERT INTO user_miss SET user_id=' . $_POST['userId']. ', user_id_miss=' . $_POST["userMissId"] . ', count_send = ' . $_POST['countSend'] . ', send ='  . $_POST['send']);
        } else {
            $result = $shardDb->query('UPDATE user_miss SET count_send =' . $_POST['countSend'] . ', send ='  . $_POST['send'].' WHERE user_id='.$_POST["userId"].' AND user_id_miss='.$_POST["userMissId"]);
        }
        $result = $shardDb->query('UPDATE user_info SET miss_date =' . time() .' AND user_id='.$_POST["userId"]);
    }
    $json_data['message'] = '';
    echo json_encode($json_data);
}
else
{
    $json_data['id'] = 1;
    $json_data['status'] = 's457';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
