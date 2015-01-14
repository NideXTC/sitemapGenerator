<?php

include 'SitemapGenerator.php';

$sitemapGenerator = new SitemapGenerator('http://www.alexis-ducerf.fr', 10);
$sitemapGenerator->generate();