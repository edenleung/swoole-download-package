<?php
namespace App;

use Symfony\Component\DomCrawler\Crawler;
use App\Exceptions\NotFoundPackage;

class Dom
{
    protected $html;

    public function __construct(string $html)
    {
        $this->crawler = new Crawler((string)$html);
    }

    /**
     * 是否存在包
     *
     * @return boolean
     */
    public function hasPackage()
    {
        return count($this->crawler->filter('.blankslate')) ? false : true; 
    }

    /**
     * 是否分页
     *
     * @return boolean
     */
    public function hasPagination()
    {
        try {
            $this->crawler->filter('.pagination a')->text();
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    /**
     * 获取版本号与下载链接
     *
     * @return void
     */
    public function getVersionUrls()
    {
        $data = [];
        $this->crawler->filter('.Box-row')->each(function (Crawler $node, $i) use(&$data) {
            $data[] = [
                'version' => $this->getVersion($node),
                'urls' => $this->getDownloadLink($node),
            ];
        });

        return $data;
    }

    /**
     * 获取版本号
     *
     * @param Crawler $node
     * @return void
     */
    protected function getVersion(Crawler $node)
    {
        return trim($node->filter('.commit .d-flex h4 a')->text());
    }

    /**
     * 获取下载链接
     *
     * @param Crawler $node
     * @return void
     */
    protected function getDownloadLink(Crawler $node)
    {
        $urls = [
            'zip' => $node->filter('ul.list-style-none li:nth-child(3) a')->attr('href'),
            'tar.gz' => $node->filter('ul.list-style-none li:nth-child(4) a')->attr('href')
        ];

        return $urls;
    }

    /**
     * 获取最后一个版本
     *
     * @return void
     */
    public function getLastVersion()
    {
        $version = $this->crawler->filter('.Box-row:last-child .d-flex a')->text();

        return trim($version);
    }

}