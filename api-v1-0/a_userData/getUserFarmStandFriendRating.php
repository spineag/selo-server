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
        $result = $shard->query("SELECT * FROM user_info");
        $ar = $result->fetchAll();
        foreach ($ar as $key2 => $k) {
            $pa = [];
            $pa['user_id'] = $k['user_id'];
            $pa['count_stand'] = $k['count_stand'];
            $partyALL[] = $pa;
        }
    }
    $countYour = 1;
    $arrClientUser = explode("&", $_POST['arrClientUser']);

    uasort($partyALL, 'cmp');
    foreach ($partyALL as $key => $party) {
        foreach ($arrClientUser as $key3 => $clientUser) {
            if((string)$party['user_id'] == (string)$clientUser) {
                $pa = [];
                $pa['user_id'] = $party['user_id'];
                $pa['count_stand'] = $party['count_stand'];
                $pa['number'] = $countYour;
                $cafeRatingALL[] = $pa;
                break;
            }
        }
        $countYour ++;
    }

    if (!$cafeRatingALL) $cafeRatingALL = 0;

    $json_data['message'] = $cafeRatingALL;
    echo json_encode($json_data);
}
catch (Exception $e)
{
    $json_data['status'] = 's080';
    $json_data['message'] = $e.' +++ '.$test;
    echo json_encode($json_data);
}

function cmp($a, $b) {
    if ((int)$a['count_stand'] > (int)$b['count_stand']) {
        return -1;
    }
    return 1;
}