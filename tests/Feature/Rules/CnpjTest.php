<?php

use App\Rules\Cnpj;
use Illuminate\Support\Facades\Validator;

it('accepts a CNPJ with valid check digits', function (string $cnpj) {
    expect(Validator::make(['cnpj' => $cnpj], ['cnpj' => new Cnpj])->passes())->toBeTrue();
})->with(['11222333000181', '04252011000110']);

it('rejects an invalid CNPJ with the contract message', function (string $cnpj) {
    $validator = Validator::make(['cnpj' => $cnpj], ['cnpj' => new Cnpj]);

    expect($validator->errors()->first('cnpj'))->toBe('CNPJ inválido');
})->with([
    'wrong check digit' => '11222333000180',
    'all digits equal' => '00000000000000',
    'a CPF' => '52998224725',
    'with punctuation' => '11.222.333/0001-81',
]);
