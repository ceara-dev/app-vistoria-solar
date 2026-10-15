<?php

use App\Rules\PhoneBr;
use Illuminate\Support\Facades\Validator;

it('accepts landlines and mobiles with area code', function (string $phone) {
    expect(Validator::make(['phone' => $phone], ['phone' => new PhoneBr])->passes())->toBeTrue();
})->with([
    'landline' => '1133334444',
    'mobile' => '11987654321',
]);

it('rejects an invalid phone with the contract message', function (string $phone) {
    $validator = Validator::make(['phone' => $phone], ['phone' => new PhoneBr]);

    expect($validator->errors()->first('phone'))->toBe('Telefone inválido');
})->with([
    'without area code' => '987654321',
    'area code starting with zero' => '0187654321',
    'eleven digits without the mobile 9' => '11887654321',
    'with punctuation' => '(11) 98765-4321',
]);
