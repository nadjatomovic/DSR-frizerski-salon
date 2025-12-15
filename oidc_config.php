<?php

if (!isset($_ENV['OIDC_CLIENT_ID'], $_ENV['OIDC_CLIENT_SECRET'], $_ENV['OIDC_REDIRECT_URI'])) {
    die('OIDC environment variables nisu postavljene (.env).');
}

define('OIDC_CLIENT_ID', $_ENV['OIDC_CLIENT_ID']);
define('OIDC_CLIENT_SECRET', $_ENV['OIDC_CLIENT_SECRET']);
define('OIDC_REDIRECT_URI', $_ENV['OIDC_REDIRECT_URI']);

define('OIDC_AUTH_ENDPOINT', 'https://accounts.google.com/o/oauth2/v2/auth');
define('OIDC_TOKEN_ENDPOINT', 'https://oauth2.googleapis.com/token');
define('OIDC_SCOPE', 'openid email profile');
