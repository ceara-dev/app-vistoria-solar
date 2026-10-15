<?php

namespace App\Http\Requests\Central\Plan;

use App\Models\Plan;

class UpdatePlanRequest extends StorePlanRequest
{
    protected function plan(): Plan
    {
        return $this->route('plan');
    }
}
