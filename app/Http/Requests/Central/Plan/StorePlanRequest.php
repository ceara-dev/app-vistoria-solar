<?php

namespace App\Http\Requests\Central\Plan;

use App\Models\Plan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Dados de um plano comercial. Base também do UpdatePlanRequest.
 */
class StorePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('plans', 'name')->ignore($this->plan())],
            'price_cents' => ['required', 'integer', 'min:0'],
            'limits' => ['nullable', 'array'],
            'limits.users' => ['nullable', 'integer', 'min:1'],
            'limits.trips_per_month' => ['nullable', 'integer', 'min:1'],
            'limits.storage_mb' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Plano da rota na edição; nulo no cadastro.
     */
    protected function plan(): ?Plan
    {
        return null;
    }
}
