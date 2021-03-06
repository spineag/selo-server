<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];
    $mainDb = $app->getMainDb($channelId);
    
    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'] . $_POST['countAll'] . $app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's363';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            try {
                if ($channelId == 4) {
                    $result = $mainDb->query("SELECT xp FROM users WHERE id =" . $_POST['userId']);
                    $xp = $result->fetch()['xp'];
                    if ($xp < $_POST['countAll']) {
                        $result = $mainDb->query('UPDATE users SET xp=' . $_POST['countAll'] . ' WHERE id=' . $_POST['userId']);
                        if (!$result) {
                            $json_data['id'] = 2;
                            $json_data['status'] = 's235';
                            throw new Exception("Bad request to DB!");
                        }
                    }
                } else {
                    $result = $mainDb->query('UPDATE users SET xp=' . $_POST['countAll'] . ' WHERE id=' . $_POST['userId']);
                    if (!$result) {
                        $json_data['id'] = 2;
                        $json_data['status'] = 's235';
                        throw new Exception("Bad request to DB!");
                    }
                }

                $json_data['message'] = '';
                echo json_encode($json_data);
            } catch (Exception $e) {
                $json_data['status'] = 's033';
                $json_data['message'] = $e->getMessage();
                echo json_encode($json_data);
            }
        }
    } else {
        $json_data['id'] = 13;
        $json_data['status'] = 's221';
        $json_data['message'] = 'bad sessionKey';
        echo json_encode($json_data);
    }
}
else
{
    $json_data['id'] = 1;
    $json_data['status'] = 's034';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
