<?php
// Configuration for Admin UI
return [
    'app_name' => getenv('APP_NAME') ?: 'Healthcare Management System',
    'app_url' => getenv('APP_URL') ?: 'http://localhost:8000',
    'admin_email' => getenv('ADMIN_EMAIL') ?: 'admin@healthcaresystem.com',
    'items_per_page' => getenv('ITEMS_PER_PAGE') ?: 20,
    'session_timeout' => getenv('SESSION_TIMEOUT') ?: 3600,
];