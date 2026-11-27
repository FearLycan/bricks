<?php
return [
    'adminEmail' => 'admin@example.com',
    // Admin read-only SQL endpoint (admin/console/db-query). Enable in params-local.php.
    'dbQuery.enabled'   => false,
    // Admin console runner and deploy (admin/console/*). Enable in params-local.php;
    // phpBinary = CLI php when web runs under php-fpm (e.g. /usr/local/bin/php83).
    'console.enabled'   => false,
    'console.phpBinary' => '',
    'console.gitBinary' => '',
];
