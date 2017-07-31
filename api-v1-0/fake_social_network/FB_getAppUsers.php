<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

$app = Application::getInstance();
$channelId = 4; // FB
$mainDb = $app->getMainDb($channelId);

$arU = ['1566615670077474'];

//$result = $mainDb->query("SELECT social_id FROM users ORDER BY RAND() LIMIT 10");
//$ar = $result->fetchAll();
//foreach ($ar as $key => $u) {
//    $arU[] = $u['social_id'];
//}

// add from user real neighbors
if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $shardDb = $app->getShardDb($_POST['userId'], $channelId);
    $result = $shardDb->query('SELECT * FROM user_friends WHERE user_id='.$_POST['userId']);
    $ar = $result->fetch();
    $idso=[];
    if ($ar['friend_1'] != '0') $idso[]=(int)$ar['friend_1'];
    if ($ar['friend_2'] != '0') $idso[]=(int)$ar['friend_2'];
    if ($ar['friend_3'] != '0') $idso[]=(int)$ar['friend_3'];
    if ($ar['friend_4'] != '0') $idso[]=(int)$ar['friend_4'];
    if ($ar['friend_5'] != '0') $idso[]=(int)$ar['friend_5'];
    if (count($idso) > 0) {
        $qwe = implode(',', array_map('intval', $idso));
        $result = $mainDb->query("SELECT social_id FROM users WHERE id IN (".$qwe.")");
        $ar = $result->fetchAll();
        foreach ($ar as $key => $u) {
            $arU[] = $u['social_id'];
        }
    }
}

$ids = implode(',', array_map('intval', $arU));
$result = $mainDb->query("SELECT id, social_id, first_name, last_name, photo_url FROM users WHERE social_id IN (".$ids.")");
$ar = $result->fetchAll();

$json_data['message'] = $ar;
echo json_encode($json_data);

