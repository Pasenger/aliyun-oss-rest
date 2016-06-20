<?php

/**
 * Created by PhpStorm.
 * User: pasenger
 * Date: 16/6/15
 * Time: 下午6:56
 */
class CommonUtils
{
    /**
     * 将数组结果转换为jsonp callback字符串
     * @param $callback
     * @param $result
     * @return string
     */
    public static function convertJsonpCallback($callback, $result){
        if($callback){
            return $callback . '(' . json_encode($result) . ')';
        }

        return json_encode($result);
    }

   
}