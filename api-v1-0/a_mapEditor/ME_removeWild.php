<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();

    // FB
    $mainDb = $app->getMainDb(4);
    try {
        $result = $mainDb->query('DELETE FROM data_map_wild WHERE id='.$_POST['dbId']);
        if ($result) {
            $json_data['message'] = '';
            echo json_encode($json_data);
        } else {
            $json_data['id'] = 2;
            $json_data['status'] = 's316';
            throw new Exception("Bad request to DB!");
        }
    }
    catch (Exception $e)
    {
        $json_data['status'] = 's134';
        $json_data['message'] = $e->getMessage();
        echo json_encode($json_data);
    }
//    // OK
//    $mainDb = $app->getMainDb(3);
//    try {
//        $result = $mainDb->query('DELETE FROM data_map_wild WHERE id='.$_POST['dbId']);
//        if ($result) {
//            $json_data['message'] = '';
//            echo json_encode($json_data);
//        } else {
//            $json_data['id'] = 2;
//            $json_data['status'] = 's316';
//            throw new Exception("Bad request to DB!");
//        }
//    }
//    catch (Exception $e)
//    {
//        $json_data['status'] = 's134';
//        $json_data['message'] = $e->getMessage();
//        echo json_encode($json_data);
//    }


}
else
{
    $json_data['id'] = 1;
    $json_data['status'] = 's135';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}