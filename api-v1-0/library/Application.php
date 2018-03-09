<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/selo-project/php/api-v1-0/library/OwnMySQLI.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/selo-project/php/api-v1-0/config/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/selo-project/php/api-v1-0/config/configNew.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/selo-project/php/api-v1-0/tools/SocialNetwork.php");

class Application
{
    private static $_instance;
    private $_cfg;
    private $_socialNetwork;
    private $_memcached;
    protected static $_settingsConst = array();

    /**
     * @return Application
     */
    final static public function getInstance(){
        if (static::$_instance == NULL) {
            static::$_instance = new Application();
        }
        return static::$_instance;
    }

    function __construct() {
        $cfgs = $GLOBALS["cfgs"];
        $this->_cfg = isset($cfgs['1']) ? $cfgs['1'] : die("Wrong configuration __construct \n");
    }
    /**
     * @return Memcached
     */
    final public function getMemcache() {
        if ($this->_memcached == NULL) {
            $this->_memcached = new Memcached();
            $this->_memcached->addServer(MEMCACHED_HOST, MEMCACHED_PORT) or die ("Could not connect to memcached!");
        }
        return $this->_memcached;
    }

    final public function getMainDb($channelId = 2) {
        if ($channelId == 2) {
            return new OwnMySQLI(SERVER_DB, USER, PASSWORD, DB);
        } else if ($channelId == 3) {
            return new OwnMySQLI(SERVER_DB_OK, USER_OK, PASSWORD_OK, DB_OK);
        } else if ($channelId == 4) {
            return new OwnMySQLI(SERVER_DB_FB, USER_FB, PASSWORD_FB, DB_FB);
        } else {
            return new OwnMySQLI(SERVER_DB, USER, PASSWORD, DB);
        }
    }

    final public function getShardDb($uid, $channelId) {
        $memcached = $this->getMemcache();
        $shardKey = $channelId."shard_".$uid;
        $dbCfgShard = $memcached->get('selo'.$shardKey);
        if (empty($dbCfgShard)) {
            $mainDb = $this->getMainDb($channelId);
            $res = $mainDb->query("SELECT shard_id, host, user, password as pass, db_name as `database`, active FROM game_shard WHERE first_user_id <='".$uid."' AND last_user_id >='".$uid."'");
            $dbCfgShard = $res->fetch();
            if ((int)$dbCfgShard['active'] == 0) {
                $result = $mainDb->query('UPDATE game_shard SET active=1 WHERE shard_id='.(int)$res['shard_id']);
            }
            $time_out = 5 * 60;
            $memcached->set('selo'.$shardKey, $dbCfgShard, $time_out);
        }

        if (!empty($dbCfgShard)) {
            return new OwnMySQLI($dbCfgShard["host"], $dbCfgShard["user"], $dbCfgShard["pass"], $dbCfgShard["database"]);
        }
        return NULL;
    }

    final public function getShardDbByName($shardName, $channelId) {
        $mainDb = $this->getMainDb($channelId);
        $res = $mainDb->query("SELECT host, user, password FROM game_shard WHERE db_name = '".$shardName."'");
        $shard = $res->fetch();
        if ($shard) {
            return new OwnMySQLI($shard["host"], $shard["user"], $shard["password"], $shardName);
        } else return NULL;
    }

    final public function getAllShardsDb($channelId) {
        $mainDb = $this->getMainDb($channelId);
        $res = $mainDb->query("SELECT shard_id, host, user, password as pass, db_name as `database` FROM game_shard WHERE active=1");
        $ar = $res->fetchAll();
        $shards = [];
        foreach ($ar as $value => $dict) {
            $shards[] = new OwnMySQLI($dict["host"], $dict["user"], $dict["pass"], $dict["database"]);
        }
        return $shards;
    }
    
    final public function md5Secret() {
        return '505';
    }


    final public function getUserId($channelId, $socialUId, $checkViewer = false) {
        $mainDb = $this->getMainDb($channelId);
        $result = $mainDb->query("SELECT id FROM users WHERE social_id =".$socialUId);
        $arr = $result->fetch();
        if ($arr) {
            $userId = $arr['id'];
        } else {
            $userId = 0;
        }
        return $userId;
    }

    public function checkNeedHelp($userId, $channelId = 2) {
        $shardDb = $this->getShardDb($userId, $channelId);
        $result = $shardDb->query("SELECT id FROM user_tree WHERE user_id =".$userId." AND state=11");
        $count = $result->num();
        return $count;
    }

    public function checkNeedHelpTrain($userId, $channelId = 2) {
        $shardDb = $this->getShardDb($userId, $channelId);
        $result = $shardDb->query("SELECT id FROM user_train_pack_item WHERE user_id =".$userId." AND want_help=1 AND help_id=0");
        $count = $result->num();
        return $count;
    }

    public function getSocialId($userId, $channelId = 2) {
        $mainDb = $this->getMainDb($channelId);
        $result = $mainDb->select('users', 'social_id', ['id' => $userId], ['int']);
        $userSocialId = (int)$result->f('social_id');
        return $userSocialId;
    }

    final public function newUser($channelId, $socialUId, $name = 'Vasia', $lname = 'Pupkin', $sex = 'w', $lang='1') {
        $mainDb = $this->getMainDb($channelId);
        $const = [];
        $result = $mainDb->query('SELECT * FROM const');
        $c = $result->fetchAll();
        for ($i=0; $i<count($c); $i++) {
            $const[$c[$i]['name']] = $c[$i]['value'];
        }

        $result = $mainDb->insert( 'users',
            ['social_id' => $socialUId, 'created_date' => time(), 'last_visit_date' => time(),
                'first_name' => $name, 'last_name' => $lname, 'tutorial_step' => 1,
                'hard_count' => $const['HARD_COUNT'], 'soft_count' => $const['SOFT_COUNT'],
                'yellow_count' => $const['YELLOW_COUNT'], 'red_count' => $const['RED_COUNT'],
                'green_count' => $const['GREEN_COUNT'], 'blue_count' => $const['BLUE_COUNT'],
                'xp' => 0, 'level' => 1],
            ['str', 'int', 'int', 'str', 'str', 'int', 'int', 'int', 'int', 'int', 'int', 'int', 'int', 'int', 'str', 'str']);

        $userId = $this->getUserId($channelId, $socialUId);
        $shardDb = $this->getShardDb($userId, $channelId);

        $result = $shardDb->query('INSERT INTO user_info SET user_id='.$userId.', cutscene=0, open_order=0, ambar_level=1, sklad_level=1, language_id='.$lang.', sex="'.$sex.'"');

        $result = $shardDb->insert('user_resource', ['user_id' => $userId, 'resource_id' => 32, 'count' => 4], ['int', 'int', 'int']);
        $result = $shardDb->insert('user_resource', ['user_id' => $userId, 'resource_id' => 21, 'count' => 3], ['int', 'int', 'int']);
        $result = $shardDb->insert('user_resource', ['user_id' => $userId, 'resource_id' => 118, 'count' => 3], ['int', 'int', 'int']);

        //add lopata
        $result = $shardDb->insert('user_resource',
            ['user_id' => $userId, 'resource_id' => 125, 'count' => 1],
            ['int', 'int', 'int']);

            // add ridges and plant on them
            $resultRidge = $shardDb->insert('user_building',
                ['user_id' => $userId, 'building_id' => 11, 'in_inventory' => 0, 'pos_x' => 33, 'pos_y' => 24, 'is_flip' => 0, 'count_cell' => 0],
                ['int', 'int', 'int', 'int', 'int', 'int', 'int']);
            $resultRidge = $shardDb->insert('user_building',
                ['user_id' => $userId, 'building_id' => 11, 'in_inventory' => 0, 'pos_x' => 35, 'pos_y' => 24, 'is_flip' => 0, 'count_cell' => 0],
                ['int', 'int', 'int', 'int', 'int', 'int', 'int']);
            $resultRidge = $shardDb->insert('user_building',
                ['user_id' => $userId, 'building_id' => 11, 'in_inventory' => 0, 'pos_x' => 37, 'pos_y' => 24, 'is_flip' => 0, 'count_cell' => 0],
                ['int', 'int', 'int', 'int', 'int', 'int', 'int']);
            $resultRidge = $shardDb->insert('user_building',
                ['user_id' => $userId, 'building_id' => 11, 'in_inventory' => 0, 'pos_x' => 33, 'pos_y' => 26, 'is_flip' => 0, 'count_cell' => 0],
                ['int', 'int', 'int', 'int', 'int', 'int', 'int']);
            $resultRidge = $shardDb->insert('user_building',
                ['user_id' => $userId, 'building_id' => 11, 'in_inventory' => 0, 'pos_x' => 35, 'pos_y' => 26, 'is_flip' => 0, 'count_cell' => 0],
                ['int', 'int', 'int', 'int', 'int', 'int', 'int']);
            $resultRidge = $shardDb->insert('user_building',
                ['user_id' => $userId, 'building_id' => 11, 'in_inventory' => 0, 'pos_x' => 37, 'pos_y' => 26, 'is_flip' => 0, 'count_cell' => 0],
                ['int', 'int', 'int', 'int', 'int', 'int', 'int']);
            $resultRidge = $shardDb->query("SELECT * FROM user_building WHERE building_id = 11 AND user_id =".$userId);
            if ($resultRidge) {
                $arr = $resultRidge->fetchAll();
                foreach ($arr as $value => $dict) { 
                    $result = $shardDb->insert('user_plant_ridge',
                        ['user_id' => $userId, 'plant_id' => 31, 'user_db_building_id' => $dict['id'], 'time_start' => time()-12000],
                        ['int', 'int', 'int', 'int']);
                }
            }
            
            // add farm and chickens    
            $resultFarm = $shardDb->insert('user_building',
                ['user_id' => $userId, 'building_id' => 14, 'in_inventory' => 0, 'pos_x' => 22, 'pos_y' => 11, 'is_flip' => 0, 'count_cell' => 0],
                ['int', 'int', 'int', 'int', 'int', 'int', 'int']);
            $resultFarm = $shardDb->query("SELECT * FROM user_building WHERE building_id = 14 AND user_id =".$userId);
            if ($resultFarm) {
                $arr = $resultFarm->fetch();
                $resultAnimal = $shardDb->insert('user_animal',
                        ['user_id' => $userId, 'animal_id' => 1, 'user_db_building_id' => $arr['id'], 'raw_time_start' => 0],
                        ['int', 'int', 'int', 'int']);
                $resultAnimal = $shardDb->insert('user_animal',
                        ['user_id' => $userId, 'animal_id' => 1, 'user_db_building_id' => $arr['id'], 'raw_time_start' => 0],
                        ['int', 'int', 'int', 'int']);
            }    
                
            $resultAmbar = $shardDb->insert('user_building',
                ['user_id' => $userId, 'building_id' => 12, 'in_inventory' => 0, 'pos_x' => $const['AMBAR_POS_X'], 'pos_y' => $const['AMBAR_POS_Y']],
                ['int', 'int', 'int', 'int', 'int']);
            $resultSklad = $shardDb->insert('user_building',
                ['user_id' => $userId, 'building_id' => 13, 'in_inventory' => 0, 'pos_x' => $const['SKLAD_POS_X'], 'pos_y' => $const['SKLAD_POS_Y']],
                ['int', 'int', 'int', 'int', 'int']);
            
            $arr = [];
            $arrIns = [2, 3, 4, 7, 8, 9];
                $arrInsIds = implode(',', array_values($arrIns));
                $result = $mainDb->query("SELECT id FROM resource WHERE resource_type = 7 AND block_by_level <=1 AND id NOT IN (".$arrInsIds.") ORDER BY RAND() LIMIT 1");
                $instrument = $result->fetch();
                if ($instrument['id']) { $arr[] = $instrument['id']; }
    
            $result = $mainDb->query("SELECT * FROM data_tree ORDER BY RAND()");
            $t = $result->fetchAll();
            $count = 0;
            foreach ($t as $value => $r) {
                $tArr = explode('&', $r['block_by_level']);
                if ($tArr[0] <= 1) {
                    $arr[] = $r['craft_resource_id'];
                    $count++;
                    if ($count >=2) break;
                }
            }
   
            $result = $mainDb->query("SELECT id FROM resource WHERE resource_type = 5 AND block_by_level <= 1 ORDER BY RAND() LIMIT 6");
            $plants = $result->fetchAll();
            foreach ($plants as $value => $p) {
                $arr[] = $p['id'];
            }
            for ($i = 0; $i < 6; $i++) {
                $arr[]= -1;
            }

            $resultNeighbor = $shardDb->insert('user_neighbor',
                ['user_id' => $userId, 'last_update' => date('j'), 'resource_id1' => $arr[0], 'resource_id2' => $arr[1], 'resource_id3' => $arr[2], 'resource_id4' => $arr[3], 'resource_id5' => $arr[4], 'resource_id6' => $arr[5]],
                ['int', 'int', 'int', 'int', 'int', 'int', 'int', 'int']);

            return $userId;
    }

    final public function checkSessionKey($userId, $sessionKey, $channelId = 2) {
        $memcache = $this->getMemcache();
        $sess = $memcache->get('selo'.(string)$userId.'ch'.$channelId);
        if (!$sess) {
            $mainDb = $this->getMainDb($channelId);
            $result = $mainDb->query("SELECT session_key FROM users WHERE id=" . $userId);
            $arr = $result->fetch();
            if (!$arr) return true;
            $sess = $arr['session_key'];
            $memcache->set('selo'.(string)$userId.'ch'.$channelId, (string)$sess, MEMCACHED_DICT_TIME);
        }
        if ((string)$sessionKey == (string)$sess || (string)$sess == '0') {
            return true;
        } else {
            return false;
        }
    }


    final public function verifySecurityKey($securityKey = '', $appGuid, $chGuid, $userSocialId, $scriptName) {
        return (!empty($securityKey) && ($securityKey == (md5($appGuid . $chGuid . $userSocialId . substr($scriptName, strrpos($scriptName, '/') + 1) . GAME_SECRET)) ||
                $securityKey == (md5($appGuid . $chGuid . $userSocialId . substr($scriptName, strrpos($scriptName, '/') + 1) . EDITOR_SECRET))));
    }

    final public function getSocialNetwork($channelId) {
        if ($this->_socialNetwork == NULL) {
            if ($channelId == 2) {
                $socialNetwork = $this->_cfg["sn"]["socialNetworkClass"];
                $this->_socialNetwork = new $socialNetwork($this->_cfg["sn"]);
            } else if ($channelId == 3) {
                $socialNetwork = $this->_cfg["snOK"]["socialNetworkClass"];
                $this->_socialNetwork = new $socialNetwork($this->_cfg["snOK"]);
            } else if ($channelId == 4) {
                $socialNetwork = $this->_cfg["snFB"]["socialNetworkClass"];
                $this->_socialNetwork = new $socialNetwork($this->_cfg["snFB"]);
            }
        }
        return $this->_socialNetwork;
    }
    
    final public function test() {
        $q = '';
        foreach ($this->_cfg as $key => $dict) {
            $q = $q.' '.$key;
        }
        return $q;
    }

    final public function getRandomResource($userId, $channelId) {
        $mainDb = $this->getMainDb($channelId);
        $result = $mainDb->query("SELECT * FROM users WHERE id =".$userId);
        if ($result) {
            $arr = $result->fetch();
            $level = $arr['level'];
        } else {
            $level = 1;
        }
        $url = 'instrumentAtlas';
        $result = $mainDb->query("SELECT id FROM resource WHERE block_by_level <=".$level." AND url <>".$url);
        if ($result) {
            $arr = $result->fetchAll();
            return array_rand($arr, 1);
        } else {
            return 10;
        }
    }
}