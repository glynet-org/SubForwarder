<?php
if ((isset($_SERVER['HTTP_USER_AGENT']) and empty($_SERVER['HTTP_USER_AGENT'])) or !isset($_SERVER['HTTP_USER_AGENT'])){
    http_response_code(403);
    exit("<h2>Access Denied</h2></br>You don't have permission to view this site.</br>Error code:403 forbidden");
}

$isTextHTML = str_contains(($_SERVER['HTTP_ACCEPT'] ?? ''), 'text/html');
const CONSOLE_URL = 'https://console.glynet.org';
$URL = CONSOLE_URL . ($_SERVER['SCRIPT_URL'] ?? '');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, $isTextHTML ? ['Accept: text/html'] : []);

$data = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_error($ch)) {
    http_response_code($code);
    die('Error !');
}
curl_close($ch);

$headers = get_headers_from_curl_response($data);

if (!$isTextHTML && (empty($headers) || $code !== 200)) {
    http_response_code($code);
    die('Error !');
}

foreach ($headers as $key => $value) {
    header("$key: $value");
}

function get_headers_from_curl_response(string &$response): array
{
    $headers = [];
    $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

    foreach (explode("\r\n", $header_text) as $i => $line) {
        if ($i === 0) continue;
        if (!str_contains($line, ': ')) continue;
        [$key, $value] = explode(': ', $line, 2);
        $headers[strtolower(trim($key))] = trim($value);
    }

    $response = trim(str_replace($header_text, '', $response));
    return $headers;
}

echo $data;
