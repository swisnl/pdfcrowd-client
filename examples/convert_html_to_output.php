<?php

use Swis\PdfcrowdClient\Pdfcrowd;

include '../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__.'/..');
$dotenv->load();

$pdfcrowd = new Pdfcrowd(getenv('PDFCROWD_USERNAME'), getenv('PDFCROWD_KEY'));

header("Content-type:application/pdf");
header("Content-Disposition:attachment;filename='downloaded.pdf'");

echo $pdfcrowd->convertHtml(file_get_contents('data/example.html'));

exit;