<?php

function goraTabSessionContext(): array
{
    $requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    $requestPath = is_string($requestPath) ? $requestPath : '/';
    if (preg_match('~\A(.*)/__tab/([a-f0-9]{32})/(?:.*)\z~', $requestPath, $matches) === 1) {
        $appPath = $matches[1] !== '' ? $matches[1] : '/';
        return [$appPath, $matches[2]];
    }

    $rewrittenTabId = (string) (
        $_SERVER['REDIRECT_GORA_TAB_ID']
        ?? $_SERVER['REDIRECT_REDIRECT_GORA_TAB_ID']
        ?? $_SERVER['GORA_TAB_ID']
        ?? ''
    );
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if (preg_match('~\A(.*)/__tab/([a-f0-9]{32})(?:/|\z)~', $scriptName, $scriptMatches) === 1) {
        $appPath = $scriptMatches[1] !== '' ? $scriptMatches[1] : '/';
        if (preg_match('/\A[a-f0-9]{32}\z/', $rewrittenTabId) !== 1) {
            $rewrittenTabId = $scriptMatches[2];
        }
    } else {
        $appPath = rtrim(dirname($scriptName), '/.');
        if ($appPath === '') {
            $appPath = '/';
        }
    }

    if (preg_match('/\A[a-f0-9]{32}\z/', $rewrittenTabId) === 1) {
        return [$appPath, $rewrittenTabId];
    }

    if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
        $tabId = bin2hex(random_bytes(16));
        $relativePath = ltrim(substr($requestPath, strlen(rtrim($appPath, '/'))), '/');
        if ($relativePath === '') {
            $relativePath = 'index.php';
        }
        $query = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_QUERY);
        $location = rtrim($appPath, '/') . '/__tab/' . $tabId . '/' . $relativePath;
        if ($query !== '') {
            $location .= '?' . $query;
        }

        header('Location: ' . $location, true, 302);
        exit;
    }

    http_response_code(400);
    exit('La sesión de esta pestaña no es válida. Recarga la página e inténtalo nuevamente.');
}

function goraStartTabSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    [$appPath, $tabId] = goraTabSessionContext();
    $cookiePath = rtrim($appPath, '/') . '/__tab/' . $tabId . '/';
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';

    session_name('GORA' . $tabId);
    session_set_cookie_params(0, $cookiePath, '', $isSecure, true);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    session_cache_limiter('nocache');
    session_start();
}
