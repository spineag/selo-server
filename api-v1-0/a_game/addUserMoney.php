<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];
    $mainDb = $app->getMainDb($channelId);

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'] . $_POST['type'] . $_POST['countAll'] . $app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's357';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            try {
                switch ((int)$_POST['type']) {
                    case 1: //HARD_CURRENCY
                        $col = 'hard_count';
                        break;
                    case 2: //SOFT_CURRENCY
                        $col = 'soft_count';
                        break;
                    case 3: //YELLOW_COUPONE
                        $col = 'yellow_count';
                        break;
                    case 4: //RED_COUPONE
                        $col = 'red_count';
                        break;
                    case 5: //BLUE_COUPONE
                        $col = 'blue_count';
                        break;
                    case 6: //GREEN_COUPONE
                        $col = 'green_count';
                        break;
                }

                $result = $mainDb->query('UPDATE users SET '.$col.'='.$_POST['countAll'].',last_visit_date='.time().' WHERE id='.$_POST['userId']);
                if (!$result) {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's234';
                    throw new Exception("Bad request to DB!");
                }

                $json_data['message'] = '';
                echo json_encode($json_data);
            } catch (Exception $e) {
                $json_data['status'] = 's016';
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
    $json_data['status'] = 's017';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
