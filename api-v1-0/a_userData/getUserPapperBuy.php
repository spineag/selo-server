<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $userId = filter_var($_POST['userId']);
        $shardDb = $app->getShardDb($userId, $channelId);
        try {
            $resp = [];
            $result = $shardDb->query("SELECT * FROM user_papper_buy WHERE user_id =" . $userId);
            if ($result) {
                $arr = $result->fetchAll();
                if (count($arr)) {
                    foreach ($arr as $value => $dict) {
                        $res = [];
                        $res['buyer_id'] = $dict['buyer_id'];
                        $res['resource_id'] = $dict['resource_id'];
                        $res['resource_count'] = $dict['resource_count'];
                        $res['xp'] = $dict['xp'];
                        $res['cost'] = $dict['cost'];
                        $res['type_resource'] = $dict['type_resource'];
                        $res['time_to_new'] = $dict['time_to_new'];
                        $res['visible'] = $dict['visible'];
                        $resp[] = $res;
                    }
                }
            } else {
                $json_data['id'] = 2;
                $json_data['status'] = 's308';
                throw new Exception("Bad request to DB!");
            }

            $json_data['message'] = $resp;
            echo json_encode($json_data);
        }
        catch (Exception $e)
        {
            $json_data['status'] = 's100';
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
    $json_data['status'] = 's101';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
