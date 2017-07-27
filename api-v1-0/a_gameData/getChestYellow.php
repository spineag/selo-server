<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

    $app = Application::getInstance();
    if (isset($_POST['channelId'])) {
        $channelId = (int)$_POST['channelId'];
    } else $channelId = 2; // VK
    $mainDb = $app->getMainDb($channelId);

    try {
        $resp = [];
        $result = $mainDb->query("SELECT * FROM data_chest_yellow WHERE id =" . $_POST['id']);
        if ($result) {
            $arr = $result->fetchAll();
            foreach ($arr as $value => $dict) {
                $res = [];
                $res['id'] = $dict['id'];
                $res['resource_id'] = $dict['resource_id'];
                $res['resource_count'] = $dict['resource_count'];
                $res['money_count'] = $dict['money_count'];
                $res['xp_count'] = $dict['xp_count'];
            }
        } else {
            $json_data['id'] = 2;
            $json_data['status'] = 's301';
            throw new Exception("Bad request to DB!");
        }

        $json_data['message'] = $res;
        echo json_encode($json_data);
    }
    catch (Exception $e)
    {
        $json_data['status'] = 's227';
        $json_data['message'] = $e->getMessage();
        echo json_encode($json_data);
    }


