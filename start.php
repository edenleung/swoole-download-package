<?php

use App\Scraper;
use Swlib\Saber;

require_once './vendor/autoload.php';

$saber = Saber::create([
    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3824.6 Safari/537.36',
    'use_pool' => true
]);

$scraper = new Scraper($saber, __DIR__ . '/packages');

$scraper->scrape([
    'xiaodit/think-permission',
])->run();
