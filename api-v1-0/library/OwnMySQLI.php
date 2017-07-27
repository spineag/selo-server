<?php

class DBStatementI
{
    private $_result;
    private $_row;

    function __construct($result)
    {
        $this->_result = $result;
    }

    public function fetch()
    {
        if ($this->_result)
        {
            return $this->_row = mysqli_fetch_assoc($this->_result);
        }
        return false;
    }

    public function fetchObj()
    {
        if ($this->_result)
        {
            return $this->_row = mysqli_fetch_object($this->_result);
        }
        return false;
    }

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

    public function row()
    {
        if ($this->_result)
        {
            $this->_row = mysqli_fetch_assoc($this->_result);
            return $this->_row;
        }
        return false;
    }

    public function f($field) // Извлекает результирующий ряд в виде ассоциативного массива
    {
        if ($this->_result)
        {
            $this->_row = mysqli_fetch_assoc($this->_result);

            return $this->_row[$field];
        }
        return false;
    }

    public function num() // Получает число рядов в результирующей выборке
    {
        if ($this->_result)
        {
            return mysqli_num_rows($this->_result);
        }
        return 0;
    }

    public function numAffectedRows() // Получает число строк, затронутых предыдущей операцией MySQL
    {
        if ($this->_result)
        {
            return mysqli_affected_rows($this->_result);
        }
        return 0;
    }

    public function getResult()
    {
        return $this->_result;
    }
}

class OwnMySQLI
{
    private $_database;
    private $_linkIdentifier;
    private $_params = array();

    static private $_connections = array();
    static private $_debug = false;

    private function _connect()
    {
        if (!empty(self::$_connections[$this->_params['key']])) {
            $this->_linkIdentifier = self::$_connections[$this->_params['key']];
        } else {
            self::$_connections[$this->_params['key']] = $this->_linkIdentifier = mysqli_connect("p:".$this->_params['host'], $this->_params['user'], $this->_params['pass']);
        }

        if (!$this->_linkIdentifier) {
            die ("Could not connect to host <b>\"".$this->_params['host']."\"</b> user <b>\"".$this->_params['user']."\"</b> ".mysqli_connect_error()."<br />\n");
        }

        $result = mysqli_query($this->_linkIdentifier, 'set names utf8');
        if ($result === false) {
            mysqli_close($this->_linkIdentifier);
            self::$_connections[$this->_params['key']] = $this->_linkIdentifier = mysqli_connect("p:".$this->_params['host'], $this->_params['user'], $this->_params['pass']);
            mysqli_query($this->_linkIdentifier, 'set names utf8');
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
            'key' => $host . $user,
        );

        $this->_connect();
    }

    /**
     * ====== PRIVATE FUNCTIONS ======
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
                        $data[$key] = mysqli_real_escape_string($this->_linkIdentifier, $data[$key]);
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
            $data = mysql_real_escape_string($this->_linkIdentifier, $data);
        }
        return $data;
    }

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

    public function getDatabaseName() { // use for shards
        return $this->_params['database'];
    }

    public function query($query)
    {
        mysqli_select_db($this->_linkIdentifier, $this->_database);
        if (static::$_debug) {
            echo $query . "<br />\n";
        }
        $result = mysqli_query($this->_linkIdentifier, $query);
        if ($result === false) {
            mysqli_close($this->_linkIdentifier);
            self::$_connections[$this->_params['key']] = $this->_linkIdentifier = mysqli_connect("p:".$this->_params['host'], $this->_params['user'], $this->_params['pass']);
            mysqli_select_db($this->_linkIdentifier, $this->_params['database']);

            $result = mysqli_query($this->_linkIdentifier, $query);

            if ($result === false) {
                die("Query:<i> ".$query."</i> ".mysqli_error($this->_linkIdentifier));
            }
        }
        return new DBStatementI($result);
    }

    public function insertId()
    {
        return mysqli_insert_id($this->_linkIdentifier);
    }

    /**
     * Executes query and Return new DBStatementI or int if this is INSERT statement
     *
     * @param $query
     * @return DBStatementI|int
     */
    public function queryLastId($query)
    {
        mysqli_select_db($this->_linkIdentifier, $this->_database);
        if (static::$_debug) {
            echo $query . "<br />\n";
        }
        $queryResult = mysqli_real_query($this->_linkIdentifier, $query);

        $mysqli_field_count = mysqli_field_count($this->_linkIdentifier);
        if ($mysqli_field_count) {
            $result = mysqli_store_result($this->_linkIdentifier);
        } else {
            $result = $queryResult;
        }

        if ( ($result === true) && (preg_match('/^\s*"?(INSERT|REPLACE)\s+/i', $query)) ) {
            $insertId = (int)$this->insertId();
            return $insertId;
        }

        return new DBStatementI($result);
    }
    
    public function queryWithAnswerId($query)
    {
        mysqli_select_db($this->_linkIdentifier, $this->_database);
        if (static::$_debug)
        {
            echo $query . "<br />\n";
        }
        $result = mysqli_query($this->_linkIdentifier, $query);
        if($result === false)
        {
            mysqli_close($this->_linkIdentifier);
            @$this->_connections[$this->_params['key']] = $this->_linkIdentifier = mysqli_connect("p:".$this->_params['host'], $this->_params['user'], $this->_params['pass']);
            mysqli_select_db($this->_linkIdentifier, $this->_params['database']);

            $result = mysqli_query($this->_linkIdentifier, $query);

            if($result === false)
            {
                die("Query:<i> ".$query."</i> ".mysqli_error($this->_linkIdentifier));
            }
        }
        return [new DBStatementI($result), mysqli_insert_id($this->_linkIdentifier)];
    }

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

    public function update($table, $set = [], $where = [], $datatypes = [], $wheretypes = [], $exclude = '')
    {
        $set 	= $this->secureData($set, $datatypes);
        $where 	= $this->secureData($where, $wheretypes);

        // SET

        $query = "UPDATE `{$table}` SET ";

        foreach($set as $key => $value)
        {
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
    
    public function normalString($s)
    {
        $s = settype( $s, 'string');
        $s = mysqli_real_escape_string($this->_linkIdentifier, $s);
        return $s;
    }
}
