<?php

/*
|--------------------------------------------------------------------------
| Rotas de registro por módulo
|--------------------------------------------------------------------------
|
| Cada módulo lista as rotas que recebem um registro da empresa. O teste de
| isolamento chama todas com um registro de outra empresa e espera 404
| (doc 06, seção 4).
|
*/

dataset('customer record routes', [
    'show' => ['get', 'tenant.customers.show'],
    'edit' => ['get', 'tenant.customers.edit'],
    'update' => ['put', 'tenant.customers.update'],
    'destroy' => ['delete', 'tenant.customers.destroy'],
]);
