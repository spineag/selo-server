<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

const TIME_GAP = 5 * 60 * 60;
if (isset($_POST['userSocialId']) && !empty($_POST['userSocialId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];
    $mainDb = $app->getMainDb($channelId);

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $userId = filter_var($_POST['userId']);
        try {
            $resp = [];
            $uid = $app->getUserId($channelId, $_POST['userSocialId']);
            $shardDb = $app->getShardDb($uid , $channelId);
            $result = $shardDb->query("SELECT market_cell FROM user_info WHERE user_id =".$uid);
            $arr = $result->fetch();
            $response['market_cell'] = $arr['market_cell'];
            $time = time() - TIME_GAP;

            $result = $shardDb->query("UPDATE user_market_item SET in_papper=0, time_in_papper = 0 WHERE user_id = ". $uid . "
            AND in_papper = 1 AND time_in_papper < " . $time);

            $result = $shardDb->query("SELECT * FROM user_market_item WHERE user_id =" . $uid);
            $res = $result->fetchAll();
            foreach ($res as $value => $d) {
                if ((int)$d['buyer_id'] > 0) {
                    $result2 = $mainDb->query("SELECT social_id FROM users WHERE id =".$d['buyer_id']);
                    $arr = $result2->fetch();
                    $d['buyer_social_id'] = $arr['social_id'];
                } else {
                    if ($_POST['userId'] == $uid) {
                        if (time() - (int)$d['time_start'] > 24 * 60 * 60) {
                            $result2 = $mainDb->query("SELECT id FROM users WHERE social_id = 1");
                            $arr = $result2->fetch();
                            $result = $shardDb->query("UPDATE user_market_item SET buyer_id=" . $arr['id'] . ", time_sold=" . time() . ", in_papper=0 WHERE id=" . $d['id']);
                            $d['buyer_social_id'] = 1;
                            $d['buyer_id'] = $arr['id'];
                            $d['time_sold'] = time();
                            $d['in_papper'] = 0;
                        }
                    }
                }
                $d['shard_name'] = $shardDb->getDatabaseName();
                $resp[] = $d;
            }

            $response['items'] = $resp;
            $json_data['message'] = $response;
            echo json_encode($json_data);
        }
        catch (Exception $e)
        {
            $json_data['status'] = 's094';
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
    $json_data['status'] = 's095';
    $json_data['message'] = 'bad POST[userSocialId]';
    echo json_encode($json_data);
}
