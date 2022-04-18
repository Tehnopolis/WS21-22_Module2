<?php

final class Utils {
    // String utils
    public static function str_starts_with(string $haystack, string $needle): string {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
    public static function as_utf8($var, $deep=TRUE){
        if(is_array($var)) {
            foreach($var as $key => $value){
                if($deep){
                    $var[$key] = Utils::as_utf8($value,$deep);
                }elseif(!is_array($value) && !is_object($value) && !mb_detect_encoding($value,'utf-8',true)){
                     $var[$key] = utf8_encode($var);
                }
            }
            return $var;
        }
        elseif(is_object($var)) {
            foreach($var as $key => $value){
                if($deep){
                    $var->$key = Utils::as_utf8($value,$deep);
                }
                elseif(!is_array($value) && !is_object($value) && !mb_detect_encoding($value,'utf-8',true)){
                     $var->$key = utf8_encode($var);
                }
            }
            return $var;
        }
        else {
            return (!mb_detect_encoding($var,'utf-8', true))?utf8_encode($var):$var;
        }
    }

    // Array utils
    public static function array_mutate(array $array, $func_mutator): array {
        $i = 0;
        foreach($array as &$item) {
            $array[$i] = call_user_func($func_mutator, $item);
            $i++;
        }
        return $array;
    }
}