<?php
require_once '../library/Application.php';
$email = Application::getInstance()->getEmail();

$_USER_INF = array();

function is_payer($social_id)
{
    $return = FALSE;
    $social_id = (int)$social_id;
    $_db = Application::getInstance()->getMainDb(2);

    $result = $_db->query(" SELECT COUNT( order_id ) AS count
                            FROM dict_payments
                            INNER JOIN users ON dict_payments.user_id = users.user_id
                            WHERE users.user_social_id =  '".$social_id."'
                            AND  `dict_payments`.`dict_money_object_id` < 60");
    if($orders = $result->fetch())
    {
        if($orders['count'] > 0)
        {
            $return = (int) $orders['count'];
        }
        else
        {
            $return = FALSE;
        }
    }
    else
    {
        $return = FALSE;
    }

    return $return;
}

$error_code = NULL;
if (!empty($_POST))
{
    if (empty($_POST['user_name']) || empty($_POST['email']) || empty($_POST['description']) || empty($_POST['theme']))
    {
        $error_code = 1;
    }
    if (empty($error))
    {
        //valid email
        if(preg_match("|^[-0-9a-z_\.]+@[-0-9a-z_^\.]+\.[a-z]{2,6}$|i", $_POST['email']))
        {
            $log = "";
            if (!empty($_POST['log']))
            {
                $log = $_POST['log'];
            }

            $to = "ivan.samchuk@joyrocks.com"; // $email;
            $subject = $_POST['theme'];

            $payer = is_payer($_POST['uid']);
                if($payer !== FALSE)
                {
                    if($payer < 5)
                    {
                        $subject = 'PAYER ('.$payer.' purchases)'.' / '. $subject;
                    }
                    else
                    {
                        $subject = 'SOS.PAYER! ('.$payer.' purchases)'.' / '.$subject;
                    }
            }
            $subject = "=?utf-8?b?".base64_encode($subject)."?=";

            $name = "log".$_POST['uid'].".txt";
            $EOL = "\r\n";
            $boundary     = "--".md5(uniqid(time()));

            $headers  = "MIME-Version: 1.0;$EOL";
            $headers .= "Content-Type: multipart/mixed;  boundary=\"$boundary\"$EOL";
            $headers .= "From: <".$_POST['email'].">";

            $html = '<html>
            <head>
            <title>'.$subject.'</title>
            </head>
            <body>
                <p>From: '.$_POST['user_name'].'</p>
                <p>'. $_POST['email'] .'</p>

                <p>'.$_POST['description'].'</p>

                <div style="padding-top:15px;">Social ID:</div>
                <div>'.$_POST['uid'].'</div>

                <div style="padding-top:15px;">Browser:</div>
                <div>'.$_SERVER['HTTP_USER_AGENT'].'</div>
            </body>
            </html>';

            $multipart  = "--".$boundary.$EOL;
            $multipart .= "Content-Type: text/html; charset=utf-8".$EOL;
            $multipart .= "Content-Transfer-Encoding: base64".$EOL;
            $multipart .= $EOL;
            $multipart .= chunk_split(base64_encode($html));

            $multipart .=  $EOL."--".$boundary.$EOL;
            $multipart .= "Content-Type: application/octet-stream; name=\"".$name."\"".$EOL;
            $multipart .= "Content-Transfer-Encoding: base64".$EOL;
            $multipart .= "Content-Disposition: attachment; filename=\"".$name."\"".$EOL;
            $multipart .= $EOL;
            $multipart .= chunk_split($log);

            $multipart .= $EOL."--".$boundary."--".$EOL;

            if(!mail($to, $subject, $multipart, $headers))
            {
                $error_code = 10;
            }
        }
        else
        {
            $error_code = 5;
        }
    }
    if ($error_code !== NULL)
    {
        echo json_encode(array('success' => false, 'error_code' => $error_code));
    }
    else
    {
//        $_db = Application::getInstance()->getMainDb();
//        $fullname = explode(" ", $_POST['user_name']);
//        $sql = "INSERT INTO support_emails (email_id, id_social, email, user_name, user_sname, count_email, user_level, last_email)
//        VALUES (NULL, '".$_POST['uid']."', '".$_POST['email']."', '".$fullname[0]."', '".$fullname[1]."', '1', '".$_USER_INF['level']."', '".time()."') ON DUPLICATE KEY UPDATE count_email=count_email+1, last_email='".time()."';";
//        $_db->query($sql);
        echo json_encode(array('success' => true));
    }
    //theme
    //themeOwn
}