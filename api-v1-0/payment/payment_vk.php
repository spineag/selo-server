<?php
define('SN', 'vk');
require_once '../library/Application.php';
header("Content-Type: application/json; encoding=utf-8");

$mainDb = Application::getInstance()->getMainDb(2);
$secret_key = 'GgqsUsmkkURizyfOAt1m';

$ar = [];
$time = time();
$input = $_POST;
$item = $_POST['item']; // наименование товара
if (strpos($item, 'item') !== false) {
    if ($item == 'item_13') {
        $db_r = $mainDb->query('SELECT * FROM data_starter_pack');
        $r = $db_r->fetch();
        $r['item_name'] = 'item_13';
        $r['id'] = '13';
        $r['url'] = 'http://505.ninja/selo-project/images/icons/starter_pack_icon.png';
        $r['cost_for_real'] = $r['new_cost'];
        $r['count_getted'] = 'Акция';
        $ar[] = $r;
    } else {
        $db_r = $mainDb->query('SELECT * FROM data_buy_money');
        while ($r = $db_r->fetch($db_r)) {
            $r['item_name'] = 'item_' . $r['id'];
            $ar[] = $r;
        }
    }
} else if (strpos($item, 'sale_pack') !== false) {
    $db_r = $mainDb->query('SELECT * FROM data_sale_pack');
    while ($r = $db_r->fetch($db_r)) {
        $r['item_name'] = 'sale_pack_' . $r['id'];
        $ar[] = $r;
    }
}

// Проверка подписи
$sig = $input['sig'];
unset($input['sig']);
ksort($input);
$str = '';
foreach ($input as $k => $v) {
    $str .= $k . '=' . $v;
}

if ($sig != md5($str . $secret_key)) {
    $response['error'] = array(
        'error_code' => 10,
        'error_msg' => 'Несовпадение вычисленной и переданной подписи запроса.',
        'critical' => true
    );
} else {
    // Подпись правильная
    switch ($input['notification_type']) {
        case 'get_item':
        case 'get_item_test':
            // Формируем текст "МОНЕТ", Рубинов
            if ($input['notification_type'] == 'get_item_test') {
                $realStr = "РУБИНОВ (тестовый режим)";
                $virtStr = "МОНЕТ (тестовый режим)";
            } else {
                $realStr = "РУБИНОВ";
                $virtStr = "МОНЕТ";
            }
            // Получение информации о товаре
            $isFound = false;
            if (strpos($item, 'item') !== false) {
                foreach ($ar as $v) {
                    if ($item == $v['item_name']) {
                        $isFound = true;
                        $response['response'] = array(
                            'item_id' => $v['id'],
                            'title' => $v['count_getted'],
                            'photo_url' => $v['url'],
                            'price' => $v['cost_for_real']
                        );
                        break;
                    }
                }
            } else if (strpos($item, 'sale_pack') !== false) {
                foreach ($ar as $v) {
                    if ($item == $v['item_name']) {
                        $isFound = true;
                        $response['response'] = array(
                            'item_id' => $v['id'],
                            'title' => $v['name'],
                            'photo_url' => $v['url'],
                            'price' => $v['new_cost']
                        );
                        break;
                    }
                }
            }
            if ($isFound == false) {
                $response['error'] = array(
                    'error_code' => 20,
                    'error_msg' => 'Товара не существует.',
                    'critical' => true
                );
            }
            break;
        case 'order_status_change':
        case 'order_status_change_test':
            // Изменение статуса заказа
            if ($input['status'] == 'chargeable') {
                $order_id = intval($input['order_id']);

                // Код проверки товара, включая его стоимость
                $app_order_id = 0; // Получающийся у вас идентификатор заказа.
                $error = 0;

                $object_id = $input['item_id'];

                $itemArray = explode("_", $input['item']);
                //  $itemArray[0] это item или offer

//                if ($itemArray[0] == "offer")
//                {
//                    $r['count']     = $input['item_price'];
//                    $r['price']     = $input['item_price'];
//                    $r['type']      = 'real';
//                    $r['object_id'] = $itemArray[1];
//
//                    $callbackHelper = new callbackHelper();
//                    $callbackHelper->offer = $r;
//                    $callbackHelper->social_id = $input['user_id'];
//                    $callbackHelper->input_price = $input['item_price'];
//
//                    $result = $callbackHelper->updateResource();
//                }
//                else
//                {
//                    $callbackHelper = new callbackHelper();
//                    $callbackHelper->object_id = $object_id;
//                    $callbackHelper->social_id = $input['user_id'];
//                    $callbackHelper->input_price = $input['item_price'];
//
//                    $result = $callbackHelper->updateResource();
//                }
                $result = true;

                if ($result === true) {
                    $response['response'] = array(
                        'order_id' => $order_id,
                        'app_order_id' => $app_order_id,
                    );
                } else {
                    $data = '[E' . $result . '] - SID: ' . $input['user_id'] . ', ITEM: ' . $input['item'] . ', ITEM_ID: ' . $input['item_id'] . ', PRICE: ' . $input['item_price'] . ', ITEM_CURRENCY_AMOUNT: ' . $input['item_currency_amount'] . ";\r\n";
                    $response['error'] = array(
                        'error_code' => $error,
                        'error_msg' => '',
                        'critical' => true
                    );
                }
            } else {
                $response['error'] = array(
                    'error_code' => 100,
                    'error_msg' => 'Передано непонятно что вместо chargeable.',
                    'critical' => true
                );
            }
            break; // order_status_change && order_status_change_test
    }
}
echo json_encode($response);
