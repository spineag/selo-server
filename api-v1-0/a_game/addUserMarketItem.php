<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $userId = filter_var($_POST['userId']);
    $channelId = (int)$_POST['channelId'];
    $shardDb = $app->getShardDb($userId, $channelId);

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'].$_POST['resourceId'].$_POST['count'].$_POST['cost'].$_POST['numberCell'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's356';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            try {
                $result = $shardDb->query('SELECT * FROM user_market_item WHERE number_cell='.$_POST['numberCell'].' AND user_id ='.$_POST['userId']);
                if ($result) {
                    $res = $result->fetch();
                    if (!$res) {
                        $time = time();
                        if (!$_POST['inPapper']) $timeInPapper = 0;
                        else {
                            $timeInPapper = time();
                            $mainDb = $app->getMainDb($channelId);
                            if ($channelId == 2) {
                                $result = $mainDb->query('UPDATE users SET in_papper=' . $time . ' WHERE id=' . $_POST['userId']);
                            } else { // == 3 || == 4
                                $result = $shardDb->query('UPDATE user_info SET in_papper=' . $time . ' WHERE user_id=' . $_POST['userId']);
                            }
                        }
                        $result = $shardDb->queryWithAnswerId('INSERT INTO user_market_item SET user_id=' . $userId .
                            ', buyer_id=0, resource_id=' . $_POST['resourceId'] . ', time_start=' . $time .
                            ',time_sold=0, cost=' . $_POST['cost'] . ', resource_count=' . $_POST['count'] . ', in_papper=' . $_POST['inPapper'] . ', number_cell=' . $_POST['numberCell'] . ', time_in_papper=' . $timeInPapper . ', level=' . $_POST['level']);
                        if ($result) {
                            $res = [];
                            $res['id'] = $result[1];
                            $res['buyer_id'] = 0;
                            $res['time_start'] = $time;
                            $res['time_sold'] = 0;
                            $res['cost'] = $_POST['cost'];
                            $res['resource_id'] = $_POST['resourceId'];
                            $res['resource_count'] = $_POST['count'];
                            $res['in_papper'] = $_POST['inPapper'];
                            $res['number_cell'] = $_POST['numberCell'];
                            $res['time_in_papper'] = $_POST['timeInPapper'];
                        } else {
                            $json_data['id'] = 2;
                            $json_data['status'] = 's233';
                            throw new Exception("Bad request to DB!");
                        }

                        $json_data['message'] = $res;
                        echo json_encode($json_data);
                    }
                }
            } catch (Exception $e) {
                $json_data['status'] = 's014';
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
    $json_data['status'] = 's015';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}