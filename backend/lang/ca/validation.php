<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Traduccions catalanes dels missatges de validació de Laravel.
    |
    */

    'accepted' => 'El camp :attribute ha de ser acceptat.',
    'accepted_if' => 'El camp :attribute ha de ser acceptat quan :other sigui :value.',
    'active_url' => 'El camp :attribute ha de ser una URL vàlida.',
    'after' => 'El camp :attribute ha de ser una data posterior a :date.',
    'after_or_equal' => 'El camp :attribute ha de ser una data posterior o igual a :date.',
    'alpha' => 'El camp :attribute només pot contenir lletres.',
    'alpha_dash' => 'El camp :attribute només pot contenir lletres, números, guions i guions baixos.',
    'alpha_num' => 'El camp :attribute només pot contenir lletres i números.',
    'any_of' => 'El valor del camp :attribute no és vàlid.',
    'array' => 'El camp :attribute ha de ser un array.',
    'ascii' => 'El camp :attribute només pot contenir caràcters alfanumèrics d\'un sol byte.',
    'before' => 'El camp :attribute ha de ser una data anterior a :date.',
    'before_or_equal' => 'El camp :attribute ha de ser una data anterior o igual a :date.',
    'between' => [
        'array' => 'El camp :attribute ha de contenir entre :min i :max elements.',
        'file' => 'El camp :attribute ha de pesar entre :min i :max kilobytes.',
        'numeric' => 'El camp :attribute ha d\'estar entre :min i :max.',
        'string' => 'El camp :attribute ha de tenir entre :min i :max caràcters.',
    ],
    'boolean' => 'El camp :attribute ha de ser cert o fals.',
    'can' => 'El camp :attribute conté un valor no autoritzat.',
    'confirmed' => 'La confirmació del camp :attribute no coincideix.',
    'contains' => 'Al camp :attribute li falta un valor obligatori.',
    'current_password' => 'La contrasenya és incorrecta.',
    'date' => 'El camp :attribute ha de ser una data vàlida.',
    'date_equals' => 'El camp :attribute ha de ser una data igual a :date.',
    'date_format' => 'El camp :attribute ha de coincidir amb el format :format.',
    'decimal' => 'El camp :attribute ha de tenir :decimal decimals.',
    'declined' => 'El camp :attribute ha de ser rebutjat.',
    'declined_if' => 'El camp :attribute ha de ser rebutjat quan :other sigui :value.',
    'different' => 'Els camps :attribute i :other han de ser diferents.',
    'digits' => 'El camp :attribute ha de tenir :digits dígits.',
    'digits_between' => 'El camp :attribute ha de tenir entre :min i :max dígits.',
    'dimensions' => 'El camp :attribute té dimensions d\'imatge no vàlides.',
    'distinct' => 'El camp :attribute té un valor duplicat.',
    'doesnt_contain' => 'El camp :attribute no pot contenir cap dels valors següents: :values.',
    'doesnt_end_with' => 'El camp :attribute no pot acabar amb cap dels valors següents: :values.',
    'doesnt_start_with' => 'El camp :attribute no pot començar amb cap dels valors següents: :values.',
    'email' => 'El camp :attribute ha de ser una adreça de correu vàlida.',
    'encoding' => 'El camp :attribute s\'ha de codificar en :encoding.',
    'ends_with' => 'El camp :attribute ha d\'acabar amb un dels valors següents: :values.',
    'enum' => 'El valor seleccionat a :attribute no és vàlid.',
    'exists' => 'El valor seleccionat a :attribute no és vàlid.',
    'extensions' => 'El camp :attribute ha de tenir una de les extensions següents: :values.',
    'file' => 'El camp :attribute ha de ser un fitxer.',
    'filled' => 'El camp :attribute ha de tenir un valor.',
    'gt' => [
        'array' => 'El camp :attribute ha de tenir més de :value elements.',
        'file' => 'El camp :attribute ha de ser més gran que :value kilobytes.',
        'numeric' => 'El camp :attribute ha de ser més gran que :value.',
        'string' => 'El camp :attribute ha de tenir més de :value caràcters.',
    ],
    'gte' => [
        'array' => 'El camp :attribute ha de tenir :value elements o més.',
        'file' => 'El camp :attribute ha de ser més gran o igual que :value kilobytes.',
        'numeric' => 'El camp :attribute ha de ser més gran o igual que :value.',
        'string' => 'El camp :attribute ha de tenir almenys :value caràcters.',
    ],
    'hex_color' => 'El camp :attribute ha de ser un color hexadecimal vàlid.',
    'image' => 'El camp :attribute ha de ser una imatge.',
    'in' => 'El valor seleccionat a :attribute no és vàlid.',
    'in_array' => 'El camp :attribute ha d\'existir a :other.',
    'in_array_keys' => 'El camp :attribute ha de contenir almenys una de les claus següents: :values.',
    'integer' => 'El camp :attribute ha de ser un nombre enter.',
    'ip' => 'El camp :attribute ha de ser una adreça IP vàlida.',
    'ipv4' => 'El camp :attribute ha de ser una adreça IPv4 vàlida.',
    'ipv6' => 'El camp :attribute ha de ser una adreça IPv6 vàlida.',
    'json' => 'El camp :attribute ha de ser una cadena JSON vàlida.',
    'list' => 'El camp :attribute ha de ser una llista.',
    'lowercase' => 'El camp :attribute ha d\'estar en minúscules.',
    'lt' => [
        'array' => 'El camp :attribute ha de tenir menys de :value elements.',
        'file' => 'El camp :attribute ha de ser menor que :value kilobytes.',
        'numeric' => 'El camp :attribute ha de ser menor que :value.',
        'string' => 'El camp :attribute ha de tenir menys de :value caràcters.',
    ],
    'lte' => [
        'array' => 'El camp :attribute no pot tenir més de :value elements.',
        'file' => 'El camp :attribute ha de ser menor o igual que :value kilobytes.',
        'numeric' => 'El camp :attribute ha de ser menor o igual que :value.',
        'string' => 'El camp :attribute ha de tenir com a màxim :value caràcters.',
    ],
    'mac_address' => 'El camp :attribute ha de ser una adreça MAC vàlida.',
    'max' => [
        'array' => 'El camp :attribute no pot tenir més de :max elements.',
        'file' => 'El camp :attribute no pot ser més gran que :max kilobytes.',
        'numeric' => 'El camp :attribute no pot ser més gran que :max.',
        'string' => 'El camp :attribute no pot tenir més de :max caràcters.',
    ],
    'max_digits' => 'El camp :attribute no pot tenir més de :max dígits.',
    'mimes' => 'El camp :attribute ha de ser un fitxer de tipus: :values.',
    'mimetypes' => 'El camp :attribute ha de ser un fitxer de tipus: :values.',
    'min' => [
        'array' => 'El camp :attribute ha de tenir almenys :min elements.',
        'file' => 'El camp :attribute ha de pesar com a mínim :min kilobytes.',
        'numeric' => 'El camp :attribute ha de ser com a mínim :min.',
        'string' => 'El camp :attribute ha de tenir almenys :min caràcters.',
    ],
    'min_digits' => 'El camp :attribute ha de tenir almenys :min dígits.',
    'missing' => 'El camp :attribute ha d\'estar absent.',
    'missing_if' => 'El camp :attribute ha d\'estar absent quan :other sigui :value.',
    'missing_unless' => 'El camp :attribute ha d\'estar absent tret que :other sigui :value.',
    'missing_with' => 'El camp :attribute ha d\'estar absent quan :values estigui present.',
    'missing_with_all' => 'El camp :attribute ha d\'estar absent quan :values estiguin presents.',
    'multiple_of' => 'El camp :attribute ha de ser un múltiple de :value.',
    'not_in' => 'El valor seleccionat a :attribute no és vàlid.',
    'not_regex' => 'El format del camp :attribute no és vàlid.',
    'numeric' => 'El camp :attribute ha de ser un nombre.',
    'password' => [
        'letters' => 'El camp :attribute ha de contenir almenys una lletra.',
        'mixed' => 'El camp :attribute ha de contenir almenys una lletra majúscula i una de minúscula.',
        'numbers' => 'El camp :attribute ha de contenir almenys un número.',
        'symbols' => 'El camp :attribute ha de contenir almenys un símbol.',
        'uncompromised' => 'El :attribute facilitat apareix en una filtració de dades. Tria un :attribute diferent.',
    ],
    'present' => 'El camp :attribute ha d\'estar present.',
    'present_if' => 'El camp :attribute ha d\'estar present quan :other sigui :value.',
    'present_unless' => 'El camp :attribute ha d\'estar present tret que :other sigui :value.',
    'present_with' => 'El camp :attribute ha d\'estar present quan :values estigui present.',
    'present_with_all' => 'El camp :attribute ha d\'estar present quan :values estiguin presents.',
    'prohibited' => 'El camp :attribute està prohibit.',
    'prohibited_if' => 'El camp :attribute està prohibit quan :other sigui :value.',
    'prohibited_if_accepted' => 'El camp :attribute està prohibit quan :other sigui acceptat.',
    'prohibited_if_declined' => 'El camp :attribute està prohibit quan :other sigui rebutjat.',
    'prohibited_unless' => 'El camp :attribute està prohibit tret que :other estigui a :values.',
    'prohibits' => 'El camp :attribute impedeix que :other estigui present.',
    'regex' => 'El format del camp :attribute no és vàlid.',
    'required' => 'El camp :attribute és obligatori.',
    'required_array_keys' => 'El camp :attribute ha de contenir entrades per: :values.',
    'required_if' => 'El camp :attribute és obligatori quan :other sigui :value.',
    'required_if_accepted' => 'El camp :attribute és obligatori quan :other sigui acceptat.',
    'required_if_declined' => 'El camp :attribute és obligatori quan :other sigui rebutjat.',
    'required_unless' => 'El camp :attribute és obligatori tret que :other estigui a :values.',
    'required_with' => 'El camp :attribute és obligatori quan :values estigui present.',
    'required_with_all' => 'El camp :attribute és obligatori quan :values estiguin presents.',
    'required_without' => 'El camp :attribute és obligatori quan :values no estigui present.',
    'required_without_all' => 'El camp :attribute és obligatori quan cap dels valors :values està present.',
    'same' => 'El camp :attribute ha de coincidir amb :other.',
    'size' => [
        'array' => 'El camp :attribute ha de contenir :size elements.',
        'file' => 'El camp :attribute ha de pesar :size kilobytes.',
        'numeric' => 'El camp :attribute ha de ser :size.',
        'string' => 'El camp :attribute ha de tenir :size caràcters.',
    ],
    'starts_with' => 'El camp :attribute ha de començar amb un dels valors següents: :values.',
    'string' => 'El camp :attribute ha de ser una cadena.',
    'timezone' => 'El camp :attribute ha de ser una zona horària vàlida.',
    'unique' => 'El camp :attribute ja ha estat utilitzat.',
    'uploaded' => 'No s\'ha pogut pujar el fitxer :attribute.',
    'uppercase' => 'El camp :attribute ha d\'estar en majúscules.',
    'url' => 'El camp :attribute ha de ser una URL vàlida.',
    'ulid' => 'El camp :attribute ha de ser un ULID vàlid.',
    'uuid' => 'El camp :attribute ha de ser un UUID vàlid.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'missatge-personalitzat',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [],

];
