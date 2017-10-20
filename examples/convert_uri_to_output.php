<?php

use Swis\PdfcrowdClient\Pdfcrowd;

include '../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__.'/..');
$dotenv->load();

$pdfcrowd = new Pdfcrowd(getenv('PDFCROWD_USERNAME'), getenv('PDFCROWD_KEY'));

header("Content-type:application/pdf");
header("Content-Disposition:attachment;filename='uri_to_output.pdf'");

echo $pdfcrowd->convertURI('https://google.com');

exit;