<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();

    // FB
    $mainDb = $app->getMainDb(4);
    try {
        $result = $mainDb->queryWithAnswerId('INSERT INTO data_map_wild SET wild_id='.$_POST['wildId'].', pos_x='.$_POST['posX'].', pos_y='.$_POST['posY']);    
        if ($result) {
            $json_data['message'] = $result[1];
        } else {
            $json_data['id'] = 2;
            $json_data['status'] = 's120';
            $json_data['message'] = 'bad query';
        }

        echo json_encode($json_data);
    }
    catch (Exception $e)
    {
        $json_data['status'] = 's121';
        $json_data['message'] = $e->getMessage();
        echo json_encode($json_data);
    }
//    // OK
//    $mainDb = $app->getMainDb(3);
//    try {
//        $result = $mainDb->queryWithAnswerId('INSERT INTO data_map_wild SET wild_id='.$_POST['wildId'].', pos_x='.$_POST['posX'].', pos_y='.$_POST['posY']);
//        if ($result) {
//            $json_data['message'] = $result[1];
//        } else {
//            $json_data['id'] = 2;
//            $json_data['status'] = 's120';
//            $json_data['message'] = 'bad query';
//        }
//
//        echo json_encode($json_data);
//    }
//    catch (Exception $e)
//    {
//        $json_data['status'] = 's121';
//        $json_data['message'] = $e->getMessage();
//        echo json_encode($json_data);
//    }


}
else
{
    $json_data['id'] = 1;
    $json_data['status'] = 's122';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}