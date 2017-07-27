<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');

    $app = Application::getInstance();
    $userSocialId = filter_var($_POST['userSocialId']);
    $packId = filter_var($_POST['packId']);
    $requestId = filter_var($_POST['requestId']);
    $channelId = 4;
    if ($_POST['browserName']) $bname = $_POST['browserName']; else $bname = 'hz';
    if ($_POST['versionBrowser']) $bvers = $_POST['versionBrowser']; else $bvers = 'hz';
    if ($_POST['OS']) $bOS = $_POST['OS']; else $bOS = 'hz';

    $mainDb = $app->getMainDb($channelId);
    $result = $mainDb->query("SELECT is_tester, level FROM users WHERE social_id =".$userSocialId);
    $userLevel = 0;
    $isUserTester = 0;
    $r = $result->fetch();
    if ($r && (int)$r['is_tester'] != 0) $isUserTester = 1;
    if ($r && $r['level']) $userLevel = (int)$r['level'];

    $time = date("Y-m-d H:i:s");
    $mainDb->query('INSERT INTO transactions SET uid='. $userSocialId .', product_code='.$packId.', time_try="'.$time.'",request_id="'.$requestId.'", status="start", level='.$userLevel.
        ', test='.$isUserTester.', browser="'.$bname.'", version_browser="'.$bvers.'", os_user="'.$bOS.'"');
    echo '';