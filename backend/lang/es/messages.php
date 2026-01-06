<?php

return [
    'security' => [
        'too_many_requests' => 'Demasiadas solicitudes. Inténtalo de nuevo más tarde.',
    ],
    'account' => [
        'delete_confirmation' => 'Escribe DELETE para confirmar la eliminación de la cuenta.',
    ],
    'auth' => [
        'password_confirm' => 'Confirma tu contraseña para continuar.',
        'password_mismatch' => 'La contraseña proporcionada no coincide con tu contraseña actual.',
        'email_exists' => 'Ya existe una cuenta con este correo. Inicia sesión o restablece tu contraseña.',
        'password_confirm_email' => 'Confirma tu contraseña para cambiar el correo.',
        'password_reset_link_sent' => 'Si existe una cuenta con ese correo, recibirás un enlace para restablecer la contraseña.',
    ],
    'session' => [
        'store_unavailable' => 'El almacén de sesiones no está disponible para esta solicitud.',
        'not_found' => 'Sesión no encontrada.',
        'cannot_revoke_current' => 'No puedes revocar la sesión actual.',
    ],
];
