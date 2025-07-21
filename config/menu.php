<?php

return [
    // Navbar items:
    [
        'type' => 'navbar-search',
        'text' => 'buscar',
        'topnav_right' => true,
    ],
    [
        'type' => 'fullscreen-widget',
        'topnav_right' => true,
    ],
    
    // Sidebar items:
    [
        'type' => 'sidebar-menu-search',
        'text' => 'buscar',
    ],
    [
        'text' => 'blog',
        'url' => 'admin/blog',
        'can' => 'manage-blog',
    ],
    /*
    [
        'text' => 'pages',
        'url' => 'admin/pages',
        'icon' => 'far fa-fw fa-file',
        'label' => 4,
        'label_color' => 'success',
    ],

    */
    ['header' => 'odoo_masters'],
    [
        'text' => 'customers',
        'url' => 'odoo_masters/customers',
        'icon' => 'fab fas fa-building pr-1 text-left',
    ],
    [
        'text' => 'products',
        'url' => 'odoo_masters/products',
        'icon' => 'fab fa-fw  fa-product-hunt pr-1 text-left',
    ],
    ['header' => 'control_panel'],
    [
        'text' => 'users',
        'url' => 'admin/users',
        'icon' => 'fas pull-left fa-fw fa-users pr-1 text-left',
    ],
    
    [
        'text' => 'permissions',
        'url' => 'admin/permissions',
        'icon' => 'fas fa-lock-open pr-1 text-left',
    ],
    [
        'text' => 'roles',
        'url' => 'admin/roles',
        'icon' => 'fas fa-fw fa-user-tie pr-1 text-left',
    ],
    /*
    [
        'text' => 'multilevel',
        'icon' => 'fas fa-fw fa-share',
        'submenu' => [
            [
                'text' => 'level_one',
                'url' => '#',
            ],
            [
                'text' => 'level_one',
                'url' => '#',
                'submenu' => [
                    [
                        'text' => 'level_two',
                        'url' => '#',
                    ],
                    [
                        'text' => 'level_two',
                        'url' => '#',
                        'submenu' => [
                            [
                                'text' => 'level_three',
                                'url' => '#',
                            ],
                            [
                                'text' => 'level_three',
                                'url' => '#',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'text' => 'level_one',
                'url' => '#',
            ],
        ],
    ],
    */
];