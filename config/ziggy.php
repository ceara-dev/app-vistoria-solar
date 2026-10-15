<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rotas ocultas
    |--------------------------------------------------------------------------
    |
    | O `@routes` publica no HTML todas as rotas nomeadas. A área da Central
    | não interessa às empresas, então fica de fora (doc 04, seção 11). As
    | páginas da Central publicam o grupo `central` no lugar do padrão.
    |
    */

    'except' => ['central.*'],

    'groups' => [
        'central' => [
            'central.*',
            'logout',
        ],
    ],

];
