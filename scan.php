<?php

use App\Crawler;

set_time_limit(0);
ini_set('memory_limit', '2048M');

require 'vendor/autoload.php';

// Configure the database.
$_ENV['DB_NAME'] = 'marijuana';
$_ENV['DB_HOST'] = '127.0.0.1';
$_ENV['DB_PORT'] = '3307';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';

// Create the store.
$store = query('stores')
    ->where('name', '=', 'Rise')
    ->first();

if(!$store){
    query('stores')
        ->insert(['name' => 'Rise']);

    $store = query('stores')
        ->where('name', '=', 'Rise')
        ->first();
}

// Beyond / Hello (Irwin) - https://www.iheartjane.com/embed/stores/2935
// Rise (Latrobe) - https://www.iheartjane.com/embed/stores/1549

// Configure the behavior of the crawler.
$crawler = new Crawler('https://www.iheartjane.com/embed/stores/1549', function ($source, simple_html_dom $html) use ($store) {
    if (strpos($source, '/products/') === false) {
        return;
    }

    $title = trim($html->find('h2', 0)->innertext);
    $description = trim($html->find('p', 0)->innertext);
    $attributes = [];

    // Verify that the description is able to be parsed.
    if (strpos($description, 'Caryophyllene:') === false) {
        return;
    }

    // Parse the chemical components.
    array_map(function ($attribute) use (&$attributes) {
        $attribute = trim(str_replace('%', '', urldecode(str_replace("&nbsp;", "", $attribute))));
        $components = explode(':', $attribute);

        if (count($components) == 2) {
            $attributes[trim($components[0])] = trim($components[1]);
        }

        return $attribute;
    }, explode('|', $description));

    $product = query('products')
        ->where('store_id', '=', get($store, 'id'))
        ->where('name', '=', $title)
        ->first();

    // Create the product if it doesn't exist.
    if(!$product){
        query('products')->insert([
            'name' => $title,
            'store_id' => get($store, 'id')
        ]);

        $product = query('products')
            ->where('store_id', '=', get($store, 'id'))
            ->where('name', '=', $title)
            ->first();
    }

    // Create each attribute.
    foreach($attributes as $name => $value){
        $attribute = query('product_attributes')
            ->where('product_id', '=', get($product, 'id'))
            ->where('name', '=', $name)
            ->first();

        if(!$attribute){
            query('product_attributes')
                ->insert([
                    'product_id' => get($product, 'id'),
                    'name' => $name,
                    'value' => $value
                ]);
        }
        else{
            query('product_attributes')
                ->where('id', '=', get($attribute, 'id'))
                ->update([
                    'value' => $value
                ]);
        }
    }
});

// Traverse the site.
$crawler->setInward(false)
    ->setJavascript(true)
    ->crawl();

exit();