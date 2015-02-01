# sitemapGenerator

A script for the sitemap generation. The script will create a sitemap with links & images and will compress it 


## Installation 

```
php composer.phar require nidextc/sitemap-generator
```
SitemapGenerator requires PHP 5.3.0 or later.

## Usage examples

```
<?php

require_once 'vendor/autoload.php';
use SeoTools\SitemapGenerator;

$sitemapGenerator = new SitemapGenerator('http://www.alexis-ducerf.fr', 10, date('Y-m-d'), 'monthly');
$sitemapGenerator->generate();
$sitemapGenerator->compress();

````


|          Parameters         |                                  Detail                                  |
|:---------------------------:|------------------------------------------------------------------------|
| http://www.alexis-ducerf.fr | the absolute URL of the website                                          |
| 10                          | The max number of pages crawled                                          |
| date('Y-m-d')               | If the server don't send the last modification date, set it as default |
| monthly                     | The pages modification frequency                                         |