<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        return Customer::all();
    }

    public function show($id)
    {
        return Customer::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $data = $request->validate([
            'first_name' => 'sometimes|required|string|max:100', // required =  This field must be present and not empty, sometimes = Only validate this rule if the field exists in the request.
            'last_name'  => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|unique:customers,email,' . $id,
            'address'    => 'sometimes|nullable|string|max:255',
        ]);

        $customer->update($data);

        return response()->json([
            'message' => 'Customer updated successfully',
            'data' => $customer,
        ]);
    }


    public function destroy($id)
    {
        Customer::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
