@if (($canExport ?? false))
    <div class="flex items-center gap-2">
        <a href="{{ request()->fullUrlWithQuery(['format' => 'xlsx']) }}" class="btn-white text-sm inline-flex items-center gap-1.5">
            <x-icon name="document" class="h-4 w-4" /> Excel
        </a>
        <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}" class="btn-white text-sm inline-flex items-center gap-1.5">
            <x-icon name="document" class="h-4 w-4" /> PDF
        </a>
    </div>
@endif
