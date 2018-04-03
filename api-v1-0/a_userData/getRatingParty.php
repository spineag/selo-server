<?php
/**
 * Created by IntelliJ IDEA.
 * User: user
 * Date: 7/15/15
 * Time: 11:57 AM
 */

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

$app = Application::getInstance();
$channelId = (int)$_POST['channelId'];
$mainDb = $app->getMainDb($channelId);
$allShardDb = $app->getAllShardsDb($channelId);

try {
    $partyALL = [];
    foreach ($allShardDb as $key => $shard) {
        $result = $shard->query("SELECT * FROM user_party WHERE id_party =" .$_POST['idParty']);
        $ar = $result->fetchAll();
        foreach ($ar as $key2 => $k) {
            $pa = [];
            $pa['id'] = $k['id'];
            $pa['user_id'] = $k['user_id'];
            $pa['count_resource'] = $k['count_resource'];
            $pa['took_gift'] = $k['took_gift'];
            $pa['show_window'] = $k['show_window'];
            $partyALL[] = $pa;
        }
    }
    $countYour = 1;
    uasort($partyALL, 'cmp');
    foreach ($partyALL as $key => $party) {
        if((string)$party['user_id'] == (string)$_POST['userId']) break;
        $countYour ++;
    }
    array_splice($partyALL, 20);
    $resp = [];
    foreach ($partyALL as $key => $party) {
        $result2 = $mainDb->query('SELECT level, first_name, last_name, social_id FROM users WHERE id =' . $party['user_id']);
        $partyTWO = $result2->fetch();
        $res = [];
        $res['id'] = $party['id'];
        $res['user_id'] = $party['user_id'];
        $res['show_window'] = $party['show_window'];
        $res['took_gift'] = $party['took_gift'];
        $res['count_resource'] = $party['count_resource'];
        $res['social_id'] = $partyTWO['social_id'];
        $res['photo_url'] = $partyTWO['photo_url'];
        $res['level'] = $partyTWO['level'];
        $res['name'] = $partyTWO['first_name'];
        $res['last_name'] = $partyTWO['last_name'];
        $resp[] = $res;
    }
    $resp[] = $countYour;

    $json_data['message'] = $resp;
    echo json_encode($json_data);
}
catch (Exception $e)
{
    $json_data['status'] = 's080';
    $json_data['message'] = $e.' +++ '.$test;
    echo json_encode($json_data);
}

function cmp($a, $b) {
    if ((int)$a['count_resource'] > (int)$b['count_resource']) {
        return -1;
    }
    return 1;
}