<?php

namespace App\Http\Controllers;

use App\Services\Documents\AssociationDocumentGuideService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AssociationGuideController extends Controller
{
    public function __construct(
        private readonly AssociationDocumentGuideService $guide,
    ) {}

    /** PDF panduan dokumen persatuan + Borang EFT untuk ALP. */
    public function download(Request $request): Response
    {
        abort_unless(
            $request->user()->can('applications.create')
            || $request->user()->canCreateApplicationOnBehalf(),
            403
        );

        $pdf = $this->guide->generate();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.AssociationDocumentGuideService::DOWNLOAD_FILENAME.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
