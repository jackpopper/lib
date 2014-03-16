<?php
class DB {
    const sql_debug = false; // SQL debug flag
    private $table;
    private $link;
    private $result;

    public function __construct() {
        $this->link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
        if (!$this->link) {
            echo '[ERROR] Not connected : '.mysql_error()."\n";
            exit(1);
        }
        $this->query("SET NAMES utf8");
    }

    public function __destruct() {
        mysql_close($this->link);
    }

    public function selectDb($database) {
        $db_selected = mysql_select_db($database, $this->link);
        if (!$db_selected) {
            echo '[ERROR] Can\'t use db : '.mysql_error()."\n";
            exit(1);
        }
    }

    public function setTable($table) {
        $this->table = $table;
    }

    public function query($query) {
        if (self::sql_debug) {
            echo $query."\n";
        } else {
            $this->result = mysql_query($query);
            if (!$this->result) {
                echo '[ERROR] '.mysql_error()."\n";
                echo '[ERROR] Invalid query : '.$query."\n";
                exit(1);
            }
        }
    }

    public function truncate($table) {
        $this->query('TRUNCATE TABLE '.$table);
    }

    public function selectQuery($params = array()) {
        $query_ary = array('SELECT');
        if (empty($params['select'])) {
            $query_ary[] = '*';
        } else {
            $query_ary[] = $params['select'];
        }
        if (!empty($params['table'])) {
            $query_ary[] = 'FROM '.$params['table'];
        } elseif (!empty($this->table)) {
            $query_ary[] = 'FROM '.$this->table;
        } else {
            echo '[ERROR] Undefined table name : '.mysql_error()."\n";
            exit(1);
        }
        if (!empty($params['where'])) {
            $query_ary[] = 'WHERE '.$params['where'];
        }
        if (!empty($params['order'])) {
            $query_ary[] = 'ORDER BY '.$params['order'];
        }
        if (!empty($params['group'])) {
            $query_ary[] = 'GROUP BY '.$params['group'];
        }
        if (!empty($params['limit'])) {
            $query_ary[] = 'LIMIT '.$params['limit'];
        }

        $this->query(implode(' ', $query_ary));
    }

    public function insertQuery($params = array()) {
        $query_ary = array();
        if (!empty($params['table'])) {
            $query_ary[] = 'INSERT INTO '.$params['table'];
        } elseif (!empty($this->table)) {
            $query_ary[] = 'INSERT INTO '.$this->table;
        } else {
            echo '[ERROR] Undefined table name : '.mysql_error()."\n";
            exit(1);
        }
        if (empty($params['value'])) {
            echo '[ERROR] Not found values : '.mysql_error()."\n";
            exit(1);
        } else {
            $column_ary = array();
            $value_ary = array();
            foreach ($params['value'] as $key => $val) {
                if (is_int($key)) {
                    $value_ary[] = $val;
                } else {
                    $column_ary[] = $key;
                    $value_ary[] = $val;
                }
            }
            if (!empty($column_ary)) {
                $query_ary[] = '('.implode(',', $column_ary).')';
            } 
            $query_ary[] = 'VALUES';
            $query_ary[] = '('.implode(',', $value_ary).')';
        }

        $this->query(implode(' ', $query_ary));
    }

    public function updateQuery($params = array()) {
        $query_ary = array();
        if (!empty($params['table'])) {
            $query_ary[] = 'UPDATE '.$params['table'].' SET';
        } elseif (!empty($this->table)) {
            $query_ary[] = 'UPDATE '.$this->table.' SET';
        } else {
            echo '[ERROR] Undefined table name : '.mysql_error()."\n";
            exit(1);
        }
        if (empty($params['value'])) {
            echo '[ERROR] Not found values : '.mysql_error()."\n";
            exit(1);
        } else {
            $set_ary = array();
            foreach ($params['value'] as $key => $val) {
                $set_ary[] = $key.'='.$val;
            }
            $query_ary[] = implode(',', $set_ary);
        }
        if (!empty($params['where'])) {
            $query_ary[] = 'WHERE '.$params['where'];
        }

        $this->query(implode(' ', $query_ary));
    }

    public function replaceQuery($params = array()) {
        $query_ary = array();
        if (!empty($params['table'])) {
            $query_ary[] = 'REPLACE INTO '.$params['table'];
        } elseif (!empty($this->table)) {
            $query_ary[] = 'REPLACE INTO '.$this->table;
        } else {
            echo '[ERROR] Undefined table name : '.mysql_error()."\n";
            exit(1);
        }
        if (empty($params['value'])) {
            echo '[ERROR] Not found values : '.mysql_error()."\n";
            exit(1);
        } else {
            $column_ary = array();
            $value_ary = array();
            foreach ($params['value'] as $key => $val) {
                if (is_int($key)) {
                    $value_ary[] = $val;
                } else {
                    $column_ary[] = $key;
                    $value_ary[] = $val;
                }
            }
            if (!empty($column_ary)) {
                $query_ary[] = '('.implode(',', $column_ary).')';
            } 
            $query_ary[] = 'VALUES';
            $query_ary[] = '('.implode(',', $value_ary).')';
        }

        $this->query(implode(' ', $query_ary));
    }

    public function deleteQuery($params = array()) {
        $query_ary = array();
        if (!empty($params['table'])) {
            $query_ary[] = 'DELETE FROM '.$params['table'];
        } elseif (!empty($this->table)) {
            $query_ary[] = 'DELETE FROM '.$this->table;
        } else {
            echo '[ERROR] Undefined table name : '.mysql_error()."\n";
            exit(1);
        }
        if (empty($params['where'])) {
            echo '[ERROR] Not found values : '.mysql_error()."\n";
            exit(1);
        } else {
            $query_ary[] = 'WHERE '.$params['where'];
        }

        $this->query(implode(' ', $query_ary));
    }

    public function numRow() {
        $row_num = mysql_num_rows($this->result);
        return $row_num;
    }

    public function fetchRow() {
        $row = mysql_fetch_row($this->result);
        return $row;
    }

    public function fetchAssoc() {
        $row = mysql_fetch_assoc($this->result);
        return $row;
    }

    public function getVarcharQueryStr($str) {
//        return "'".addslashes($str)."'";
        return "'".mysql_real_escape_string($str)."'";
    }
}
