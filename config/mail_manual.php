<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Manual SMTP Settings
    |--------------------------------------------------------------------------
    |
    | Configure these values in .env to control outbound email delivery
    | manually from the application (without queues).
    |

    my@refery.app
    M*_ZabM7L0=8

    mail.refery.app
    IMAP Port: 993 POP3 Port: 995
    mail.refery.app
    SMTP Port: 465
    */
    // Configure manual SMTP credentials directly in this file.
    'host' => 'mail.refery.app',
    'port' => 465,
    'encryption' => 'ssl', // tls, ssl, or '' for none
    'username' => 'my@refery.app',
    'password' => 'M*_ZabM7L0=8',
    'timeout' => 30,
    'from_address' => 'my@refery.app',
    'from_name' => 'ReferyApp',
];
