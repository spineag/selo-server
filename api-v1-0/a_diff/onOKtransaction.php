<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $userSocialId = filter_var($_POST['userId']);
    $channelId = 3; // only for OK for now

    $mainDb = $app->getMainDb($channelId);
    try {
        if ($_POST['isPayed'] == '0') {
            $result = $mainDb->query('SELECT * FROM transaction_lost WHERE uid=' . $userSocialId . ' AND product_code=' . $_POST['productCode'].' ORDER BY unitime DESC LIMIT 1');
            if ($result) {
                $q = $result->fetch();
                $res = 'FIND';
                $result = $mainDb->query('DELETE FROM transaction_lost WHERE id=' . $q['id']);
                $result = $mainDb->query('UPDATE transactions SET getted=1 WHERE uid='.$userSocialId.' AND unitime='.$q['unitime']);
            } else {
                $res = 'NO_ROW'; // no row in BD
            }
        } else {
            $res = 'DELETED';
            $result = $mainDb->query('SELECT * FROM transaction_lost WHERE uid=' . $userSocialId . ' AND product_code=' . $_POST['productCode'].' ORDER BY unitime DESC LIMIT 1');
            if ($result) {
                $q = $result->fetch();
                $result = $mainDb->query('DELETE FROM transaction_lost WHERE id='.$q['id']);
                $result = $mainDb->query('UPDATE transactions SET getted=1 WHERE product_code='.$_POST['productCode'].' AND unitime='.$q['unitime']);  // not use userSocialId because it has bugs.. hz why
            }
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