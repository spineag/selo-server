<?php
require_once '../library/Application.php';

/*
* Класс отвечает за следующие операции:
* проверка корректности платежа, сохранение информации о платеже,
* ответ на запрос сервера одноклассников.
*/
class Payment {
    const ERROR_TYPE_UNKNOWN = 1;
    const ERROR_TYPE_SERVISE = 2;
    const ERROR_TYPE_CALLBACK_INVALID_PYMENT = 3;
    const ERROR_CHECK_CODE = 4;
    const ERROR_TYPE_SYSTEM = 9999;
    const ERROR_TYPE_PARAM_SIGNATURE = 104;

    const APP_PUBLIC_KEY = "CBAEDBIMEBABABABA";
    const APP_SECRET_KEY = "EC804AAB7DD4B598C4F2C3C5";

    private static $errors = array(
        1 => "UNKNOWN: please, try again later. If error repeats, contact application support team.",
        2 => "SERVICE: service temporary unavailible. Please try again later",
        3 => "CALLBACK_INVALID_PAYMENT: invalid payment data. Please try again later. If error repeats, contact application support team. ",
        9999 => "SYSTEM: critical system error. Please contact application support team.",
        104 => "PARAM_SIGNATURE: invalid signature. Please contact application support team."
    );

    public static function calcSignature($request){
        $tmp = $request;
        unset($tmp["sig"]);
        ksort($tmp);
        $resstr = "";
        foreach($tmp as $key=>$value){
            $resstr = $resstr.$key."=".$value;
        }
        $resstr = $resstr.self::APP_SECRET_KEY;
        return md5($resstr);
    }

    public static function returnPaymentOK(){
        $rootElement = 'callbacks_payment_response';
        $dom = self::createXMLWithRoot($rootElement);
        $root = $dom->getElementsByTagName($rootElement)->item(0);
        $root->appendChild($dom->createTextNode('true'));
        $dom->formatOutput = true;
        $rezString = $dom->saveXML();
        header('Content-Type: application/xml');
        print $rezString;
    }
    public static function returnPaymentError($errorCode){
        $rootElement = 'ns2:error_response';
        $dom = self::createXMLWithRoot($rootElement);
        $root = $dom->getElementsByTagName($rootElement)->item(0);
        $el = $dom->createElement('error_code');
        $el->appendChild($dom->createTextNode($errorCode));
        $root->appendChild($el);
        if (array_key_exists($errorCode, self::$errors)){
            $el = $dom->createElement('error_msg');
            $el->appendChild($dom->createTextNode(self::$errors[$errorCode]));
            $root->appendChild($el);
        }

        $dom->formatOutput = true;
        $rezString = $dom->saveXML();
        header('Content-Type: application/xml');
        header('invocation-error:'.$errorCode);
        print $rezString;
    }

    public static function checkCode($code, $amount){
        $isGood = false;
        $mainDb = Application::getInstance()->getMainDb(3);
        if ($code == 13) {
            $result = $mainDb->query("SELECT new_cost FROM data_starter_pack");
            $a = $result->fetch();
            if ((int)$a['new_cost'] == $amount) {
                $isGood = true;
            }
        } else if ($code >= 100000) {
            $code = $code - 100000;
            $result = $mainDb->query("SELECT DISTINCT new_cost FROM data_sale_pack WHERE id =".$code);
            $a = $result->fetchAll();
            if (!empty($a)) {
                foreach ($a as $key => $r) {
                    if ((int)$r['new_cost'] == $amount) {
                        $isGood = true;
                        break;
                    }
                }
            }
        } else {
            $result = $mainDb->query("SELECT cost_for_real FROM data_buy_money WHERE id=".$code);
            $a = $result->fetchAll();
            if (!empty($a)) {
                foreach ($a as $key => $r) {
                    if ((int)$r['cost_for_real'] == $amount) {
                        $isGood = true;
                        break;
                    }
                }
            }
        }
        return $isGood;
    }

    public static function saveTransactionInit($uid, $code, $amount, $time, $req, $unixtime){
        $mainDb = Application::getInstance()->getMainDb(3);
        try {
            $mainDb->query('INSERT INTO transaction_lost SET uid="'.$uid.'", product_code='.$code.', amount='.$amount.', time_try="'.$time.'", request_id="'.$req.'", unixtime='.$unixtime);
        } catch(Exception $e) {
            Payment::test($e->getMessage(), 401);
        }
    }

    public static function saveTransaction($uid, $code, $amount, $time, $req, $unixtime){
        $mainDb = Application::getInstance()->getMainDb(3);
        try {
            $mainDb->query('INSERT INTO transactions SET uid="'.$uid.'", product_code='.$code.', amount='.$amount.', time_try="'.$time.'", request_id="'.$req.'", unixtime='.$unixtime.', getted=0');
            $mainDb->query('DELETE FROM transaction_lost WHERE request_id="'.$req.'"');
        } catch(Exception $e) {
            Payment::test($e->getMessage(), 402);
        }
    }

    public static function saveErrorTransaction($uid, $code, $amount, $time, $req, $error, $unixtime){
        $mainDb = Application::getInstance()->getMainDb(3);
        if ($uid == '') $uid = -1;
        try {
            $mainDb->query('INSERT INTO trans_error SET uid="'.$uid.'", product_code='.$code.', amount='.$amount.', time_try="'.$time.'", request_id="'.$req.'", error='.$error.', unixtime='.$unixtime);
            $mainDb->query('DELETE FROM transaction_lost WHERE request_id="'.$req.'"');
        } catch(Exception $e) {
            Payment::test($e->getMessage(), 403);
        }
    }

    public static function test($t, $step){
        $mainDb = Application::getInstance()->getMainDb(3);
        $mainDb->query('INSERT INTO test SET info="'.$t.'", step='.$step);
    }

    // функция создает объект DomDocument и доб авляет в него в качестве корневого тега $root
    private static function createXMLWithRoot($root){
        // создание xml документа
        $dom = new DomDocument('1.0');
        // добавление корневого тега
        $root = $dom->appendChild($dom->createElement($root));
        $attr = $dom->createAttribute("xmlns:ns2");
        $attr->value = "http://api.forticom.com/1.0/";
        $root->appendChild($attr);
        return $dom;
    }
}

/*  Обработка платежа начинается отсюда */
if (array_key_exists("product_code", $_GET) && array_key_exists("amount", $_GET) && array_key_exists("sig", $_GET)){
    $code = (int)$_GET["product_code"];
    $amount = (int)$_GET["amount"];
    $time = date("Y-m-d H:i:s");
    $unixtime = time();
    $uid = $_GET["uid"];
    $req = $uid.'.'.$unixtime;
    Payment::saveTransactionInit($uid, $code, $amount, $time, $req, $unixtime);
    $isGood = Payment::checkCode($code, $amount);
    if ($isGood) {
        if ($_GET["sig"] == Payment::calcSignature($_GET)) {
            Payment::saveTransaction($uid, $code, $amount, $time, $req, $unixtime);
            Payment::returnPaymentOK();
        } else {
            Payment::saveErrorTransaction($uid, $code, $amount, $time, $req, Payment::ERROR_TYPE_PARAM_SIGNATURE, $unixtime);
            Payment::returnPaymentError(Payment::ERROR_TYPE_PARAM_SIGNATURE);
        }
    } else {
        Payment::saveErrorTransaction($uid, $code, $amount, $time, $req, Payment::ERROR_CHECK_CODE, $unixtime);
        Payment::returnPaymentError(Payment::ERROR_TYPE_CALLBACK_INVALID_PYMENT);
    }
} else {
    $code = 0;
    $unixtime = time();
    if (array_key_exists("product_code", $_GET)) $code = $code + 10;
        else  $code = $code + 5;
    if (array_key_exists("amount", $_GET)) $code = $code + 100;
        else  $code = $code + 50;
    if (array_key_exists("sig", $_GET)) $code = $code + 1000;
        else  $code = $code + 500;
    Payment::saveErrorTransaction('-', $code, 0, date("Y-m-d H:i:s"), '-', Payment::ERROR_TYPE_CALLBACK_INVALID_PYMENT, $unixtime);
    Payment::returnPaymentError(Payment::ERROR_TYPE_CALLBACK_INVALID_PYMENT);
}

