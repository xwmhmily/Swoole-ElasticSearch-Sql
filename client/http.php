<?php

$url = "http://127.0.0.1:8888/search?keyword=%E7%BE%8E%E9%82%A6&tags=%E6%97%B6%E5%B0%9A,%E6%A0%BC%E5%AD%90&price=1,12550&sort=4";

$retval = file_get_contents($url);

print_r($retval);

echo PHP_EOL.'__DONE__';