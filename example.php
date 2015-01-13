<?php

include 'SitemapGenerator.php';

$sitemapGenerator = new SitemapGenerator('http://www.alexis-ducerf.fr/', 100);
$sitemapGenerator->generate();