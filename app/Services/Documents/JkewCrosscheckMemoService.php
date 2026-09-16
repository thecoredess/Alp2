<?php

namespace App\Services\Documents;

use App\Models\Application;
use App\Support\HijriDate;
use Carbon\Carbon;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

/** Jana memo semakan silang JKEW (Word) daripada templat rasmi. */
class JkewCrosscheckMemoService
{
    public function templateDirectory(): string
    {
        return resource_path('templates/jkew-crosscheck-memo-src');
    }

    public function templateExists(): bool
    {
        return is_readable($this->templateDirectory().'/word/document.xml');
    }

    public function downloadFilename(Application $application, string $extension): string
    {
        $no = preg_replace('/[^A-Za-z0-9\-_]/', '-', $application->application_number) ?: 'permohonan';

        return 'Memo-Semakan-Silang-JKEW-'.$no.'.'.ltrim($extension, '.');
    }

    /** @return array<string, mixed> */
    public function viewData(Application $application, ?Carbon $at = null): array
    {
        $application->loadMissing('alp');
        $at ??= now();
        $alp = $application->alp;

        return [
            'application' => $application,
            'memoDateGregorian' => $at->locale('ms')->translatedFormat('j F Y'),
            'memoDateHijri' => HijriDate::malayLabel($at),
            'alpName' => trim(($alp->ref_code ?? '').' — '.($alp->name ?? ''), ' —') ?: '—',
            'recipientName' => $application->recipient_name ?: '—',
            'rosNumber' => $application->recipient_ros_number ?: '—',
            'programTitle' => $application->purpose ?: '—',
            'programDate' => $application->program_date?->format('d/m/Y') ?? '—',
        ];
    }

    public function filledDocx(Application $application): string
    {
        abort_unless($this->templateExists(), 404, 'Templat memo semakan silang tidak dijumpai.');

        $tempRoot = sys_get_temp_dir().'/jkew-memo-'.uniqid('', true);
        $tempSrc = $tempRoot.'/src';
        $tempZip = $tempRoot.'/memo.docx';

        try {
            $this->copyDirectory($this->templateDirectory(), $tempSrc);

            $documentPath = $tempSrc.'/word/document.xml';
            $xml = file_get_contents($documentPath);
            if ($xml === false) {
                throw new RuntimeException('Gagal membaca templat memo.');
            }

            $xml = $this->ensureHijriPlaceholder($xml);
            $xml = $this->stripHighlights($xml);
            $replacements = $this->docxReplacements($application);
            $xml = str_replace(array_keys($replacements), array_values($replacements), $xml);
            file_put_contents($documentPath, $xml);

            $this->zipDirectory($tempSrc, $tempZip);
            $content = file_get_contents($tempZip);
            if ($content === false) {
                throw new RuntimeException('Gagal menjana fail memo.');
            }

            return $content;
        } finally {
            $this->deleteDirectory($tempRoot);
        }
    }

    private function ensureHijriPlaceholder(string $xml): string
    {
        if (str_contains($xml, '{{MEMO_DATE_HIJRI}}')) {
            return $xml;
        }

        $replacement = '<w:p w14:paraId="0C2957C6" w14:textId="395E4BEC" w:rsidR="00E168CE" w:rsidRDefault="00E168CE" w:rsidP="00E168CE"><w:pPr><w:spacing w:after="0"/><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>{{MEMO_DATE_HIJRI}}</w:t></w:r></w:p>';

        return (string) preg_replace(
            '/<w:p w14:paraId="0C2957C6"[\s\S]*?<\/w:p>/',
            $replacement,
            $xml,
            1
        );
    }

    /** @return array<string, string> */
    private function docxReplacements(Application $application): array
    {
        $data = $this->viewData($application);

        return [
            '{{MEMO_DATE}}' => $this->escapeXml((string) $data['memoDateGregorian']),
            '{{MEMO_DATE_HIJRI}}' => $this->escapeXml((string) $data['memoDateHijri']),
            '{{ALP_NAME}}' => $this->escapeXml((string) $data['alpName']),
            '{{RECIPIENT_NAME}}' => $this->escapeXml((string) $data['recipientName']),
            '{{ROS_NUMBER}}' => $this->escapeXml((string) $data['rosNumber']),
            '{{PROGRAM_TITLE}}' => $this->escapeXml((string) $data['programTitle']),
            '{{PROGRAM_DATE}}' => $this->escapeXml((string) $data['programDate']),
        ];
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function stripHighlights(string $xml): string
    {
        return (string) preg_replace('/<w:highlight w:val="[^"]*"\/>/', '', $xml);
    }

    private function copyDirectory(string $source, string $destination): void
    {
        if (! is_dir($destination) && ! mkdir($destination, 0755, true) && ! is_dir($destination)) {
            throw new RuntimeException('Gagal mencipta direktori sementara.');
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($iterator as $item) {
            $target = $destination.DIRECTORY_SEPARATOR.$iterator->getSubPathName();
            if ($item->isDir()) {
                if (! is_dir($target) && ! mkdir($target, 0755, true) && ! is_dir($target)) {
                    throw new RuntimeException('Gagal menyalin struktur templat.');
                }
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }

    private function zipDirectory(string $source, string $destination): void
    {
        $zip = new ZipArchive;
        if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal membuka arkib memo.');
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $filePath = $file->getRealPath();
            $relativePath = substr((string) $filePath, strlen($source) + 1);
            $zip->addFile($filePath, str_replace('\\', '/', $relativePath));
        }

        $zip->close();
    }

    private function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($directory);
    }
}
