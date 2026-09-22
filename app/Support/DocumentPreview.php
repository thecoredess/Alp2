<?php

namespace App\Support;

use App\Models\Application;
use App\Models\ApplicationDocument;
use Illuminate\Support\Collection;

/** Data pratonton dokumen untuk modal popup (semua peranan). */
class DocumentPreview
{
    /**
     * @param  Collection<int, ApplicationDocument>|iterable<int, ApplicationDocument>  $documents
     * @return list<array{id: int, title: string, filename: string, url: string, downloadUrl: string, kind: string}>
     */
    public static function itemsFor(Application $application, iterable $documents): array
    {
        return collect($documents)
            ->map(fn (ApplicationDocument $doc) => self::itemFor($application, $doc))
            ->values()
            ->all();
    }

    /** @return array{id: int, title: string, filename: string, url: string, downloadUrl: string, kind: string} */
    public static function itemFor(Application $application, ApplicationDocument $doc): array
    {
        $mime = strtolower((string) ($doc->mime_type ?? ''));
        $name = strtolower((string) $doc->original_filename);

        $kind = 'other';
        if (str_starts_with($mime, 'image/') || preg_match('/\.(jpe?g|png|gif|webp|bmp)$/', $name)) {
            $kind = 'image';
        } elseif ($mime === 'application/pdf' || str_ends_with($name, '.pdf')) {
            $kind = 'pdf';
        }

        return [
            'id' => $doc->id,
            'title' => $doc->document_type->simpleLabel(),
            'filename' => $doc->original_filename,
            'url' => route('applications.documents.view', [$application, $doc]),
            'downloadUrl' => route('applications.documents.download', [$application, $doc]),
            'kind' => $kind,
        ];
    }
}
