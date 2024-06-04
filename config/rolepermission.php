<?php

$config = [
    'dashboard' => array('read'),
    'users_accounts' => array('create', 'read', 'update'),
    'package' => array('create', 'read', 'update'),
    'about_us' => array('read', 'update'),
    'privacy_policy' => array('read', 'update'),
    'terms_condition' => array('read', 'update'),
    'firebase_setting' => array('read', 'update'),
    'app_setting' => array('read', 'update'),
    'advertisement' => array('read', 'update'),
    'web_setting' => array('read', 'update'),
    'language' => array('create', 'read', 'update', 'delete'),
    'facility' => array('create', 'read', 'update', 'delete'),
    'near_by_places' => array('create', 'read', 'update', 'delete'),
    'seo_setting' => array('create', 'read', 'update', 'delete'),
    'customer' => array('create', 'read', 'update', 'delete'),
    'slider' => array('create', 'read', 'update', 'delete'),
    'categories' => array('create', 'read', 'update', 'delete'),
    'property' => array('create', 'read', 'update', 'delete'),
    'notification' => array('read', 'update', 'delete'),
];
return $config;
