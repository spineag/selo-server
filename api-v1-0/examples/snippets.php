<?php
//ALTER TABLE selo_1.user_quest ADD CONSTRAINT abQuest UNIQUE(user_id, quest_id);

// дублікати
//SELECT t1.* FROM farm_1.user_quest_task AS t1
//    LEFT JOIN (SELECT id FROM farm_1.user_quest_task GROUP BY user_id, task_id) AS t2
//        ON t1.id = t2.id
//    WHERE t2.id IS NULL