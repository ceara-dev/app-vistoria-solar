<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Trip;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Totais da rota calculados no aparelho. Datas em ISO 8601, de preferência em UTC.
 */
class UpsertTripRequest extends FormRequest
{
    /**
     * Quem pode gravar rotas. Se a rota já existe e é de outra pessoa, o TripService recusa.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Trip::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'started_at' => ['required', 'date'],
            'finished_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'distance_m' => ['nullable', 'numeric', 'min:0'],
            'moving_seconds' => ['nullable', 'integer', 'min:0'],
            'paused_seconds' => ['nullable', 'integer', 'min:0'],
            'max_speed_ms' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
