# Web Crawler

A generic web crawler callback system for data scraping, analysis, and more.

```php
use App\Crawler;

// Instantiate the crawler instance, referencing your target address as the first parameter.
$crawler = new Crawler('https://www.example.com/',
    function ($source, simple_html_dom $html) {
        // 
        // This callback will be executed for every page, so any additional logic or
        // post-processing can be provided here.
        // 
        // @see https://simplehtmldom.sourceforge.io/
        // 
    }
);

// Configure the crawler to only ever move inward / outward, and to enable / disable JS.
$crawler->setInward(true)
    ->setJavascript(false)
    ->crawl();
```
