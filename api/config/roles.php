<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Available Roles
    |--------------------------------------------------------------------------
    |
    | This array defines all available roles in the application.
    | These roles are used for seeding, validation, and role checks.
    |
    */

    'available' => [
        'admin',
        'manager',
        'user',
        'customer',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Role
    |--------------------------------------------------------------------------
    |
    | This role is automatically assigned to newly registered users.
    |
    */

    'default' => 'user',

    /*
    |--------------------------------------------------------------------------
    | Role Display Names
    |--------------------------------------------------------------------------
    |
    | Human-readable names for each role (for UI display).
    |
    */

    'display_names' => [
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'user' => 'User',
        'customer' => 'Customer',
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards
    |--------------------------------------------------------------------------
    |
    | The guards that roles should be created for.
    |
    */

    'guards' => [
        'web',
        'sanctum',
    ],

];
