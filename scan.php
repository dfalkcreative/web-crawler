<?php

use App\Crawler;

set_time_limit(0);
ini_set('memory_limit', '2048M');

require 'vendor/autoload.php';

// Instantiate the crawler instance, referencing your root address.
$crawler = new Crawler('https://www.example.com/',
    function ($source, simple_html_dom $html) {
        // This callback will be executed for every page, so any additional logic or
        // post-processing can be provided here.
    }
);

// Configure the crawler and begin traversal.
$crawler->setInward(true)
    ->setJavascript(false)
    ->crawl();

exit();