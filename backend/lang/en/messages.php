<?php

return [
    'security' => [
        'too_many_requests' => 'Too many requests. Please try again later.',
    ],
    'account' => [
        'delete_confirmation' => 'Please type DELETE to confirm account deletion.',
    ],
    'auth' => [
        'password_confirm' => 'Please confirm your password to continue.',
        'password_mismatch' => 'The provided password does not match your current password.',
        'email_exists' => 'An account with this email already exists. Please sign in or reset your password.',
        'password_confirm_email' => 'Please confirm your password to change email.',
        'password_reset_link_sent' => 'If an account exists for that email, you will receive a password reset link.',
    ],
    'session' => [
        'store_unavailable' => 'Session store is not available for this request.',
        'not_found' => 'Session not found.',
        'cannot_revoke_current' => 'You cannot revoke the current session.',
    ],
];
