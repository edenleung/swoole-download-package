<?php
namespace App;

use Swlib\Saber;
use App\Dom;

require_once '../vendor/autoload.php';

class Scraper
{
    private $saber;

    public function __construct()
    {
        $this->saber = Saber::create([
            'base_uri' => 'https://github.com/',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3824.6 Safari/537.36',
            'use_pool' => true
        ]);
    }

    public function fetchPagination(string $package, string $lastVersion = '')
    {
        $url = $package . '/tags?after=' . $lastVersion;
        try {
            echo "=============================================\n";
            echo "开始拉取:{$url}\n";
            echo "=============================================\n";
            $html = $this->saber->get($url, ['max_co' => 5, 'timeout' => 500]);
            

            $dom = new Dom((string)$html);

            if (true === $dom->hasPackage()) {
                $lastVersion = $dom->getLastVersion();
                $data = $dom->getVersionUrls();
                
                foreach($data as $item) {
                    foreach($item['urls'] as $url) {
                        $this->publish($item['version'], $url);
                    }
                }
                
                $this->fetchPagination($package, $lastVersion);
                
            } else {
                echo "拉取完成\n";
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 发布下载任务
     *
     * @param string $version
     * @param array $urls
     * @return void
     */
    protected function publish(string $version, string $url)
    {
        $msg = json_encode(['version' => $version, 'url' => $url]);

        $client = new \Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $client->on("connect", function($cli) use ($version, $msg) {
            echo '发布下载任务-'. $version . "\n";
            $cli->send($msg);
        });

        $client->on("receive", function($cli) {
        });

        $client->on("error", function($cli) {
        });

        $client->on("close", function($cli) {
        });
        
        $client->connect('0.0.0.0', 9502);
    }
}
