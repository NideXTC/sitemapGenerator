<?php

date_default_timezone_set('Europe/Paris');
require_once 'vendor/autoload.php';
use SeoTools\SitemapGenerator;

$sitemapGenerator = new SitemapGenerator('http://www.alexis-ducerf.fr', 50000, date('Y-m-d'), 'monthly');
$sitemapGenerator->generate();
$sitemapGenerator->compress();