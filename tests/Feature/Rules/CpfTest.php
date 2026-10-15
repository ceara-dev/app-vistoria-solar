<?php

use App\Rules\Cpf;
use Illuminate\Support\Facades\Validator;

it('accepts a CPF with valid check digits', function (string $cpf) {
    expect(Validator::make(['cpf' => $cpf], ['cpf' => new Cpf])->passes())->toBeTrue();
})->with(['52998224725', '11144477735']);

it('rejects an invalid CPF with the contract message', function (string $cpf) {
    $validator = Validator::make(['cpf' => $cpf], ['cpf' => new Cpf]);

    expect($validator->errors()->first('cpf'))->toBe('CPF inválido');
})->with([
    'wrong check digit' => '52998224724',
    'all digits equal' => '11111111111',
    'ten digits' => '5299822472',
    'with punctuation' => '529.982.247-25',
]);
