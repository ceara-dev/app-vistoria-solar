<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\TripPointStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Lote de pontos de uma rota. A autorização (rota própria) fica na definição da rota.
 */
class StoreTripPointsRequest extends FormRequest
{
    public const MAX_POINTS = 500;

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
            'points' => ['required', 'array', 'min:1', 'max:'.self::MAX_POINTS],
            'points.*.id' => ['required', 'uuid', 'distinct'],
            'points.*.latitude' => ['required', 'numeric', 'between:-90,90'],
            'points.*.longitude' => ['required', 'numeric', 'between:-180,180'],
            'points.*.recorded_at' => ['required', 'date'],
            'points.*.status' => ['required', Rule::enum(TripPointStatus::class)],
            'points.*.speed_ms' => ['required', 'numeric', 'min:0'],
        ];
    }
}
