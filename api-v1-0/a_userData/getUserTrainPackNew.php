<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userSocialId']) && !empty($_POST['userSocialId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];
    $mainDb = $app->getMainDb($channelId);
    $memcache = $app->getMemcache();

    $result = $mainDb->query("SELECT * FROM users WHERE social_id =".$_POST['userSocialId']);
    $arr = $result->fetch();
    $userId = $arr['id'];
    $userLevel = $arr['level'];

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $shardDb = $app->getShardDb($userId, $channelId);
        try {
            $result = $shardDb->query("SELECT * FROM user_train_pack WHERE user_id =".$userId);
            $arrMainPack = $result->fetch();
            $isGood = true;
            if ($arrMainPack) {
                $result = $shardDb->query("SELECT * FROM user_train_pack_item WHERE user_id =" . $userId . " AND user_train_pack_id=" . $arrMainPack['id']);
                $arrPacks = $result->fetchAll();
                if (!$arrPacks) $isGood = false;
            } else {
                $isGood = false;
            }

            if (!$isGood) {
                $result = $shardDb->query('DELETE FROM user_train_pack WHERE user_id='.$userId);
                $result = $shardDb->query('DELETE FROM user_train_pack_item WHERE user_id='.$userId);
                $arrDataTrainResource = [];
                $arrIds = [];
                $result = $mainDb->query("SELECT resource_id, big_count, small_count FROM data_train_resource");
                $arrDataTrainResource = $result->fetchAll();
                for ($i=0; $i<count($arrDataTrainResource); $i++) {
                    $arrIds[] = (int)$arrDataTrainResource[$i]['resource_id'];
                };

                $result = $mainDb->query("SELECT id, order_price, order_xp FROM resource WHERE block_by_level <=".$userLevel." AND id IN (" .implode(',', array_map('intval', $arrIds)). ") ORDER BY RAND() LIMIT 3");
                $dataResource = $result->fetchAll();
                $arr = [];
                for ($i=0; $i<3; $i++) {
                    $arrInfo = [];
                    $arrInfo['id'] = (int)$dataResource[$i]['id'];
                    $arrInfo['order_price'] = (int)$dataResource[$i]['order_price'];
                    $arrInfo['order_xp'] = (int)$dataResource[$i]['order_xp'];
                    for ($k=0; $k<count($arrDataTrainResource); $k++) {
                        if ($arrDataTrainResource[$k]['resource_id'] == $arrInfo['id']) {
                            $arrInfo['big_count'] = (int)$arrDataTrainResource[$k]['big_count'];
                            $arrInfo['small_count'] = (int)$arrDataTrainResource[$k]['small_count'];
                            break;
                        }
                    }
                    $arr[] = $arrInfo;
                }
//                $XPCount = ((int)$arr[0]['order_xp'] + (int)$arr[1]['order_xp'] + (int)$arr[2]['order_xp']) / 2;
//                $COINSCount = (int)$arr[1]['order_price'] + (int)$arr[1]['order_price'] + (int)$arr[2]['order_price'] + 10 / 2;
                //find the result XPCount and COINSCount
//                $tempArr=[];
//                foreach($arr as $key=>$arrT){
//                    $tempArr[$key]=$arr['order_xp'];
//                }
//                array_multisort($tempArr, SORT_NUMERIC, $arr);
//                $XPCount = (int)$arr[1]['order_xp'] + (int)$arr[2]['order_xp'];
//                $COINSCount = (int)$arr[1]['order_price'] + (int)$arr[2]['order_price'];

                $result = $shardDb->queryWithAnswerId('INSERT INTO user_train_pack SET user_id='.$userId);//', count_xp='.$XPCount.', count_money='.$COINSCount);
                $packId = $result[1];
                $arrMainPack = [];
                $arrMainPack['id'] = $packId;
//                $arrMainPack['count_xp'] = $XPCount;
//                $arrMainPack['count_money'] = $COINSCount;

                if ($userLevel >= 20) {
                    $countCells = 4;
                } else {
                    $countCells = 3;
                }
                $arrPacks = [];
//                $arrTempCountResource = [];
                for ($i = 0; $i < 3; $i++) {
                    if ($userLevel >= 20) {
                        $countResource = (int)rand($arr[$i]['big_count']/3*2, $arr[$i]['big_count']);
                    } else {
                        $countResource = (int)rand($arr[$i]['small_count']/3*2, $arr[$i]['small_count']);
                    }
//                    $arrTempCountResource[] = $countResource;
                    for ($k = 0; $k < $countCells; $k++) {
                        $p = [];
                        $result = $shardDb->queryWithAnswerId('INSERT INTO user_train_pack_item SET user_id='.$userId.', user_train_pack_id='.$packId.', resource_id='.$arr[$i]['id'].',
                         count_resource='.$countResource.', count_xp='.(int)$arr[$i]['order_xp']*$countResource.', count_money='.(int)$arr[$i]['order_price']*$countResource.', is_full=0, want_help=0, help_id=0');
                        $p['id'] = $result[1];
                        $p['resource_id'] = $arr[$i]['id'];
                        $p['count_xp'] = $arr[$i]['order_xp']*$countResource;
                        $p['count_resource'] = $countResource;
                        $p['count_money'] = $arr[$i]['order_price']*$countResource;
                        $p['is_full'] = 0;
                        $p['want_help'] = 0;
                        $p['help_id'] = 0;
                        $arrPacks[] = $p;
                    }
                }
            }
            $XPCount = ((int)$arrPacks[0]['count_xp']  + (int)$arrPacks[3]['count_xp'] + (int)$arrPacks[6]['count_xp']) / 2;
            $COINSCount = ((int)$arrPacks[0]['count_money']  + (int)$arrPacks[3]['count_money'] + (int)$arrPacks[6]['count_money']) * .6;
            $result = $shardDb->query('UPDATE user_train_pack SET count_xp =' . $XPCount .', count_money =' . $COINSCount . ' WHERE user_id=' . $userId);

            $result = $shardDb->query("SELECT * FROM user_train WHERE user_id =" . $userId);

            $pack = [];
            $pack['id'] = $arrMainPack['id'];
            $pack['count_xp'] = $XPCount;
            $pack['count_money'] = $COINSCount;
            if ($result) {
                $arr = $result->fetch();
                $pack['state'] = $arr['state'];
                $pack['time_work'] = time() - $arr['time_start'];
            }
            $pack['items'] = [];
            foreach ($arrPacks as $key => $d) {
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
