<?php

use Swis\PdfcrowdClient\Pdfcrowd;

include '../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__.'/..');
$dotenv->load();

$pdfcrowd = new Pdfcrowd(getenv('PDFCROWD_USERNAME'), getenv('PDFCROWD_KEY'));

$filename = 'output/uri_to_file.pdf';
$output_file = fopen($filename, 'w');

$pdfcrowd->setOutputDestination($output_file);
$pdfcrowd->convertURI('https://google.com');

echo "file was outputted to ".$filename."\n";

exit;