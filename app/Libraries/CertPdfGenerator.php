<?php

namespace App\Libraries;

use Config\Certificate as CertificateConfig;
use setasign\Fpdi\Fpdi;
use TCPDF;

class CertPdfGenerator
{
    protected CertificateConfig $config;
    protected CertQrGenerator $qrGenerator;

    public function __construct()
    {
        $this->config = config(CertificateConfig::class);
        $this->qrGenerator = new CertQrGenerator();
    }

    public function generate(array $request, array $template, array $student, string $verificationToken, ?string $signatureImagePath = null): ?string
    {
        $pdf = new Fpdi();

        $templatePath = $this->resolveTemplatePath($template['template_file']);
        if (!$templatePath || !file_exists($templatePath)) {
            return null;
        }

        $pdf->setSourceFile($templatePath);
        $tplId = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($tplId);

        $fieldMapping = json_decode($template['field_mapping'] ?? '{}', true);

        foreach ($fieldMapping as $field => $config) {
            $value = $this->getFieldValue($field, $student, $request);
            if ($value !== null) {
                $pdf->SetFont('THSarabun', '', $config['font_size'] ?? 16);
                $pdf->SetXY($config['x'], $config['y']);
                $pdf->Cell(0, 0, $value, 0, 0, 'L');
            }
        }

        if ($signatureImagePath && file_exists($signatureImagePath)) {
            $pdf->Image($signatureImagePath, $template['signature_x'], $template['signature_y'], 50, 25, 'PNG');
        }

        $qrSvg = $this->qrGenerator->generate($verificationToken, (int) $template['qr_size']);
        $qrPath = $this->saveTempQr($qrSvg, $verificationToken);
        if ($qrPath) {
            $pdf->Image($qrPath, $template['qr_x'], $template['qr_y'], $template['qr_size'], $template['qr_size'], 'PNG');
            @unlink($qrPath);
        }

        // Generate output path with year-based organization
        $dateStr = date('Ymd_His');
        $filename = 'CERT_' . $request['request_number'] . '_' . $dateStr . '.pdf';
        $year = date('Y');
        $outputDir = rtrim($this->config->certificateOutputPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $year;
        $outputPath = $outputDir . DIRECTORY_SEPARATOR . $filename;

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0775, true);
        }

        $pdf->Output($outputPath, 'F');

        return 'uploads/cert_system/certificates/' . $year . '/' . $filename;
    }

    public function hashFile(string $filepath): string
    {
        return hash_file('sha256', $filepath);
    }

    protected function resolveTemplatePath(?string $relative): ?string
    {
        if (!$relative) {
            return null;
        }

        // New path structure: uploads/cert_system/templates/YYYY/filename.pdf
        if (str_starts_with($relative, 'uploads/cert_system/')) {
            return FCPATH . $relative;
        }

        // Legacy path support: uploads/cert_templates/filename.pdf
        if (str_starts_with($relative, 'uploads/cert_templates/')) {
            $year = date('Y');
            $filename = basename($relative);
            $newPath = $this->config->templateUploadPath . $year . '/' . $filename;
            if (file_exists($newPath)) {
                return $newPath;
            }
            // Fallback to legacy location
            return FCPATH . $relative;
        }

        return $relative;
    }

    protected function getFieldValue(string $field, array $student, array $request): ?string
    {
        $map = [
            'student_name'    => ($student['th_name'] ?? '') . ' ' . ($student['thai_lastname'] ?? ''),
            'student_id'      => $student['login_uid'] ?? '',
            'program_name'    => $this->resolveProgramName($student),
            'request_number'  => $request['request_number'] ?? '',
            'purpose'         => $request['purpose'] ?? '',
            'date'            => date('d/m/Y'),
            'date_thai'       => $this->formatThaiDate(),
        ];
        return $map[$field] ?? null;
    }

    /**
     * Resolve program name from student data
     * Uses program_name if available, otherwise looks up from program_id
     */
    protected function resolveProgramName(array $student): string
    {
        // If program_name is already provided (e.g., from CSV import extra_data)
        if (!empty($student['program_name'])) {
            return $student['program_name'];
        }

        // If we have program_id, look up the name from database
        if (!empty($student['program_id'])) {
            try {
                $db = \Config\Database::connect();
                $program = $db->table('programs')
                    ->where('id', $student['program_id'])
                    ->select('name_th')
                    ->get()
                    ->getRow();

                if ($program && !empty($program->name_th)) {
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
        $path = $tmpDir . DIRECTORY_SEPARATOR . 'qr_' . $token . '.png';

        $dom = new \DOMDocument();
        $dom->loadXML($svgContent);

        $svg = $dom->documentElement;
        $width = $svg->getAttribute('width') ?: 200;
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
