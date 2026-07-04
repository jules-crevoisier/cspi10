<?php
declare(strict_types=1);

namespace App\Core;

use Composer\CaBundle\CaBundle;

/**
 * Requêtes HTTP sortantes avec certificats CA (compatible Windows local + Docker).
 */
final class HttpClient
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     * @return array{http_code: int, body: string, error: string}
     */
    public static function postJson(string $url, array $payload, array $headers): array
    {
        if (!function_exists('curl_init')) {
            return ['http_code' => 0, 'body' => '', 'error' => 'Extension curl absente'];
        }

        $ch = curl_init();
        if ($ch === false) {
            return ['http_code' => 0, 'body' => '', 'error' => 'curl_init a échoué'];
        }

        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = $name . ': ' . $value;
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CAINFO => CaBundle::getBundledCaBundlePath(),
        ]);

        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'http_code' => $httpCode,
            'body' => is_string($body) ? $body : '',
            'error' => $error,
        ];
    }
}
