<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    if (isset($_POST['channelId'])) {
        $channelId = (int)$_POST['channelId'];
    } else $channelId = 2; // VK

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'].$_POST['buyerId'].$_POST['resourceId'].$_POST['resourceCount'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's402';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            $userId = filter_var($_POST['userId']);
            $shardDb = $app->getShardDb($userId, $channelId);
            try {
                $time = time();
                $result = $shardDb->query('UPDATE user_papper_buy SET resource_id=' . $_POST['resourceId'] . ', resource_count=' .
                    $_POST['resourceCount'] . ', xp=' . $_POST['xp'] . ', cost=' . $_POST['cost'] . ', time_to_new=' .
                    $time . ', visible=' . $_POST['visible'] . ', type_resource =' . $_POST['typeResource'] .
                    ' WHERE user_id=' . $userId . ' AND buyer_id=' . $_POST['buyerId']);
                if (!$result) {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's339';
                    throw new Exception("Bad request to DB!");
                }

                $json_data['message'] = '';
                echo json_encode($json_data);
            } catch (Exception $e) {
                $json_data['status'] = 's186';
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
    $json_data['status'] = 's187';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
