<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];
    $shardDb = $app->getShardDb($_POST['userId'], $channelId);

        try {
            $result = $shardDb->query("SELECT * FROM user_analytics WHERE user_id =" . $_POST['userId']);
            if ($result) {
                $arr = $result->fetchAll();
                if (!$arr) $result = $shardDb->query('INSERT INTO user_analytics SET user_id=' . $_POST['userId']. ',buy_paper =' . $_POST['buyPaper'] . ', done_order=' . $_POST['doneOrder'] . ', done_nyashuk=' . $_POST['doneNyashuk'] . ', count_session=' . $_POST['countSession']);
                else $result = $shardDb->query('UPDATE user_analytics SET buy_paper =' . $_POST['buyPaper'] . ', done_order=' . $_POST['doneOrder'] . ', done_nyashuk=' . $_POST['doneNyashuk'] . ', count_session=' . $_POST['countSession'] . ' WHERE user_id=' . $_POST['userId']);

            } else if (!$result) {
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
