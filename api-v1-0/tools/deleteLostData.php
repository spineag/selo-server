<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

    $app = Application::getInstance();
    $mainDb = $app->getMainDb(2);
    $channelId = 1; // VK

// $result = $mainDb->query("SELECT * FROM users ORDER BY id DESC LIMIT 1");
$result = $mainDb->query("SELECT id FROM users");
$a = $result->fetchAll();
$arrIds = [];
foreach ($a as $value => $dict) {
                $arrIds[] = $dict['id'];
        }
$maxID = $arrIds[count($arrIds)-1];

for ($i=0; $i<$maxID; $i++) {   // последнее значение $maxID очевидно, что можно не проверять, так как для него не нужно удалять
    if (in_array($i, $arrIds)) {
        // echo $i.'+  ';
    } else {
        echo "delete for userId = ".$i."\n";
        $result = $mainDb->query('DELETE FROM user_animal WHERE user_id='.$i);
        $result = $mainDb->query('DELETE FROM user_plant_ridge WHERE user_id='.$i);
        $result = $mainDb->query('DELETE FROM user_recipe_fabrica WHERE user_id='.$i);
        $result = $mainDb->query('DELETE FROM user_building WHERE user_id='.$i);
        $result = $mainDb->query('DELETE FROM user_building_open WHERE user_id='.$i);
        $result = $mainDb->query('DELETE FROM user_tree WHERE user_id='.$i);
        $result = $mainDb->query('DELETE FROM user_resource WHERE user_id='.$i);
        $result = $mainDb->query('DELETE FROM user_train_pack_item WHERE user_id='.$i);
        $result = $mainDb->query('DELETE FROM user_train_pack WHERE user_id='.$i);
        $result = $mainDb->query('DELETE FROM user_train WHERE user_id='.$i);
        $result = $mainDb->query('DELETE FROM user_market_item WHERE user_id='.$i);
        $result = $mainDb->query('DELETE FROM user_market_item WHERE buyer_id='.$i);
    }
}



