<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mensagens de validação
    |--------------------------------------------------------------------------
    |
    | As regras do contrato de validação (.docs/VALIDATION.md) usam exatamente
    | os textos de lá, os mesmos de resources/js/Constants/messages.ts:
    | required, max.string, min.string, email, confirmed, unique, exists,
    | date e date_format. Toda mensagem nova entra primeiro no contrato.
    |
    */

    'accepted' => 'O campo :attribute deve ser aceito',
    'accepted_if' => 'O campo :attribute deve ser aceito quando :other for :value',
    'active_url' => 'O campo :attribute deve ser uma URL válida',
    'after' => 'O campo :attribute deve ser uma data posterior a :date',
    'after_or_equal' => 'Data deve ser igual ou após :date',
    'alpha' => 'O campo :attribute deve conter apenas letras',
    'alpha_dash' => 'O campo :attribute deve conter apenas letras, números, traços e sublinhados',
    'alpha_num' => 'O campo :attribute deve conter apenas letras e números',
    'any_of' => 'O campo :attribute é inválido',
    'array' => 'O campo :attribute deve ser uma lista',
    'array_keys' => 'O campo :attribute deve conter apenas as chaves: :values',
    'ascii' => 'O campo :attribute deve conter apenas caracteres alfanuméricos e símbolos simples',
    'base64' => 'O campo :attribute deve ser um texto Base64 válido',
    'before' => 'O campo :attribute deve ser uma data anterior a :date',
    'before_or_equal' => 'Data deve ser igual ou antes de :date',
    'between' => [
        'array' => 'O campo :attribute deve ter entre :min e :max itens',
        'file' => 'O campo :attribute deve ter entre :min e :max kilobytes',
        'numeric' => 'O campo :attribute deve estar entre :min e :max',
        'string' => 'O campo :attribute deve ter entre :min e :max caracteres',
    ],
    'boolean' => 'O campo :attribute deve ser verdadeiro ou falso',
    'can' => 'O campo :attribute contém um valor não autorizado',
    'confirmed' => 'A confirmação não confere',
    'contains' => 'O campo :attribute não contém um valor obrigatório',
    'current_password' => 'A senha está incorreta',
    'date' => 'Data inválida',
    'date_equals' => 'O campo :attribute deve ser uma data igual a :date',
    'date_format' => 'Data inválida',
    'decimal' => 'O campo :attribute deve ter :decimal casas decimais',
    'declined' => 'O campo :attribute deve ser recusado',
    'declined_if' => 'O campo :attribute deve ser recusado quando :other for :value',
    'different' => 'Os campos :attribute e :other devem ser diferentes',
    'digits' => 'O campo :attribute deve ter :digits dígitos',
    'digits_between' => 'O campo :attribute deve ter entre :min e :max dígitos',
    'dimensions' => 'A imagem do campo :attribute tem dimensões inválidas',
    'distinct' => 'O campo :attribute tem um valor repetido',
    'doesnt_contain' => 'O campo :attribute não pode conter: :values',
    'doesnt_end_with' => 'O campo :attribute não pode terminar com: :values',
    'doesnt_start_with' => 'O campo :attribute não pode começar com: :values',
    'email' => 'E-mail inválido',
    'encoding' => 'O campo :attribute deve estar codificado em :encoding',
    'ends_with' => 'O campo :attribute deve terminar com: :values',
    'enum' => 'O valor selecionado é inválido',
    'exists' => 'O valor selecionado é inválido',
    'extensions' => 'O campo :attribute deve ter uma das extensões: :values',
    'file' => 'O campo :attribute deve ser um arquivo',
    'filled' => 'Este campo é obrigatório',
    'gt' => [
        'array' => 'O campo :attribute deve ter mais de :value itens',
        'file' => 'O campo :attribute deve ser maior que :value kilobytes',
        'numeric' => 'O campo :attribute deve ser maior que :value',
        'string' => 'O campo :attribute deve ter mais de :value caracteres',
    ],
    'gte' => [
        'array' => 'O campo :attribute deve ter :value itens ou mais',
        'file' => 'O campo :attribute deve ser maior ou igual a :value kilobytes',
        'numeric' => 'O campo :attribute deve ser maior ou igual a :value',
        'string' => 'O campo :attribute deve ter :value caracteres ou mais',
    ],
    'hex_color' => 'O campo :attribute deve ser uma cor hexadecimal válida',
    'image' => 'O campo :attribute deve ser uma imagem',
    'in' => 'O valor selecionado é inválido',
    'in_array' => 'O campo :attribute deve existir em :other',
    'in_array_keys' => 'O campo :attribute deve conter ao menos uma das chaves: :values',
    'integer' => 'O campo :attribute deve ser um número inteiro',
    'ip' => 'O campo :attribute deve ser um endereço IP válido',
    'ipv4' => 'O campo :attribute deve ser um endereço IPv4 válido',
    'ipv6' => 'O campo :attribute deve ser um endereço IPv6 válido',
    'json' => 'O campo :attribute deve ser um JSON válido',
    'list' => 'O campo :attribute deve ser uma lista',
    'lowercase' => 'O campo :attribute deve estar em letras minúsculas',
    'lt' => [
        'array' => 'O campo :attribute deve ter menos de :value itens',
        'file' => 'O campo :attribute deve ser menor que :value kilobytes',
        'numeric' => 'O campo :attribute deve ser menor que :value',
        'string' => 'O campo :attribute deve ter menos de :value caracteres',
    ],
    'lte' => [
        'array' => 'O campo :attribute não pode ter mais de :value itens',
        'file' => 'O campo :attribute deve ser menor ou igual a :value kilobytes',
        'numeric' => 'O campo :attribute deve ser menor ou igual a :value',
        'string' => 'O campo :attribute deve ter :value caracteres ou menos',
    ],
    'mac_address' => 'O campo :attribute deve ser um endereço MAC válido',
    'max' => [
        'array' => 'O campo :attribute não pode ter mais de :max itens',
        'file' => 'O campo :attribute não pode ter mais de :max kilobytes',
        'numeric' => 'O campo :attribute não pode ser maior que :max',
        'string' => 'Máximo de :max caracteres',
    ],
    'max_digits' => 'O campo :attribute não pode ter mais de :max dígitos',
    'mimes' => 'O campo :attribute deve ser um arquivo do tipo: :values',
    'mimetypes' => 'O campo :attribute deve ser um arquivo do tipo: :values',
    'min' => [
        'array' => 'O campo :attribute deve ter pelo menos :min itens',
        'file' => 'O campo :attribute deve ter pelo menos :min kilobytes',
        'numeric' => 'O campo :attribute deve ser pelo menos :min',
        'string' => 'Mínimo de :min caracteres',
    ],
    'min_digits' => 'O campo :attribute deve ter pelo menos :min dígitos',
    'missing' => 'O campo :attribute não deve ser enviado',
    'missing_if' => 'O campo :attribute não deve ser enviado quando :other for :value',
    'missing_unless' => 'O campo :attribute não deve ser enviado, a menos que :other seja :value',
    'missing_with' => 'O campo :attribute não deve ser enviado quando :values estiver presente',
    'missing_with_all' => 'O campo :attribute não deve ser enviado quando :values estiverem presentes',
    'multiple_of' => 'O campo :attribute deve ser múltiplo de :value',
    'not_in' => 'O valor selecionado é inválido',
    'not_regex' => 'O formato do campo :attribute é inválido',
    'numeric' => 'O campo :attribute deve ser um número',
    'password' => [
        'letters' => 'A senha deve conter pelo menos uma letra',
        'mixed' => 'A senha deve conter pelo menos uma letra maiúscula e uma minúscula',
        'numbers' => 'A senha deve conter pelo menos um número',
        'symbols' => 'A senha deve conter pelo menos um símbolo',
        'uncompromised' => 'Esta senha apareceu em um vazamento de dados. Escolha outra',
    ],
    'present' => 'O campo :attribute deve estar presente',
    'present_if' => 'O campo :attribute deve estar presente quando :other for :value',
    'present_unless' => 'O campo :attribute deve estar presente, a menos que :other seja :value',
    'present_with' => 'O campo :attribute deve estar presente quando :values estiver presente',
    'present_with_all' => 'O campo :attribute deve estar presente quando :values estiverem presentes',
    'prohibited' => 'O campo :attribute não é permitido',
    'prohibited_if' => 'O campo :attribute não é permitido quando :other for :value',
    'prohibited_if_accepted' => 'O campo :attribute não é permitido quando :other for aceito',
    'prohibited_if_declined' => 'O campo :attribute não é permitido quando :other for recusado',
    'prohibited_unless' => 'O campo :attribute não é permitido, a menos que :other esteja em :values',
    'prohibits' => 'O campo :attribute impede que :other seja enviado',
    'regex' => 'O formato do campo :attribute é inválido',
    'required' => 'Este campo é obrigatório',
    'required_array_keys' => 'O campo :attribute deve conter: :values',
    'required_if' => 'Este campo é obrigatório',
    'required_if_accepted' => 'Este campo é obrigatório',
    'required_if_declined' => 'Este campo é obrigatório',
    'required_unless' => 'Este campo é obrigatório',
    'required_with' => 'Este campo é obrigatório',
    'required_with_all' => 'Este campo é obrigatório',
    'required_without' => 'Este campo é obrigatório',
    'required_without_all' => 'Este campo é obrigatório',
    'same' => 'Os campos :attribute e :other devem ser iguais',
    'size' => [
        'array' => 'O campo :attribute deve ter :size itens',
        'file' => 'O campo :attribute deve ter :size kilobytes',
        'numeric' => 'O campo :attribute deve ser :size',
        'string' => 'O campo :attribute deve ter :size caracteres',
    ],
    'starts_with' => 'O campo :attribute deve começar com: :values',
    'string' => 'O campo :attribute deve ser um texto',
    'timezone' => 'O campo :attribute deve ser um fuso horário válido',
    'unique' => 'Este valor já está em uso',
    'uploaded' => 'Falha no envio do campo :attribute',
    'uppercase' => 'O campo :attribute deve estar em letras maiúsculas',
    'url' => 'O campo :attribute deve ser uma URL válida',
    'ulid' => 'O campo :attribute deve ser um ULID válido',
    'uuid' => 'O campo :attribute deve ser um UUID válido',

    /*
    |--------------------------------------------------------------------------
    | Regras do projeto (app/Rules)
    |--------------------------------------------------------------------------
    */

    'cnpj' => 'CNPJ inválido',
    'cpf' => 'CPF inválido',
    'phone_br' => 'Telefone inválido',
    'zip_code' => 'CEP inválido',

    /*
    |--------------------------------------------------------------------------
    | Mensagens específicas
    |--------------------------------------------------------------------------
    */

    'custom' => [],

    /*
    |--------------------------------------------------------------------------
    | Nomes dos campos
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'company_name' => 'nome da empresa',
        'document' => 'documento',
        'trade_name' => 'nome fantasia',
        'email' => 'e-mail',
        'finished_at' => 'fim',
        'name' => 'nome',
        'password' => 'senha',
        'password_confirmation' => 'confirmação da senha',
        'role' => 'papel',
        'started_at' => 'início',
        'tenant_id' => 'empresa',
    ],

];
