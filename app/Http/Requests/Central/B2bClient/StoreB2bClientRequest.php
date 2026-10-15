<?php

namespace App\Http\Requests\Central\B2bClient;

use App\Models\B2bClient;
use App\Rules\Cnpj;
use App\Rules\PhoneBr;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Dados do cliente B2B (razão social, CNPJ e contato). Base também do
 * UpdateB2bClientRequest.
 */
class StoreB2bClientRequest extends FormRequest
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
            'legal_name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'document' => [
                'nullable',
                'string',
                new Cnpj,
                Rule::unique('b2b_clients', 'document')->ignore($this->b2bClient()),
            ],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'string', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', new PhoneBr],
        ];
    }

    /**
     * Cliente da rota na edição; nulo no cadastro.
     */
    protected function b2bClient(): ?B2bClient
    {
        return null;
    }

    /**
     * CNPJ, telefone e e-mail são guardados como no restante do sistema.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'document' => $this->digitsOrNull($this->input('document')),
            'contact_phone' => $this->digitsOrNull($this->input('contact_phone')),
            'contact_email' => is_string($this->input('contact_email')) && $this->input('contact_email') !== ''
                ? Str::lower($this->input('contact_email'))
                : null,
        ]);
    }

    /**
     * Texto sem nenhum dígito fica como veio, para a validação recusar.
     */
    private function digitsOrNull(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $value);

        return $digits === '' ? $value : $digits;
    }
}
