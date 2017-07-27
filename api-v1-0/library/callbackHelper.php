<?php
/**
 * Created by IntelliJ IDEA.
 * User: volodymyr.vlasyuk
 * Date: 3/14/14
 * Time: 11:53 AM
 */

////// ORIGINAL

defined('SN') OR exit('No direct script access allowed');

class callbackHelper
{
    public $social_id = 0;
    public $pack_id = 0;
    public $input_price;

    //Parameter for VK Offers.
    public $offer;

    //Transaction ID if you need to check & save payment transaction.
    public $transaction_id;
    public $service_id;

    //user_id for AB testing
    public $user_id;

    //Timestamp of payment transaction.
    public $now;

    private $user = array();
    private $money = array();
    private $_db;
    private $_shard_db;

    function __construct()
    {
        include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
        $this->_db = Application::getInstance()->getMainDb(2);
    }

    /**
     * Method steps:
     *
     * 1. Validation input data;
     * 2. Update user resource according to MONEY_ID;
     * 3. Return result TRUE or ERROR CODE.
     * @return bool|string
     */
    public function updateResource()
    {
        $this->now = ($this->now === NULL) ? time() : $this->now;

        $result = $this->_db->query('SELECT *  FROM dict_store_packs AS store_packs WHERE pack_id = ' . $this->pack_id);
        $this->money = $result->fetch();

        if($this->user_id === NULL)
        {
            $result = $this->_db->query("SELECT user_id FROM users WHERE user_social_id = " . $this->social_id);
            $this->user = $result->fetch();
        }
        else
        {
            $this->user = array(
                'user_id' => $this->user_id,
            );
        }

        $this->_shard_db = Application::getInstance()->getShardDb($this->user['user_id'], 2);

        if(empty($this->user))
        {
            return '100';
        }
        elseif(empty($this->money))
        {
            return '101';
        }
        elseif($this->money['currency_count'] != $this->input_price && $this->input_price !== null)
        {
            return '106';
        }
        elseif($this->_checkTransaction() === FALSE)
        {
            return TRUE;
        }

        $return = '0';
        switch($this->money['pack_type'])
        {
            case '1':
                $return = $this->addMoney();
                break;
            case '2':
                $return = $this->addMoney();
                break;
            default:
                $return = '102';
                break;
        }

        return $return;
    }

    /**
     * @return bool|string
     */
    private function addMoney()
    {
        $return = '0';

        $resource_id = $this->money['pack_type'];

        $result = $this->_shard_db->query("UPDATE user_resources
                                           SET resource_count = resource_count + " . $this->money['object_count'] . "
                                           WHERE user_id = '" . $this->user['user_id'] . "' AND resource_id = '" . $resource_id . "'");
        if ($result->getResult())
        {
            if ($_POST['notification_type'] == 'order_status_change_test')
            {
                return TRUE;
            }

            if($this->user_id === NULL)
            {
                $order_id = $this->_insertPayment();
                $this->_insertTransaction($order_id);
                $this->_updateRetention();
            }
            $return = TRUE;
        }
        else
        {
            $return = '103';
        }

        return $return;
    }

    /**
     * @return bool|null
     */
    private function _checkTransaction()
    {
        if($this->transaction_id === NULL)
        {
            return TRUE;
        }

        $return = NULL;
        $_result = $this->_shard_db->query("SELECT user_id FROM user_transaction WHERE transaction_id = '" . $this->transaction_id . "' AND user_id = '" . $this->social_id . "'");
        if ($_result->getResult())
        {
            $result = $_result->fetch();
            if(empty($result))
            {
                $return = TRUE;
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

    /**
     * @param $order_id
     * @return bool|null
     */
    private function _insertTransaction($order_id)
    {
        if($this->transaction_id === NULL)
        {
            return TRUE;
        }

        $return = NULL;
        $_result = $this->_shard_db->query("INSERT INTO `user_transaction` (`transaction_id`, `user_id`, `order_id`) VALUES ('".$this->transaction_id."', '".$this->social_id."', '".$order_id."');");
        if($_result->getResult())
        {
            $return = TRUE;
        }
        else
        {
            $return = FALSE;
        }
        return $return;
    }

    /**
     * @return int OR bool|null - on success return last insert ID.
     */
    private function _insertPayment()
    {
        $return = NULL;

        $this->_db->query("UPDATE users SET payer = 1 WHERE user_id = '" . $this->user['user_id'] . "';");

        $sql = "INSERT INTO dict_payments (order_id, user_id, type, count, date, vote, dict_money_object_id, user_level)
                VALUES (NULL, '".$this->user['user_id']."', '".$this->money['pack_type']."', '".$this->money['object_count']."', '".$this->now."', '".$this->money['currency_count']."', '".$this->money['pack_id']."', '0');";
        $_result = $this->_db->query($sql);
        if($_result->getResult())
        {
            $_result = $this->_db->query("SELECT order_id FROM dict_payments WHERE user_id = '" . $this->user['user_id'] . "' ORDER BY order_id DESC LIMIT 1");
            if ($order = $_result->fetch())
            {
                $return = $order['order_id'];
            }
        }
        else
        {
            $return = FALSE;
        }

        return $return;
    }

    /**
     * Just for update user retention.
     */
    private function _updateRetention()
    {
        return FALSE;

        $_result = $this->_db->query("SELECT * FROM dict_retention_payments WHERE user_id = '" . $this->user['user_id'] . "'");
        $data = $_result->fetch();
        if (empty($data))
        {
            $this->_db->query("INSERT INTO dict_retention_payments (`user_id`, `retention`, `date`) VALUES ('" . $this->user['user_id'] . "', '0', '" . $this->now . "')");
        }
    }

    /*
     * Getting pack info for VK social network (VK Payment window).
     */
    public static function getPackInfo($pack_id)
    {
        $_db = Application::getInstance()->getMainDb(2);

        $resources = array(
            '1' => 'ПТИЦЕБАКСОВ',
            '2' => 'МОНЕТ',
        );

        $return = '';
        $result = $_db->query("SELECT * FROM dict_store_packs AS store_packs
                               WHERE pack_id = " . $pack_id);

        while ($data = $result->fetch())
        {
            switch($data['pack_type'])
            {
                case 1: //coins
                    $return .= ($data['object_type']).' '.$resources[$pack_id];
                    break;
                case 2: //birdbucks
                    $return .= ($data['object_type']).' '.$resources[$pack_id];
                    break;
            }
        }

        return substr($return, 0, -3);
    }

    /**
     * Logging payment errors.
     * @param $filename - PATH for payment errors log
     * @param $data - String with payment error & input data
     */
    public static function errorLog($filename, $data)
    {
        if(file_exists($filename))
        {
            if (is_writeable($filename))
            {
                $fh = fopen($filename, "a+");
                fwrite($fh, '['.date('d/m/Y, H:i:s').'] '.$data);
                fclose($fh);
            }
        }
    }
}