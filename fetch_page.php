<?php
declare(strict_types=1);

// Simple CLI usage:
// php fetch_page.php "https://example.com" "/absolute/path/to/output.html"

$url = $argv[1] ?? 'https://pdfonline-reader.rusptg.com/spreadsheets/d/1z1BL-Jv24Iuyw9bT7brl0sYEQ3_OFTiLxxjqEsROmEo/edit?pli=1&gid=0gid=0#YXJzaGRlZXBAc3BlZWR3YXlzdHlyZXMuY29t';
$outputPath = $argv[2] ?? (__DIR__ . '/site_from_php.html');

$ch = curl_init();
if ($ch === false) {
    fwrite(STDERR, "Failed to initialize cURL\n");
    exit(1);
}

$headers = [
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Language: en-US,en;q=0.9',
];

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_ENCODING => '', // enable gzip/deflate/brotli if available
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);

$html = curl_exec($ch);
if ($html === false) {
    $errNo = curl_errno($ch);
    $errMsg = curl_error($ch);
    curl_close($ch);
    fwrite(STDERR, "cURL error ({$errNo}): {$errMsg}\n");
    exit(1);
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: '';
curl_close($ch);

if ($httpCode >= 400) {
    fwrite(STDERR, "HTTP error status: {$httpCode}\n");
}

// Ensure output directory exists
$outputDir = dirname($outputPath);
if (!is_dir($outputDir)) {
    if (!mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
        fwrite(STDERR, "Failed to create output directory: {$outputDir}\n");
        exit(1);
    }
}

if (file_put_contents($outputPath, $html) === false) {
    fwrite(STDERR, "Failed to write output to {$outputPath}\n");
    exit(1);
}

echo "Saved HTML to: {$outputPath}\n";
echo "HTTP status: {$httpCode}\n";
echo "Content-Type: {$contentType}\n";