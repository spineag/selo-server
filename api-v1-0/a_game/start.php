<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['idSocial']) && !empty($_POST['idSocial'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];
    $memcache = $app->getMemcache();
    $mainDb = $app->getMainDb($channelId);

    try {
        $socialUId = $_POST['idSocial'];
        $uid = $app->getUserId($channelId, $socialUId);
        if ($uid < 1) {
            try {
                if (isset($_POST['name']) && !empty($_POST['name'])) $name = $_POST['name'];
                else $name = 'undefined';
                if (isset($_POST['lastName']) && !empty($_POST['lastName'])) $lastName = $_POST['lastName'];
                else $lastName = 'Undefined';
                if (isset($_POST['sex']) && !empty($_POST['sex'])) $sex = $_POST['sex'];
                else $sex = 'unisex';
                if (isset($_POST['defaultLanguage']) && !empty($_POST['defaultLanguage'])) {
                    $lang = (int)$_POST['defaultLanguage'];
                } else {
                    if ($channelId == 4) {
                        $lang = 2;
                    } else {
                        $lang = 1;
                    }
                }
                $uid = $app->newUser($channelId, $socialUId, $name, $lastName, $sex, $lang);
            } catch (Exception $e) {
                $json_data['id'] = 2;
                $json_data['status'] = '00000';
                $json_data['message'] = $e->getMessage(); 
                echo json_encode($json_data);
            }
            if ($uid < 0) {
                $json_data['id'] = 2;
                $json_data['status'] = 's328';
                throw new Exception("Bad request to DB!");
            }
        }
        if (isset($_POST['sessionKey']) && !empty($_POST['sessionKey'])) {
            $sess = $_POST['sessionKey'];
            if ($sess == '') $sess = '0';
        } else {
            $sess = '0';
        }
        if ($channelId == 4) { // FB
            if (isset($_POST['photo']) && !empty($_POST['photo'])) $photo = $_POST['photo'];
                else $photo = 'unknown';
            $timezone = 0;
            if (isset($_POST['timezone']) && !empty($_POST['timezone'])) $timezone = $_POST['timezone'];
            $result = $mainDb->query('UPDATE users SET session_key=' . $sess . ',photo_url="' . $photo . '", timezone = '.$timezone.' WHERE id=' . $uid);
        } else {
            $result = $mainDb->query('UPDATE users SET session_key=' . $sess . ' WHERE id=' . $uid);
        }
        if (!$result) {
            $json_data['status'] = 's221';
            $json_data['message'] = $e->getMessage();
            echo json_encode($json_data);
        }
        $memcache->set((string)$uid.'ch'.$channelId, (string)$sess, MEMCACHED_DICT_TIME);
        
        $json_data['message'] = $uid;
        echo json_encode($json_data);
    }
    catch (Exception $e)
    {
        $json_data['status'] = 's165';
        $json_data['message'] = $e->getMessage();
        echo json_encode($json_data);
    }
}
else
{
    $json_data['id'] = 1;
    $json_data['status'] = 's166';
    $json_data['message'] = 'bad POST[idSocial]';
    echo json_encode($json_data);
}
