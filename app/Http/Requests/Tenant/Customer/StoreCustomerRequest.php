<?php

namespace App\Http\Requests\Tenant\Customer;

use App\Enums\BrazilianState;
use App\Enums\CustomerType;
use App\Models\Customer;
use App\Rules\Cnpj;
use App\Rules\Cpf;
use App\Rules\PhoneBr;
use App\Rules\ZipCode;
use App\Tenancy\TenantRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Dados do cliente com endereços e contatos. Base também do UpdateCustomerRequest.
 */
class StoreCustomerRequest extends FormRequest
{
    public const MAX_ADDRESSES = 10;

    public const MAX_CONTACTS = 20;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Customer::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customer = $this->customer();

        return [
            'type' => ['required', Rule::enum(CustomerType::class)],
            'name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'document' => [
                'nullable',
                'string',
                $this->enum('type', CustomerType::class) === CustomerType::Company ? new Cnpj : new Cpf,
                TenantRule::unique('customers', 'document')->ignore($customer),
            ],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', new PhoneBr],
            'notes' => ['nullable', 'string', 'max:2000'],

            'addresses' => ['array', 'max:'.self::MAX_ADDRESSES],
            'addresses.*.id' => ['nullable', 'integer', TenantRule::exists('customer_addresses')->where('customer_id', $customer?->id)],
            'addresses.*.label' => ['nullable', 'string', 'max:100'],
            'addresses.*.zip_code' => ['required', 'string', new ZipCode],
            'addresses.*.street' => ['required', 'string', 'max:255'],
            'addresses.*.number' => ['required', 'string', 'max:20'],
            'addresses.*.complement' => ['nullable', 'string', 'max:255'],
            'addresses.*.district' => ['required', 'string', 'max:255'],
            'addresses.*.city' => ['required', 'string', 'max:255'],
            'addresses.*.state' => ['required', Rule::enum(BrazilianState::class)],

            'contacts' => ['array', 'max:'.self::MAX_CONTACTS],
            'contacts.*.id' => ['nullable', 'integer', TenantRule::exists('customer_contacts')->where('customer_id', $customer?->id)],
            'contacts.*.name' => ['required', 'string', 'max:255'],
            'contacts.*.position' => ['nullable', 'string', 'max:100'],
            'contacts.*.email' => ['nullable', 'string', 'email', 'max:255'],
            'contacts.*.phone' => ['nullable', 'string', new PhoneBr],
        ];
    }

    /**
     * Cliente da rota na edição; nulo no cadastro. Um id de endereço ou de
     * contato só é aceito se for deste cliente.
     */
    protected function customer(): ?Customer
    {
        return null;
    }

    /**
     * Documentos, telefones e CEPs são guardados só com os dígitos, e e-mails em minúsculas.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'document' => $this->digitsOrNull($this->input('document')),
            'phone' => $this->digitsOrNull($this->input('phone')),
            'email' => $this->lowercaseOrNull($this->input('email')),
            'addresses' => $this->normalizeRows('addresses', fn (array $address): array => [
                ...$address,
                'zip_code' => $this->digitsOrNull($address['zip_code'] ?? null),
            ]),
            'contacts' => $this->normalizeRows('contacts', fn (array $contact): array => [
                ...$contact,
                'phone' => $this->digitsOrNull($contact['phone'] ?? null),
                'email' => $this->lowercaseOrNull($contact['email'] ?? null),
            ]),
        ]);
    }

    /**
     * @param  callable(array<string, mixed>): array<string, mixed>  $normalize
     * @return mixed Lista normalizada, ou o valor original se não for lista (a validação recusa)
     */
    private function normalizeRows(string $key, callable $normalize): mixed
    {
        $rows = $this->input($key, []);

        if (! is_array($rows)) {
            return $rows;
        }

        return array_map(fn (mixed $row): mixed => is_array($row) ? $normalize($row) : $row, $rows);
    }

    /**
     * Texto sem nenhum dígito fica como veio, para a validação recusar em vez
     * de virar campo vazio.
     */
    private function digitsOrNull(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $value);

        return $digits === '' ? $value : $digits;
    }

    private function lowercaseOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? Str::lower($value) : null;
    }
}
