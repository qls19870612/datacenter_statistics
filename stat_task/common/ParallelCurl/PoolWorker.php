<?php
namespace common\ParallelCurl;

use \Worker;

class PoolWorker extends Worker {
    /** @var array */
    public $workers = array();

    public function stack(&$work)
    {
        parent::stack($work);
        $this->workers[count($this->workers)] = $work;
    }

}
