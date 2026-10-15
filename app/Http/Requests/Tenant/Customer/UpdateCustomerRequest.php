<?php

namespace App\Http\Requests\Tenant\Customer;

use App\Models\Customer;

class UpdateCustomerRequest extends StoreCustomerRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->customer());
    }

    protected function customer(): Customer
    {
        return $this->route('customer');
    }
}
