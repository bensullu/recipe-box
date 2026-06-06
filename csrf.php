<?php
// CSRF token helpers - generate, render and verify random per-form tokens

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Returns a CSRF token for the given form key, creating one if missing.
function csrf_token(string $formKey): string {
    if (empty($_SESSION["csrf_tokens"][$formKey])) {
        $_SESSION["csrf_tokens"][$formKey] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_tokens"][$formKey];
}

// Echoes a hidden input that carries the CSRF token. Place inside <form>.
function csrf_field(string $formKey): void {
    $token = csrf_token($formKey);
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, "UTF-8") . '">';
}

// Verifies the submitted token against the one stored in the session.
function csrf_verify(string $formKey, ?string $submitted): bool {
    $stored = $_SESSION["csrf_tokens"][$formKey] ?? null;
    if ($stored === null || $submitted === null || !is_string($submitted)) {
        return false;
    }
    $ok = hash_equals($stored, $submitted);
    if (!$ok) {
        unset($_SESSION["csrf_tokens"][$formKey]);
    }
    return $ok;
}

// Removes the token after a successful single-use action.
function csrf_consume(string $formKey): void {
    unset($_SESSION["csrf_tokens"][$formKey]);
}
