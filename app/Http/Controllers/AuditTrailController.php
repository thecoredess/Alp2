<?php

namespace App\Http\Controllers;

use App\Enums\RoleName;
use App\Models\User;
use App\Services\Reports\AuditReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditTrailController extends Controller
{
    public function __construct(private readonly AuditReportService $audit) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->hasRole(RoleName::SUPER_ADMIN->value), 403);

        $userFilter = $request->filled('user') ? $request->integer('user') : null;
        if ($userFilter === null && $request->filled('pengguna')) {
            $userFilter = $request->integer('pengguna');
        }

        $filters = [
            'user_id' => $userFilter,
            'action' => $request->filled('action') ? $request->string('action')->toString() : null,
            'entity_type' => $request->filled('entiti') ? $request->string('entiti')->toString() : null,
            'entity_id' => $request->filled('entity_id') ? $request->integer('entity_id') : null,
            'date_from' => $request->filled('dari') ? $request->date('dari')?->toDateString() : null,
            'date_to' => $request->filled('hingga') ? $request->date('hingga')?->toDateString() : null,
        ];

        return view('admin.settings.audit-trail', [
            'rows' => $this->audit->paginate($filters),
            'actions' => $this->audit->distinctActions($filters),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }
}
