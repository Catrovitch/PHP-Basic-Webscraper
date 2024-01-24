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

// Specify the CSV file path
$csvFilePath = 'output.csv';

// Open the CSV file for writing
$csvFile = fopen($csvFilePath, 'w');

// Write the CSV header
fputcsv($csvFile, ['Product Name', 'Price', 'Specifications']);

// Write each product information to the CSV file
foreach ($products as $product) {
    // Combine specifications into a single string
    $specsString = implode(", ", $product['specs']);
    
    // Write a row to the CSV file
    fputcsv($csvFile, [$product['name'], $product['price'], $specsString]);
}

// Close the CSV file
fclose($csvFile);

echo "CSV file created successfully: $csvFilePath\n";
