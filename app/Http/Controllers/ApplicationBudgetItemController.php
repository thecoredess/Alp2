<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationBudgetItemRequest;
use App\Models\Application;
use App\Models\ApplicationBudgetItem;
use App\Services\Audit\AuditService;
use Illuminate\Http\RedirectResponse;

class ApplicationBudgetItemController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function store(ApplicationBudgetItemRequest $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $quantity = (int) $request->validated('quantity');
        $unitCost = (string) $request->validated('unit_cost');
        $total = ApplicationBudgetItem::computeTotal($quantity, $unitCost); // Money, tepat

        $application->budgetItems()->create([
            'description' => $request->validated('description'),
            'quantity' => $quantity,
            'unit' => $request->validated('unit'),
            'unit_cost' => $unitCost,
            'total' => $total->value(),
            'remarks' => $request->validated('remarks'),
            'sort_order' => (int) $application->budgetItems()->max('sort_order') + 1,
        ]);

        $application->recalculateRequestedAmount();
        $this->audit->log('APPLICATION_BUDGET_UPDATED', $application, null, ['action' => 'add_item']);

        return back()->with('status', 'Item bajet ditambah.');
    }

    public function update(ApplicationBudgetItemRequest $request, Application $application, ApplicationBudgetItem $item): RedirectResponse
    {
        $this->authorize('update', $application);
        abort_unless($item->application_id === $application->id, 404);

        $quantity = (int) $request->validated('quantity');
        $unitCost = (string) $request->validated('unit_cost');
        $total = ApplicationBudgetItem::computeTotal($quantity, $unitCost);

        $item->update([
            'description' => $request->validated('description'),
            'quantity' => $quantity,
            'unit' => $request->validated('unit'),
            'unit_cost' => $unitCost,
            'total' => $total->value(),
            'remarks' => $request->validated('remarks'),
        ]);

        $application->recalculateRequestedAmount();
        $this->audit->log('APPLICATION_BUDGET_UPDATED', $application, null, ['action' => 'update_item', 'item_id' => $item->id]);

        return back()->with('status', 'Item bajet dikemas kini.');
    }

    public function destroy(Application $application, ApplicationBudgetItem $item): RedirectResponse
    {
        $this->authorize('update', $application);
        abort_unless($item->application_id === $application->id, 404);

        $item->delete();
        $application->recalculateRequestedAmount();
        $this->audit->log('APPLICATION_BUDGET_UPDATED', $application, null, ['action' => 'remove_item']);

        return back()->with('status', 'Item bajet dibuang.');
    }
}
