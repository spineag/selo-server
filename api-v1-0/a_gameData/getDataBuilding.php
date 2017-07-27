<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

$app = Application::getInstance();
if (isset($_POST['channelId'])) {
    $channelId = (int)$_POST['channelId'];
} else $channelId = 2; // VK
$mainDb = $app->getMainDb($channelId);
$memcache = $app->getMemcache();

try {
    $resp = $memcache->get('getDataBuilding3'.$channelId);
    if (!$resp) {
        $result = $mainDb->query("SELECT * FROM building");
        if ($result) {
            $buildingsALL = $result->fetchAll();
        } else {
            $json_data['id'] = 1;
            $json_data['status'] = 's261';
            throw new Exception("Bad request to DB!");
        }
        $resp = [];
        if (!empty($buildingsALL)) {
            foreach ($buildingsALL as $key => $dict) {
                $buildingItem = [];
                $buildingItem['id'] = $dict['id'];
                $buildingItem['name'] = $dict['name'];
                $buildingItem['width'] = $dict['width'];
                $buildingItem['height'] = $dict['height'];
                $buildingItem['build_type'] = $dict['build_type'];
                $buildingItem['url'] = $dict['url'];
                $buildingItem['image'] = $dict['image'];
                $buildingItem['inner_x'] = $dict['inner_x'];
                $buildingItem['inner_y'] = $dict['inner_y'];
                $buildingItem['xp_for_build'] = $dict['xp_for_build'];
                $buildingItem['visible'] = $dict['visible'];
                $buildingItem['start_action'] = $dict['start_action'];
                $buildingItem['end_action'] = $dict['end_action'];
                $buildingItem['text_id'] = $dict['text_id'];
                switch ($dict['build_type']) {
                    case 1: // CHEST
                        $result = $mainDb->query("SELECT * FROM data_map_building WHERE building_id =" . $dict['id']);
                        $chest = $result->fetch();
                        if (empty($chest)) {
                            $json_data['id'] = 1;
                            $json_data['status'] = 's262';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['cost'] = $chest['cost'];
                        $buildingItem['block_by_level'] = $chest['block_by_level'];
                        break;
                    case 2: // RIDGE
//                    $result = $mainDb->select("data_ridge", "*", "building_id='".$dict['id']."'");
                        $result = $mainDb->query("SELECT * FROM data_ridge WHERE building_id =" . $dict['id']);
                        $ridge = $result->fetch();
                        if (empty($ridge)) {
                            $json_data['id'] = 2;
                            $json_data['status'] = 's263';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['cost'] = $ridge['cost'];
                        $buildingItem['currency'] = $ridge['currency'];
                        $buildingItem['block_by_level'] = $ridge['block_by_level'];
                        $buildingItem['count_unblock'] = $ridge['count_unblock'];
                        unset($ridge);
                        break;
                    case 3: // TREE
//                    $result = $mainDb->select("data_tree", "*", "building_id='".$dict['id']."'");
                        $result = $mainDb->query("SELECT * FROM data_tree WHERE building_id =" . $dict['id']);
                        $tree = $result->fetch();
                        if (empty($tree)) {
                            $json_data['id'] = 3;
                            $json_data['status'] = 's264';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['cost'] = $tree['cost'];
                        $buildingItem['currency'] = $tree['currency'];
                        $buildingItem['block_by_level'] = $tree['block_by_level'];
                        $buildingItem['cost_skip'] = $tree['cost_skip'];
                        $buildingItem['craft_resource_id'] = $tree['craft_resource_id'];
                        $buildingItem['count_craft_resource'] = $tree['count_craft_resource'];
                        $buildingItem['count_unblock'] = $tree['count_unblock'];
                        $buildingItem['instrument_id'] = $tree['instrument_id'];
                        unset($tree);
                        break;
                    case 4: // DECOR
                        $result = $mainDb->query("SELECT * FROM data_decor WHERE building_id =" . $dict['id']);
                        $decor = $result->fetch();
                        if (empty($decor)) {
                            $json_data['id'] = 4;
                            $json_data['status'] = 's265';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['block_by_level'] = $decor['block_by_level'];
                        $buildingItem['cost'] = $decor['cost'];
                        $buildingItem['delta_cost'] = $decor['delta_cost'];
                        $buildingItem['currency'] = $decor['currency_type'];
                        $buildingItem['filter'] = $decor['filter_type'];
                        $buildingItem['group'] = $decor['filter_group'];
                        $buildingItem['color'] = $decor['color'];
                        $buildingItem['daily_bonus'] = $decor['daily_bonus'];
                        break;
                    case 9: // DECOR_FULL_FENCE
                        $result = $mainDb->query("SELECT * FROM data_decor WHERE building_id =" . $dict['id']);
                        $decor = $result->fetch();
                        if (empty($decor)) {
                            $json_data['id'] = 9;
                            $json_data['status'] = 's266';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['block_by_level'] = $decor['block_by_level'];
                        $buildingItem['cost'] = $decor['cost'];
                        $buildingItem['delta_cost'] = $decor['delta_cost'];
                        $buildingItem['filter'] = $decor['filter_type'];
                        $buildingItem['currency'] = $decor['currency_type'];
                        $buildingItem['group'] = $decor['filter_group'];
                        $buildingItem['color'] = $decor['color'];
                        break;
                    case 10: // DECOR_POST_FENCE
                        $result = $mainDb->query("SELECT * FROM data_decor WHERE building_id =" . $dict['id']);
                        $decor = $result->fetch();
                        if (empty($decor)) {
                            $json_data['id'] = 10;
                            $json_data['status'] = 's267';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['block_by_level'] = $decor['block_by_level'];
                        $buildingItem['cost'] = $decor['cost'];
                        $buildingItem['delta_cost'] = $decor['delta_cost'];
                        $buildingItem['filter'] = $decor['filter_type'];
                        $buildingItem['currency'] = $decor['currency_type'];
                        $buildingItem['group'] = $decor['filter_group'];
                        $buildingItem['color'] = $decor['color'];
                        break;
                    case 11: // FABRICA
//                    $result = $mainDb->select("data_fabrica", "*", "building_id='".$dict['id']."'");
                        $result = $mainDb->query("SELECT * FROM data_fabrica WHERE building_id =" . $dict['id']);
                        $fabrica = $result->fetch();
                        if (empty($fabrica)) {
                            $json_data['id'] = 11;
                            $json_data['status'] = 's268';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['cost'] = $fabrica['cost'];
                        $buildingItem['currency'] = $fabrica['currency'];
                        $buildingItem['cost_skip'] = $fabrica['cost_skip'];
                        $buildingItem['build_time'] = $fabrica['build_time'];
                        $buildingItem['block_by_level'] = $fabrica['block_by_level'];
                        $buildingItem['count_cell'] = $fabrica['count_cell'];
                        unset($fabrica);
                        break;
                    case 12: // WILD
                        $result = $mainDb->query("SELECT * FROM data_wild WHERE building_id =" . $dict['id']);
                        $tree = $result->fetch();
                        if (empty($tree)) {
                            $json_data['id'] = 12;
                            $json_data['status'] = 's269';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['instrument_id'] = $tree['instrument_id'];
                        break;
                    case 13: // AMBAR
//                    $result = $mainDb->select("data_ambar", "*", "building_id='".$dict['id']."'");
                        $result = $mainDb->query("SELECT * FROM data_ambar WHERE building_id ='" . $dict['id'] . "'");
                        $ambar = $result->fetch();
                        if (empty($ambar)) {
                            $json_data['id'] = 13;
                            $json_data['status'] = 's270';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['start_count_resources'] = $ambar['start_count_resources'];
                        $buildingItem['start_count_instruments'] = $ambar['start_count_instruments'];
                        $buildingItem['delta_count_resources'] = $ambar['delta_count_resources'];
                        $buildingItem['delta_count_instruments'] = $ambar['delta_count_instruments'];
                        $buildingItem['up_instrument_id_1'] = $ambar['up_instrument_id_1'];
                        $buildingItem['up_instrument_id_2'] = $ambar['up_instrument_id_2'];
                        $buildingItem['up_instrument_id_3'] = $ambar['up_instrument_id_3'];
                        break;
                    case 14: // SKLAD
//                    $result = $mainDb->select("data_ambar", "*", "building_id='".$dict['id']."'");
                        $result = $mainDb->query("SELECT * FROM data_ambar WHERE building_id =" . $dict['id']);
                        $sklad = $result->fetch();
                        if (empty($sklad)) {
                            $json_data['id'] = 14;
                            $json_data['status'] = 's271';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['start_count_resources'] = $sklad['start_count_resources'];
                        $buildingItem['start_count_instruments'] = $sklad['start_count_instruments'];
                        $buildingItem['delta_count_resources'] = $sklad['delta_count_resources'];
                        $buildingItem['delta_count_instruments'] = $sklad['delta_count_instruments'];
                        $buildingItem['up_instrument_id_1'] = $sklad['up_instrument_id_1'];
                        $buildingItem['up_instrument_id_2'] = $sklad['up_instrument_id_2'];
                        $buildingItem['up_instrument_id_3'] = $sklad['up_instrument_id_3'];
                        break;
                    case 15: // DECOR_TAIL
                        $result = $mainDb->query("SELECT * FROM data_decor WHERE building_id =" . $dict['id']);
                        $decor = $result->fetch();
                        if (empty($decor)) {
                            $json_data['id'] = 15;
                            $json_data['status'] = 's272';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['block_by_level'] = $decor['block_by_level'];
                        $buildingItem['cost'] = $decor['cost'];
                        $buildingItem['delta_cost'] = $decor['delta_cost'];
                        $buildingItem['filter'] = $decor['filter_type'];
                        $buildingItem['currency'] = $decor['currency_type'];

                        break;
                    case 16: // FARM
//                    $result = $mainDb->select("data_farm", "*", "building_id='".$dict['id']."'");
                        $result = $mainDb->query("SELECT * FROM data_farm WHERE building_id =" . $dict['id']);
                        $farm = $result->fetch();
                        if (empty($farm)) {
                            $json_data['id'] = 16;
                            $json_data['status'] = 's273';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['cost'] = $farm['cost'];
                        $buildingItem['currency'] = $farm['currency'];
                        $buildingItem['block_by_level'] = $farm['block_by_level'];
                        $buildingItem['inner_house_x'] = $farm['inner_house_x'];
                        $buildingItem['inner_house_y'] = $farm['inner_house_y'];
                        $buildingItem['image_house'] = $farm['image_house'];
                        $buildingItem['max_count'] = $farm['max_count'];
                        break;
                    case 20: // ORDER
                        $result = $mainDb->query("SELECT * FROM data_map_building WHERE building_id =" . $dict['id']);
                        $build = $result->fetch();
                        if (empty($build)) {
                            $json_data['id'] = 20;
                            $json_data['status'] = 's274';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['cost'] = $build['cost'];
                        $buildingItem['block_by_level'] = $build['block_by_level'];
                        break;
                    case 21: // MARKET
                        $result = $mainDb->query("SELECT * FROM data_map_building WHERE building_id =" . $dict['id']);
                        $build = $result->fetch();
                        if (empty($build)) {
                            $json_data['id'] = 21;
                            $json_data['status'] = 's275';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['cost'] = $build['cost'];
                        $buildingItem['block_by_level'] = $build['block_by_level'];
                        break;
                    case 22: // DAILY_BONUS
                        $result = $mainDb->query("SELECT * FROM data_map_building WHERE building_id =" . $dict['id']);
                        $build = $result->fetch();
                        if (empty($build)) {
                            $json_data['id'] = 22;
                            $json_data['status'] = 's276';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['cost'] = $build['cost'];
                        $buildingItem['block_by_level'] = $build['block_by_level'];
                        break;
                    case 23: // SHOP
                        $result = $mainDb->query("SELECT * FROM data_map_building WHERE building_id =" . $dict['id']);
                        $build = $result->fetch();
                        if (empty($build)) {
                            $json_data['id'] = 23;
                            $json_data['status'] = 's277';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['cost'] = $build['cost'];
                        $buildingItem['block_by_level'] = $build['block_by_level'];
                        break;
                    case 24: // CAVE
                        $result = $mainDb->query("SELECT * FROM data_map_building_cave WHERE building_id =" . $dict['id']);
                        $build = $result->fetch();
                        if (empty($build)) {
                            $json_data['id'] = 24;
                            $json_data['status'] = 's278';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['cost'] = $build['cost'];
                        $buildingItem['block_by_level'] = $build['block_by_level'];
                        $buildingItem['image_active'] = $build['image_active'];
                        $buildingItem['resource_id'] = $build['resource_id'];
                        $buildingItem['raw_resource_id'] = $build['raw_resource_id'];
                        $buildingItem['variaty'] = $build['variaty'];
                        $buildingItem['build_time'] = $build['build_time'];
                        $buildingItem['cost_skip'] = $build['cost_skip'];
                        break;
                    case 25: // PAPER
                        $result = $mainDb->query("SELECT * FROM data_map_building WHERE building_id =" . $dict['id']);
                        $build = $result->fetch();
                        if (empty($build)) {
                            $json_data['id'] = 25;
                            $json_data['status'] = 's279';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['cost'] = $build['cost'];
                        $buildingItem['block_by_level'] = $build['block_by_level'];
                        break;
                    case 26: // TRAIN
                        $result = $mainDb->query("SELECT * FROM data_map_building WHERE building_id =" . $dict['id']);
                        $build = $result->fetch();
                        if (empty($build)) {
                            $json_data['id'] = 26;
                            $json_data['status'] = 's280';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['cost'] = $build['cost'];
                        $buildingItem['block_by_level'] = $build['block_by_level'];
                        $buildingItem['build_time'] = $build['build_time'];
                        $buildingItem['cost_skip'] = $build['cost_skip'];
                        break;
                    case 30: // DECOR_ANIMATION
                        $result = $mainDb->query("SELECT * FROM data_decor WHERE building_id =" . $dict['id']);
                        $decor = $result->fetch();
                        if (empty($decor)) {
                            $json_data['id'] = 4;
                            $json_data['status'] = 's265';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['block_by_level'] = $decor['block_by_level'];
                        $buildingItem['cost'] = $decor['cost'];
                        $buildingItem['delta_cost'] = $decor['delta_cost'];
                        $buildingItem['currency'] = $decor['currency_type'];
                        $buildingItem['filter'] = $decor['filter_type'];
                        $buildingItem['cat_need'] = $decor['cat_need'];
                        $buildingItem['group'] = $decor['filter_group'];
                        $buildingItem['color'] = $decor['color'];
                        break;
                    case 31: // DECOR_FENCE_GATE
                        $result = $mainDb->query("SELECT * FROM data_decor WHERE building_id =" . $dict['id']);
                        $decor = $result->fetch();
                        if (empty($decor)) {
                            $json_data['id'] = 4;
                            $json_data['status'] = 's265';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['block_by_level'] = $decor['block_by_level'];
                        $buildingItem['cost'] = $decor['cost'];
                        $buildingItem['delta_cost'] = $decor['delta_cost'];
                        $buildingItem['currency'] = $decor['currency_type'];
                        $buildingItem['filter'] = $decor['filter_type'];
                        $buildingItem['group'] = $decor['filter_group'];
                        $buildingItem['color'] = $decor['color'];
                        break;
                    case 32: // DECOR_FENCE_ARKA
                        $result = $mainDb->query("SELECT * FROM data_decor WHERE building_id =" . $dict['id']);
                        $decor = $result->fetch();
                        if (empty($decor)) {
                            $json_data['id'] = 4;
                            $json_data['status'] = 's265';
                            throw new Exception("Bad request to DB!");
                        }
                        $buildingItem['block_by_level'] = $decor['block_by_level'];
                        $buildingItem['cost'] = $decor['cost'];
                        $buildingItem['delta_cost'] = $decor['delta_cost'];
                        $buildingItem['currency'] = $decor['currency_type'];
                        $buildingItem['filter'] = $decor['filter_type'];
                        $buildingItem['group'] = $decor['filter_group'];
                        $buildingItem['color'] = $decor['color'];
                        break;
                    default:
                        break;
                }
                $resp[] = $buildingItem;
            }
        } else {
            $json_data['id'] = 4;
            $json_data['status'] = 's281';
            throw new Exception("Bad request to DB!");
        }
        $memcache->set('getDataBuilding3'.$channelId, $resp, MEMCACHED_DICT_TIME);
    }

    $json_data['message'] = $resp;
    echo json_encode($json_data);
}
catch (Exception $e)
{
    $json_data['status'] = 's075';
    $json_data['message'] = $e;
    echo json_encode($json_data);
}


