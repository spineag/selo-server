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
    const ERROR_TYPE_SYSTEM = 9999;
    const ERROR_TYPE_PARAM_SIGNATURE = 104;

    // в эти переменные следует записать открытый и секретный ключи приложения
    const APP_PUBLIC_KEY = "CBALJOGLEBABABABA";
    const APP_SECRET_KEY = "864364A475EBF25367549586";

    // массив пар код продукта => цена
    private static $catalog = array();

    // массив пар код ошибки => описание
    private static $errors = array(
        1 => "UNKNOWN: please, try again later. If error repeats, contact application support team.",
        2 => "SERVICE: service temporary unavailible. Please try again later",
        3 => "CALLBACK_INVALID_PAYMENT: invalid payment data. Please try again later. If error repeats, contact application support team. ",
        9999 => "SYSTEM: critical system error. Please contact application support team.",
        104 => "PARAM_SIGNATURE: invalid signature. Please contact application support team."
    );

//    public static function fillCatalog() {
//        self::$catalog = array(
//            "1" => 20,
//            "2" => 50,
//            "3" => 100,
//            "4" => 190,
//            "5" => 490,
//            "6" => 990,
//            "7" => 30,
//            "8" => 60,
//            "9" => 90,
//            "10" => 240,
//            "11" => 690,
//            "12" => 1490,
//            "13" => 85,
//            "14" => 80
//        );
//        return self::$catalog;
//    }

    // функция рассчитывает подпись для пришедшего запроса
    // подробнее про алгоритм расчета подписи можно посмотреть в документации (http://apiok.ru/wiki/pages/viewpage.action?pageId=42476522)
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
    // функция провкерки корректности платежа
    public static function checkPayment($productCode, $price){
        if (array_key_exists($productCode, self::$catalog) && (self::$catalog[$productCode] == $price)) {
            return true;
        } else {
            return false;
        }
    }

    // функция возвращает ответ на сервер одноклассников
    // о корректном платеже
    public static function returnPaymentOK(){
        $rootElement = 'callbacks_payment_response';
        $dom = self::createXMLWithRoot($rootElement);
        $root = $dom->getElementsByTagName($rootElement)->item(0);

        // добавление текста "true" в тег <callbacks_payment_response> 
        $root->appendChild($dom->createTextNode('true'));

        // генерация xml 
        $dom->formatOutput = true;
        $rezString = $dom->saveXML();

        // установка заголовка
        header('Content-Type: application/xml');
        // вывод xml
        print $rezString;
    }
    // функция возвращает ответ на сервер одноклассников
    // об ошибочном платеже и информацию лб ошибке
    public static function returnPaymentError($errorCode){
        $rootElement = 'ns2:error_response';
        $dom = self::createXMLWithRoot($rootElement);
        $root = $dom->getElementsByTagName($rootElement)->item(0);
        // добавление кода ошибки и описания ошибки
        $el = $dom->createElement('error_code');
        $el->appendChild($dom->createTextNode($errorCode));
        $root->appendChild($el);
        if (array_key_exists($errorCode, self::$errors)){
            $el = $dom->createElement('error_msg');
            $el->appendChild($dom->createTextNode(self::$errors[$errorCode]));
            $root->appendChild($el);
        }

        // генерация xml 
        $dom->formatOutput = true;
        $rezString = $dom->saveXML();

        // добавление необходимых заголовков
        header('Content-Type: application/xml');
        // ВАЖНО: если не добавить этот заголовок, система может некорректно обработать ответ
        header('invocation-error:'.$errorCode);
        // вывод xml
        print $rezString;
    }

    // Рекомендуется хранить информацию обо всех транзакциях
    public static function saveTransaction($uid, $product_code){
        // тут может быть код для сохранения информации о транзакции
        $mainDb = Application::getInstance()->getMainDb(3);
        try {
            $time = date("Y-m-d H:i:s");
            $t = time();
            $mainDb->query('INSERT INTO transactions SET uid='. $uid .', product_code='.$product_code.', time_try="'.$time.'", unitime='.$t);
            $mainDb->query('INSERT INTO transaction_lost SET uid='. $uid .', product_code='.$product_code.', time_buy="'.$time.'", unitime='.$t);
        } catch(Exception $e) {}
    }

    public static function saveErrorTransaction($uid, $errorNumber, $product){
        $mainDb = Application::getInstance()->getMainDb(3);
        try {
            $time = date("Y-m-d H:i:s");
            if ($uid == '') $uid = -1;
            $mainDb->query('INSERT INTO trans_error SET user_id='.$uid.', error_n='.$errorNumber.', product='.$product.', time_try="'.$time.'"');
        } catch(Exception $e) {
            $mainDb->query('INSERT INTO trans_error SET user_id=-5, error_n=-5, product=-5, time_try="123 321"');
        }
    }

    public static function test($n){
        $mainDb = Application::getInstance()->getMainDb(3);
        $time = date("Y-m-d H:i:s");
        $mainDb->query('INSERT INTO trans_error SET user_id=0, error_n='.$n.', product=0, time_try="'.$time.'"');
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

/*
* Обработка платежа начинается отсюда
*/
if (array_key_exists("product_code", $_GET) && array_key_exists("amount", $_GET) && array_key_exists("sig", $_GET)){

    $mainDb = Application::getInstance()->getMainDb(3);
    $isGood = false;
    if ($_GET["product_code"] == "13") {
        $result = $mainDb->query("SELECT new_cost FROM data_starter_pack");
        $a = $result->fetch();
        if ((int)$a['new_cost'] == (int)$_GET['amount']) {
            $isGood = true;
        }
    } else if ($_GET["product_code"] == "14") {
        $result = $mainDb->query("SELECT new_cost FROM data_sale_pack");
        $a = $result->fetch();
        if ((int)$a['new_cost'] == (int)$_GET['amount']) {
            $isGood = true;
        }
    } else {
        $result = $mainDb->query("SELECT cost_for_real FROM data_buy_money WHERE id=".$_GET['product_code']);
        $a = $result->fetch();
        if ((int)$a['cost_for_real'] == (int)$_GET['amount']) {
            $isGood = true;
        }
    }

//    $c = Payment::fillCatalog();
//    if (Payment::checkPayment($_GET["product_code"], $_GET["amount"])) {

    if ($isGood) {
        if ($_GET["sig"] == Payment::calcSignature($_GET)) {
            Payment::saveTransaction($_GET["uid"], $_GET["product_code"]);
            Payment::returnPaymentOK();
        } else {
            // здесь можно что-нибудь сделать, если подпись неверная
            Payment::saveErrorTransaction($_GET["uid"], Payment::ERROR_TYPE_PARAM_SIGNATURE, $_GET["product_code"]);
            Payment::returnPaymentError(Payment::ERROR_TYPE_PARAM_SIGNATURE);
        }
    } else {
        // здесь можно что-нибудь сделать, если информация о покупке некорректна
        Payment::saveErrorTransaction($_GET["uid"], 4, $_GET["product_code"]);
        Payment::returnPaymentError(Payment::ERROR_TYPE_CALLBACK_INVALID_PYMENT);
    }
} else {
    // здесь можно что-нибудь сделать, если информация о покупке или подпись отсутствуют в запросе
    $code = '';
    if (array_key_exists("product_code", $_GET)) $code = $code.'9';
        else  $code = $code.'6';
    if (array_key_exists("amount", $_GET)) $code = $code.'9';
        else  $code = $code.'6';
    if (array_key_exists("sig", $_GET)) $code = $code.'9';
        else  $code = $code.'6';
    Payment::saveErrorTransaction($_GET["uid"], Payment::ERROR_TYPE_CALLBACK_INVALID_PYMENT, $code);
    Payment::returnPaymentError(Payment::ERROR_TYPE_CALLBACK_INVALID_PYMENT);
}

