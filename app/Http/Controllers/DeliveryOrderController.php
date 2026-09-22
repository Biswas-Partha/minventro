<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use Illuminate\Http\Request;

class DeliveryOrderController extends Controller
{
    /**
     * Display a listing of delivery orders.
     */
    public function index(Request $request)
    {
        $query = DeliveryOrder::with(['customer', 'deliveryAddress'])
            ->orderByDesc('created_at');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return response()->json($query->get());
    }

    /**
     * Store a newly created delivery order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'         => 'required|exists:customers,id',
            'delivery_address_id' => 'required|exists:customer_addresses,id',
            'transport_method'    => 'required|in:own_rider,courier',
            'courier_name'        => 'required_if:transport_method,courier|nullable|string|max:100',
            'tracking_number'     => 'required_if:transport_method,courier|nullable|string|max:100',
            'items'               => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity'    => 'required|integer|min:1',
            'notes'               => 'nullable|string',
        ]);

        if ($validated['transport_method'] === 'own_rider') {
            $validated['courier_name'] = null;
            $validated['tracking_number'] = null;
        }

        $validated['status'] = 'pending';

        $deliveryOrder = DeliveryOrder::create($validated);

        return response()->json($deliveryOrder->load(['customer', 'deliveryAddress']), 201);
    }

    /**
     * Display the specified delivery order.
     */
    public function show(DeliveryOrder $deliveryOrder)
    {
        return response()->json($deliveryOrder->load(['customer', 'deliveryAddress']));
    }

    /**
     * Remove the specified delivery order.
     */
    public function destroy(DeliveryOrder $deliveryOrder)
    {
        $deliveryOrder->delete();

        return response()->json(null, 204);
    }
}
