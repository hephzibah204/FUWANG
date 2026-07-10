<?php

namespace App\Http\Controllers\LogisticsOps;

use App\Http\Controllers\Controller;
use App\Models\LogisticsInventoryItem;
use App\Models\LogisticsStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = LogisticsInventoryItem::query()->orderBy('name');
        if ($search = $request->string('search')->trim()->value()) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%");
        }

        $items = $query->paginate(20)->withQueryString();

        return view('logistics.ops.inventory.index', [
            'items' => $items,
            'staff' => Auth::guard('logistics_staff')->user(),
        ]);
    }

    public function create()
    {
        return view('logistics.ops.inventory.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:60', 'unique:logistics_inventory_items,sku'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'quantity' => ['required', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $item = LogisticsInventoryItem::query()->create([
            'sku' => $validated['sku'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'quantity' => (int) $validated['quantity'],
            'location' => $validated['location'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $staff = Auth::guard('logistics_staff')->user();
        if ($staff instanceof LogisticsStaff) {
            $staff->logActivity('logistics_inventory.created', "Created inventory item {$item->sku}");
        }

        return redirect()->route('logistics.ops.inventory.index')->with('success', 'Inventory item created.');
    }

    public function edit(LogisticsInventoryItem $item)
    {
        return view('logistics.ops.inventory.edit', compact('item'));
    }

    public function update(Request $request, LogisticsInventoryItem $item)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:60', Rule::unique('logistics_inventory_items', 'sku')->ignore($item->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'quantity' => ['required', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $item->fill([
            'sku' => $validated['sku'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'quantity' => (int) $validated['quantity'],
            'location' => $validated['location'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ])->save();

        $staff = Auth::guard('logistics_staff')->user();
        if ($staff instanceof LogisticsStaff) {
            $staff->logActivity('logistics_inventory.updated', "Updated inventory item {$item->sku}");
        }

        return redirect()->route('logistics.ops.inventory.edit', $item->id)->with('success', 'Inventory item updated.');
    }

    public function destroy(LogisticsInventoryItem $item)
    {
        $sku = $item->sku;
        $item->delete();

        $staff = Auth::guard('logistics_staff')->user();
        if ($staff instanceof LogisticsStaff) {
            $staff->logActivity('logistics_inventory.deleted', "Deleted inventory item {$sku}");
        }

        return redirect()->route('logistics.ops.inventory.index')->with('success', 'Inventory item deleted.');
    }
}

