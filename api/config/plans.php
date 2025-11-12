<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SaaS Plans Configuration
    |--------------------------------------------------------------------------
    |
    | This file defines the available plans and their features for the SaaS
    | application. Each plan specifies pricing, deployment mode, and features.
    |
    */

    'starter' => [
        'name' => 'Starter',
        'price' => 29,
        'deployment_mode' => 'shared',
        'features' => [
            'max_users' => 5,
            'max_projects' => 10,
            'api_access' => false,
            'custom_domain' => false,
            'priority_support' => false,
            'advanced_analytics' => false,
        ],
        'limits' => [
            'storage_gb' => 10,
            'api_calls_per_month' => 1000,
        ],
    ],

    'professional' => [
        'name' => 'Professional',
        'price' => 99,
        'deployment_mode' => 'shared',
        'features' => [
            'max_users' => 20,
            'max_projects' => 50,
            'api_access' => true,
            'custom_domain' => false,
            'priority_support' => true,
            'advanced_analytics' => true,
        ],
        'limits' => [
            'storage_gb' => 50,
            'api_calls_per_month' => 10000,
        ],
    ],

    'enterprise' => [
        'name' => 'Enterprise',
        'price' => 499,
        'deployment_mode' => 'isolated',
        'features' => [
            'max_users' => -1, // unlimited
            'max_projects' => -1, // unlimited
            'api_access' => true,
            'custom_domain' => true,
            'priority_support' => true,
            'advanced_analytics' => true,
            'dedicated_account_manager' => true,
            'sla_guarantee' => true,
        ],
        'limits' => [
            'storage_gb' => 500,
            'api_calls_per_month' => -1, // unlimited
        ],
    ],
];
