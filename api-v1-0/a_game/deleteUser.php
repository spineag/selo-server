<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $userId = filter_var($_POST['userId']);
    $channelId = (int)$_POST['channelId'];
    $mainDb = $app->getMainDb($channelId);
    $shardDb = $app->getShardDb($userId, $channelId);

    try {

        $shardDb->query('DELETE FROM user_info WHERE user_id='.$_POST['userId']);
        
        $shardDb->query('DELETE FROM user_animal WHERE user_id='.$_POST['userId']);

        $shardDb->query('DELETE FROM user_cave WHERE user_id='.$_POST['userId']);

        $shardDb->query('DELETE FROM user_plant_ridge WHERE user_id='.$_POST['userId']);

        $shardDb->query('DELETE FROM user_recipe_fabrica WHERE user_id='.$_POST['userId']);

        $shardDb->query('DELETE FROM user_building WHERE user_id='.$_POST['userId']);

        $shardDb->query('DELETE FROM user_building_open WHERE user_id='.$_POST['userId']);

        $shardDb->query('DELETE FROM user_tree WHERE user_id='.$_POST['userId']);

        $shardDb->query('DELETE FROM user_resource WHERE user_id='.$_POST['userId']);

        $shardDb->query('DELETE FROM user_train_pack_item WHERE user_id='.$_POST['userId']);
        $shardDb->query('DELETE FROM user_train_pack WHERE user_id='.$_POST['userId']);
        $shardDb->query('DELETE FROM user_train WHERE user_id='.$_POST['userId']);
        
        $shardDb->query('DELETE FROM user_removed_wild WHERE user_id='.$_POST['userId']);

        $shardDb->query('DELETE FROM user_market_item WHERE user_id='.$_POST['userId']);
        $shardDb->query('DELETE FROM user_market_item WHERE buyer_id='.$_POST['userId']);
        
        $shardDb->query('DELETE FROM user_neighbor WHERE user_id='.$_POST['userId']);
        
        $shardDb->query('DELETE FROM user_order WHERE user_id='.$_POST['userId']);
        
        $shardDb->query('DELETE FROM user_papper_buy WHERE user_id='.$_POST['userId']);

        $shardDb->query('DELETE FROM user_quest WHERE user_id='.$_POST['userId']);
        $shardDb->query('DELETE FROM user_quest_task WHERE user_id='.$_POST['userId']);
//        $shardDb->query('DELETE FROM user_quests_temp WHERE user_id='.$_POST['userId']);

        $mainDb->query('DELETE FROM users WHERE id='.$_POST['userId']);
    }

    catch (Exception $e)
    {
        $json_data['status'] = 's054';
        $json_data['message'] = $e->getMessage();
        echo json_encode($json_data);
    }
}
else
{
    $json_data['id'] = 1;
    $json_data['status'] = 's055';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}