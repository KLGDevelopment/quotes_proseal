<?php

return [
    'use_teams' => true,
    'teams' => [
        'team_model' => App\Models\Profile::class,
        'teams_table' => 'profiles',
        'team_foreign_key' => 'profile_id',
    ],
    'role' => [
        'role_model' => App\Models\Role::class,
        'roles_table' => 'roles',
    ],
    'user_models' => [
        'users' => App\Models\User::class,
    ],
    'tables' => [
        'role_user' => 'role_user',
        'profile_user' => 'profile_user',
        'profile_role' => 'profile_role',
    ],
];
