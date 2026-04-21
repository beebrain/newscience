<?php

namespace App\Libraries;

use Config\Certificate as CertificateConfig;
use setasign\Fpdi\Fpdi;

class CertPdfGenerator
{
    protected CertificateConfig $config;
    protected CertQrGenerator $qrGenerator;

    public function __construct()
    {
        $this->config      = config(CertificateConfig::class);
        $this->qrGenerator = new CertQrGenerator();
    }

    /**
     * @param array      $request         request_number, purpose
     * @param array      $template        row from cert_templates
     * @param array      $student         tf_name, tl_name, ...
     * @param array|null $event           row from cert_events (optional background_file, background_kind, layout_json)
     */
    public function generate(
        array $request,
        array $template,
        array $student,
        string $verificationToken,
        ?string $signatureImagePath = null,
        ?array $event = null
    ): ?string {
        $effective = $this->effectiveTemplateForEvent($template, $event ?? []);

        $bgKind = isset($event['background_kind']) ? strtolower((string) $event['background_kind']) : '';
        $bgFile = isset($event['background_file']) ? trim((string) $event['background_file']) : '';

        if ($bgKind !== '' && $bgFile !== '') {
            $absBg = $this->resolveEventBackgroundPath($bgFile);
            if ($absBg && is_file($absBg)) {
                if ($bgKind === 'image') {
                    return $this->generateWithImageBackground(
                        $absBg,
                        $request,
                        $effective,
                        $student,
                        $verificationToken,
                        $signatureImagePath
                    );
                }
                if ($bgKind === 'pdf') {
                    return $this->generateWithPdfBackground(
                        $absBg,
                        $request,
                        $effective,
                        $student,
                        $verificationToken,
                        $signatureImagePath
                    );
                }
            }
        }

        $pdf = new Fpdi();

        $templatePath = $this->resolveTemplatePath($template['template_file']);
        if (! $templatePath || ! file_exists($templatePath)) {
            return null;
        }

        $pdf->setSourceFile($templatePath);
        $tplId = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($tplId);

        $this->drawOverlays($pdf, $effective, $student, $request, $verificationToken, $signatureImagePath);

        return $this->writeOutputPdf($pdf, $request['request_number'] ?? 'CERT');
    }

    public function hashFile(string $filepath): string
    {
        return hash_file('sha256', $filepath);
    }

    /**
     * @param array $template base cert_templates row
     * @param array $event    cert_events row (may contain layout_json)
     *
     * @return array merged row (field_mapping as JSON string for decode in drawOverlays)
     */
    public function effectiveTemplateForEvent(array $template, array $event): array
    {
        $out = $template;
        $raw = $event['layout_json'] ?? null;
        if (! is_string($raw) || trim($raw) === '') {
            return $out;
        }
        $layout = json_decode($raw, true);
        if (! is_array($layout)) {
            return $out;
        }
        if (! empty($layout['field_mapping']) && is_array($layout['field_mapping'])) {
            $baseMap = json_decode($out['field_mapping'] ?? '{}', true);
            if (! is_array($baseMap)) {
                $baseMap = [];
            }
            $merged = array_merge($baseMap, $layout['field_mapping']);
            $out['field_mapping'] = json_encode($merged, JSON_UNESCAPED_UNICODE);
        }
        foreach (['signature_x', 'signature_y', 'qr_x', 'qr_y', 'qr_size'] as $k) {
            if (isset($layout[$k])) {
                $out[$k] = $layout[$k];
            }
        }

        return $out;
    }

    protected function generateWithPdfBackground(
        string $absolutePdfPath,
        array $request,
        array $effective,
        array $student,
        string $verificationToken,
        ?string $signatureImagePath
    ): ?string {
        $pdf = new Fpdi();
        try {
            $pdf->setSourceFile($absolutePdfPath);
            $tplId = $pdf->importPage(1);
            $pdf->AddPage();
            $pdf->useTemplate($tplId);
        } catch (\Throwable $e) {
            log_message('error', 'CertPdfGenerator PDF background: ' . $e->getMessage());

            return null;
        }

        $this->drawOverlays($pdf, $effective, $student, $request, $verificationToken, $signatureImagePath);

        return $this->writeOutputPdf($pdf, $request['request_number'] ?? 'CERT');
    }

    protected function generateWithImageBackground(
        string $absoluteImagePath,
        array $request,
        array $effective,
        array $student,
        string $verificationToken,
        ?string $signatureImagePath
    ): ?string {
        $pdf = new Fpdi();
        $pdf->AddPage();
        $pdf->Image($absoluteImagePath, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0, true);

        $this->drawOverlays($pdf, $effective, $student, $request, $verificationToken, $signatureImagePath);

        return $this->writeOutputPdf($pdf, $request['request_number'] ?? 'CERT');
    }

    protected function drawOverlays(
        Fpdi $pdf,
        array $effective,
        array $student,
        array $request,
        string $verificationToken,
        ?string $signatureImagePath
    ): void {
        $fieldMapping = json_decode($effective['field_mapping'] ?? '{}', true);
        if (! is_array($fieldMapping)) {
            $fieldMapping = [];
        }

        foreach ($fieldMapping as $field => $cfg) {
            if (! is_array($cfg)) {
                continue;
            }
            $value = $this->getFieldValue((string) $field, $student, $request);
            if ($value !== null) {
                $x = (float) ($cfg['x'] ?? 0);
                $y = (float) ($cfg['y'] ?? 0);
                $fs = (float) ($cfg['font_size'] ?? 16);
                $bw = isset($cfg['box_w']) ? (float) $cfg['box_w'] : 0.0;
                $bh = isset($cfg['box_h']) ? (float) $cfg['box_h'] : 0.0;
                if ($field === 'student_name') {
                    $value = trim((string) preg_replace('/\s+/u', ' ', str_replace(["\r", "\n", "\t"], ' ', (string) $value)));
                    $pdf->setRTL(false);
                }
                $pdf->SetFont('THSarabun', '', $fs);
                if ($bw > 0 && $bh > 0) {
                    $pdf->MultiCell($bw, 0, (string) $value, 0, 'C', false, 0, $x, $y, true, 0, false, true, $bh, 'M');
                } else {
                    $pdf->SetXY($x, $y);
                    $pdf->Cell(0, 0, (string) $value, 0, 0, 'L');
                }
            }
        }

        if ($signatureImagePath && file_exists($signatureImagePath)) {
            $pdf->Image(
                $signatureImagePath,
                (float) ($effective['signature_x'] ?? 0),
                (float) ($effective['signature_y'] ?? 0),
                50,
                25,
                'PNG'
            );
        }

        $qrSvg = $this->qrGenerator->generate($verificationToken, (int) ($effective['qr_size'] ?? 40));
        $qrPath = $this->saveTempQr($qrSvg, $verificationToken);
        if ($qrPath) {
            $pdf->Image(
                $qrPath,
                (float) ($effective['qr_x'] ?? 0),
                (float) ($effective['qr_y'] ?? 0),
                (float) ($effective['qr_size'] ?? 40),
                (float) ($effective['qr_size'] ?? 40),
                'PNG'
            );
            @unlink($qrPath);
        }
    }

    protected function writeOutputPdf(Fpdi $pdf, string $requestNumber): string
    {
        $dateStr   = date('Ymd_His');
        $filename  = 'CERT_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $requestNumber) . '_' . $dateStr . '.pdf';
        $year      = date('Y');
        $outputDir = rtrim($this->config->certificateOutputPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $year;
        $outputPath = $outputDir . DIRECTORY_SEPARATOR . $filename;

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0775, true);
        }

        $pdf->Output($outputPath, 'F');

        return 'uploads/cert_system/certificates/' . $year . '/' . $filename;
    }

    protected function resolveEventBackgroundPath(string $relative): ?string
    {
        $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($relative));
        if ($relative === '') {
            return null;
        }
        if (str_starts_with($relative, 'writable' . DIRECTORY_SEPARATOR) || str_starts_with($relative, 'writable/')) {
            return ROOTPATH . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $relative), DIRECTORY_SEPARATOR);
        }

        return null;
    }

    protected function resolveTemplatePath(?string $relative): ?string
    {
        if (! $relative) {
            return null;
        }

        if (str_starts_with($relative, 'uploads/cert_system/')) {
            return FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        }

        if (str_starts_with($relative, 'uploads/cert_templates/')) {
            $year     = date('Y');
            $filename = basename($relative);
            $newPath  = $this->config->templateUploadPath . $year . '/' . $filename;
            if (file_exists($newPath)) {
                return $newPath;
            }

            return FCPATH . $relative;
        }

        return $relative;
    }

    protected function getFieldValue(string $field, array $student, array $request): ?string
    {
        $map = [
            'student_name'   => trim(($student['tf_name'] ?? '') . ' ' . ($student['tl_name'] ?? '')),
            'student_id'     => $student['login_uid'] ?? '',
            'program_name'   => $this->resolveProgramName($student),
            'request_number' => $request['request_number'] ?? '',
            'purpose'        => $request['purpose'] ?? '',
            'date'           => date('d/m/Y'),
            'date_thai'      => $this->formatThaiDate(),
        ];

        return $map[$field] ?? null;
    }

    protected function resolveProgramName(array $student): string
    {
        if (! empty($student['program_name'])) {
            return $student['program_name'];
        }

        if (! empty($student['program_id'])) {
            try {
                $db = \Config\Database::connect();
                $program = $db->table('programs')
                    ->where('id', $student['program_id'])
                    ->select('name_th')
                    ->get()
                    ->getRow();

                if ($program && ! empty($program->name_th)) {
                    return $program->name_th;
                }
            } catch (\Exception $e) {
                log_message('error', 'CertPdfGenerator: Failed to resolve program name: ' . $e->getMessage());
            }
        }

        return '';
    }

    protected function formatThaiDate(): string
    {
        $months = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        $d = (int) date('j');
        $m = $months[(int) date('n') - 1];
        $y = (int) date('Y') + 543;

        return "{$d} {$m} {$y}";
    }

    protected function saveTempQr(string $svgContent, string $token): ?string
    {
        $tmpDir = sys_get_temp_dir();
        $path   = $tmpDir . DIRECTORY_SEPARATOR . 'qr_' . $token . '.png';

        $dom = new \DOMDocument();
        $dom->loadXML($svgContent);

        $svg    = $dom->documentElement;
        $width  = $svg->getAttribute('width') ?: 200;
        $height = $svg->getAttribute('height') ?: 200;

        $image = imagecreatetruecolor((int) $width, (int) $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);

        $black = imagecolorallocate($image, 0, 0, 0);

        $rects = $dom->getElementsByTagName('rect');
        foreach ($rects as $rect) {
            $x = (float) $rect->getAttribute('x');
            $y = (float) $rect->getAttribute('y');
            $w = (float) $rect->getAttribute('width');
            $h = (float) $rect->getAttribute('height');
            if ($w > 0 && $h > 0 && $rect->getAttribute('fill') !== 'none') {
                imagefilledrectangle($image, (int) $x, (int) $y, (int) ($x + $w), (int) ($y + $h), $black);
            }
        }

        imagepng($image, $path);
        imagedestroy($image);

        return $path;
    }
}
