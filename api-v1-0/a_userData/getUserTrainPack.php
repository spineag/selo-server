<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userSocialId']) && !empty($_POST['userSocialId'])) {
    $app = Application::getInstance();
    if (isset($_POST['channelId'])) {
        $channelId = (int)$_POST['channelId'];
    } else $channelId = 2; // VK
    $mainDb = $app->getMainDb($channelId);

    $result = $mainDb->query("SELECT * FROM users WHERE social_id =".$_POST['userSocialId']);
    $arr = $result->fetch();
    $userId = $arr['id'];
    $userLevel = $arr['level'];

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $shardDb = $app->getShardDb($userId, $channelId);
        try {
            $result = $shardDb->query("SELECT * FROM user_train_pack WHERE user_id =".$userId);
            $arr = $result->fetch();
            if (empty($arr)) {
                $result = $mainDb->query("SELECT resource_id, big_count, small_count FROM data_train_resource");
                $arrTrainResource = $result->fetchAll();
                for ($i=0; $i<count($arrTrainResource); $i++) {
                    $arr[] = $arrTrainResource[$i]['resource_id'];
                };
                $result = $mainDb->query("SELECT id, order_price, order_xp FROM resource WHERE block_by_level <=".$userLevel." AND id IN (" .implode(',', array_map('intval', $arr)). ") ORDER BY RAND() LIMIT 3");
                $arrDataResource = $result->fetchAll();
                $arr = [];
                for ($i=0; $i<3; $i++) {
                    $arrInfo = [];
                    $arrInfo['id'] = $arrDataResource[$i]['id'];
                    $arrInfo['order_price'] = $arrDataResource[$i]['order_price'];
                    $arrInfo['order_xp'] = $arrDataResource[$i]['order_xp'];
                    for ($k=0; $k<count($arrTrainResource); $k++) {
                        if ($arrTrainResource[$k]['resource_id'] == $arrInfo['id']) {
                            $arrInfo['big_count'] = $arrTrainResource[$k]['big_count'];
                            $arrInfo['small_count'] = $arrTrainResource[$k]['small_count'];
                            break;
                        }
                    }
                    $arr[] = $arrInfo;
                }

                //find the result XPCount and COINSCount
//                $tempArr=[];
//                foreach($arr as $key=>$arrT){
//                    $tempArr[$key]=$arrT['order_xp'];
//                }
//                array_multisort($tempArr, SORT_NUMERIC, $arr);
//                $XPCount = (int)$arr[1]['order_xp'] + (int)$arr[2]['order_xp'];
//                $COINSCount = (int)$arr[1]['order_price'] + (int)$arr[2]['order_price'];

                $result = $shardDb->insert('user_train_pack',
                    ['user_id' => $userId, 'count_xp' => $XPCount, 'count_money' => $COINSCount],
                    ['int', 'int', 'int']);
                $result = $shardDb->query("SELECT id FROM user_train_pack WHERE user_id =".$userId);
                if (!$result) {
                    $json_data['status'] = 's108';
                    $json_data['message'] = 3;
                    echo json_encode($json_data);
                }
                $pack = $result->fetch();
                if ($userLevel >= 20) {
                    $countCells = 4;
                } else {
                    $countCells = 3;
                }
                $arrTempCountResource = [];
                for ($i = 0; $i < 3; $i++) {
                    if ($userLevel >= 20) {
                            $countResource = rand($arr[$i]['big_count']/3*2,$arr[$i]['big_count']);
                        } else {
                            $countResource = rand($arr[$i]['small_count']/3*2, $arr[$i]['small_count']);
                        }
                    $arrTempCountResource[] = $countResource;
                    for ($k = 0; $k < $countCells; $k++) {
                        $result = $shardDb->insert('user_train_pack_item',
                            ['user_id' => $userId, 'user_train_pack_id' => $pack['id'], 'resource_id' => $arr[$i]['id'], 'count_resource' => $countResource, 'count_xp' => $arr[$i]['order_xp']*$countResource, 'count_money' => $arr[$i]['order_price']*$countResource, 'is_full' => 0],
                            ['int', 'int', 'int', 'int', 'int', 'int', 'int']);
                    }
                }
                $XPCount = ((int)$arr[0]['order_xp'] * $arrTempCountResource[0]  + (int)$arr[1]['order_xp'] * $arrTempCountResource[1] + (int)$arr[2]['order_xp'] * $arrTempCountResource[2]) / 2;
                $COINSCount = ((int)$arr[0]['order_price'] * $arrTempCountResource[0] + (int)$arr[1]['order_price'] * $arrTempCountResource[1] + (int)$arr[2]['order_price'] * $arrTempCountResource[2] + 10) / 2;
                $result = $shardDb->query('UPDATE user_train_pack SET count_xp =' . $XPCount .', count_money =' . $COINSCount . ' WHERE user_id=' . $userId);

                $result = $shardDb->query("SELECT * FROM user_train_pack WHERE user_id =".$userId);
                $arr = $result->fetch();
            }

            $pack = [];
            $pack['id'] = $arr['id'];
            $pack['count_xp'] = $arr['count_xp'];
            $pack['count_money'] = $arr['count_money'];
            $pack['items'] = [];
            $result = $shardDb->query("SELECT * FROM user_train_pack_item WHERE user_id =".$userId." AND user_train_pack_id=".$arr['id']);
            $arr = $result->fetchAll();
            if (!empty($arr)) {
                foreach ($arr as $key => $d) {
                    $item = [];
                    $item['id'] = $d['id'];
                    $item['resource_id'] = $d['resource_id'];
                    $item['count_xp'] = $d['count_xp'];
                    $item['count_money'] = $d['count_money'];
                    $item['count_resource'] = $d['count_resource'];
                    $item['is_full'] = $d['is_full'];
                    $item['want_help'] = $d['want_help'];
                    $item['help_id'] = $d['help_id'];
                    $pack['items'][] = $item;
                }
            }

            $json_data['message'] = $pack;
            echo json_encode($json_data);
        }
        catch (Exception $e)
        {
            $json_data['status'] = 's109';
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
    $json_data['status'] = 's110';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
