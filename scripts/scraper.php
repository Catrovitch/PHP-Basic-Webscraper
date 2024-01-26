<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

// Target URL
$url = 'http://www.magnacapax.fi/vuokrapalvelimet.php';

// Initialize Guzzle Client
$client = new Client();

// Make a GET request to the URL
$response = $client->request('GET', $url);

// Get the HTML content
$htmlString = (string) $response->getBody();

// Suppress any warnings related to HTML parsing
libxml_use_internal_errors(true);

// Load HTML content into a DOMDocument
$doc = new DOMDocument();
$doc->loadHTML($htmlString);

// Create an XPath object
$xpath = new DOMXPath($doc);

// Define XPath expression to select product containers with either class
$products = $xpath->evaluate('//div[@class="medium-3 column"]/ul[contains(@class, "pricing-table")]');

// Check if any products are found
if ($products->length === 0) {
    echo "No products found." . PHP_EOL;
    exit; // Stop execution if no products are found
}

// Open the CSV file for writing
$csvFilePath = 'output.csv';
$csvFile = fopen($csvFilePath, 'w');

// Write the CSV header
fputcsv($csvFile, ['Product Name', 'Price']);

// Iterate through products and write information to the CSV file
foreach ($products as $product) {
    // Extract product name and price
    $title = $xpath->evaluate('string(.//li[contains(@class, "title")]/a)', $product);
    $price = $xpath->evaluate('string(.//li[contains(@class, "price")])', $product);

    // Write a row to the CSV file
    fputcsv($csvFile, [$title, $price]);
}

// Close the CSV file
fclose($csvFile);

echo "CSV file created successfully: $csvFilePath\n";
