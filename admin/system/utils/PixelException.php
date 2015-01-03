<?php namespace com\admin\system\utils;

class PixelException extends \Exception{
    function __construct($message = "", $code = 0, Exception $previous = null){
        // Use this for logging later.
        parent::__construct($message, $code, $previous);
        exit;
    }
}