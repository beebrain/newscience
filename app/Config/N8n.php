<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * N8n integration for Barcode import.
 * Set in .env: n8n.baseUrl, n8n.apiKey (optional), n8n.webhookPath (optional)
 *
 * Expected JSON from N8n: { "barcodes": [ "code1", "code2", ... ] } or { "data": [ { "code": "..." }, ... ] }
 */
class N8n extends BaseConfig
{
    /** N8n instance base URL (no trailing slash) */
    public string $baseUrl = '';

    /** API key or token for outbound requests to N8n (optional) */
    public string $apiKey = '';

    /** Webhook path relative to baseUrl for fetching barcodes (e.g. "webhook/barcodes") */
    public string $webhookPath = '';

    /** Enable calling N8n to fetch barcode JSON */
    public bool $enabled = false;
}
