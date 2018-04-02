<?php
try {
    $parameters = [
        'photo' => new CURLFile($_POST["image_url"])
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $_POST["upload_url"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
    $result = curl_exec($ch);
    curl_close($ch);
    echo $result;

} catch (Exception $e) {
    echo 'exception: ',  $e->getMessage();
}


