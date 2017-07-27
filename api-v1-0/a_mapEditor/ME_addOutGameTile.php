<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();

    // VK
    $mainDb = $app->getMainDb(2);
    try {
        $result = $mainDb->query("INSERT INTO data_outgame_tile SET pos_x=".$_POST['posX'].", pos_y=".$_POST['posY']);
       if ($result) {
            $json_data['message'] = '';
            echo json_encode($json_data);
        } else {
            $json_data['id'] = 2;
            $json_data['status'] = 's117';
            $json_data['message'] = 'bad query';
        }
        
    }
    catch (Exception $e)
    {
        $json_data['status'] = 's118';
        $json_data['message'] = $e->getMessage();
        echo json_encode($json_data);
    }

    // OK
    $mainDb = $app->getMainDb(3);
    try {
        $result = $mainDb->query("INSERT INTO data_outgame_tile SET pos_x=".$_POST['posX'].", pos_y=".$_POST['posY']);
       if ($result) {
            $json_data['message'] = '';
            echo json_encode($json_data);
        } else {
            $json_data['id'] = 2;
            $json_data['status'] = 's117';
            $json_data['message'] = 'bad query';
        }

    }
    catch (Exception $e)
    {
        $json_data['status'] = 's118';
        $json_data['message'] = $e->getMessage();
        echo json_encode($json_data);
    }


}
else
{
    $json_data['id'] = 1;
    $json_data['status'] = 's119';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
