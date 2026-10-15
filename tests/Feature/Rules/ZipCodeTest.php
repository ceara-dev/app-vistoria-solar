<?php

use App\Rules\ZipCode;
use Illuminate\Support\Facades\Validator;

it('accepts a zip code with eight digits', function () {
    expect(Validator::make(['zip' => '01310100'], ['zip' => new ZipCode])->passes())->toBeTrue();
});

it('rejects an invalid zip code with the contract message', function (string $zipCode) {
    $validator = Validator::make(['zip' => $zipCode], ['zip' => new ZipCode]);

    expect($validator->errors()->first('zip'))->toBe('CEP inválido');
})->with([
    'seven digits' => '0131010',
    'all zeros' => '00000000',
    'with punctuation' => '01310-100',
]);
