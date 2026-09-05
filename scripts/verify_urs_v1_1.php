<?php
$dst = dirname(__DIR__).'/URS/URS_Sistem_Pengurusan_Permohonan_Peruntukan_Sumbangan_ALP_DBKL_Standard_v1.1.docx';
$z = new ZipArchive();
$z->open($dst);
$xml = $z->getFromName('word/document.xml');
$core = $z->getFromName('docProps/core.xml');
$z->close();

$checks = [
    'Standard_v1.1' => str_contains($xml, 'Standard_v1.1') || str_contains($xml, 'Standard v1.1'),
    'OS-01' => str_contains($xml, 'OS-01'),
    'OS-10' => str_contains($xml, 'OS-10'),
    'Addendum Hybrid' => str_contains($xml, 'Addendum Model Hybrid'),
    'Bah6 updated' => str_contains($xml, 'Perkara di luar skop ditetapkan'),
    'Polisi URS note' => str_contains($xml, 'Polisi URS'),
    'P01 hybrid' => str_contains($xml, 'P01 Hybrid'),
    'core v1.1' => str_contains($core, 'v1.1'),
];

foreach ($checks as $label => $ok) {
    echo ($ok ? 'OK' : 'MISSING').": $label\n";
}
