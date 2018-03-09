<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $userId = filter_var($_POST['userId']);
    $channelId = (int)$_POST['channelId'];
    $shardDb = $app->getShardDb($userId, $channelId);

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        try {
            $result = $shardDb->query('DELETE FROM user_building WHERE id='.$_POST['dbId'].' AND user_id = '.$userId);
            $result2 = $shardDb->query('DELETE FROM user_tree WHERE id='.$_POST['treeDbId'].' AND user_id = '.$userId);
            if ($result && $result2) {
                $json_data['message'] = '';
                echo json_encode($json_data);
            } else {
                $json_data['id'] = 2;
                $json_data['status'] = 's250';
                throw new Exception("Bad request to DB!");
            }
        }

        catch (Exception $e)
        {
            $json_data['status'] = 's060';
            $json_data['message'] = $e->getMessage();
            echo json_encode($json_data);
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
    $json_data['status'] = 's061';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}