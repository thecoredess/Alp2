<?php

namespace App\Http\Controllers;

use App\Models\Recipient;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Senarai penerima/persatuan (CRS — entiti ROS). */
class RecipientController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('applications.view_all'), 403);

        $recipients = Recipient::query()
            ->withCount('applications')
            ->when($request->filled('cari'), function ($q) use ($request) {
                $term = '%'.$request->string('cari').'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('ros_number', 'like', $term)
                        ->orWhere('address', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('recipients.index', compact('recipients'));
    }

    public function show(Request $request, Recipient $recipient): View
    {
        abort_unless($request->user()->can('applications.view_all'), 403);

        $recipient->load(['applications' => fn ($q) => $q->with(['alp:id,ref_code,name', 'financialYear:id,year'])->latest('id')]);

        return view('recipients.show', compact('recipient'));
    }
}
