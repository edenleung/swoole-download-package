<?php
namespace App;

use App\Scraper;
use Swlib\Saber;

require_once '../vendor/autoload.php';

class App
{
    protected $scraper;

    protected $packages;

    public function __construct(Scraper $scraper)
    {
        $this->scraper = $scraper;
    }

    public function scrape(array $packages)
    {
        $this->packages = $packages;

        return $this;
    }

    public function run()
    {
        foreach ($this->packages as $package) {
            go(function () use ($package) {
                $this->scraper->fetchPagination($package);
            });
        }
    }
}

$scraper = new Scraper;
$app = new App($scraper);
$app->scrape([
    'xiaodit/think-permission'
])->run();
