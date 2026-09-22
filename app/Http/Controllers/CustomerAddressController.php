<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;

class CustomerAddressController extends Controller
{
    /**
     * List all addresses for a given customer.
     * GET /api/customer-addresses?customer_id=X
     */
    public function index(Request $request)
    {
        $request->validate(['customer_id' => 'required|exists:customers,id']);
        return CustomerAddress::where('customer_id', $request->customer_id)
            ->orderByDesc('is_default')
            ->get();
    }

    /**
     * Create a new address for a customer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'label'       => 'required|string|max:50',
            'street'      => 'required|string|max:255',
            'city'        => 'required|string|max:100',
            'is_default'  => 'boolean',
        ]);

        // If marking as default, unset all others for this customer
        if (!empty($validated['is_default'])) {
            CustomerAddress::where('customer_id', $validated['customer_id'])
                ->update(['is_default' => false]);
        }

        // If this is the customer's first address, force it as default
        $count = CustomerAddress::where('customer_id', $validated['customer_id'])->count();
        if ($count === 0) {
            $validated['is_default'] = true;
        }

        $address = CustomerAddress::create($validated);
        return response()->json($address, 201);
    }

    /**
     * Update an existing address.
     */
    public function update(Request $request, CustomerAddress $customerAddress)
    {
        $validated = $request->validate([
            'label'      => 'required|string|max:50',
            'street'     => 'required|string|max:255',
            'city'       => 'required|string|max:100',
            'is_default' => 'boolean',
        ]);

        // If marking as default, unset all others for this customer
        if (!empty($validated['is_default'])) {
            CustomerAddress::where('customer_id', $customerAddress->customer_id)
                ->where('id', '!=', $customerAddress->id)
                ->update(['is_default' => false]);
        }

        $customerAddress->update($validated);
        return response()->json($customerAddress, 200);
    }

    /**
     * Delete an address.
     * If the deleted address was default, promote the next one.
     */
    public function destroy(CustomerAddress $customerAddress)
    {
        $wasDefault  = $customerAddress->is_default;
        $customerId  = $customerAddress->customer_id;

        $customerAddress->delete();

        if ($wasDefault) {
            $next = CustomerAddress::where('customer_id', $customerId)->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return response()->json(null, 204);
    }
}
