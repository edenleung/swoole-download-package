<?php
namespace App;

use App\Scraper;
use Swlib\SaberGM;

require_once '../vendor/autoload.php';

/**
 * 下载服务
 *
 */
class Download
{
    protected $server;

    public function __construct()
    {
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
                $response = SaberGM::download(
                    'https://codeload.github.com/'. $user.'/'. $package .'/'. $ext .'/' . $version,
                    $savePath
                );

                if ($response->success) {
                    echo "下载完成\n";
                }

            } catch (\Exception $e) {
                $this->download($version, $url);
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
