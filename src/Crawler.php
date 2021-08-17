<?php

namespace App;

use Closure;
use Exception;
use App\Traits\HasLogging;
use App\Traits\TracksVisits;
use Spatie\Browsershot\Browsershot;

/**
 * Class Crawler
 */
class Crawler
{
    use HasLogging,
        TracksVisits;

    /**
     * The max links to traverse.
     */
    const MAX_LINKS = 50000;


    /**
     * The root URL to crawl from.
     *
     * @var string
     */
    protected $root = '';


    /**
     * Indicates whether or not the traversal can only go outward.
     *
     * @var bool
     */
    protected $inward = true;


    /**
     * Indicates whether or not to wait on script execution.
     *
     * @var bool
     */
    protected $javascript = true;


    /**
     * The action to execute per page.
     *
     * @var Closure
     */
    protected $callback = null;


    /**
     * Crawler constructor.
     *
     * @param string $root
     * @param null $callback
     */
    public function __construct($root = '', $callback = null)
    {
        $this->setRoot($root)
            ->setCallback($callback);
    }


    /**
     * Runs the crawler.
     */
    public function crawl()
    {
        // Verify that the root has not already been run.
        if (self::hasVisited($this->getRoot()) || count(self::$visited) > self::MAX_LINKS) {
            return;
        }

        self::addVisit($this->getRoot());

        // Parse the page contents.
        $this->log("[GET] {$this->getRoot()}");

        $body = $this->getJavascript() ? Browsershot::url($this->getRoot())
            ->waitUntilNetworkIdle()
            ->bodyHtml() : file_get_contents($this->getRoot());

        $html = str_get_html($body);

        // Verify that the page was parsed correctly.
        if(!$html){
            return;
        }

        // Parse the URL segments.
        $info = parse_url($this->getRoot());
        $root = "{$info['scheme']}://{$info['host']}";
        $callback = $this->getCallback();

        // Run the callback if applicable.
        if ($callback) {
            try{
                $callback($this->getRoot(), $html);
            }
            catch(Exception $e){
                $this->log('[ERROR] An unexpected error occurred while executing the defined operation.');
                $this->log($e->getMessage());
            }
        }

        // Find all links.
        foreach ($html->find('a') as $element) {
            $href = trim(
                urldecode(
                    str_replace($this->getRoot(), '',
                        explode('?', $element->href)[0]
                    )
                )
            );

            // Verify that the link is able to be parsed.
            if (!$href || in_array($href, ['#', 'javascript:void(0);'])) {
                continue;
            }

            // Append the root if we're using a relative path.
            if (substr($href, 0, 1) == '/') {
                $href = "{$root}{$href}";
            }

            // Remove the trailing slash if we have one.
            if(strlen($href) > 1 && substr($href, -1) == '/'){
                $href = substr_replace($href, "", -1);
            }

            // Verify that we're within the website.
            if (strpos($href, $root) === false) {
                continue;
            }

            // Handle inward blocking.
            if(!$this->getInward() && strpos($href, $this->getRoot())){
                $this->log("[SKIP] Inward traversal is disabled. ($href)");
                continue;
            }

            // Run against the discovered link.
            (new Crawler($href, $callback))
                ->setInward($this->getInward())
                ->setJavascript($this->getJavascript())
                ->crawl();
        }
    }


    /**
     * Enables / disables inward traversal.
     *
     * @param bool $inward
     * @return $this
     */
    public function setInward($inward = true){
        $this->inward = $inward;

        return $this;
    }


    /**
     * Indicates whether or not to traverse inward based on URL segments.
     *
     * @return bool
     */
    public function getInward(){
        return $this->inward;
    }


    /**
     * Enables / disables Javascript execution.
     *
     * @param bool $javascript
     * @return $this
     */
    public function setJavascript($javascript = true){
        $this->javascript = $javascript;

        return $this;
    }


    /**
     * Indicates whether or not Javascript is enabled.
     *
     * @return bool
     */
    public function getJavascript(){
        return $this->javascript;
    }


    /**
     * Assigns the root URL.
     *
     * @param string $root
     * @return $this
     */
    public function setRoot($root = '')
    {
        $this->root = $root;

        return $this;
    }


    /**
     * Returns the root URL.
     *
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }


    /**
     * Assigns the page callback.
     *
     * @param Closure|null $callback
     * @return $this
     */
    public function setCallback(Closure $callback = null)
    {
        $this->callback = $callback;

        return $this;
    }


    /**
     * Returns the page callback.
     *
     * @return Closure
     */
    public function getCallback()
    {
        return $this->callback;
    }
}