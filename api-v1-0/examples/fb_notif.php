<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/php/fb-php-graph-sdk-5.5/src/Facebook/autoload.php';

$app_id = "1936104599955682";
$app_secret = "dd3c1b11a323f01a3ac23a3482724c49";
$app_token = "1936104599955682|BJ5JAYUV8FSdztyc3MW2lHVbXoU";

$fb = new Facebook\Facebook([
    'app_id' => 'APP_ID',
    'app_secret' => 'APP_SECRET',
    'default_graph_version' => 'v2.9',
]);
$helper = $fb->getCanvasHelper();
$permissions = []; // optional
try {
    if (isset($_SESSION['facebook_access_token'])) {
        $accessToken = $_SESSION['facebook_access_token'];
    } else {
        $accessToken = $helper->getAccessToken();
    }
} catch(Facebook\Exceptions\FacebookResponseException $e) {
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}
if (isset($accessToken)) {
    if (isset($_SESSION['facebook_access_token'])) {
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    } else {
        $_SESSION['facebook_access_token'] = (string) $accessToken;
        $oAuth2Client = $fb->getOAuth2Client();
        $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
        $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    }
    try {
        $request = $fb->get('/me');
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        if ($e->getCode() == 190) {
            unset($_SESSION['facebook_access_token']);
            $helper = $fb->getRedirectLoginHelper();
            $loginUrl = $helper->getLoginUrl('https://apps.facebook.com/APP_NAMESPACE/', $permissions);
            echo "<script>window.top.location.href='".$loginUrl."'</script>";
            exit;
        }
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }

    try {
        $profile_request = $fb->get('/me?fields=name,first_name,last_name,email');
        $profile = $profile_request->getGraphNode()->asArray();
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
        echo 'Graph returned an error: ' . $e->getMessage();
        unset($_SESSION['facebook_access_token']);
        echo "<script>window.top.location.href='https://apps.facebook.com/APP_NAMESPACE/'</script>";
        exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {

        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }
    // sending notification to user
    $sendNotif = $fb->post('/' . $profile['id'] . '/notifications', array('href' => '?true=43', 'template' => 'click here for more information!'), $app_token);
    // Now you can redirect to another page and use the access token from $_SESSION['facebook_access_token']
} else {
    $helper = $fb->getRedirectLoginHelper();
    $loginUrl = $helper->getLoginUrl('https://apps.facebook.com/APP_NAMESPACE/', $permissions);
    echo "<script>window.top.location.href='".$loginUrl."'</script>";
}