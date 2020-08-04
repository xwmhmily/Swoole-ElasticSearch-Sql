<?php

$url = 'http://127.0.0.1:8888/search?keyword=湖人&price=50,1000&tags=复古,紫金&sort=1&category_id=20&brand_id=8';

$products = file_get_contents($url);

print_r($products);

echo PHP_EOL.'__DONE__';