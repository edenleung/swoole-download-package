<?php
namespace App;

use App\Scraper;
use Swlib\Saber;

require_once '../vendor/autoload.php';

/**
 * 下载服务
 *
 */
class Download
{
    protected $server;

    protected $saber;

    public function __construct()
    {
        $this->saber = Saber::create([
            'base_uri' => 'https://codeload.github.com',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3824.6 Safari/537.36',
            'use_pool' => true
        ]);

        $this->createSwooleServer();
    }

    protected function createSwooleServer()
    {
        $this->server = new \Swoole\Server('0.0.0.0', 9502);
        $this->server->on("Receive", [$this, 'onReceive']);
    }

    public function onReceive($server, $fd, $reactor_id, $data)
    {
        $data = json_decode($data, true);
        $version = $data['version'];
        $url = $data['url'];
        $ext = $data['ext'];

        $this->download($version, $url, $ext);
    }

    /**
     * 下载、保存包
     *
     * @return void
     */
    protected function download(string $version, string $url, string $ext)
    {
        go(function () use ($version, $url, $ext) {
            list($tmp, $user, $package, $archive, $file) = explode('/', $url);
            $dir = "./packages/{$user}/{$package}";

            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $savePath = $dir . '/' . $file;
            try {
                $response = $this->saber->download(
                    '/' . $user.'/'. $package .'/'. $ext .'/' . $version,
                    $savePath
                );

                if ($response->success) {
                    echo "下载完成\n";
                }
            } catch (\Exception $e) {
                $this->download($version, $url, $ext);
            }
        });
    }

    public function run()
    {
        $this->server->start();
    }
}

$server = new Download();

$server->run();
