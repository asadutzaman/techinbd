<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CustomerAddress;

class AddressController extends Controller
{


    public function index()
    {
        $addresses = Auth::user()->addresses()->orderBy('is_default', 'desc')->get();
        return view('customer.addresses.index', compact('addresses'));
    }

    public function create()
    {
        return view('customer.addresses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:shipping,billing',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_default' => 'boolean',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();
        $data['is_default'] = $request->has('is_default');

        // If this is set as default, unset other defaults of the same type
        if ($data['is_default']) {
            Auth::user()->addresses()
                ->where('type', $data['type'])
                ->update(['is_default' => false]);
        }

        CustomerAddress::create($data);

        return redirect()->route('customer.addresses.index')
                        ->with('success', 'Address added successfully!');
    }

    public function edit(CustomerAddress $address)
    {
        // Ensure user can only edit their own addresses
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        return view('customer.addresses.edit', compact('address'));
    }

    public function update(Request $request, CustomerAddress $address)
    {
        // Ensure user can only update their own addresses
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'type' => 'required|in:shipping,billing',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_default' => 'boolean',
        ]);

        $data = $request->all();
        $data['is_default'] = $request->has('is_default');

        // If this is set as default, unset other defaults of the same type
        if ($data['is_default']) {
            Auth::user()->addresses()
                ->where('type', $data['type'])
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($data);

        return redirect()->route('customer.addresses.index')
                        ->with('success', 'Address updated successfully!');
    }

    public function destroy(CustomerAddress $address)
    {
        // Ensure user can only delete their own addresses
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $address->delete();

        return redirect()->route('customer.addresses.index')
                        ->with('success', 'Address deleted successfully!');
    }

    public function setDefault(CustomerAddress $address)
    {
        // Ensure user can only modify their own addresses
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        // Unset other defaults of the same type
        Auth::user()->addresses()
            ->where('type', $address->type)
            ->update(['is_default' => false]);

        // Set this as default
        $address->update(['is_default' => true]);

        return redirect()->route('customer.addresses.index')
                        ->with('success', 'Default address updated successfully!');
    }
}