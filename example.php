<?php

include 'SitemapGenerator.php';

$sitemapGenerator = new SitemapGenerator('http://www.alexis-ducerf.fr', 10, date('Y-m-d'), 'monthly');
$sitemapGenerator->generate();
$sitemapGenerator->compress();