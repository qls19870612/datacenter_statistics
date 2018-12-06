<?php
namespace common\ParallelCurl;

use \Thread;

class AsyncOperation extends Thread {
    public $url;
    public $storage;
    public $complete;
    public $sid;
    public $data;

    public function __construct($url, $storage, $sid, $data = null) {
        $this->url = $url;
        $this->storage = $storage;
        $this->sid = $sid;
        $this->complete = false;
        $this->data = $data;
    }

    public function run() {
        $data = $this->getByCurl();
        if (empty($data)) {
            echo 'FAILED ' . $this->url . PHP_EOL;
        } else {
            $this->storage[$this->sid] = $data;
            echo 'FINISHED ' . $this->url . PHP_EOL;
        }
        $this->complete = true;
    }

    public function getByCurl() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        if ($this->data!==null) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));
        }
//        $times = 0;
//        do {
//            $times++;
//            $content = curl_exec($ch);
//        } while (empty($content) && $times < 3);//尝试2次均失败则放弃
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    public function isComplete() {
        return $this->complete === true;
    }
}