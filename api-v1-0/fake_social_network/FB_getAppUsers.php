<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/php/api-v1-0/library/defaultResponseJSON.php');

$app = Application::getInstance();
$channelId = 4; // FB
$mainDb = $app->getMainDb($channelId);

$arU = ['1466574610082248', '1401720896515331', '1185277884914305', '1402089059863470', '444635519203361', '124198351457341', '1674570479227054', '101993630392675', '10206660664875203'];

$result = $mainDb->query("SELECT social_id FROM users ORDER BY RAND() LIMIT 10");
$ar = $result->fetchAll();
foreach ($ar as $key => $u) {
    $arU[] = $u['social_id'];
}

$ids = implode(',', array_map('intval', $arU));
$result = $mainDb->query("SELECT id, social_id, first_name, last_name, photo_url FROM users WHERE social_id IN (".$ids.")");
$ar = $result->fetchAll();

$json_data['message'] = $ar;
echo json_encode($json_data);

