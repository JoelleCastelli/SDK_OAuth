<?php

class Autoload {

    public static function register() {
        spl_autoload_register(function ($class){
            $class = str_replace('\\', '/', $class);
            $class .= '.php';
           if(file($class)) {
               include $class;
           }
        });

    }

}


