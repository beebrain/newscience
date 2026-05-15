<?php

namespace App\Libraries;

use Config\Certificate as CertificateConfig;
use setasign\Fpdi\Fpdi;

if (! class_exists('FPDF', false)) {
    $fpdfBootstrap = ROOTPATH . 'vendor/setasign/fpdf/fpdf.php';
    if (is_file($fpdfBootstrap)) {
        require_once $fpdfBootstrap;
    }
}

class CertPdfGenerator
{
    protected CertificateConfig $config;
    protected CertQrGenerator $qrGenerator;

    /** Absolute path with trailing directory separator (FPDF AddFont) */
    protected static function certPdfFontDir(): string
    {
        return rtrim(APPPATH . 'Fonts' . DIRECTORY_SEPARATOR . 'cert_pdf', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    protected static function thaiFontDefinitionsPresent(): bool
    {
        $dir = self::certPdfFontDir();

        return is_file($dir . 'THSarabunNew.php');
    }

    public function __construct()
    {
        $this->config      = config(CertificateConfig::class);
        $this->qrGenerator = new CertQrGenerator();
    }

    /**
     * ลงทะเบียน TH Sarabun (จาก app/Fonts/cert_pdf — สร้างด้วย vendor/setasign/fpdf/makefont)
     */
    protected function registerThaiFonts(Fpdi $pdf): void
    {
        $dir = self::certPdfFontDir();
        if (! is_file($dir . 'THSarabunNew.php')) {
            log_message('error', 'CertPdfGenerator: Missing font definition ' . $dir . 'THSarabunNew.php');

            return;
        }
        $pdf->AddFont('THSarabun', '', 'THSarabunNew.php', $dir);
        if (is_file($dir . 'THSarabunNew_Bold.php')) {
            $pdf->AddFont('THSarabun', 'B', 'THSarabunNew_Bold.php', $dir);
        }
    }

    /**
     * FPDF ใช้ encoding iso-8859-11 ตามไฟล์ฟอนต์ที่ generate — แปลงจาก UTF-8
     */
    protected function encodeTextForThaiFont(string $utf8): string
    {
        if ($utf8 === '') {
            return '';
        }
        if (function_exists('iconv')) {
            $conv = @iconv('UTF-8', 'ISO-8859-11//IGNORE', $utf8);
            if ($conv !== false && $conv !== '') {
                return $conv;
            }
        }
        if (function_exists('mb_convert_encoding')) {
            $conv = @mb_convert_encoding($utf8, 'ISO-8859-11', 'UTF-8');
            if ($conv !== false && $conv !== '') {
                return $conv;
            }
        }

        return $utf8;
    }

    /**
     * อ่าน metrics จาก FPDF หลัง SetFont (ใช้จำลอง MultiCell)
     *
     * @return array{cw: array<string, float|int>, cMargin: float, fontSize: float}|null
     */
    protected function readFpdiFontLayoutState(Fpdi $pdf): ?array
    {
        try {
            $ref = new \ReflectionObject($pdf);
            $pCf = $ref->getProperty('CurrentFont');
            $pCf->setAccessible(true);
            $current = $pCf->getValue($pdf);
            if (! is_array($current) || empty($current['cw']) || ! is_array($current['cw'])) {
                return null;
            }
            $pCm = $ref->getProperty('cMargin');
            $pCm->setAccessible(true);
            $pFs = $ref->getProperty('FontSize');
            $pFs->setAccessible(true);

            return [
                'cw'      => $current['cw'],
                'cMargin' => (float) $pCm->getValue($pdf),
                'fontSize'=> (float) $pFs->getValue($pdf),
            ];
        } catch (\Throwable $e) {
            log_message('debug', 'CertPdfGenerator readFpdiFontLayoutState: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * นับจำนวนบรรทัดที่ MultiCell จะสร้าง (logic สอดคล้อง setasign/fpdf MultiCell, align ไม่ใช่ J)
     */
    protected function countMulticellLines(Fpdi $pdf, float $w, string $txt): int
    {
        $state = $this->readFpdiFontLayoutState($pdf);
        if ($state === null || $w <= 0) {
            return 1;
        }
        $cw      = $state['cw'];
        $cMargin = $state['cMargin'];
        $fontSize = $state['fontSize'];
        $wmax    = ($w - 2 * $cMargin) * 1000 / $fontSize;

        $s  = str_replace("\r", '', (string) $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] === "\n") {
            $nb--;
        }

        $sep = -1;
        $i   = 0;
        $j   = 0;
        $l   = 0;
        $ns  = 0;
        $nl  = 1;

        while ($i < $nb) {
            $c = $s[$i];
            if ($c === "\n") {
                $i++;
                $sep = -1;
                $j   = $i;
                $l   = 0;
                $ns  = 0;
                $nl++;
                continue;
            }
            if ($c === ' ') {
                $sep = $i;
                $ns++;
            }
            $l += (float) ($cw[$c] ?? 0);
            if ($l > $wmax) {
                if ($sep === -1) {
                    if ($i === $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j   = $i;
                $l   = 0;
                $ns  = 0;
                $nl++;
            } else {
                $i++;
            }
        }

        return $nl;
    }

    /**
     * เลือกขนาดฟอนต์ให้ข้อความพอดีกรอบ (กว้าง x สูง) โดยย่อจาก font_size ลงทีละขั้น
     *
     * @return array{fs: float, lineH: float, lines: int}
     */
    protected function resolveFontSizeForBox(
        Fpdi $pdf,
        string $fontFamily,
        string $fontStyle,
        string $drawText,
        float $bw,
        float $bh,
        float $maxFs,
        float $minFs,
        float $lineHeightFactor = 0.45
    ): array {
        $minFs = max(6.0, min($minFs, $maxFs));
        $maxFs = max($minFs, $maxFs);
        $step  = 0.5;

        for ($fs = $maxFs; $fs >= $minFs - 0.001; $fs -= $step) {
            $pdf->SetFont($fontFamily, $fontStyle, $fs);
            if ($this->readFpdiFontLayoutState($pdf) === null) {
                return ['fs' => $fs, 'lineH' => max(4.0, $fs * $lineHeightFactor), 'lines' => 1];
            }
            $lineH = max(4.0, $fs * $lineHeightFactor);
            $lines = $this->countMulticellLines($pdf, $bw, $drawText);
            if ($lines * $lineH <= $bh) {
                return ['fs' => $fs, 'lineH' => $lineH, 'lines' => $lines];
            }
        }

        $pdf->SetFont($fontFamily, $fontStyle, $minFs);
        $lineH = max(4.0, $minFs * $lineHeightFactor);

        return [
            'fs'    => $minFs,
            'lineH' => $lineH,
            'lines' => $this->countMulticellLines($pdf, $bw, $drawText),
        ];
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
        foreach (['signature_x', 'signature_y', 'qr_x', 'qr_y', 'qr_size', 'page_orientation'] as $k) {
            if (isset($layout[$k])) {
                $out[$k] = $layout[$k];
            }
        }
        // 'orientation' is the canonical key written by cert-layout-picker.js
        if (isset($layout['orientation']) && ! isset($out['page_orientation'])) {
            $out['page_orientation'] = $layout['orientation'];
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
        $landscape = strtolower(trim((string) ($effective['page_orientation'] ?? ''))) === 'landscape';
        if ($landscape) {
            $pdf->AddPage('L', 'A4');
            $pdf->Image($absoluteImagePath, 0, 0, 297, 210);
        } else {
            $pdf->AddPage();
            $pdf->Image($absoluteImagePath, 0, 0, 210, 297);
        }

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

        $this->registerThaiFonts($pdf);
        $hasThaiFont = self::thaiFontDefinitionsPresent();

        foreach ($fieldMapping as $field => $cfg) {
            if (! is_array($cfg)) {
                continue;
            }
            // ชื่อกิจกรรมไม่ต้องพิมพ์ทับใบรับรอง — มีอยู่บนแม่แบบรูปอยู่แล้ว
            if ($field === 'purpose') {
                continue;
            }
            $value = $this->getFieldValue((string) $field, $student, $request);
            if ($value !== null) {
                $x = (float) ($cfg['x'] ?? 0);
                $y = (float) ($cfg['y'] ?? 0);
                $fs = (float) ($cfg['font_size'] ?? 16);
                $bw = isset($cfg['box_w']) ? (float) $cfg['box_w'] : 0.0;
                $bh = isset($cfg['box_h']) ? (float) $cfg['box_h'] : 0.0;
                $minFs = isset($cfg['font_size_min']) ? (float) $cfg['font_size_min'] : 8.0;
                $fitBox = true;
                if (isset($cfg['fit_box'])) {
                    $fb = $cfg['fit_box'];
                    $fitBox = ! ($fb === false || $fb === 0 || $fb === '0' || $fb === 'false');
                }
                $cellAlign = isset($cfg['text_align']) ? strtoupper(substr((string) $cfg['text_align'], 0, 1)) : 'C';
                if (! in_array($cellAlign, ['L', 'C', 'R', 'J'], true)) {
                    $cellAlign = 'C';
                }
                if ($field === 'student_name') {
                    $value = trim((string) preg_replace('/\s+/u', ' ', str_replace(["\r", "\n", "\t"], ' ', (string) $value)));
                }
                $fontStyle = '';
                if (! empty($cfg['font_weight']) && strtolower((string) $cfg['font_weight']) === 'bold'
                    && is_file(self::certPdfFontDir() . 'THSarabunNew_Bold.php')) {
                    $fontStyle = 'B';
                }
                $fontFamily = $hasThaiFont ? 'THSarabun' : 'helvetica';
                $drawText     = $hasThaiFont ? $this->encodeTextForThaiFont((string) $value) : (string) $value;

                if ($bw > 0 && $bh > 0 && $fitBox) {
                    // ให้ชื่อผู้รับใหญ่เท่ากรอบที่ผู้ใช้ลาก แล้วลด size ลงเองถ้าข้อความยาว
                    // resolveFontSizeForBox ใช้ lineHeightFactor 0.45 (mm per pt) — สลับค่าให้ fs สูงสุดเต็มกรอบ 1 บรรทัด
                    $maxFsForBox = ($field === 'student_name' && $bh > 0)
                        ? max($fs, $bh / 0.45)
                        : $fs;
                    $picked = $this->resolveFontSizeForBox(
                        $pdf,
                        $fontFamily,
                        $fontStyle,
                        $drawText,
                        $bw,
                        $bh,
                        $maxFsForBox,
                        $minFs
                    );
                    $pdf->SetFont($fontFamily, $fontStyle, $picked['fs']);
                    $lineH   = $picked['lineH'];
                    $totalH  = $picked['lines'] * $lineH;
                    $offsetY = max(0.0, ($bh - $totalH) / 2);
                    $pdf->SetXY($x, $y + $offsetY);
                    $pdf->MultiCell($bw, $lineH, $drawText, 0, $cellAlign, false);
                } elseif ($bw > 0 && $bh > 0) {
                    $pdf->SetFont($fontFamily, $fontStyle, $fs);
                    $lineH = max(5.0, $fs * 0.45);
                    $pdf->SetXY($x, $y);
                    $pdf->MultiCell($bw, $lineH, $drawText, 0, $cellAlign, false);
                } else {
                    $pdf->SetFont($fontFamily, $fontStyle, $fs);
                    $pdf->SetXY($x, $y);
                    $pdf->Cell(0, 0, $drawText, 0, 0, 'L');
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

        // PNG โดยตรง (หลีกเลี่ยง SVG + DOMDocument; chillerlan v5 ค่าเริ่มต้น outputBase64 ทำให้ SVG ไม่ใช่ XML)
        $qrPx   = max(120, min(800, (int) (($effective['qr_size'] ?? 40) * 8)));
        $qrPng  = $this->qrGenerator->generatePng($verificationToken, $qrPx);
        $qrPath = $this->saveTempQrPng($qrPng, $verificationToken);
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

    protected function saveTempQrPng(string $pngBinary, string $token): ?string
    {
        if ($pngBinary === '') {
            return null;
        }
        $safeToken = preg_replace('/[^A-Za-z0-9._-]+/', '_', $token);
        $path       = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'qr_' . $safeToken . '_' . bin2hex(random_bytes(4)) . '.png';

        if (@file_put_contents($path, $pngBinary) === false) {
            return null;
        }

        return $path;
    }
}
