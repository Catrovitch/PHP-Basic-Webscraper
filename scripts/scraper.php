<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

// Target URL
$url = 'http://www.magnacapax.fi/vuokrapalvelimet.php';

// Initialize Guzzle Client
$client = new Client();

// Make a GET request to the URL
$response = $client->request('GET', $url);

// Get the HTML content
$html = $response->getBody()->getContents();

// Create a new instance of the Symfony DomCrawler
$crawler = new Crawler($html);

// Extract product information from the pricing table
$products = $crawler->filter('ul.pricing-table li.title')->each(function ($node) {
    $name = $node->filter('a')->count() > 0 ? $node->filter('a')->text() : '';
    $price = $node->nextAll()->filter('li.price')->count() > 0 ? $node->nextAll()->filter('li.price')->text() : '';
    $specs = $node->nextAll()->filter('li.bullet-item')->each(function ($specNode) {
        return $specNode->text();
    });

    return [
        'name' => $name,
        'price' => $price,
        'specs' => $specs,
    ];
});

// Display the extracted product information
foreach ($products as $product) {
    echo 'Product Name: ' . $product['name'] . PHP_EOL;
    echo 'Price: ' . $product['price'] . PHP_EOL;

    echo 'Specifications:' . PHP_EOL;
    foreach ($product['specs'] as $spec) {
        echo '  - ' . $spec . PHP_EOL;
    }

    echo PHP_EOL; // Add a line break for better readability
}
