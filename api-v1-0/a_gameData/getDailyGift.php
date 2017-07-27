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
if (isset($_POST['channelId'])) {
    $channelId = (int)$_POST['channelId'];
} else $channelId = 2; // VK
$mainDb = $app->getMainDb($channelId);

try {
        $result = $mainDb->query("SELECT * FROM data_daily_gift");
        if ($result) {
            $giftALL = $result->fetchAll();
        } else {
            $json_data['id'] = 1;
            $json_data['status'] = 's259';
            throw new Exception("Bad request to DB!");
        }
        $resp = [];
        if (!empty($giftALL)) {
            foreach ($giftALL as $key => $recipe) {
                $giftALL['resource_id'] = $recipe['resource_id'];
                $giftALL['count'] = $recipe['count'];
                $giftALL['type'] = $recipe['type'];
                $resp[] = $giftALL;
            }
        } else {
            $json_data['id'] = 2;
            $json_data['status'] = 's260';
            throw new Exception("Bad request to DB!");
        }
    $json_data['message'] = $resp;
    echo json_encode($json_data);

} catch (Exception $e) {
    $json_data['status'] = 's074';
    $json_data['message'] = $e;
    echo json_encode($json_data);
}

