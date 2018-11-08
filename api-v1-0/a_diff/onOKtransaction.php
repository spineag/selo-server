<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $userSocialId = filter_var($_POST['userId']);
    $channelId = 3; // only for OK

    $mainDb = $app->getMainDb($channelId);
    try {
        if ($_POST['isPayed'] == '0') {
            $result = $mainDb->query('SELECT * FROM transactions WHERE uid='.$userSocialId.' AND product_code='.$_POST['productCode'].' AND getted = 0 ORDER BY unixtime DESC LIMIT 1');
            if ($result) {
                $q = $result->fetch();
                $res = 'FIND';
                if ($q) {
                    $result = $mainDb->query('UPDATE transactions SET getted=1 WHERE id='.$q['id']);
                }
            } else {
                $res = 'NO_ROW'; // no row in BD
            }
        } else {
            $res = 'DELETED';
        }

        if ($result) {
            $json_data['message'] = $res;
            echo json_encode($json_data);
        } else {
            $json_data['id'] = 2;
            $json_data['status'] = 's...';
            throw new Exception("Bad request to DB!");
        }
    } catch (Exception $e){
        $json_data['status'] = 's...';
        $json_data['message'] = $e->getMessage();
        echo json_encode($json_data);
    }
} else {
    $json_data['id'] = 1;
    $json_data['status'] = 's...';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}