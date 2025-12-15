<?php
session_start();
require_once __DIR__ . '/oidc_config.php';

$state = bin2hex(random_bytes(16));
$_SESSION['oidc_state'] = $state;

$params = [
  'client_id' => OIDC_CLIENT_ID,
  'redirect_uri' => OIDC_REDIRECT_URI,
  'response_type' => 'code',
  'scope' => OIDC_SCOPE,
  'state' => $state,
  'prompt' => 'select_account'
];

header('Location: ' . OIDC_AUTH_ENDPOINT . '?' . http_build_query($params));
exit;
