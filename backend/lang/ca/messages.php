<?php

return [
    'security' => [
        'too_many_requests' => 'Massa sol·licituds. Torna-ho a provar més tard.',
    ],
    'account' => [
        'delete_confirmation' => "Escriu DELETE per confirmar l'eliminació del compte.",
    ],
    'auth' => [
        'password_confirm' => 'Confirma la teva contrasenya per continuar.',
        'password_mismatch' => 'La contrasenya proporcionada no coincideix amb la teva contrasenya actual.',
        'email_exists' => 'Ja existeix un compte amb aquest correu. Inicia sessió o restableix la contrasenya.',
        'password_confirm_email' => 'Confirma la teva contrasenya per canviar el correu.',
        'password_reset_link_sent' => 'Si existeix un compte amb aquest correu, rebràs un enllaç per restablir la contrasenya.',
    ],
    'session' => [
        'store_unavailable' => 'El magatzem de sessions no està disponible per a aquesta sol·licitud.',
        'not_found' => 'Sessió no trobada.',
        'cannot_revoke_current' => 'No pots revocar la sessió actual.',
    ],
];
