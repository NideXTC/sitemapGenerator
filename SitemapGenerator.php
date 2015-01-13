<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

/**
 * Class SitemapGenerator
 * @author Alexis Ducerf - http://www.alexis-ducerf.fr <alexis.ducerf@gmail.com>
 * @date Feb 13th, 2015
 */
class SitemapGenerator
{
    private $_url;
    private $_max_links;
    private $_links;
    private $_html;
    private $_actual;

    function __construct($url, $max_links = 50000)
    {
        $this->_url = $url;
        $this->_max_links = $max_links;
        $this->link($url);
    }

    public function getLinks()
    {
        $dom = new domDocument;
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

    }

    public function link($link)
    {
        if (!empty($link) && !isset($this->_links[$link]) && $this->_max_links >= sizeof($this->_links)) {
            $h = get_headers($link, 1);
            $dt = NULL;

            if (!($h || strstr($h[0], '200') === FALSE)) {
                $this->_links[$link] = $h['Last-Modified'];
            } else {
                $this->_links[$link] = '';
            }
            $this->_actual = $link;
            $this->navigate();
        }
    }

    public function generate()
    {
        $xml = '<?xml version="1.0"?>' . PHP_EOL .
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($this->_links as $link => $date) {
            $xml .= '    <url>' . PHP_EOL;
            $xml .= '        <loc>' . $link . '</loc>' . PHP_EOL;
            if($date != null){
                $xml .= '        <lastmod>' . $date . '</lastmod>' . PHP_EOL;
            }

            if($link === $this->_url ){
                $xml .= '       <priority>1</priority>' . PHP_EOL;
            } else {
                $xml .= '        <priority>0.5</priority>' . PHP_EOL;
            }

            $xml .= '    </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        file_put_contents('sitemap.xml',$xml);
    }

    public function navigate()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_actual);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        $this->_html = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $links = $this->getLinks();
        foreach ($links as $link) {
            $this->link($link);
        }
    }
}
