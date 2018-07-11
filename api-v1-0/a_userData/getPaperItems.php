<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $mainDb = $app->getMainDb($channelId);
        $userId = filter_var($_POST['userId']);
        $shardDb = $app->getShardDb($userId, $channelId);
        try {
            $resp = [];
            $temp = [];
            $endTime = time() - 5*60*60;
//            if ($channelId == 2) {
            $arShards = $app->getAllShardsDb($channelId);
            foreach ($arShards as $key => $shard) {
                $result = $shard->query("SELECT * FROM user_market_item WHERE buyer_id = 0 AND  level <= ".$_POST['level']." AND user_id <> ".$userId." ORDER BY RAND() LIMIT 60");
                $arr = $result->fetchAll();
                foreach ($arr as $key => $a) {
                    $q = [];
                    $q['id'] = $a['id'];
                    $q['user_id'] = $a['user_id'];
                    $q['cost'] = $a['cost'];
                    $q['resource_id'] = $a['resource_id'];
                    $q['resource_count'] = $a['resource_count'];
                    $q['shard_name'] = $shard->getDatabaseName();

//                        $result2 = $mainDb->query("SELECT * FROM users WHERE id =".$q['user_id']);
//                        $arr2 = $result->fetch();
//                        $q['user_social_id'] = $arr2['social_id'];
//                        $q['level'] = $arr2['level'];
//                        $q['need_help'] = $app->checkNeedHelp($q['user_id'], $channelId);
//                        if ($q['need_help'] == 0) $q['need_help'] = $app->checkNeedHelpTrain($q['user_id'], $channelId);

                    $temp[]=$q;
                }
            }
            if (count($temp) > 60) {
                shuffle($temp);
                $temp = array_slice($temp, 0, 60);
            }
            foreach ($temp as $key => $dict) {
                $result = $mainDb->query("SELECT * FROM users WHERE id =".$dict['user_id']);
                $arr = $result->fetch();
                $q = [];
                $q['id'] = $dict['id'];
                $q['user_id'] = $dict['user_id'];
                $q['cost'] = $dict['cost'];
                $q['resource_id'] = $dict['resource_id'];
                $q['resource_count'] = $dict['resource_count'];
                $q['shard_name'] = $dict['shard_name'];
                $q['user_social_id'] = $arr['social_id'];
                $q['level'] = $arr['level'];
                $q['need_help'] = $app->checkNeedHelp($q['user_id'], $channelId);
                if ($q['need_help'] == 0) $q['need_help'] = $app->checkNeedHelpTrain($q['user_id'], $channelId);
                $resp[] = $q;
            }
//            } else { // delete this after testing
//                $result = $shardDb->query("SELECT * FROM user_market_item WHERE in_papper = 1 AND buyer_id = 0 AND time_in_papper > " . $endTime . " AND level <= " . $_POST['level'] . " AND user_id <> " . $userId . " ORDER BY RAND() LIMIT 60");
//                if ($result) {
//                    $arr = $result->fetchAll();
//                    foreach ($arr as $key => $dict) {
//                        $res = [];
//                        $res['id'] = $dict['id'];
//                        $res['user_id'] = $dict['user_id'];
//                        $res['cost'] = $dict['cost'];
//                        $res['resource_id'] = $dict['resource_id'];
//                        $res['resource_count'] = $dict['resource_count'];
//
//                        $result2 = $mainDb->query("SELECT * FROM users WHERE id =" . $dict['user_id']);
//                        $arr = $result2->fetch();
//                        $res['user_social_id'] = $arr['social_id'];
//                        $res['level'] = $arr['level'];
//                        $res['need_help'] = $app->checkNeedHelp($dict['user_id'], $channelId);
//                        if ($res['need_help'] == 0) $res['need_help'] = $app->checkNeedHelpTrain($dict['user_id'], $channelId);
//                        $resp[] = $res;
//                    }
//                } else {
//                    $json_data['id'] = 2;
//                    $json_data['status'] = 's297';
//                    throw new Exception("Bad request to DB!");
//                }
//            }

            $json_data['message'] = $resp;
            echo json_encode($json_data);
        }
        catch (Exception $e)
        {
            $json_data['status'] = 's084';
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
    $json_data['status'] = 's085';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
