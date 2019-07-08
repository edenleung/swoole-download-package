<?php
namespace App;

use App\Dom;
use Swlib\Saber;

final class Scraper
{
    private $saber;

    private $package;

    private $savePath;

    const HOST = 'https://github.com';

    const DOWNLOAD = 'https://codeload.github.com';

    public function __construct(Saber $saber, string $savePath)
    {
        $this->saber = $saber;
        $this->savePath = $savePath;
    }

    /**
     * 配置
     *
     * @param array $packages
     * @return void
     */
    public function scrape(array $packages)
    {
        $this->packages = $packages;

        return $this;
    }

    /**
     * 开始运行
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->packages as $package) {
            go(function () use ($package) {
                $this->fetchPagination($package);
            });
        }
    }

    /**
     * 拉取信息
     *
     * @param string $package
     * @param string $lastVersion
     * @return void
     */
    public function fetchPagination(string $package, string $lastVersion = '')
    {
        $url = $package . '/tags?after=' . $lastVersion;
        try {
            echo "=============================================\n";
            echo "开始拉取:{$url}\n";
            echo "=============================================\n";
            $html = $this->saber->get(Scraper::HOST . '/' . $url, ['max_co' => 5, 'timeout' => 500]);
            

            $dom = new Dom((string)$html);

            if (true === $dom->hasPackage()) {
                $lastVersion = $dom->getLastVersion();
                $data = $dom->getVersionUrls();
                
                foreach($data as $item) {
                    foreach($item['urls'] as $ext => $url) {
                        $this->download($item['version'], $url, $ext);
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
     * 下载、保存包
     *
     * @return void
     */
    protected function download(string $version, string $url, string $ext)
    {
        go(function () use ($version, $url, $ext) {
            list($tmp, $user, $package, $archive, $file) = explode('/', $url);
            $dir = $this->savePath . "/{$user}/{$package}";

            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $savePath = $dir . '/' . $file;
            try {
                $response = $this->saber->download(
                    Scraper::DOWNLOAD . '/' . $user.'/'. $package .'/'. $ext .'/' . $version,
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
}
