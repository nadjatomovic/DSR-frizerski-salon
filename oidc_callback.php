<?php
session_start();
require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/oidc_config.php';

// error handling minimal
if (!isset($_GET['state'], $_GET['code'])) {
  header("Location: login.php?err=oidc_missing_params");
  exit;
}
if (!isset($_SESSION['oidc_state']) || $_GET['state'] !== $_SESSION['oidc_state']) {
  header("Location: login.php?err=oidc_bad_state");
  exit;
}

// Exchange code -> tokens
$code = $_GET['code'];

$postData = http_build_query([
  'client_id' => OIDC_CLIENT_ID,
  'client_secret' => OIDC_CLIENT_SECRET,
  'code' => $code,
  'redirect_uri' => OIDC_REDIRECT_URI,
  'grant_type' => 'authorization_code'
]);

$ch = curl_init(OIDC_TOKEN_ENDPOINT);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => $postData,
  CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
]);
$tokenResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode < 200 || $httpCode >= 300) {
  header("Location: login.php?err=oidc_token_exchange");
  exit;
}

$tokens = json_decode($tokenResponse, true);
$idToken = $tokens['id_token'] ?? null;
if (!$idToken) {
  header("Location: login.php?err=oidc_no_id_token");
  exit;
}

// Validate + extract identity (simple way for project)
$infoJson = file_get_contents('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken));
$info = json_decode($infoJson, true);

if (!$info || ($info['aud'] ?? '') !== OIDC_CLIENT_ID) {
  header("Location: login.php?err=oidc_bad_token");
  exit;
}

$email = $info['email'] ?? null;
$givenName = $info['given_name'] ?? '';
$familyName = $info['family_name'] ?? '';
$fullName = $info['name'] ?? '';

if (!$email) {
  header("Location: login.php?err=oidc_no_email");
  exit;
}

// --------------
// DB: find by email
// --------------
$stmt = $conn->prepare("SELECT uporabnik_id, ime, priimek, email, tip_id AS tip
FROM Uporabnik
WHERE email=?
LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows === 1) {
  // exists -> login (tip can be 1 or 2)
  $user = $res->fetch_assoc();
  $stmt->close();
} else {
  $stmt->close();

  // not exists -> create as customer tip=2
  $ime = $givenName ?: ($fullName ?: 'Google');
  $priimek = $familyName ?: 'User';
  $telefon = '000000000';
  $geslo = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
  $tipStranka = 2; // STRANKA

  $ins = $conn->prepare("
    INSERT INTO Uporabnik (ime, priimek, telefon, email, geslo, tip_id)
    VALUES (?, ?, ?, ?, ?, ?)
");
$ins->bind_param(
    "sssssi",
    $ime,
    $priimek,
    $telefon,
    $email,
    $geslo,
    $tipStranka
);
$ins->execute();
$newId = $ins->insert_id;
$ins->close();

$user = [
    'uporabnik_id' => $newId,
    'ime' => $ime,
    'priimek' => $priimek,
    'email' => $email,
    'tip' => $tipStranka
];
}

// set session like your app (prilagodi ključeve ako treba)
$_SESSION['user'] = [
  'id' => (int)$user['uporabnik_id'],
  'ime' => $user['ime'],
  'priimek' => $user['priimek'] ?? '',
  'email' => $user['email'],
  'tip' => (int)$user['tip'],
];

unset($_SESSION['oidc_state']);

// Redirect by role
if ((int)$user['tip'] === 1) {
  header("Location: index.php");
} else {
  header("Location: index.php");
}
exit;
