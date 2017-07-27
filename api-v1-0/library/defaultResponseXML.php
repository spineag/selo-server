<?php
/**
 * Created by IntelliJ IDEA.
 * User: oleksiy.stadnyk
 * Date: 10/8/14
 * Time: 2:51 PM
 */

////// ORIGINAL

header('Content-type: application/xml; charset=utf-8');
$dom = new DOMDocument('1.0', 'utf-8');
$response = $dom->appendChild($dom->createElement('response'));
$status =  $response->appendChild($dom->createElement('status'));
$idAttr = $dom->createAttribute("id");
$messageAttr = $dom->createAttribute("message");

$status->appendChild($idAttr);
$status->appendChild($messageAttr);
$idAttr->value = 0;
$messageAttr->value = "ok";
