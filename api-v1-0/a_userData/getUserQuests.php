<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $userId = filter_var($_POST['userId']);
    $channelId = (int)$_POST['channelId'];
    $shardDb = $app->getShardDb($userId, $channelId);
    $mainDb = $app->getMainDb($channelId);
    $memcache = $app->getMemcache();

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's463';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {

            // remove duplicates
//            if ($channel == 2) {
                try {
                    $result = $shardDb->query("SELECT id FROM user_quest WHERE user_id = ".$userId." group by user_id, quest_id having count(*)>1");
                    $ar = $result->fetchAll();
                    if ($ar) { // there are dublicates :(
                        $arDubl = [];
                        foreach ($ar as $value => $dict) {
                            $arDubl[] = $dict['id'];
                        }
                        $dubl = implode(',', array_map('intval', $arDubl));
                        $result = $shardDb->query("DELETE FROM user_quest WHERE user_id =" . $userId . " AND id IN (" . $dubl . ") AND date_finish='0'");
                        $result = $shardDb->query("DELETE FROM user_quest_task WHERE user_id =" . $userId . " AND quest_id IN (" . $dubl . ") AND is_done=0");
                    }
                } catch (Exception $e) {
//                    $json_data['status'] = 's...';
//                    $json_data['message'] = $e->getMessage();
//                    echo json_encode($json_data);
                }
//            }

            try {
                $result = $shardDb->query("SELECT * FROM user_quest WHERE user_id =".$userId. " AND get_award = 0 AND is_out_date = 0");
                $quests = $result->fetchAll();
                if (count($quests)) {
                    $userQuestsIDs = [];
                    foreach ($quests as $value => $dict) {
                        $userQuestsIDs[] = $dict['quest_id'];
                        $result = $mainDb->query("SELECT * FROM quests WHERE id = ".$dict['quest_id']);
                        $quests[$value]['quest_data'] = $result->fetch();
                    }
                    $result = $mainDb->query("SELECT id FROM quests WHERE id IN (" . implode(',', array_map('intval', $userQuestsIDs)) . ") AND " . time() . " > date_finish AND date_finish <> 0");
                    $q = $result->fetchAll();
                    if (count($q)) {
                        $finished = [];
                        foreach ($q as $value => $dict) {
                            $id = $dict['id'];
                            $finished[] = $id;
                            foreach ($quests as $value1 => $dict1) {
                                if ($dict1['id'] == $id) {
                                    unset($quests[$value1]);
                                    break;
                                }
                            }
                        }
                        $quests = array_values($quests);
                        $result = $shardDb->query("UPDATE user_quest SET is_out_date = 1 WHERE user_id = '.$userId.' AND id IN (" . implode(',', array_map('intval', $finished)) . ")");
                        $result = $shardDb->query("UPDATE user_quest_task SET is_out_date = 1 WHERE user_id = '.$userId.' AND quest_id IN (" . implode(',', array_map('intval', $finished)) . ")");
                        $key = array_search($id, $userQuestsIDs);
                        if (!is_null($key) && $key !== false) {
                            unset($userQuestsIDs[$key]);
                        }
                    }

                    $result = $shardDb->query("SELECT * FROM user_quest_task WHERE user_id = ".$userId." AND is_out_date=0 AND quest_id IN (" . implode(',', array_map('intval', $userQuestsIDs)) . ")");
                    $tasks = $result->fetchAll();
                    foreach ($tasks as $value => $dict) {
                        $result = $mainDb->query("SELECT * FROM quest_task WHERE id = ".$dict['task_id']);
                        $tasks[$value]['task_data'] = $result->fetch();
                    }
                    $result = $mainDb->query("SELECT * FROM quest_award WHERE quest_id IN (" . implode(',', array_map('intval', $userQuestsIDs)) . ")");
                    $awards = $result->fetchAll();

                } else {
                    $quests = [];
                    $tasks = [];
                    $awards = [];
                }

                $ar = [];
                $ar['quests'] = $quests;
                $ar['tasks'] = $tasks;
                $ar['awards'] = $awards;
                $json_data['message'] = $ar;
                echo json_encode($json_data);

            } catch (Exception $e) {
                $json_data['status'] = 's443';
                $json_data['message'] = $e->getMessage();
                echo json_encode($json_data);
            }
        }
    } else {
        $json_data['id'] = 13;
        $json_data['status'] = 's444';
        $json_data['message'] = 'bad sessionKey';
        echo json_encode($json_data);
    }
}
else
{
    $json_data['id'] = 1;
    $json_data['status'] = 's445';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}