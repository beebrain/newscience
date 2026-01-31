<?php
/**
 * ดึงข้อมูลบุคลากรจาก sci.uru.ac.th ตามหลักสูตร (ucurriculum_id)
 * เก็บลงตาราง personnel และ map กับ department_id
 *
 * Usage: php scripts/fetch_personnel_by_curriculum.php
 */

$baseUrl = 'https://sci.uru.ac.th';
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'newscience';

// ucurriculum_id จากเว็บ sci.uru.ac.th -> ชื่อหลักสูตร -> department_id ใน project
$curriculumToDept = [
    1  => ['name' => 'ผู้บริหาร',           'department_id' => 1],  // สำนักงานคณบดี
    2  => ['name' => 'คณิตศาสตร์',          'department_id' => 2],  // สาขาวิชาคณิตศาสตร์ประยุกต์
    3  => ['name' => 'เคมี',                'department_id' => 4],  // สาขาวิชาเคมี
    4  => ['name' => 'ชีววิทยา',            'department_id' => 3],  // สาขาวิชาชีววิทยา
    5  => ['name' => 'เทคโนโลยีสารสนเทศ',   'department_id' => 5],
    6  => ['name' => 'ฟิสิกส์',             'department_id' => null], // ไม่มีใน project
    7  => ['name' => 'วิทยาการข้อมูล',      'department_id' => 7],
    8  => ['name' => 'วิทยาการคอมพิวเตอร์', 'department_id' => 6],
    9  => ['name' => 'วิทยาศาสตร์การกีฬา',  'department_id' => 8],
    10 => ['name' => 'สิ่งแวดล้อม',         'department_id' => 9],  // วิทยาศาสตร์สิ่งแวดล้อม
    11 => ['name' => 'สาธารณสุขศาสตร์',     'department_id' => 10],
    12 => ['name' => 'อาหารและโภชนาการ',   'department_id' => 11],
    14 => ['name' => 'สายสนับสนุน',         'department_id' => 1],  // สำนักงานคณบดี
];

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
$mysqli->set_charset('utf8mb4');
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$personnelImageDir = __DIR__ . '/../public/uploads/personnel';
if (!is_dir($personnelImageDir)) {
    if (!is_dir(__DIR__ . '/../public/uploads')) {
        mkdir(__DIR__ . '/../public/uploads', 0755, true);
    }
    mkdir($personnelImageDir, 0755, true);
}

/**
 * Download personnel image from sci.uru.ac.th/personnel/getimage/{id} and save as .jpg
 * Filename: personnel-sci-{personId}.jpg (English-only, avoids encoding issues).
 */
function downloadPersonnelImage($imageUrl, $firstName, $lastName, $personId = null) {
    global $personnelImageDir;
    if (empty($imageUrl) || strpos($imageUrl, 'http') !== 0) return '';
    $ch = curl_init($imageUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_TIMEOUT        => 15,
    ]);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode !== 200 || empty($data)) return '';
    // บันทึกเป็น .jpg เสมอ (sci.uru.ac.th/personnel/getimage/{id} คืนค่าเป็นรูป JPEG)
    $ext = 'jpg';
    $baseName = $personId ? "personnel-sci-{$personId}" : ('personnel-' . time());
    $filename = $baseName . '.' . $ext;
    $path = $personnelImageDir . DIRECTORY_SEPARATOR . $filename;
    $n = 0;
    while (file_exists($path)) {
        $n++;
        $filename = $baseName . '-' . $n . '.' . $ext;
        $path = $personnelImageDir . DIRECTORY_SEPARATOR . $filename;
    }
    if (file_put_contents($path, $data) !== false) {
        return 'uploads/personnel/' . $filename;
    }
    return '';
}

function fetchHtml($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER  => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code === 200 && $html) ? $html : null;
}

function parsePersonName($fullName) {
    $fullName = trim(preg_replace('/\s+/u', ' ', $fullName));
    $titles = [
        'ผู้ช่วยศาสตราจารย์ ดร.', 'รองศาสตราจารย์ ดร.', 'ศาสตราจารย์ ดร.',
        'ผู้ช่วยศาสตราจารย์', 'รองศาสตราจารย์', 'ศาสตราจารย์',
        'อาจารย์ ดร.', 'อาจารย์', 'ดร.',
        'นางสาว', 'นาง', 'นาย'  // สายสนับสนุน
    ];
    $title = '';
    $name = $fullName;
    foreach ($titles as $t) {
        if (mb_strpos($fullName, $t) === 0) {
            $title = $t;
            $name = trim(mb_substr($fullName, mb_strlen($t)));
            break;
        }
    }
    // ตัดตำแหน่งท้ายชื่อ
    $positions = [
        ' คณบดี', ' รองคณบดี', ' ผู้ช่วยคณบดี', ' หัวหน้าหน่วยจัดการงานวิจัย',
        ' ประธานหลักสูตร', ' กรรมการหลักสูตร', ' อาจารย์ประจำหลักสูตร',
        ' เจ้าหน้าที่บริหารงานทั่วไป', ' นักวิชาการศึกษา', ' บรรณารักษ์',
        ' นักจัดการงานทั่วไป', ' ผู้ปฏิบัติงานบริหาร', ' นักวิชาการโสตทัศนศึกษา',
        ' ช่างเครื่องคอมพิวเตอร์', ' ผู้ปฏิบัติงานวิทยาศาสตร์', ' พนักงานทั่วไป',
    ];
    foreach ($positions as $pos) {
        if (mb_strpos($name, $pos) !== false) {
            $name = trim(mb_substr($name, 0, mb_strpos($name, $pos)));
            break;
        }
    }
    $parts = preg_split('/\s+/u', $name, 2);
    $firstName = $parts[0] ?? '';
    $lastName = $parts[1] ?? '';
    return compact('title', 'firstName', 'lastName');
}

function extractPositionFromLinkText($linkText) {
    $positions = [
        'คณบดี', 'รองคณบดี', 'ผู้ช่วยคณบดี', 'หัวหน้าหน่วยจัดการงานวิจัย',
        'ประธานหลักสูตร', 'กรรมการหลักสูตร', 'อาจารย์ประจำหลักสูตร',
        'เจ้าหน้าที่บริหารงานทั่วไป', 'นักวิชาการศึกษา', 'บรรณารักษ์',
        'นักจัดการงานทั่วไป', 'ผู้ปฏิบัติงานบริหาร', 'นักวิชาการโสตทัศนศึกษา',
        'ช่างเครื่องคอมพิวเตอร์', 'ผู้ปฏิบัติงานวิทยาศาสตร์', 'พนักงานทั่วไป',
    ];
    foreach ($positions as $pos) {
        if (mb_strpos($linkText, $pos) !== false) {
            return $pos;
        }
    }
    return 'อาจารย์';
}

// ดึงรายละเอียดจากหน้า viewperson (ชื่อ, อีเมล, หลักสูตร, รูป)
function fetchPersonDetail($baseUrl, $personId) {
    $url = $baseUrl . '/personnel/viewperson/' . $personId;
    $html = fetchHtml($url);
    if (!$html) return null;
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    $xpath = new DOMXPath($dom);
    $rows = $xpath->query("//table//tr");
    $data = ['name' => '', 'email' => '', 'curriculum' => '', 'image_url' => ''];
    foreach ($rows as $row) {
        $cells = $xpath->query(".//td | .//th", $row);
        if ($cells->length < 2) continue;
        $label = trim($cells->item(0)->textContent ?? '');
        $value = trim($cells->item(1)->textContent ?? '');
        if ($label === 'ชื่อ-นามสกุล') $data['name'] = $value;
        if ($label === 'อีเมล') $data['email'] = preg_replace('/^[,\s]+/', '', $value);
        if ($label === 'หลักสูตร') $data['curriculum'] = $value;
        // รูปอาจอยู่ในแถวนี้ (img ใน cell)
        $img = $xpath->query(".//img", $row)->item(0);
        if ($img) {
            $src = $img->getAttribute('src') ?: $img->getAttribute('data-src');
            if ($src) {
                $data['image_url'] = (strpos($src, 'http') === 0) ? $src : rtrim($baseUrl, '/') . '/' . ltrim($src, '/');
            }
        }
    }
    // ถ้ายังไม่มีรูป ลองหา img ใดๆ ในหน้าหลัก (มักเป็นรูปโปรไฟล์)
    if (empty($data['image_url'])) {
        $imgs = $xpath->query("//img[contains(@src,'person') or contains(@src,'upload') or contains(@src,'photo') or contains(@src,'image') or contains(@src,'.jpg') or contains(@src,'.jpeg') or contains(@src,'.png')]");
        foreach ($imgs as $img) {
            $src = $img->getAttribute('src') ?: $img->getAttribute('data-src');
            if ($src && strlen($src) > 10) {
                $data['image_url'] = (strpos($src, 'http') === 0) ? $src : rtrim($baseUrl, '/') . '/' . ltrim($src, '/');
                break;
            }
        }
    }
    if (empty($data['image_url'])) {
        $imgs = $xpath->query("//div[contains(@class,'content') or contains(@class,'main')]//img | //main//img | //article//img");
        foreach ($imgs as $img) {
            $src = $img->getAttribute('src') ?: $img->getAttribute('data-src');
            if ($src && strlen($src) > 5 && !preg_match('/logo|icon|banner|sprite/i', $src)) {
                $data['image_url'] = (strpos($src, 'http') === 0) ? $src : rtrim($baseUrl, '/') . '/' . ltrim($src, '/');
                break;
            }
        }
    }
    return $data;
}

echo "=== ดึงข้อมูลบุคลากร sci.uru.ac.th ตามหลักสูตร ===\n\n";

$allPersonnel = []; // key = viewperson id, value = record
$personIdsByDept = []; // department_id => [person_ids]

foreach ($curriculumToDept as $ucurriculumId => $info) {
    $deptId = $info['department_id'];
    $deptName = $info['name'];
    if ($deptId === null) {
        echo "Skip curriculum_id=$ucurriculumId ($deptName) - no department mapping\n";
        continue;
    }
    $url = $baseUrl . '/personnel?ucurriculum_id=' . $ucurriculumId;
    echo "Fetch: $deptName (ucurriculum_id=$ucurriculumId) ... ";
    $html = fetchHtml($url);
    if (!$html) {
        echo "Failed\n";
        continue;
    }
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    $xpath = new DOMXPath($dom);
    $links = $xpath->query("//a[contains(@href, '/personnel/viewperson/')]");
    $count = 0;
    foreach ($links as $a) {
        $href = $a->getAttribute('href');
        if (!preg_match('#/personnel/viewperson/(\d+)#', $href, $m)) continue;
        $personId = (int) $m[1];
        $linkText = trim($a->textContent ?? '');
        if (strlen($linkText) < 4) continue;
        $parsed = parsePersonName($linkText);
        $position = extractPositionFromLinkText($linkText);
        $key = $personId;
        if (!isset($allPersonnel[$key])) {
            $allPersonnel[$key] = [
                'sci_id'         => $personId,
                'title'          => $parsed['title'],
                'first_name'     => $parsed['firstName'],
                'last_name'      => $parsed['lastName'],
                'position'      => $position,
                'department_id' => $deptId,
                'email'         => '',
                'image'         => '',
                'curriculum'    => $deptName,
            ];
        }
        $allPersonnel[$key]['department_id'] = $deptId;
        $allPersonnel[$key]['curriculum'] = $deptName;
        if (!isset($personIdsByDept[$deptId])) $personIdsByDept[$deptId] = [];
        $personIdsByDept[$deptId][] = $personId;
        $count++;
    }
    echo "$count คน\n";
}

echo "\nรวม " . count($allPersonnel) . " คน (ไม่ซ้ำตาม viewperson id)\n";
echo "ดึงอีเมลและรูปจากหน้า viewperson ทั้งหมด...\n";

$num = 0;
$total = count($allPersonnel);
foreach ($allPersonnel as $personId => &$person) {
    $num++;
    $detail = fetchPersonDetail($baseUrl, $personId);
    if ($detail) {
        if (!empty($detail['email'])) {
            $person['email'] = trim(explode(',', $detail['email'])[0]);
        }
        // รูปบุคลากร sci.uru.ac.th ใช้รูปแบบ https://sci.uru.ac.th/personnel/getimage/{id}
        $imageUrl = rtrim($baseUrl, '/') . '/personnel/getimage/' . $personId;
        $localFile = downloadPersonnelImage(
            $imageUrl,
            $person['first_name'],
            $person['last_name'],
            $personId
        );
        if ($localFile) {
            $person['image'] = $localFile;
            $fn = trim($person['first_name']);
            $ln = trim($person['last_name']);
            echo "  [$num/$total] รูป: $fn $ln\n";
        }
    }
    usleep(150000); // 0.15 วินาที ระหว่าง request
}
unset($person);

// บันทึกลง DB
$stmtSelect = $mysqli->prepare("SELECT id FROM personnel WHERE first_name = ? AND last_name = ?");
$stmtInsert = $mysqli->prepare("INSERT INTO personnel (title, first_name, last_name, first_name_en, last_name_en, position, position_en, department_id, email, phone, image, sort_order, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())");
$stmtUpdate = $mysqli->prepare("UPDATE personnel SET title = ?, position = ?, department_id = ?, email = ?, image = ?, sort_order = ?, updated_at = NOW() WHERE id = ?");

$sortOrder = 0;
$inserted = 0;
$updated = 0;
foreach ($allPersonnel as $person) {
    $sortOrder++;
    $stmtSelect->bind_param('ss', $person['first_name'], $person['last_name']);
    $stmtSelect->execute();
    $res = $stmtSelect->get_result();
    $row = $res->fetch_assoc();
    $stmtSelect->free_result();
    if ($row) {
        $imgVal = $person['image'] ?? '';
        $stmtUpdate->bind_param('ssissii',
            $person['title'],
            $person['position'],
            $person['department_id'],
            $person['email'],
            $imgVal,
            $sortOrder,
            $row['id']
        );
        $stmtUpdate->execute();
        $updated++;
    } else {
        $img = $person['image'] ?? '';
        $fnEn = '';
        $lnEn = '';
        $posEn = '';
        $phone = '';
        $stmtInsert->bind_param('sssssssisssi',  // 12 params
            $person['title'],
            $person['first_name'],
            $person['last_name'],
            $fnEn,
            $lnEn,
            $person['position'],
            $posEn,
            $person['department_id'],
            $person['email'],
            $phone,
            $img,
            $sortOrder
        );
        $stmtInsert->execute();
        $inserted++;
    }
}
$stmtSelect->close();
$stmtInsert->close();
$stmtUpdate->close();

echo "\n=== สรุป ===\n";
echo "เพิ่มใหม่: $inserted\n";
echo "อัปเดต: $updated\n";
echo "รวม: " . ($inserted + $updated) . "\n";
echo "Done.\n";
