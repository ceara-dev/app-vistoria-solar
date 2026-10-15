<?php

namespace App\Http\Requests\Central\B2bClient;

use App\Models\B2bClient;

class UpdateB2bClientRequest extends StoreB2bClientRequest
{
    protected function b2bClient(): B2bClient
    {
        return $this->route('b2bClient');
    }
}
