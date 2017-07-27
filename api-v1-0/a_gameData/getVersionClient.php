<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');

$app = Application::getInstance();
$channelId = (int)$_POST['channelId'];
$userSocialId = '0';
$isTester = false;
if (isset($_POST['userSocialId']) && !empty($_POST['userSocialId']))  $userSocialId = $_POST['userSocialId'];
$mainDb = $app->getMainDb($channelId);

if ($userSocialId != '0') {
    $result = $mainDb->query("SELECT is_tester FROM users WHERE social_id=".$userSocialId);
    $t = $result->fetch();
    if ($t['is_tester'] == 1 || $t['is_tester'] == '1') $isTester = true;
}

if ($isTester) {
    $result = $mainDb->query("SELECT version FROM version WHERE name='client_test'");
    $v = $result->fetch();
    $version = $v['version'];
} else {
    $result = $mainDb->query("SELECT version FROM version WHERE name='client'");
    $v = $result->fetch();
    $version = $v['version'];
}

echo $version;




