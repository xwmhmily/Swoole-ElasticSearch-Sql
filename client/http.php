<?php

$url = 'http://127.0.0.1:8888/search?keyword=美邦&tags=时尚,格子&price=1,12550&sort=4';

$retval = file_get_contents($url);

print_r($retval);

echo PHP_EOL.'__DONE__';