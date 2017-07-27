<?php
require_once 'vkapi.class.php';

interface SocialNetworkInterface {

    public function getUsers($socialNetworkUids);
    public function isGroupMember($socialNetworkUid, $socialNetworkGroupId);
    public function setUserLevel($socialNetworkUid, $socialNetworkLevel);

    public function sendNotification($socialNetworkUid, $message);
    public function getJavaScript();
    public function transactionChek($transaction_id);
    public function getFriendCount();
    public function getFriends();
    public function getFriendsApp();
    public function getFriendsOnline();
    public function transactionCreate($price, $serviceId);
    public function addActivity($text);
    public function isBirthDay($date);
    public function getSocialObject();
    public function userSync($socialNetworkUid, $socialNetworkLevel, $socialNetworkXp);
    public function check_in_another_game($socialNetworkUid);
    public function setCounters($usersAndCounters);

    public function check_targeting($socialNetworkUid, $country, $age_range, $gender, $bdate);
    public function check_connection();
}

// -----------------------------------------------------------------------------------------------

class VKSocialNetwork implements SocialNetworkInterface {
    private $_vk;
    private $_socialNetworkParameters;
    function __construct($socialNetworkParameters) {
        $this->_socialNetworkParameters = $socialNetworkParameters;
    }

    public function getSocialObject() {
        if (empty($this->_vk)) {
            $this->_vk = new vkapi(
                $this->_socialNetworkParameters["api_id"],
                $this->_socialNetworkParameters["secret_key"]);
        }
        return $this->_vk;
    }

    public function setCounters($usersAndCounters) {
        if($usersAndCounters === null) { return true; }
        $limit = 200;
        $requestArray = array();
        foreach($usersAndCounters as $user) {
            if($limit<=0) { break; }
            $requestArray[] = $user['id'].':'.$user['counter'];
            $limit--;
        }
        $requestString = implode(',', $requestArray);
        $resultResponse = $this->getSocialObject()->api('secure.setCounter', array('counters' => $requestString));
        return $resultResponse['response'];
    }

    public function getUsers($socialNetworkUid) {
        $resultResponse = $this->getSocialObject()->api('users.get', array('uids'=>$socialNetworkUid, 'fields'=>'sex, bdate, city, country, first_name, last_name'));
        return $resultResponse['response'];
    }

    public function isGroupMember($socialNetworkUid, $socialNetworkGroupId) {
        return $this->getSocialObject()->api('groups.isMember', array('gid'=>$socialNetworkGroupId, 'uid'=>$socialNetworkUid));
    }

    public function setUserLevel($socialNetworkUid, $socialNetworkLevel) {
        return $this->getSocialObject()->api('secure.setUserLevel', array('user_id'=>$socialNetworkUid, 'level'=>$socialNetworkLevel));
    }

    public function sendNotification($socialNetworkUid, $message) {
        return $this->getSocialObject()->api('secure.sendNotification', array('timestamp'=>time(), 'user_ids'=>$socialNetworkUid, 'message'=>$message));
    }

    public function isBirthDay($date) {
        if (!empty($date))  {
            $data = explode(".", $date);
            $day = date('j');
            $month = date('n');
            if ($data[0] == $day && $data[1] == $month) { return true; }
        }
        return false;
    }

    public function check_targeting($socialNetworkUid, $country, $age_range, $gender, $bdate) {
        $user = $this->getUsers($socialNetworkUid);
        $user = $user[0];

        if (!empty($country)) {
            $targ = explode(",", $country);
            $country_array = array(1=>"RU", 2=>"UA");

            if (!in_array($country_array[$user['country']], $targ)) return false;
        }

        if (!empty($age_range)) {
            if (empty($bdate)) return false;
            $bdate = explode(".", $bdate);
            if (empty($bdate[2])) return false;
            $year = date('Y');
            $u_age = $year - $bdate[2];
            $age = explode("-", $age_range);

            if ($u_age < $age[0] || $u_age > $age[1]) return false;
        }

        if (!empty($gender)) {
            if ($user["sex"] != $gender) return false;
        }

        return true;
    }

    public function check_connection() {
        $res = $this->getUsers("26342690");
        if (!empty($res[0])){
            return "ok";
        } else {
            return "error";
        }
    }

    public function getJavaScript() { return false; }
    public function transactionChek($transaction_id) { return false; }
    public function getFriendCount() {  return 0; }
    public function getFriends() { return false; }
    public function getFriendsApp() { return false; }
    public function getFriendsOnline() { return false; }
    public function transactionCreate($price, $serviceId) { return false; }
    public function addActivity($text) { return false; }
    public function userSync($socialNetworkUid, $socialNetworkLevel, $socialNetworkXp) { return false;}
    public function check_in_another_game($socialNetworkUid) { return false; }
}

// -----------------------------------------------------------------------------------------------------

class OkSocialNetwork implements SocialNetworkInterface {
    private $app_id;
    private $secret_key;

    function __construct($socialNetworkParameters){
//        $this->app_id = $socialNetworkParameters["api_id"];
//        $this->secret_key = $socialNetworkParameters["secret_key"];
        $this->app_id = "1248696832";
        $this->secret_key = "864364A475EBF25367549586";
        $this->public_key = "CBALJOGLEBABABABA";
    }
    public function getUsers($socialNetworkUid) {
        $path = "http://api.odnoklassniki.ru/fb.do?";
        $params = array(
            'application_key=' . $this->app_id,
            'uids=' . $socialNetworkUid,
            'format=JSON',
            'fields=first_name,last_name,gender,birthday,age,location',
            'method=users.getInfo'
        );
        sort($params);
        $sig = md5(implode("", $params) . $this->secret_key);
        $request = $path . implode("&", $params) . "&sig=".$sig;
        $page = file_get_contents($request);
        return json_decode($page, true);
    }

    public function isGroupMember($socialNetworkUid, $socialNetworkGroupId) {
        $path = "http://api.odnoklassniki.ru/fb.do?";
        $params = array('application_key=' . $this->app_id,
            'uid=' . $socialNetworkUid,
            'format=JSON',
            'method=group.getUserGroupsV2'
        );
        sort($params);
        $sig = md5(implode("", $params) . $this->secret_key);
        $request = $path . implode("&", $params) . "&sig=".$sig;
        $page = file_get_contents($request);
        $result = json_decode($page);
        $res['response'] = 0;
        $groups = $result->groups;
        if (!empty($groups)){
            foreach ($groups as $k){
                if ($k->groupId == $socialNetworkGroupId){
                    $res['response'] = 1;
                }
            }
        }
        return $res;
    }

    public function sendNotification($arr, $notif) {
        if (is_array($notif)) {
            $expires = date("Y.m.d H:s", $notif['date_end']);
            $params = array(
                'application_key='.$this->public_key,
                'text='.$notif['message'],
                'format='.'json',
                'expires='.$expires,
                'last_access_range='.$notif['last_access_range']
            );

            sort($params);
            $sig = md5(implode("", $params) . $this->secret_key);
            $url = "http://api.odnoklassniki.ru/api/notifications/sendMass";
            $paramsAll = array(
                'format' => 'json',
                'application_key' => $this->public_key,
                'text' => $notif['message'],
                'expires' => $expires,
                'last_access_range' => $notif['last_access_range'],
                'sig' => $sig
            );
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $paramsAll);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($curl);
            curl_close($curl);
            return $result;
        } else return false;
    }

    public function isBirthDay($date) {
        if (!empty($date)) {
            $data = explode("-", $date);
            $data = array_reverse($data);
            $day = date('d');
            $month = date('m');
            if ($data[0] == $day && $data[1] == $month){
                return true;
            }
        }
        return false;
    }

    public function check_targeting($socialNetworkUid, $country, $age_range, $gender, $bdate) {
        $user = $this->getUsers($socialNetworkUid);
        $user = $user[0];
        if (!empty($country)) {
            if (!isset($user['location']['countryCode']) || !in_array($user['location']['countryCode'], explode(",", $country))) { return FALSE; }
        }

        if (!empty($age_range)) {
            if (empty($bdate)) { return FALSE; }
            $bdate = explode("-", $bdate);
            if (empty($bdate[0])) return false;
            $year = date('Y');
            $u_age = $year - $bdate[0];
            $age = explode("-", $age_range);
            if ($u_age < $age[0] || $u_age > $age[1]) return false;
        }
        return true;
    }

    public function userSync($socialNetworkUid, $socialNetworkLevel, $socialNetworkXp) { return false; }
    public function check_in_another_game($socialNetworkUid) { return NULL; }
    public function getJavaScript() { return false; }
    public function transactionChek($transaction_id) { return false; }
    public function getFriendCount() { return 0; }
    public function getFriends() { return false; }
    public function getFriendsApp() { return false; }
    public function getFriendsOnline() { return false; }
    public function transactionCreate($price, $serviceId) { return false;}
    public function addActivity($text) { return false; }
    public function getSocialObject() { return false; }
    public function setUserLevel($socialNetworkUid, $socialNetworkLevel) { return false; }
    public function setCounters($usersAndCounters) { return false; }
    public function check_connection() { return "ok"; }
}

// -----------------------------------------------------------------------------------------------------

class FBSocialNetwork implements SocialNetworkInterface {
    private $app_id;
    private $secret_key;

    function __construct($socialNetworkParameters){
//        $this->app_id = $socialNetworkParameters["api_id"];
//        $this->secret_key = $socialNetworkParameters["secret_key"];
        $this->app_id = "105089583507105";
        $this->secret_key = "2b62f8a1aed1b7a677a215949d071bcd";
    }
    public function getUsers($socialNetworkUid) {
//        $path = "http://api.odnoklassniki.ru/fb.do?";
//        $params = array(
//            'application_key=' . $this->app_id,
//            'uids=' . $socialNetworkUid,
//            'format=JSON',
//            'fields=first_name,last_name,gender,birthday,age,location',
//            'method=users.getInfo'
//        );
//        sort($params);
//        $sig = md5(implode("", $params) . $this->secret_key);
//        $request = $path . implode("&", $params) . "&sig=".$sig;
//        $page = file_get_contents($request);
//        return json_decode($page, true);
        return ''; 
    }

    public function isGroupMember($socialNetworkUid, $socialNetworkGroupId) {
//        $path = "http://api.odnoklassniki.ru/fb.do?";
//        $params = array('application_key=' . $this->app_id,
//            'uid=' . $socialNetworkUid,
//            'format=JSON',
//            'method=group.getUserGroupsV2'
//        );
//        sort($params);
//        $sig = md5(implode("", $params) . $this->secret_key);
//        $request = $path . implode("&", $params) . "&sig=".$sig;
//        $page = file_get_contents($request);
//        $result = json_decode($page);
//        $res['response'] = 0;
//        $groups = $result->groups;
//        if (!empty($groups)){
//            foreach ($groups as $k){
//                if ($k->groupId == $socialNetworkGroupId){
//                    $res['response'] = 1;
//                }
//            }
//        }
//        return $res;
        return false;
    }

    public function sendNotification($arr, $notif) {
//        if (is_array($notif)) {
//            $expires = date("Y.m.d H:s", $notif['date_end']);
//            $params = array(
//                'application_key=' . $this->app_id,
//                'text='.$notif['message'],
//                'format=json',
//                'expires='.$expires,
//                'last_access_range='.$notif['last_access_range']
//            );
//
//            sort($params);
//            $sig = md5(implode("", $params) . $this->secret_key);
//            $url = "http://api.odnoklassniki.ru/api/notifications/sendMass";
//            $paramsAll = array(
//                'format' => 'json',
//                'application_key' => $this->app_id,
//                'text' => $notif['message'],
//                'expires' => $expires,
//                'last_access_range='.$notif['last_access_range'],
//                'sig' => $sig
//            );
//            $curl = curl_init();
//            curl_setopt($curl, CURLOPT_URL, $url);
//            curl_setopt($curl, CURLOPT_POST, 1);
//            curl_setopt($curl, CURLOPT_POSTFIELDS, $paramsAll);
//            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//            $result = curl_exec($curl);
//            curl_close($curl);
//            return $result;
//        } else return false;
        return false;
    }

    public function isBirthDay($date) {
//        if (!empty($date)) {
//            $data = explode("-", $date);
//            $data = array_reverse($data);
//            $day = date('d');
//            $month = date('m');
//            if ($data[0] == $day && $data[1] == $month){
//                return true;
//            }
//        }
        return false;
    }

    public function check_targeting($socialNetworkUid, $country, $age_range, $gender, $bdate) {
//        $user = $this->getUsers($socialNetworkUid);
//        $user = $user[0];
//        if (!empty($country)) {
//            if (!isset($user['location']['countryCode']) || !in_array($user['location']['countryCode'], explode(",", $country))) { return FALSE; }
//        }
//
//        if (!empty($age_range)) {
//            if (empty($bdate)) { return FALSE; }
//            $bdate = explode("-", $bdate);
//            if (empty($bdate[0])) return false;
//            $year = date('Y');
//            $u_age = $year - $bdate[0];
//            $age = explode("-", $age_range);
//            if ($u_age < $age[0] || $u_age > $age[1]) return false;
//        }
//        return true;
        return false;
    }

    public function userSync($socialNetworkUid, $socialNetworkLevel, $socialNetworkXp) { return false; }
    public function check_in_another_game($socialNetworkUid) { return NULL; }
    public function getJavaScript() { return false; }
    public function transactionChek($transaction_id) { return false; }
    public function getFriendCount() { return 0; }
    public function getFriends() { return false; }
    public function getFriendsApp() { return false; }
    public function getFriendsOnline() { return false; }
    public function transactionCreate($price, $serviceId) { return false;}
    public function addActivity($text) { return false; }
    public function getSocialObject() { return false; }
    public function setUserLevel($socialNetworkUid, $socialNetworkLevel) { return false; }
    public function setCounters($usersAndCounters) { return false; }
    public function check_connection() { return "ok"; }
}