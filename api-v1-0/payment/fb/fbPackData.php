<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');

$packId = (int)$_GET['p'];
$version = (int)$_GET['v'];
$requestId = $_GET['r'];
$mainDb = $app->getMainDb(4);

if ($packId < 13) {
    $result = $mainDb->query("SELECT * FROM data_buy_money WHERE id=".$packId);
    $r = $result->fetch();
    if ($r) {
        $cost = $r['cost_for_real'];
        $pic = $r['url'];
        $count = $r['count'];
        if ($packId < 7) {
            $st1 = (string)$count.' Ruby Pack';
            $st2 = 'A '.$count.' rubies!';
        } else {
            $st1 = (string)$count.' Coin Pack';
            $st2 = 'A '.$count.' coins!';
        }
    } else {
        $cost = 100000;
        $pic = 'https://505.ninja/images/icons/starter_pack_icon.png';
        $count = 1;
        $st1 = 'Error';
        $st2 = 'Error';
    }
} else if ($packId == 13) {
    $result = $mainDb->query("SELECT * FROM data_starter_pack WHERE id=" . $packId);
    $r = $result->fetch();
    if ($r) {
        $cost = $r['new_cost'];
        $st1 = 'Starter Pack';
        $st2 = 'Starter Pack';
    } else {
        $cost = 50000;
        $st1 = 'Error';
        $st2 = 'Error';
    }
    $pic = 'https://505.ninja/images/icons/starter_pack_icon.png';
} else if ($packId == 14) {
    $result = $mainDb->query("SELECT * FROM data_sale_pack WHERE id=" . $packId);
    $r = $result->fetch();
    if ($r) {
        $cost = $r['new_cost'];
        $st1 = 'Sale Pack';
        $st2 = 'Sale Pack';
    } else {
        $cost = 75000;
        $st1 = 'Error';
        $st2 = 'Error';
    }
    $pic = 'https://505.ninja/images/icons/starter_pack_icon.png';
}


?>


<!DOCTYPE html>
<html>
<head prefix=
      "og: http://ogp.me/ns#
     fb: http://ogp.me/ns/fb#
     product: http://ogp.me/ns/product#">
    <meta property="og:type"                   content="og:product" />
    <meta property="og:title"                  content=<?php echo $st1 ?> />
    <meta property="og:image"                  content=<?php echo $pic ?> />
    <meta property="og:description"            content=<?php echo $st2 ?> />
    <meta property="og:url"                    content=<?php echo "https://505.ninja/php/api-v1-0/payment/fb/fbPackData.php?v=".$version."&p=".$packId."&r=".$requestId ?>/>
    <meta property="product:price:amount"      content=<?php echo $cost ?>/>
    <meta property="product:price:currency"    content="USD"/>
</head>
</html>


