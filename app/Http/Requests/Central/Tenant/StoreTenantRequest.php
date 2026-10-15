<?php

namespace App\Http\Requests\Central\Tenant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Cadastro de empresa pela Central. O administrador recebe o convite de
 * proprietário por e-mail.
 */
class StoreTenantRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'email', 'max:255'],
            'plan_id' => ['nullable', 'integer', Rule::exists('plans', 'id')->where('is_active', true)],
            'b2b_client_id' => ['nullable', 'integer', Rule::exists('b2b_clients', 'id')],
        ];
    }

    /**
     * E-mails são guardados em minúsculas, como no convite e no login.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('admin_email'))) {
            $this->merge(['admin_email' => Str::lower(trim($this->input('admin_email')))]);
        }
    }
}
