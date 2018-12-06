<?php
namespace common;
use PDO;
use PDOException;
use PDOStatement;
/**
 *数据库 mysql pdo操作类
 * $config = array('host' => '115.239.231.13', 'user' => 'root', 'password' => 'molin_da12321', 'dbname' => 'mysql', 'port' => 6035);
 * $mysql_pdo = new mysqlpdo($config);
 * var_dump($mysql_pdo->fetchAll("show tables"));
 * var_dump($mysql_pdo->fetchRow("show tables"));
 * var_dump($mysql_pdo->fetchOne("show tables"));
 * Class mysqlpdo
 */
class MysqlPdo
{
    //数据库信息
    private $Host = '';
    private $User = '';
    private $PassWd = '';
    private $DBName = '';
    private $Port = '';

    //数据库句柄
    /**
     * @var PDO $conn
     */
    protected $conn = null;

    /**
     * @var PDOStatement $pdostmt
     */
    protected $pdostmt;
    //数据库连接状态
    public $ERROR = array();
    public $state = false;

    /**
     * 初始化函数
     */
    public function __construct($config)
    {
        $this->Host = $config['host'];
        $this->User = $config['user'];
        $this->PassWd = $config['password'];
        $this->Port = $config['port'];
        $this->connect();
    }

    /**
     * connect
     * 连接数据库
     */
    public function connect()
    {
        try {
            $this->conn = new PDO ('mysql:host=' . $this->Host . ";port=" . $this->Port, $this->User, $this->PassWd);
            $this->conn->exec("SET NAMES  utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->state = true;
        } catch (PDOException $e) {
            $this->errorLog("PDOException：" . $e->getMessage()." [IN] ".__METHOD__);
            exit();
        }
    }

    /**
     * 加判断是否已经连接
     *
     * @return bool
     */
    public function isConnected()
    {
        return (bool)$this->conn;
    }

    public function query($sql)
    {
        $this->sql = $sql;
        try {
            $this->free();
            $this->pdostmt = $this->conn->prepare($sql);
            return $this->pdostmt->execute();
        } catch (PDOException $e) {
            $this->errorLog("pdo不可用：" . $e->getMessage()."[SQL]$sql");
            return array();
        }

    }

    //简化插入insert
    public function insert($table, $data)
    {
        $sql = $this->parseArray("insert", $table, $data);
        $this->query($sql);
    }

    /**
     * 数据更新
     * @param <type> $table 表名
     * @param <type> $data  数据 ，数组类型
     * @param <type> $where  更新条件
     */
    public function update($table, $data, $where = null)
    {
        $sql = $this->parseArray("update", $table, $data, $where);
        $this->query($sql);
    }

    /**
     * fetchRow
     *
     * 获取当前数据集的当前行
     *
     * return Array
     */

    public function fetchRow($sql = '')
    {
        $this->sql = $sql;
        try {
            $this->query($sql);
            return $this->pdostmt->fetch(constant('PDO::FETCH_ASSOC'));
        } catch (PDOException $e) {
            $this->errorLog("pdo不可用：" . $e->getMessage());
            return array();
        }
    }


    /**
     * 查询多条数据
     * @param <type> $sql
     * @return <type>
     */
    public function fetchAll($sql)
    {
        $this->sql = $sql;
        try {
            $this->query($sql);
            return $this->pdostmt->fetchAll(constant('PDO::FETCH_ASSOC'));
        } catch (PDOException $e) {
            $this->errorLog("pdo不可用：" . $e->getMessage());
            return array();
        }
    }

    /**
     * fetchOne
     *
     * 获取当前数据集内的所有第一行的第一列数据
     *
     * return Array
     */

    public function fetchOne($sql = '')
    {
        $this->sql = $sql;
        try {
            $this->query($sql);
            return $this->pdostmt->fetchColumn();
        } catch (PDOException $e) {
            $this->errorLog("pdo不可用：" . $e->getMessage());
            return array();
        }
    }

    /**
     * getInsertID
     * 获取插入的数据的ID
     * return int
     */
    public function getInsertID()
    {
        return $this->conn->lastInsertId();
    }


    public function free()
    {
        if (!empty ($this->pdostmt)) {
            $this->pdostmt = null;
        }

        if (!empty ($this->prepare_pdostmt)) {
            $this->prepare_pdostmt = null;
        }
    }

    public function __destruct()
    {
        $this->free();
        if (!empty($this->conn)) {
            $this->conn = null;
        }
    }


    public function parseArray($status, $table, $data, $where = null)
    {
        $columnName = '';
        $value = '';
        $updateData = '';
        if (is_array($data)) {
            if ($status == 'insert') {
                foreach ($data as $key => $val) {
                    $columnName .= '`' . $key . '`' . ",";
                    $value .= $this->conn->quote($val) . ",";
                }
                $columnName = substr($columnName, 0, -1);
                $value = substr($value, 0, -1);
                return "INSERT IGNORE INTO {$table} ($columnName)  VALUES ($value)";
            } else if ($status == 'update') {
                foreach ($data as $key => $val) {
                    $updateData .= ' `' . $key . '`  =' . $this->conn->quote($val) . ",";
                }
                $updateData = substr($updateData, 0, -1);
                return "update  $table set $updateData " . $this->parseWhere($where);
            }
        }
        return "";
    }

    public function parseWhere($where)
    {
        $whereStr = '';
        if (is_string($where) || is_null($where)) {
            $whereStr = $where;
        }
        return empty ($whereStr) ? '' : ' WHERE ' . $whereStr;
    }

    public function  errorLog($error)
    {
        var_dump($error);
        $this->ERROR[] = $error;
    }


    /**
     * @var PDOStatement $prepare_pdostmt
     */
    private $prepare_pdostmt = null;


    /**
     * 开启事务
     */
    public function beginTransaction()
    {
        $this->conn->beginTransaction();
    }

    //提交事务
    public function  commit()
    {
        $this->conn->commit();
    }

    /**
     * 回滚事务
     */
    public function rollback()
    {
        $this->conn->rollback();
    }

    public function  insertfieldprepare($table, array $field_array)
    {
        implode(",", $field_array);
        implode("?,", $field_array);
        $field_name_str = "";
        $field_perch_str = "";
        foreach ($field_array as $field_name) {
            $field_name_str .= "`" . str_replace(':', '', $field_name) . "`,";
            $field_perch_str .= "$field_name,";
        }
        $field_name_str = substr($field_name_str, 0, -1);
        $field_perch_str = substr($field_perch_str, 0, -1);
        try {
            $this->prepare_pdostmt = $this->conn->prepare("INSERT INTO {$table} ( {$field_name_str}  ) VALUES ($field_perch_str )");
        } catch (PDOException $e) {
            $this->errorLog("pdo不可用：" . $e->getMessage());
            return array();
        }
    }

    public function   insertvalueprepare(array  $val_array)
    {
        try {
            $i = 1;
            foreach ($val_array as $key => $val) {
                $this->prepare_pdostmt ->bindValue(":{$key}", $val );
                $i++;
            }
            $this->prepare_pdostmt->execute();
        } catch (PDOException $e) {
            $this->errorLog("pdo不可用：" . $e->getMessage());
            return array();
        }
    }
}