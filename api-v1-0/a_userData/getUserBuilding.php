<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    if (isset($_POST['channelId'])) {
        $channelId = (int)$_POST['channelId'];
    } else $channelId = 2; // VK

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $mainDb = $app->getMainDb($channelId);
        $userId = filter_var($_POST['userId']);
        $shardDb = $app->getShardDb($userId, $channelId);
        $m = md5($_POST['userId'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's417';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            try {
                $resp = [];
                $result = $shardDb->query("SELECT ub.id, ub.building_id, pos_x, pos_y, is_flip, in_inventory,count_cell,
                                                 ub.user_id, date_start_build, is_open 
                                          FROM user_building ub
                                          LEFT JOIN user_building_open ubo
                                          ON ubo.user_id = ub.user_id AND ub.id = ubo.user_db_building_id
                                          WHERE ub.user_id = " . $userId);
                if ($result) {
                    while ($arr = $result->fetch()) {
                        if (!is_null($arr['date_start_build'])) {
                            $arr['time_build_building'] = (int)time() - (int)$arr['date_start_build'];
                        }
                        unset($arr['date_start_build']);
                        if (is_null($arr['is_open'])) {
                            unset($arr['is_open']);
                        }
                        $resp[] = $arr;
                    }
                } else {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's299';
                    throw new Exception("Bad request to DB!");
                }
                if ($channelId == 2) {
                    $result = $mainDb->query("SELECT unlocked_land FROM users WHERE id = " . $_POST['userId']);
                    $u = $result->fetchAll();
                    $u = $u[0]['unlocked_land'];
                    $arrLocked = explode("&", $u);
                } else { // == 3 || == 4
                    $result = $shardDb->query("SELECT unlocked_land FROM user_info WHERE user_id = " . $_POST['userId']);
                    $u = $result->fetchAll();
                    $u = $u[0]['unlocked_land'];
                    $arrLocked = explode("&", $u);
                }

                $result = $mainDb->query("SELECT * FROM map_building");
                if ($result) {
                    $arr = $result->fetchAll();
                    foreach ($arr as $value => $dict) {
                        if (in_array($dict['id'], $arrLocked)) continue;
                        $build = [];
                        $build['id'] = $dict['id'];
                        $build['building_id'] = $dict['building_id'];
                        $build['pos_x'] = $dict['pos_x'];
                        $build['pos_y'] = $dict['pos_y'];
                        $startBuild = $shardDb->query("SELECT * FROM user_building_open WHERE user_id =" . $_POST['userId'] . " AND building_id =" . $dict['building_id']);
                        $date = $startBuild->fetch();
                        if ($date) {
                            $build['time_build_building'] = (int)time() - (int)$date['date_start_build'];
                            $build['is_open'] = $date['is_open'];
                        }
                        $resp[] = $build;
                    }
                } else {
                    $json_data['id'] = 3;
                    $json_data['status'] = 's300';
                    throw new Exception("Bad request to DB!");
                }

                $json_data['message'] = $resp;
                echo json_encode($json_data);
            } catch (Exception $e) {
                $json_data['status'] = 's088';
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
    $json_data['status'] = 's089';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
