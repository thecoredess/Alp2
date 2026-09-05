<?php
/**
 * Extract plain text from URS v1.2 docx for analysis.
 */
$src = dirname(__DIR__).'/URS/URS_Sistem_Pengurusan_Sumbangan ALP DBKL_v1_2_5926.docx';
$out = dirname(__DIR__).'/storage/app/_urs_v12_text.txt';

if (! file_exists($src)) {
    fwrite(STDERR, "Missing: $src\n");
    exit(1);
}

$zip = new ZipArchive();
if ($zip->open($src) !== true) {
    fwrite(STDERR, "Cannot open docx\n");
    exit(1);
}

$xml = $zip->getFromName('word/document.xml');
$zip->close();

if ($xml === false) {
    fwrite(STDERR, "No document.xml\n");
    exit(1);
}

// Convert Word XML paragraphs to text
$xml = preg_replace('/<\/w:p>/', "\n", $xml);
$xml = preg_replace('/<\/w:tr>/', "\n", $xml);
$xml = preg_replace('/<w:tab[^\/]*\/>/', "\t", $xml);
$xml = preg_replace('/<w:br[^\/]*\/>/', "\n", $xml);
$text = strip_tags($xml);
$text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
$text = preg_replace("/[ \t]+/", ' ', $text);
$text = preg_replace("/\n{3,}/", "\n\n", $text);
$text = trim($text);

file_put_contents($out, $text);
echo "Wrote ".strlen($text)." chars to $out\n";
echo "Lines: ".substr_count($text, "\n")."\n";
