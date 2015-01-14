# sitemapGenerator

A script for the sitemap generation. It will create and compress the sitemap. 

```
<?php

include 'SitemapGenerator.php';

$sitemapGenerator = new SitemapGenerator('http://www.alexis-ducerf.fr', 10, date('Y-m-d'), 'monthly');
$sitemapGenerator->generate();
$sitemapGenerator->compress();

````


|          Parameters         |                                  Detail                                  |
|:---------------------------:|------------------------------------------------------------------------|
| http://www.alexis-ducerf.fr | the absolute URL of the website                                          |
| 10                          | The max number of pages crawled                                          |
| date('Y-m-d')               | If the server don't send the last modification date, set this as default |
| monthly                     | The pages modification frequency                                         |