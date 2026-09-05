<?php
$dst = dirname(__DIR__).'/URS/URS_Sistem_Pengurusan_Permohonan_Peruntukan_Sumbangan_ALP_DBKL_Standard_v1.1.docx';
$z = new ZipArchive();
$z->open($dst);
$xml = $z->getFromName('word/document.xml');
$z->close();
preg_match_all('/Standard[^<]{0,40}/', $xml, $m);
foreach (array_unique($m[0]) as $s) echo $s."\n";
preg_match_all('/v1\.[0-9]/', $xml, $v);
echo 'v refs: '.implode(', ', array_unique($v[0]))."\n";
