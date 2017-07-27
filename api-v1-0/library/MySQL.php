<?php

////// ORIGINAL

class DBStatement
{
    private $_result;
    private $_row;

    function __construct($result)
    {
        $this->_result = $result;
    }

    /** fetch 1 row
     *
     * @return array|bool
     */
    public function fetch()
    {
        if ($this->_result)
        {
            return $this->_row = mysql_fetch_assoc($this->_result);
        }
        return false;
    }

    /** fetch 1 row as object
     *
     * @return object|bool
     */
    public function fetchObj()
    {
        if ($this->_result)
        {
            return $this->_row = mysql_fetch_object($this->_result);
        }
        return false;
    }

    /** fetch all data
     *
     * @return array|bool
     */
    public function fetchAll()
    {
        if ($this->_result)
        {
            $allData = [];
            while ($row = mysqli_fetch_assoc($this->_result))
            {
                $allData[] = $row;
            }

            return $allData;
        }
        return false;
    }

    /** get fetched row
     *
     * @return array|bool
     */
    public function row()
    {
        if ($this->_result)
        {
            $this->_row = mysql_fetch_assoc($this->_result);
            return $this->_row;
        }
        return false;
    }

    /** get field value by name
     *
     * @param $field
     * @return bool
     */
    public function f($field)
    {
        if ($this->_result)
        {
            $this->_row = mysql_fetch_assoc($this->_result);

            return $this->_row[$field];
        }
        return false;
    }

    /** get fetched num rows
     * @return int
     */
    public function num()
    {
        if ($this->_result)
        {
            return mysql_num_rows($this->_result);
        }
        return 0;
    }

    /** returns last insert ID
     *
     * @return int
     */
    public function lastInsertID()
    {
        return mysql_insert_id();
    }

    /** returns num affected rows
     *
     * @return int
     */
    public function numAffectedRows()
    {
        if ($this->_result)
        {
            return mysql_affected_rows($this->_result);
        }
        return 0;
    }

    public function getResult()
    {
        return $this->_result;
    }
}

class Mysql
{
    private $_database;
    private $_linkIdentifier;
    private $_params = array();

    static private $_connections = array();
    static private $_debug = false;

    private function _connect()
    {
        if (!empty($this->_connections[$this->_params['key']]))
        {
            $this->_linkIdentifier = $this->_connections[$this->_params['key']];
        }
        else
        {
            @$this->_connections[$this->_params['key']] = $this->_linkIdentifier = mysql_pconnect($this->_params['host'], $this->_params['user'], $this->_params['pass']);
        }

        if (!$this->_linkIdentifier)
        {
            die ("Could not connect to host <b>\"".$this->_params['host']."\"</b> user <b>\"".$this->_params['user']."\"</b> ".mysql_error()."<br />\n");
        }

        $result = mysql_query('set names utf8', $this->_linkIdentifier);
        if($result === false)
        {
            mysql_close($this->_linkIdentifier);
            @$this->_connections[$this->_params['key']] = $this->_linkIdentifier = mysql_pconnect($this->_params['host'], $this->_params['user'], $this->_params['pass']);
            mysql_query('set names utf8', $this->_linkIdentifier);
        }
        $this->_database = $this->_params['database'];
    }

    function __construct($host, $user, $pass, $database)
    {
        $this->_params = array(
            'host' => $host,
            'user' => $user,
            'pass' => $pass,
            'database' => $database,
            'key' => $host.$user,
        );

        $this->_connect();
    }

    /**
     * ====== PRIVATE FUNCTIONS ======
     */

    /** Performs a 'mysql_real_escape_string' on the entire array/string
     *
     * @param $data
     * @param array $types
     * @return array
     */
    private function secureData($data, $types)
    {
        if(is_array($data))
        {
            $i = 0;
            foreach($data as $key => $val)
            {
                if(!is_array($data[$key]))
                {
                    if (is_array($types) && isset($types[$i]) && $this->verifyData($data[$key], $types[$i]))
                    {
                        $data[$key] = mysql_real_escape_string($data[$key], $this->_linkIdentifier);
                    }
                    else
                    {
                        $data[$key] = '';
                    }
                    $i++;
                }
            }
        }
        else
        {
            $data = $this->verifyData($data, $types);
            $data = mysql_real_escape_string($data, $this->_linkIdentifier);
        }
        return $data;
    }


    /** verify the variable type: none, str, int, float, bool, datetime, ts2dt (given timestamp convert to mysql datetime), hexcolor, email
     *
     * @param $data
     * @param string $type
     * @return bool
     */
    private function verifyData($data, $type = '')
    {
        switch($type)
        {
            case 'none':
                break;
            case 'str':
                $data = settype( $data, 'string');
                break;
            case 'int':
                $data = settype( $data, 'integer');
                break;
            case 'float':
                $data = settype( $data, 'float');
                break;
            case 'bool':
                $data = settype( $data, 'boolean');
                break;
            // Y-m-d H:i:s
            // 2014-01-01 12:30:30
            case 'datetime':
                $data = trim( $data );
                $data = preg_replace('/[^\d\-: ]/i', '', $data);
                preg_match( '/^([\d]{4}-[\d]{2}-[\d]{2} [\d]{2}:[\d]{2}:[\d]{2})$/', $data, $matches );
                $data = $matches[1];
                break;
            case 'ts2dt':
                $data = settype( $data, 'integer');
                $data = date('Y-m-d H:i:s', $data);
                break;
            case 'hexcolor':
                preg_match( '/(#[0-9abcdef]{6})/i', $data, $matches );
                $data = $matches[1];
                break;
            case 'email':
                $data = filter_var($data, FILTER_VALIDATE_EMAIL);
                break;
            default:
                $data = '';
                break;
        }

        return $data;
    }

    /**
     * ====== PRIVATE FUNCTIONS ======
     */

    public static function setDebug($debug)
    {
        static::$_debug = $debug;
    }

    /**
     *
     * @param $query
     * @return DBStatement
     */
    public function query($query)
    {
        mysql_select_db($this->_database, $this->_linkIdentifier);
        if (static::$_debug)
        {
            echo $query . "<br />\n";
        }
        $result = mysql_query($query, $this->_linkIdentifier);
        if($result === false)
        {
            mysql_close($this->_linkIdentifier);
            @$this->_connections[$this->_params['key']] = $this->_linkIdentifier = mysql_pconnect($this->_params['host'], $this->_params['user'], $this->_params['pass']);
            mysql_select_db($this->_params['database'], $this->_linkIdentifier);

            $result = mysql_query($query, $this->_linkIdentifier);

            if($result === false)
            {
                die("Query:<i> ".$query."</i> ".mysql_error($this->_linkIdentifier));
            }
        }
        return new DBStatement($result);
    }

    /** Gets a single row from $from where $where is true
     *
     * @param $from
     * @param string $cols
     * @param array $where
     * @param string $orderBy
     * @param string $limit
     * @param bool $like
     * @param string $operand
     * @param array $wheretypes
     * @return DBStatement
     */
    public function select($from, $cols = '*', $where = [], $wheretypes = [], $orderBy = '', $limit = '', $like = false, $operand = 'AND')
    {
        $query = "SELECT {$cols} FROM `{$from}` WHERE ";

        if(is_array($where) && !empty($where))
        {
            // Prepare Variables
            $where = $this->secureData($where, $wheretypes);

            foreach($where as $key => $value)
            {
                if($like)
                {
                    $query .= "`{$key}` LIKE '%{$value}%' {$operand} ";
                }
                else
                {
                    $query .= "`{$key}` = '{$value}' {$operand} ";
                }
            }

            $query = substr($query, 0, -(strlen($operand)+2));

        }
        else
        {
            $query = substr($query, 0, -6);
        }

        if($orderBy != '')
        {
            $query .= ' ORDER BY ' . $orderBy;
        }

        if($limit != '')
        {
            $query .= ' LIMIT ' . $limit;
        }

        return $this->query($query);
    }

    /** Updates a record in the database based on WHERE
     *
     * @param $table
     * @param array $set
     * @param array $where
     * @param array $datatypes
     * @param array $wheretypes
     * @param string $exclude
     * @return bool
     */
    public function update($table, $set = [], $where = [], $datatypes = [], $wheretypes = [], $exclude = '')
    {
        $set 	= $this->secureData($set, $datatypes);
        $where 	= $this->secureData($where, $wheretypes);

        // SET

        $query = "UPDATE `{$table}` SET ";

        foreach($set as $key => $value)
        {
//            if(in_array($key, $exclude))
//            {
//                continue;
//            }

            $query .= "`{$key}` = '{$value}', ";
        }

        $query = substr($query, 0, -2);

        // WHERE

        $query .= ' WHERE ';

        foreach($where as $key=>$value)
        {
            $query .= "`{$key}` = '{$value}' AND ";
        }

        $query = substr($query, 0, -5);

        return $this->query($query);
    }

    /** Adds a record to the database based on the array key names
     *
     * @param $table
     * @param array $vars
     * @param array $datatypes
     * @param string $exclude
     * @return DBStatement
     */
    public function insert($table, $vars = [], $datatypes = [], $exclude = [])
    {
        // Prepare Variables
        $vars = $this->secureData($vars, $datatypes);

        $query = "INSERT INTO `{$table}` SET ";

        foreach($vars as $key => $value)
        {
            if(in_array($key, $exclude))
            {
                continue;
            }
            $query .= "`{$key}` = '{$value}', ";
        }

        $query = trim($query, ', ');

        return $this->query($query);
    }

    /** Deletes a record from the database
     *
     * @param $table
     * @param array $where
     * @param string $limit
     * @param bool $like
     * @param array $wheretypes
     * @return mixed
     */
    public function delete($table, $where = [], $wheretypes = [], $limit = '', $like = false)
    {
        $query = "DELETE FROM `{$table}` WHERE ";
        if(is_array($where))
        {
            // Prepare Variables
            $where = $this->secureData($where, $wheretypes);

            foreach($where as $key=>$value){
                if($like){
                    $query .= "`{$key}` LIKE '%{$value}%' AND ";
                }else{
                    $query .= "`{$key}` = '{$value}' AND ";
                }
            }

            $query = substr($query, 0, -5);
        }

        if($limit != ''){
            $query .= ' LIMIT ' . $limit;
        }

        return $this->query($query);
    }
}
