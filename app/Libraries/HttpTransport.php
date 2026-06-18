<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * HTTP GET / POST (body เป็น string เช่น JSON) ผ่าน CI4 CURLRequest
 * ต้องมี ext-curl (มาตรฐานบน production); ผู้เรียกที่อยากกันกรณีไม่มี curl เช็คด้วย curlAvailable()
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

        return \Config\Services::curlrequest([
            'timeout'     => (int) $clientConfig['timeout'],
            'http_errors' => (bool) $clientConfig['http_errors'],
        ])->get($url, array_intersect_key($requestOptions, ['headers' => true]));
    }

    /**
     * @param array{timeout?:int,http_errors?:bool}     $clientConfig
     * @param array{headers?:array<string,string>,body?:string|null} $requestOptions
     */
    public static function post(string $url, array $clientConfig = [], array $requestOptions = []): object
    {
        $clientConfig = array_merge(['timeout' => 30, 'http_errors' => true], $clientConfig);

        return \Config\Services::curlrequest([
            'timeout'     => (int) $clientConfig['timeout'],
            'http_errors' => (bool) $clientConfig['http_errors'],
        ])->post($url, array_intersect_key($requestOptions, ['headers' => true, 'body' => true]));
    }
}
