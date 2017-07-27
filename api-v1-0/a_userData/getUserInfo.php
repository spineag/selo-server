<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's413';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            $mainDb = $app->getMainDb($channelId);
            $userId = filter_var($_POST['userId']);
            $shardDb = $app->getShardDb($userId, $channelId);
            try {
                $result = $mainDb->query("SELECT * FROM users WHERE id =" . $_POST['userId']);
                $u = $result->fetch();
                $socialId = $u['social_id'];
                $user = [];
                $user['hard_count'] = $u['hard_count'];
                $user['soft_count'] = $u['soft_count'];
                $user['yellow_count'] = $u['yellow_count'];
                $user['green_count'] = $u['green_count'];
                $user['red_count'] = $u['red_count'];
                $user['blue_count'] = $u['blue_count'];
                $user['level'] = $u['level'];
                $user['xp'] = $u['xp'];
                $user['is_tester'] = $u['is_tester'];
                $user['tutorial_step'] = $u['tutorial_step'];
                $user['day'] = time();

                $result = $shardDb->query("SELECT * FROM user_info WHERE user_id =" . $_POST['userId']);
                $uS = $result->fetch();
                $user['scale'] = $uS['scale'];
                $user['music'] = $uS['musics'];
                $user['sound'] = $uS['sounds'];
                $user['market_cell'] = $uS['market_cell'];
                $user['day_daily_gift'] = $uS['day_daily_gift'];
                $user['count_daily_gift'] = $uS['count_daily_gift'];
                $user['starter_pack'] = $uS['starter_pack'];
                $user['sale_pack'] = $uS['sale_pack'];
                $user['miss_date'] = $uS['miss_date'];
                $user['ambar_level'] = $uS['ambar_level'];
                $user['sklad_level'] = $uS['sklad_level'];
                $user['cut_scene'] = $uS['cutscene'];
                $user['mini_scene'] = $uS['miniscene'];
                $user['language'] = $uS['language_id'];
                $user['daily_bonus_day'] = gmdate("d", $uS['daily_bonus_day']);
                $user['count_daily_bonus'] = $uS['count_daily_bonus'];
                if ($uS['mouse_day'] == '0') {
                    $user['mouse_day'] = 0;
                } else {
                    $user['mouse_day'] = gmdate("d", $uS['mouse_day']);
                }
                $user['announcement'] = $uS['announcement'];
                $user['mouse_count'] = $uS['mouse_count'];
                $user['time_paper'] = $uS['time_paper'];
                $user['in_papper'] = $uS['in_papper'];
                $user['chest_day'] = gmdate("d", $uS['chest_day']);
                $user['count_chest'] = $uS['count_chest'];
                $user['notification_new'] = $uS['notification_new'];
                $user['wall_order_item_time'] = gmdate("d", $uS['wall_order_item_time']);
                $user['wall_train_item'] = gmdate("d", $uS['wall_train_item']);
                $user['open_order'] = $uS['open_order'];

                if ($channelId == 3) { // OK
                    $result = $mainDb->query("SELECT * FROM transaction_lost WHERE uid=" . $socialId);
                    $ar = $result->fetchAll();
                    if ($ar && count($ar)) {
                        foreach ($ar as $key => $p) {
                            if ($p['product_code'] == '13') {

                                $result = $mainDb->query('UPDATE users SET starter_pack = 1 WHERE id=' . $_POST['userId']);
                                $result = $mainDb->query("SELECT * FROM data_starter_pack");
                                $startPackData = $result->fetch();
                                $oType = (int)$startPackData['object_type'];
                                if ($oType == 8 || $oType == 7 || $oType == 5) { // RESOURCE, INSTRUMENT, PLANT
                                    $result = $shardDb->query("SELECT count FROM user_resource WHERE user_id = " . $_POST['userId'] . " AND resource_id=" . $startPackData['object_id']);
                                    $ar2 = $result->fetch();
                                    if (count($ar2)) {
                                        $count = (int)$ar2['count'] + (int)$startPackData['object_count'];
                                        $result = $shardDb->query("UPDATE user_resource SET count=" . $count . " WHERE user_id=" . $_POST['userId'] . " AND resource_id=" . $startPackData['object_id']);
                                    } else {
                                        $result = $shardDb->query('INSERT INTO user_resource SET user_id=' . $_POST['userId'] . ', resource_id=' . $startPackData['object_id'] . ', count=' . $startPackData['object_count']);
                                    }
                                } else if ($oType == 4 || $oType == 9 || $oType == 10 || $oType == 30 || $oType == 31 || $oType == 32) {  // diff decors
                                    $c = (int)$startPackData['object_count'];
                                    if ($c < 1) $c = 1;
                                    for ($x = 0; $x < $c; $x++) {
                                        $result = $shardDb->query('INSERT INTO user_building SET building_id = ' . $startPackData['object_id'] . ', user_id=' . $_POST['userId'] . ', pos_x=0, pos_y=0, in_inventory=1, is_flip=0, count_cell=0');
                                    }
                                }
                                $user['hard_count'] = (int)$user['hard_count'] + (int)$startPackData['hard_count'];
                                $user['soft_count'] = (int)$user['soft_count'] + (int)$startPackData['soft_count'];
                                $result = $mainDb->query('UPDATE users SET hard_count=' . $user['hard_count'] . ', soft_count = ' . $user['soft_count'] . ' WHERE id=' . $_POST['userId']);

                            } else if ($p['product_code'] == '14') {

                                $result = $mainDb->query('UPDATE users SET sale_pack = 1 WHERE id=' . $_POST['userId']);
                                $result = $mainDb->query("SELECT * FROM data_sale_pack");
                                $salePackData = $result->fetch();
                                $t = $salePackData['object_id'];
                                $ids = explode("&", $t);
                                $t = $salePackData['object_type'];
                                $types = explode("&", $t);
                                $t = $salePackData['object_count'];
                                $counts = explode("&", $t);
                                for ($x = 0; $x < count($ids); $x++) {
                                    $oType = (int)$types[$x];
                                    if ($oType == 8 || $oType == 7 || $oType == 5) { // RESOURCE, INSTRUMENT, PLANT
                                        $result = $shardDb->query("SELECT count FROM user_resource WHERE user_id = " . $_POST['userId'] . " AND resource_id=" . (int)$ids[$x]);
                                        $ar2 = $result->fetch();
                                        if (count($ar2)) {
                                            $count = (int)$ar2['count'] + (int)$counts[$x];
                                            $result = $shardDb->query("UPDATE user_resource SET count=" . $count . " WHERE user_id=" . $_POST['userId'] . " AND resource_id=" . (int)$ids[$x]);
                                        } else {
                                            $count = (int)$counts[$x];
                                            $result = $shardDb->query('INSERT INTO user_resource SET user_id=' . $_POST['userId'] . ', resource_id=' . (int)$ids[$x] . ', count=' . $count);
                                        }
                                    } else if ($oType == 4 || $oType == 9 || $oType == 10 || $oType == 30 || $oType == 31 || $oType == 32) {  // diff decors
                                        $c = (int)$counts[$x];
                                        if ($c < 1) $c = 1;
                                        for ($aa = 0; $aa < $c; $aa++) {
                                            $result = $shardDb->query('INSERT INTO user_building SET building_id = ' . (int)$ids[$x] . ', user_id=' . $_POST['userId'] . ', pos_x=0, pos_y=0, in_inventory=1, is_flip=0, count_cell=0');
                                        }
                                    }
                                }

                            } else {

                                $result = $mainDb->query("SELECT * FROM data_buy_money");
                                $dataMoney = $result->fetchAll();
                                foreach ($dataMoney as $k => $m) {
                                    if ($m['id'] == $p['product_code']) {
                                        if ($m['type_money'] == '1') {
                                            $user['hard_count'] = (int)$user['hard_count'] + (int)$m['count_getted'];
                                            $result = $mainDb->query('UPDATE users SET hard_count=' . $user['hard_count'] . ' WHERE id=' . $_POST['userId']);
                                        } else {
                                            $user['soft_count'] = (int)$user['soft_count'] + (int)$m['count_getted'];
                                            $result = $mainDb->query('UPDATE users SET soft_count=' . $user['soft_count'] . ' WHERE id=' . $_POST['userId']);
                                        }
                                    }
                                    break;
                                }
                            }

                            $result = $mainDb->query("DELETE FROM transaction_lost WHERE id=" . $p['id']);
                            $result = $mainDb->query('UPDATE transactions SET getted=1 WHERE uid=' . $socialId . ' AND unitime=' . $p['unitime']);
                        }
                    }
                } else if ($channelId == 4) { // FB
                    $user['next_time_invite'] = $uS['next_time_invite'];
                }

                $check = (int)$user['ambar_max'] + (int)$user['sklad_max'] + (int)$user['ambar_level'] + (int)$user['sklad_level'] + (int)$user['hard_count'] + (int)$user['soft_count'] +
                    (int)$user['yellow_count'] + (int)$user['green_count'] + (int)$user['red_count'] + (int)$user['blue_count'] + (int)$user['level'] + (int)$user['xp'] + (int)$user['count_cats'] +
                    (int)$user['tutorial_step'] + (int)$user['count_chest'] + (int)$user['count_daily_bonus'];
                $user['test_date'] = $check;

                $json_data['message'] = $user;
                echo json_encode($json_data);
            } catch (Exception $e) {
                $json_data['status'] = 's092';
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
    $json_data['status'] = 's093';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
