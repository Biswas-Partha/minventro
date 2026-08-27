<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index()
    {
        return StockMovement::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            'ref' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($validated['type'] === 'out' && $product->stock < $validated['qty']) {
            return response()->json(['message' => 'Insufficient stock'], 400);
        }

        DB::beginTransaction();
        try {
            if ($validated['type'] === 'in') {
                $product->increment('stock', $validated['qty']);
            } else {
                $product->decrement('stock', $validated['qty']);
            }

            $movement = StockMovement::create([
                'type' => $validated['type'],
                'product_id' => $validated['product_id'],
                'qty' => $validated['qty'],
                'ref' => $validated['ref'],
                'user' => 'Admin' // Static for now, can be auth()->user()->name later
            ]);

            DB::commit();
            return response()->json($movement, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error recording stock movement'], 500);
        }
    }

    public function show(StockMovement $stockMovement)
    {
        return $stockMovement;
    }

    // We typically don't allow updating or deleting stock movements in a strict system, 
    // but we can add basic ones for completeness or leave them empty.
    public function update(Request $request, StockMovement $stockMovement)
    {
        return response()->json(['message' => 'Stock movements cannot be updated'], 403);
    }

    public function destroy(StockMovement $stockMovement)
    {
        return response()->json(['message' => 'Stock movements cannot be deleted'], 403);
    }
}
