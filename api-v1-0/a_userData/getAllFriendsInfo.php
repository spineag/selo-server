<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userSocialIds']) && !empty($_POST['userSocialIds'])) {
    $app = Application::getInstance();
    if (isset($_POST['channelId'])) {
        $channelId = (int)$_POST['channelId'];
    } else $channelId = 2; // VK
    $mainDb = $app->getMainDb($channelId);
    
    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's415';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            try {
                $ids = explode("&", $_POST['userSocialIds']);
                $ids = join(',', $ids);
                $result = $mainDb->query("SELECT id, social_id, last_visit_date, level FROM users WHERE social_id IN (".$ids.")");
                $a = $result->fetchAll();
                $arr = [];
                foreach ($a as $value => $dict) {
                    $b = [];
                    $b['social_id'] = $dict['social_id'];
                    $b['id'] = $dict['id'];
                    $b['level'] = $dict['level'];
                    $b['last_visit_date'] = $dict['last_visit_date'];
                    $b['need_help'] = $app->checkNeedHelp($b['id'], $channelId);
                    if ($b['need_help'] == 0) $b['need_help'] = $app->checkNeedHelpTrain($b['id'], $channelId);
                    $arr[] = $b;
                }

                $json_data['message'] = $arr;
                echo json_encode($json_data);
            } catch (Exception $e) {
                $json_data['status'] = 's415';
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
    $json_data['status'] = 's073';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
