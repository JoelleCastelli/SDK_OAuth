<?php

class Helpers {

    public static function isJson($string): bool
    {
        json_decode($string, true);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function getTokenFromResponse($result) {
        if(Helpers::isJson($result)) {
            $result = json_decode($result, true);
            $token = $result['access_token'];
        } else {
            $string = explode("&", $result)[0];
            $token = explode("=", $string)[1];
        }
        return $token;
    }

}