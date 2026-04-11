<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * HTTP GET / POST (body เป็น string เช่น JSON) — ใช้ CI CURLRequest เมื่อมี ext-curl
 * ถ้าไม่มี php-curl ใช้ stream wrapper แทน เพื่อกัน fatal "Call to undefined function curl_exec"
 * แนะนำ production: ติดตั้งแพ็กเก์ php-curl (ประสิทธิภาพและรองรับ multipart ดีกว่า)
 */
final class HttpTransport
{
    public static function curlAvailable(): bool
    {
        return \function_exists('curl_exec');
    }

    /**
     * @param array{timeout?:int,http_errors?:bool} $clientConfig ส่งเข้า Services::curlrequest
     * @param array{headers?:array<string,string>}  $requestOptions
     */
    public static function get(string $url, array $clientConfig = [], array $requestOptions = []): object
    {
        $clientConfig = array_merge(['timeout' => 30, 'http_errors' => true], $clientConfig);

        if (self::curlAvailable()) {
            $client = \Config\Services::curlrequest([
                'timeout'     => (int) $clientConfig['timeout'],
                'http_errors' => (bool) $clientConfig['http_errors'],
            ]);

            return $client->get($url, array_intersect_key($requestOptions, ['headers' => true]));
        }

        return self::streamRequest('GET', $url, $clientConfig, $requestOptions);
    }

    /**
     * @param array{timeout?:int,http_errors?:bool}     $clientConfig
     * @param array{headers?:array<string,string>,body?:string|null} $requestOptions
     */
    public static function post(string $url, array $clientConfig = [], array $requestOptions = []): object
    {
        $clientConfig = array_merge(['timeout' => 30, 'http_errors' => true], $clientConfig);

        if (self::curlAvailable()) {
            $client = \Config\Services::curlrequest([
                'timeout'     => (int) $clientConfig['timeout'],
                'http_errors' => (bool) $clientConfig['http_errors'],
            ]);

            return $client->post($url, array_intersect_key($requestOptions, ['headers' => true, 'body' => true]));
        }

        return self::streamRequest('POST', $url, $clientConfig, $requestOptions);
    }

    /**
     * @param array{timeout?:int,http_errors?:bool}     $clientConfig
     * @param array{headers?:array<string,string>,body?:string|null} $requestOptions
     */
    private static function streamRequest(string $method, string $url, array $clientConfig, array $requestOptions): object
    {
        $timeout = (int) $clientConfig['timeout'];
        $headers = $requestOptions['headers'] ?? [];
        if (! isset($headers['User-Agent'])) {
            $headers['User-Agent'] = 'newScience/1 (stream; enable php-curl for best support)';
        }

        $lines = [];
        foreach ($headers as $k => $v) {
            $lines[] = $k . ': ' . $v;
        }
        $headerStr = implode("\r\n", $lines);

        $httpOpts = [
            'method'        => $method,
            'header'        => $headerStr,
            'timeout'       => $timeout,
            'ignore_errors' => true,
        ];
        if ($method === 'POST') {
            $httpOpts['content'] = (string) ($requestOptions['body'] ?? '');
        }

        $ctx = stream_context_create([
            'http' => $httpOpts,
            'ssl'  => [
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ],
        ]);

        $raw = @file_get_contents($url, false, $ctx);

        $code = 0;
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $h) {
                if (preg_match('#\AHTTP/\S+\s+(\d{3})#', $h, $m)) {
                    $code = (int) $m[1];
                }
            }
        }

        if ($raw === false) {
            $raw = '';
            if ($code < 100) {
                $code = 503;
            }
        } elseif ($code < 100 && $raw !== '') {
            $code = 200;
        }

        return new class ($code, (string) $raw) {
            public function __construct(private int $code, private string $raw) {}

            public function getStatusCode(): int
            {
                return $this->code;
            }

            public function getBody(): string
            {
                return $this->raw;
            }
        };
    }
}
