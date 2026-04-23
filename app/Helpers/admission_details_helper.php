<?php

/**
 * Admission details (การรับสมัคร) — stored as JSON in program_pages.admission_details_json
 *
 * Structure:
 *   {
 *     plan_seats:   string,                       // "30 คน"
 *     requirements: {                             // คุณสมบัติผู้เข้าเรียน (8 fields)
 *       study_plan, mor_kor_2_url, english_grade, selection_criteria,
 *       tuition_per_term, duration, credits_note, program_type
 *     },
 *     supports: {                                 // สิ่งสนับสนุนการเรียน (default true ทั้งหมด)
 *       scholarship, first_term_loan, ksl_loan, study_scholarship,
 *       entrepreneur_fund, dormitory
 *     }
 *   }
 */

if (! function_exists('admission_details_requirement_keys')) {
    /** @return list<string> */
    function admission_details_requirement_keys(): array
    {
        return [
            'study_plan', 'mor_kor_2_url', 'english_grade', 'selection_criteria',
            'tuition_per_term', 'duration', 'credits_note', 'program_type',
        ];
    }
}

if (! function_exists('admission_details_support_keys')) {
    /** @return list<string> */
    function admission_details_support_keys(): array
    {
        return [
            'scholarship', 'first_term_loan', 'ksl_loan',
            'study_scholarship', 'entrepreneur_fund', 'dormitory',
        ];
    }
}

if (! function_exists('admission_details_default_structure')) {
    /**
     * Default structure — supports ทั้ง 6 เป็น true, text ทั้งหมดว่าง
     */
    function admission_details_default_structure(): array
    {
        $req = [];
        foreach (admission_details_requirement_keys() as $k) {
            $req[$k] = '';
        }
        $sup = [];
        foreach (admission_details_support_keys() as $k) {
            $sup[$k] = true;
        }

        return [
            'plan_seats'   => '',
            'requirements' => $req,
            'supports'     => $sup,
        ];
    }
}

if (! function_exists('admission_details_decode')) {
    /**
     * Decode raw DB value → structured array พร้อม default สำหรับ key ที่ขาด
     *
     * @param string|array|null $raw
     */
    function admission_details_decode($raw): array
    {
        $default = admission_details_default_structure();
        if ($raw === null || $raw === '') {
            return $default;
        }
        $data = is_array($raw) ? $raw : json_decode((string) $raw, true);
        if (! is_array($data)) {
            return $default;
        }

        $out               = $default;
        $out['plan_seats'] = isset($data['plan_seats']) ? (string) $data['plan_seats'] : '';

        if (isset($data['requirements']) && is_array($data['requirements'])) {
            foreach (admission_details_requirement_keys() as $k) {
                if (array_key_exists($k, $data['requirements'])) {
                    $out['requirements'][$k] = (string) $data['requirements'][$k];
                }
            }
        }

        if (isset($data['supports']) && is_array($data['supports'])) {
            foreach (admission_details_support_keys() as $k) {
                if (array_key_exists($k, $data['supports'])) {
                    $out['supports'][$k] = (bool) $data['supports'][$k];
                }
            }
        }

        return $out;
    }
}

if (! function_exists('admission_details_normalize')) {
    /**
     * Normalize input (array|string|null) → JSON string พร้อม clamp ความยาวและตรวจ URL
     *
     * @param mixed                  $raw
     * @param array<int,string>|null $errors (passed by ref — แก้ผลข้างเคียง)
     */
    function admission_details_normalize($raw, ?array &$errors = null): string
    {
        if ($errors === null) {
            $errors = [];
        }
        $decoded = admission_details_decode($raw);

        // clamp lengths
        $decoded['plan_seats'] = mb_substr($decoded['plan_seats'], 0, 200);
        foreach (admission_details_requirement_keys() as $k) {
            $decoded['requirements'][$k] = mb_substr((string) $decoded['requirements'][$k], 0, 500);
        }

        // validate mor_kor_2_url
        $url = trim($decoded['requirements']['mor_kor_2_url']);
        if ($url !== '' && ! preg_match('~^https?://~i', $url)) {
            $errors[] = 'admission_details.requirements.mor_kor_2_url: ต้องขึ้นต้นด้วย http:// หรือ https://';
            $decoded['requirements']['mor_kor_2_url'] = '';
        }

        return (string) json_encode($decoded, JSON_UNESCAPED_UNICODE);
    }
}
