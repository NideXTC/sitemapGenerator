<?php


/**
 * Class SitemapGenerator
 * @author Alexis Ducerf - http://www.alexis-ducerf.fr <alexis.ducerf@gmail.com>
 * @date Feb 13th, 2015
 */

namespace SeoTools;

class SitemapGenerator
{
    private $_url;
    private $_max_links;
    private $_links;
    private $_html;
    private $_actual;
    private $_lastmod;
    private $_changefreq;
    private $_piority = [];

    function __construct($url, $max_links = 50000, $lastmod = null, $changefreq = null)
    {
        if (!(substr($url, -1) == '/')) {
            $this->_url = $url . '/';
        } else {
            $this->_url = $url;
        }

        $this->_lastmod = $lastmod;
        $this->_changefreq = $changefreq;
        $this->_max_links = $max_links;

        $this->link($this->_url);
    }

    public function getLinks()
    {
        $dom = new \domDocument;
        $array = [];

        @$dom->loadHTML($this->_html);
        $links = $dom->getElementsByTagName('a');

        if (!(substr($this->_actual, -1) == '/')) {
            if (is_dir($this->_actual)) {
                $this->_actual .= '/';
            } else if ($this->_actual != $this->_url) {
                $this->_actual = str_replace(basename($this->_actual), '', $this->_actual);
            }
        }

        foreach ($links as $tag) {
            if (strstr($tag->getAttribute('href'), $this->_actual)) {
                $array[] = $tag->getAttribute('href');
            } else if (substr($tag->getAttribute('href'), 0, 1) == '/') {
                $array[] = $this->_url . $tag->getAttribute('href');
            } else if (!preg_match("/^(mailto|http|\#)/", $tag->getAttribute('href'))) {
                if (substr($tag->getAttribute('href'), 0, 2) == './') {
                    $array[] = $this->_actual . substr($tag->getAttribute('href'), 2);
                } else if (substr($tag->getAttribute('href'), 0, 3) == '../') {
                    $pad = substr_count($tag->getAttribute('href'), '../');
                    $baseUrlTemp = $this->_actual;
                    for ($i = 1; $i <= $pad + 1; $i++) {
                        $baseUrlTemp = substr($baseUrlTemp, 0, strrpos($baseUrlTemp, '/'));
                    }
                    $array[] = $baseUrlTemp . '/' . str_replace('../', '', $tag->getAttribute('href'));
                } else {
                    $array[] = $this->_actual . $tag->getAttribute('href');
                }
            }
        }

        return $array;
    }

    public function getImages()
    {
        $dom = new \domDocument;
        $array = [];

        @$dom->loadHTML($this->_html);
        $images = $dom->getElementsByTagName('img');

        foreach ($images as $tag) {
            if (!preg_match('/;base64,/i', $tag->getAttribute('src'))) {
                if (strstr($tag->getAttribute('src'), $this->_url)) {
                    $img = $tag->getAttribute('src');
                    if (!in_array($img, $array)) {
                        $array[] = $img;
                    }
                } else if (substr($tag->getAttribute('src'), 0, 1) == '/') {
                    $img = $this->_url . $tag->getAttribute('href');
                    if (!in_array($img, $array)) {
                        $array[] = $img;
                    }

                } else {
                    if (substr($tag->getAttribute('src'), 0, 2) == './') {
                        $img = $this->_url . substr($tag->getAttribute('src'), 2);
                        if (!in_array($img, $array)) {
                            $array[] = $img;
                        }
                    } else if (substr($tag->getAttribute('src'), 0, 3) == '../') {
                        $pad = substr_count($tag->getAttribute('src'), '../');
                        $baseUrlTemp = $this->_url;
                        for ($i = 1; $i <= $pad + 1; $i++) {
                            $baseUrlTemp = substr($baseUrlTemp, 0, strrpos($baseUrlTemp, '/'));
                        }
                        $img = $baseUrlTemp . '/' . str_replace('../', '', $tag->getAttribute('src'));
                        if (!in_array($img, $array)) {
                            $array[] = $img;
                        }
                    } else {
                        $img = $this->_url . $tag->getAttribute('src');
                        if (!in_array($img, $array)) {
                            $array[] = $img;
                        }
                    }
                }
            }
        }

        return $array;

    }

    public function link($link)
    {
        if (!empty($link) && !isset($this->_links[$link]) && $this->_max_links >= sizeof($this->_links) + 1) {
            $h = get_headers($link, 1);
            $dt = NULL;

            if (!($h || strstr($h[0], '200') === FALSE)) {
                $this->_links[$link]['date'] = $h['Last-Modified'];
            } else if ($this->_lastmod !== null) {
                $this->_links[$link]['date'] = $this->_lastmod;
            } else {
                $this->_links[$link]['date'] = '';
            }

            $this->_links[$link]['images'] = $this->getImages();
            $this->_actual = $link;
            $this->navigate();
        }
    }

    public function generate()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL .
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
  xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" >' . PHP_EOL;

        foreach ($this->_links as $link => $v) {
            $xml .= '    <url>' . PHP_EOL;
            $xml .= '        <loc>' . $link . '</loc>' . PHP_EOL;
            if ($v['date'] != null) {
                $xml .= '        <lastmod>' . $v['date'] . '</lastmod>' . PHP_EOL;
            }

            if ($this->_changefreq != null) {
                $xml .= '        <changefreq>' . $this->_changefreq . '</changefreq>' . PHP_EOL;
            }

            if ($link === $this->_url) {
                $xml .= '       <priority>1</priority>' . PHP_EOL;
            } else {
                $xml .= '        <priority>0.5</priority>' . PHP_EOL;
            }

            foreach ($v['images'] as $image) {
                $xml .= '        <image:image>' . PHP_EOL;
                $xml .= '            <image:loc>' . $image . '</image:loc>' . PHP_EOL;
                $xml .= '        </image:image>' . PHP_EOL;
            }

            $xml .= '    </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        file_put_contents('sitemap.xml', $xml);
    }

    public function compress()
    {
        $file = "sitemap.xml";
        $gzfile = "sitemap.xml.gz";
        $fp = gzopen($gzfile, 'w9');
        gzwrite($fp, file_get_contents($file));
        gzclose($fp);
    }

    public function navigate()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_actual);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $this->_html = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $links = $this->getLinks();
        foreach ($links as $link) {
            $this->link($link);
        }
    }
}
