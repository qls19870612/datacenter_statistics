<?php
namespace common;

use \Exception;

/**
 * Presto Client
 */
class PrestoClient {

    private $con;

    private $url = "http://115.231.216.82:8080/v1/statement";

    /**
     * @var Exception
     */
    private $exception = null;

    public function __construct($schema) {
        $this->con = new PhpPrestoClient($this->url, 'hive', $schema);
    }

    public function fetchAll($sql) {
        try {
            $this->con->PrestoQuery($sql);
            $this->con->WaitQueryExec();
            return $this->con->GetData();
        } catch (Exception $e) {
            $this->exception = $e;
            return false;
        }
    }

    public function query($sql) {
        try {
            $this->con->PrestoQuery($sql);
            $this->con->WaitQueryExec();
            return true;
        } catch (Exception $e) {
            $this->exception = $e;
            return false;
        }
    }

    public function getDbError() {
        return $this->exception == null ? '(NONE)' : $this->exception->getMessage();
    }
}