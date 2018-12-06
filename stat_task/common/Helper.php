<?php
namespace common;

/**
 * Class Helper
 * @package common
 * 助手类
 */
class Helper {

    /**
     * @var string 临时日志路径
     */
    public static $tmpLogPath = null;

    public static $gameCode = 'game_code';

    /**
     * 记录日志信息
     * 为防止多个进程同时写同一个日志文件,导致日志混乱,先将日志写到临时文件,最后再转移至主要log文件.
     * @param $msg  string  日志信息
     */
    public static function log($msg) {
        if (self::$tmpLogPath == null) {
            self::$tmpLogPath = dirname(__DIR__) . '/log/' . date('Y-m-d') . uniqid('-') . '.log';
        }

        $msg = '[' . date('Y-m-d H:i:s') . ']' . $msg . PHP_EOL;

        if (DEBUG === true) {
            echo $msg;
        }
        file_put_contents(self::$tmpLogPath, $msg, FILE_APPEND);
    }

    /**
     * 将当前进程的临时日志移动到主日志文件
     */
    public static function moveTmpLog() {
        if (self::$tmpLogPath == null || !file_exists(self::$tmpLogPath)) {
            return;
        }

        $mainLogPath = dirname(__DIR__) . '/log/' . self::$gameCode . '_' . date('Y-m-d') . '.log';
        file_put_contents($mainLogPath, file_get_contents(self::$tmpLogPath), FILE_APPEND);
        unlink(self::$tmpLogPath);
    }


    /**
     * 格式化时间间隔为'H:i:s'格式
     * 不足60s,则返回’秒.3位毫秒'格式
     * @param  $seconds float
     * @return string
     */
    static function formatTimeInterval($seconds) {
        $sec = floor($seconds);
        if ($sec < 60) {
            return round($seconds, 3) . 's';
        }

        return date('H:i:s', strtotime('0000-00-00 00:00:00') + $sec);
    }

}