<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/php/api-v1-0/library/defaultResponseJSON.php');

$app = Application::getInstance();
$channelId = 4; // FB
$mainDb = $app->getMainDb($channelId);

try {
    $result = $mainDb->query("SELECT * FROM users WHERE social_id =" . $_POST['userSocialId']);
    $ar = $result->fetch();
    $resp = [];
    if ($ar) {
        $resp['firstName'] = $ar['first_name'];
        $resp['lastName'] = $ar['last_name'];
        $resp['photo'] = $ar['photo_url'];
        $resp['timezone'] = $ar['time_zone'];
        $resp['sex'] = $ar['sex'];
    }
    $json_data['message'] = $resp;
    echo json_encode($json_data);
}
catch (Exception $e)
{
    $json_data['status'] = 's098';
    $json_data['message'] = $e->getMessage();
    echo json_encode($json_data);
}