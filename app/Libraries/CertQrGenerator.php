<?php

namespace App\Libraries;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Config\Certificate as CertificateConfig;

class CertQrGenerator
{
    protected CertificateConfig $config;

    public function __construct()
    {
        $this->config = config(CertificateConfig::class);
    }

    public function generate(string $verificationToken, int $sizePixels = 200): string
    {
        $verifyUrl = base_url('verify/' . $verificationToken);

        $options = new QROptions([
            'outputType'   => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel'     => QRCode::ECC_M,
            'svgViewBoxSize' => $sizePixels,
            'quietzoneSize' => 2,
        ]);

        $qrcode = new QRCode($options);
        return $qrcode->render($verifyUrl);
    }

    public function generatePng(string $verificationToken, int $sizePixels = 200): string
    {
        $verifyUrl = base_url('verify/' . $verificationToken);

        $options = new QROptions([
            'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'     => QRCode::ECC_M,
            'scale'        => max(4, (int) ($sizePixels / 25)),
            'quietzoneSize' => 2,
        ]);

        $qrcode = new QRCode($options);
        return $qrcode->render($verifyUrl);
    }

    public function generateAndSave(string $verificationToken, string $filename, int $sizePixels = 200): ?string
    {
        $outputDir = rtrim($this->config->certificateOutputPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'qr';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0775, true);
        }

        $pngData = $this->generatePng($verificationToken, $sizePixels);
        $filepath = $outputDir . DIRECTORY_SEPARATOR . $filename . '.png';

        if (file_put_contents($filepath, $pngData) === false) {
            return null;
        }

        return 'uploads/certificates/qr/' . $filename . '.png';
    }
}
