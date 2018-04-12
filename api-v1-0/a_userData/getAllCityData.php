<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userSocialId']) && !empty($_POST['userSocialId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];
    $mainDb = $app->getMainDb($channelId);

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'].$_POST['userSocialId'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's375';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            try {
                $result = $mainDb->query("SELECT id FROM users WHERE social_id =" . $_POST['userSocialId']);
                $arr = $result->fetch();
                $userId = $arr['id'];
                $shardDb = $app->getShardDb($userId, $channelId);
                $respBuildings = [];
                $result = $shardDb->query("SELECT ub.id, ub.building_id, pos_x, pos_y, is_flip,
                                                 ub.user_id, date_start_build, is_open 
                                          FROM user_building ub
                                          LEFT JOIN user_building_open ubo
                                          ON ubo.user_id = ub.user_id AND ub.id = ubo.user_db_building_id
                                          WHERE ub.user_id = " . $userId . " AND in_inventory = 0");
                if ($result) {
                    while ($arr = $result->fetch()) {
                        if (!is_null($arr['date_start_build'])) {
                            $arr['time_build_building'] = (int)time() - (int)$arr['date_start_build'];
                        }
                        unset($arr['date_start_build']);
                        if (is_null($arr['is_open'])) {
                            unset($arr['is_open']);
                        }
                        $respBuildings[] = $arr;
                    }
                } else {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's251';
                    throw new Exception("Bad request to DB!");
                }

                $result = $shardDb->query("SELECT unlocked_land FROM user_info WHERE user_id = " . $userId);  // need optimise and use line 22
                $u = $result->fetchAll();
                $u = $u[0]['unlocked_land'];
                $arrLocked = explode("&", $u);

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
                        $startBuild = $shardDb->query("SELECT * FROM user_building_open WHERE user_id =" . $userId . " AND building_id =" . $dict['building_id']);
                        $date = $startBuild->fetch();
                        if ($date) {
                            $build['time_build_building'] = (int)time() - (int)$date['date_start_build'];
                            $build['is_open'] = $date['is_open'];
                        }
                        if ($build['building_id'] == 49) {
                            $tr = $shardDb->query("SELECT * FROM user_train WHERE user_id =" . $userId);
                            $train = $tr->fetch();
                            $build['train_state'] = $train['state'];
                        }
                        $respBuildings[] = $build;
                    }
                } else {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's252';
                    throw new Exception("Bad request to DB!");
                }

                // plants
                $respPlants = [];
                $result = $shardDb->query("SELECT * FROM user_plant_ridge WHERE user_id =" . $userId);
                if ($result) {
                    $arr = $result->fetchAll();
                    foreach ($arr as $value => $dict) {
                        $res = [];
                        $res['id'] = $dict['id'];
                        $res['plant_id'] = $dict['plant_id'];
                        $res['user_db_building_id'] = $dict['user_db_building_id'];
                        $res['time_work'] = time() - $dict['time_start'];
                        $res['friend_id'] = $dict['friend_id'];
                        $respPlants[] = $res;
                    }
                } else {
                    $json_data['id'] = 3;
                    $json_data['status'] = 's253';
                    throw new Exception("Bad request to DB!");
                }

                // trees
                $respTrees = [];
                $result = $shardDb->query("SELECT * FROM user_tree WHERE user_id =" . $userId);
                if ($result) {
                    $arr = $result->fetchAll();
                    foreach ($arr as $value => $dict) {
                        $res = [];
                        $res['id'] = $dict['id'];
                        $res['state'] = $dict['state'];
                        $res['user_db_building_id'] = $dict['user_db_building_id'];
                        $res['time_work'] = time() - $dict['time_start'];
                        $respTrees[] = $res;
                    }
                } else {
                    $json_data['id'] = 4;
                    $json_data['status'] = 's254';
                    throw new Exception("Bad request to DB!");
                }

                // animals
                $respAnimals = [];
                $result = $shardDb->query("SELECT * FROM user_animal WHERE user_id =" . $userId);
                if ($result) {
                    $arr = $result->fetchAll();
                    foreach ($arr as $value => $dict) {
                        $res = [];
                        $res['id'] = $dict['id'];
                        $res['animal_id'] = $dict['animal_id'];
                        $res['user_db_building_id'] = $dict['user_db_building_id'];
                        $res['time_work'] = $dict['raw_time_start'];
                        $respAnimals[] = $res;
                    }
                } else {
                    $json_data['id'] = 5;
                    $json_data['status'] = 's255';
                    throw new Exception("Bad request to DB!");
                }

                //recipes
                $respRecipes = [];
                $result = $shardDb->query("SELECT * FROM user_recipe_fabrica WHERE user_id =" . $userId);
                if ($result) {
                    $arr = $result->fetchAll();
                    foreach ($arr as $value => $dict) {
                        $res = [];
                        $res['id'] = $dict['id'];
                        $res['recipe_id'] = $dict['recipe_id'];
                        $res['user_db_building_id'] = $dict['user_db_building_id'];
                        $res['delay'] = $dict['delay_time'];
                        $res['time_work'] = time() - $dict['time_start'];
                        $respRecipes[] = $res;
                    }
                } else {
                    $json_data['id'] = 6;
                    $json_data['status'] = 's256';
                    throw new Exception("Bad request to DB!");
                }

                //wild
                $arrRemoved = [];
                $result = $shardDb->query("SELECT wild_db_id FROM user_removed_wild WHERE user_id = " . $userId);
                $u = $result->fetchAll();
                foreach ($u as $value => $dict) {
                    $arrRemoved[] = $dict['wild_db_id'];
                }

                $respWilds = [];
                $result = $mainDb->query("SELECT * FROM data_map_wild");
                if ($result) {
                    $arr = $result->fetchAll();
                    foreach ($arr as $value => $dict) {
                        if (in_array($dict['id'], $arrRemoved)) continue;
                        $build = [];
                        $build['id'] = $dict['id'];
                        $build['building_id'] = $dict['wild_id'];
                        $build['pos_x'] = $dict['pos_x'];
                        $build['pos_y'] = $dict['pos_y'];
                        $build['is_flip'] = $dict['is_flip'];
                        $build['chest_id'] = $dict['chest_id'];
                        $respWilds[] = $build;
                    }
                } else {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's257';
                    throw new Exception("Bad request to DB!");
                }

                // pets
                $respPets = [];
                $result = $shardDb->query("SELECT * FROM user_pet WHERE user_id =".$userId);
                if ($result) {
                    $arr = $result->fetchAll();
                    foreach ($arr as $value => $dict) {
                        $res = [];
                        $res['id'] = $dict['id'];
                        $res['pet_id'] = $dict['pet_id'];
                        $res['house_db_id'] = $dict['house_db_id'];
                        $respPets[] = $res;
                    }
                } else {
                    $json_data['id'] = 9;
                    $json_data['status'] = 's...';
                    throw new Exception("Bad request to DB!");
                }

                $arr = [];
                $arr['building'] = $respBuildings;
                $arr['plant'] = $respPlants;
                $arr['tree'] = $respTrees;
                $arr['animal'] = $respAnimals;
                $arr['recipe'] = $respRecipes;
                $arr['wild'] = $respWilds;
                $arr['pet'] = $respPets;
                $json_data['message'] = $arr;
                echo json_encode($json_data);
            } catch (Exception $e) {
                $json_data['status'] = 's071';
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
    $json_data['status'] = 's072';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
