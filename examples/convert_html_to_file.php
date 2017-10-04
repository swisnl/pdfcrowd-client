<?php

use Swis\PdfcrowdClient\Pdfcrowd;

include '../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__.'/..');
$dotenv->load();

$pdfcrowd = new Pdfcrowd(getenv('PDFCROWD_USERNAME'), getenv('PDFCROWD_KEY'));

$filename = 'output/convert_html_output.pdf';
$output_file = fopen($filename, 'w');

$pdfcrowd->convertHtml(file_get_contents('data/example.html'), $output_file);

echo "file was outputted to ".$filename;

exit;