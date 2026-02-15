<?php

/**
 * Import Personnel from sci.uru.ac.th/personnel to Database
 * 
 * Usage: php import_personnel.php
 */

// Simple database connection (like import_from_sciuru.php)
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'newscience';

// Connect to database
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
$mysqli->set_charset('utf8mb4');

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

echo "Database connected successfully!\n\n";

// Base path for personnel images (relative to project root)
$personnelImageDir = __DIR__ . '/../public/uploads/personnel';

// Ensure personnel image directory exists
if (!is_dir($personnelImageDir)) {
    if (!is_dir(__DIR__ . '/../public/uploads')) {
        mkdir(__DIR__ . '/../public/uploads', 0755, true);
    }
    mkdir($personnelImageDir, 0755, true);
    echo "Created directory: uploads/personnel\n";
}

/**
 * Download image from URL and save to personnel image folder.
 * Returns local filename (e.g. "john-doe-123.jpg") or empty string on failure.
 */
function downloadPersonnelImage($imageUrl, $firstName, $lastName, $personId = null)
{
    global $personnelImageDir;

    if (empty($imageUrl) || strpos($imageUrl, 'http') !== 0) {
        return '';
    }

    $ch = curl_init($imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($httpCode !== 200 || empty($data)) {
        return '';
    }

    // Determine extension from URL or Content-Type
    $ext = 'jpg';
    if (preg_match('/\.(jpe?g|png|gif|webp)(\?|$)/i', $imageUrl, $m)) {
        $ext = strtolower($m[1]);
        if ($ext === 'jpeg') $ext = 'jpg';
    } elseif (preg_match('#image/(jpeg|jpg|png|gif|webp)#i', $contentType ?? '', $m)) {
        $ext = strtolower($m[1]);
        if ($ext === 'jpeg') $ext = 'jpg';
    }

    // Safe filename: firstname-lastname[-id].ext
    $slug = preg_replace('/[^a-zA-Z0-9\x{0E00}-\x{0E7F}\-]/u', '-', $firstName . '-' . $lastName);
    $slug = trim($slug, '-') ?: 'personnel';
    $baseName = $slug . ($personId ? "-$personId" : '-' . time());
    $filename = $baseName . '.' . $ext;

    // Avoid overwrite: add number if file exists
    $path = $personnelImageDir . DIRECTORY_SEPARATOR . $filename;
    $n = 0;
    while (file_exists($path)) {
        $n++;
        $filename = $baseName . '-' . $n . '.' . $ext;
        $path = $personnelImageDir . DIRECTORY_SEPARATOR . $filename;
    }

    if (file_put_contents($path, $data) !== false) {
        return $filename;
    }

    return '';
}

// Function to fetch web page
function fetchPage($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $httpCode == 200 ? $response : null;
}

// Function to parse personnel name (extract title, first name, last name)
function parsePersonName($fullName)
{
    $fullName = trim($fullName);

    // Common Thai titles
    $titles = [
        'ผู้ช่วยศาสตราจารย์ ดร.',
        'ผู้ช่วยศาสตราจารย์',
        'รองศาสตราจารย์ ดร.',
        'รองศาสตราจารย์',
        'ศาสตราจารย์ ดร.',
        'ศาสตราจารย์',
        'อาจารย์ ดร.',
        'อาจารย์',
        'ดร.'
    ];

    $title = '';
    $name = $fullName;

    // Extract title
    foreach ($titles as $t) {
        if (strpos($fullName, $t) === 0) {
            $title = $t;
            $name = trim(str_replace($t, '', $fullName));
            break;
        }
    }

    // Split name into first and last
    $nameParts = explode(' ', $name);
    $firstName = $nameParts[0] ?? '';
    $lastName = implode(' ', array_slice($nameParts, 1)) ?? '';

    return [
        'title' => $title,
        'first_name' => $firstName,
        'last_name' => $lastName
    ];
}

// Function to get department ID from database by name
function getDepartmentId($mysqli, $departmentName)
{
    // Map Thai department names to database names
    $departmentMap = [
        'ผู้บริหาร' => 'สำนักงานคณบดี',
        'คณิตศาสตร์' => 'คณิตศาสตร์',
        'เคมี' => 'เคมี',
        'ชีววิทยา' => 'ชีววิทยา',
        'เทคโนโลยีสารสนเทศ' => 'เทคโนโลยีสารสนเทศ',
        'ฟิสิกส์' => 'ฟิสิกส์',
        'วิทยาการข้อมูล' => 'วิทยาการข้อมูล',
        'วิทยาการคอมพิวเตอร์' => 'วิทยาการคอมพิวเตอร์',
        'วิทยาศาสตร์การกีฬา' => 'วิทยาศาสตร์การกีฬา',
        'สิ่งแวดล้อม' => 'สิ่งแวดล้อม',
        'สาธารณสุขศาสตร์' => 'สาธารณสุขศาสตร์',
        'อาหารและโภชนาการ' => 'อาหารและโภชนาการ',
        'สายสนับสนุน' => 'สายสนับสนุน'
    ];

    // Try to find exact match first
    $searchNames = [$departmentName];
    if (isset($departmentMap[$departmentName])) {
        $searchNames[] = $departmentMap[$departmentName];
    }

    // Also try with common prefixes
    foreach (['สาขาวิชา', 'ภาควิชา', 'แผนก'] as $prefix) {
        foreach ($searchNames as $name) {
            $searchNames[] = $prefix . $name;
        }
    }

    foreach ($searchNames as $searchName) {
        $stmt = $mysqli->prepare("SELECT id FROM departments WHERE name_th LIKE ? AND status = 'active' LIMIT 1");
        $searchPattern = '%' . $searchName . '%';
        $stmt->bind_param('s', $searchPattern);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            return $row['id'];
        }
        $stmt->close();
    }

    return null;
}

echo "=== Import Personnel from sci.uru.ac.th/personnel ===\n\n";

$url = 'https://sci.uru.ac.th/personnel';
echo "Fetching: $url\n";

$html = fetchPage($url);
if (!$html) {
    die("Error: Could not fetch page\n");
}

echo "Page fetched successfully\n";
echo "Parsing HTML...\n";

// Get all departments from database for mapping
$departments = [];
$deptResult = $mysqli->query("SELECT id, name_th FROM departments WHERE status = 'active'");
while ($row = $deptResult->fetch_assoc()) {
    $departments[$row['id']] = $row['name_th'];
}
echo "Loaded " . count($departments) . " departments from database\n\n";

// Use DOMDocument for parsing
libxml_use_internal_errors(true);
$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
libxml_clear_errors();

$xpath = new DOMXPath($dom);

// Find all personnel sections
// Look for headings that might indicate sections
$sections = $xpath->query("//h1 | //h2 | //h3 | //h4 | //strong[contains(text(), 'ผู้บริหาร') or contains(text(), 'คณิตศาสตร์') or contains(text(), 'เคมี')]");

$personnel = [];
$currentDepartment = null;

// Add known executives first so table/img parsing can fill their images
$adminDeptId = getDepartmentId($mysqli, 'ผู้บริหาร');
$knownFirst = [
    ['title' => 'ผู้ช่วยศาสตราจารย์', 'first_name' => 'ปริญญา', 'last_name' => 'ไกรวุฒินันท์', 'position' => 'คณบดี', 'sort_order' => 1, 'department_id' => $adminDeptId, 'position_en' => '', 'email' => '', 'phone' => '', 'image' => '', 'status' => 'active'],
    ['title' => 'ผู้ช่วยศาสตราจารย์', 'first_name' => 'จุฬาลักษณ์', 'last_name' => 'มหาวัน', 'position' => 'รองคณบดี', 'sort_order' => 2, 'department_id' => $adminDeptId, 'position_en' => '', 'email' => '', 'phone' => '', 'image' => '', 'status' => 'active'],
];
foreach ($knownFirst as $k) {
    $k['sort_order'] = count($personnel) + 1;
    $personnel[] = $k;
}

// Process the page content - parse by department sections
$body = $xpath->query("//body")->item(0);
if ($body) {
    $bodyText = $body->textContent;

    // Department sections to look for
    $departmentSections = [
        'ผู้บริหารคณะวิทยาศาสตร์และเทคโนโลยี' => 'ผู้บริหาร',
        'คณิตศาสตร์' => 'คณิตศาสตร์',
        'เคมี' => 'เคมี',
        'ชีววิทยา' => 'ชีววิทยา',
        'เทคโนโลยีสารสนเทศ' => 'เทคโนโลยีสารสนเทศ',
        'ฟิสิกส์' => 'ฟิสิกส์',
        'วิทยาการข้อมูล' => 'วิทยาการข้อมูล',
        'วิทยาการคอมพิวเตอร์' => 'วิทยาการคอมพิวเตอร์',
        'วิทยาศาสตร์การกีฬา' => 'วิทยาศาสตร์การกีฬา',
        'สิ่งแวดล้อม' => 'สิ่งแวดล้อม',
        'สาธารณสุขศาสตร์' => 'สาธารณสุขศาสตร์',
        'อาหารและโภชนาการ' => 'อาหารและโภชนาการ',
        'สายสนับสนุน' => 'สายสนับสนุน'
    ];

    // Strategy: Parse the entire page text and identify sections
    $allText = $body->textContent;

    // Split text by department sections
    $sectionPattern = '/(' . implode('|', array_keys($departmentSections)) . ')/u';
    $sections = preg_split($sectionPattern, $allText, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);

    $currentSection = null;
    $currentDeptId = null;

    for ($i = 0; $i < count($sections); $i += 2) {
        if ($i + 1 < count($sections)) {
            $sectionName = trim($sections[$i + 1][0]);
            $sectionContent = $sections[$i + 2][0] ?? '';

            // Find matching department
            foreach ($departmentSections as $key => $deptName) {
                if (strpos($sectionName, $key) !== false || strpos($sectionName, $deptName) !== false) {
                    $currentSection = $deptName;
                    $currentDeptId = getDepartmentId($mysqli, $deptName);
                    echo "Found department section: $deptName (ID: " . ($currentDeptId ?? 'null') . ")\n";

                    // Parse personnel in this section
                    // Look for patterns: Title + Name (may or may not have position)
                    preg_match_all('/(ผู้ช่วยศาสตราจารย์(?:\s+ดร\.)?|รองศาสตราจารย์(?:\s+ดร\.)?|ศาสตราจารย์(?:\s+ดร\.)?|อาจารย์(?:\s+ดร\.)?|ดร\.)\s+([^\s]+(?:\s+[^\s]+){0,3})/u', $sectionContent, $matches, PREG_SET_ORDER);

                    $personCount = 0;
                    foreach ($matches as $match) {
                        $title = trim($match[1]);
                        $name = trim($match[2]);

                        // Skip if name is too short or is a department name
                        if (strlen($name) < 3 || in_array($name, array_keys($departmentSections))) {
                            continue;
                        }

                        $nameParts = parsePersonName($title . ' ' . $name);

                        // Skip if first name is too short
                        if (strlen($nameParts['first_name']) < 2) {
                            continue;
                        }

                        // Try to find position in the text after the name
                        $position = 'อาจารย์'; // default
                        $namePattern = preg_quote($name, '/');
                        if (preg_match('/' . $namePattern . '\s+([^\s\n]+)/u', $sectionContent, $posMatch)) {
                            $possiblePos = trim($posMatch[1]);
                            if (in_array($possiblePos, ['คณบดี', 'รองคณบดี', 'ผู้ช่วยคณบดี', 'หัวหน้า', 'รองหัวหน้า', 'อาจารย์'])) {
                                $position = $possiblePos;
                            }
                        }

                        // Check if already exists
                        $exists = false;
                        $existingIdx = null;
                        foreach ($personnel as $idx => $p) {
                            if ($p['first_name'] === $nameParts['first_name'] && $p['last_name'] === $nameParts['last_name']) {
                                $exists = true;
                                $existingIdx = $idx;
                                break;
                            }
                        }

                        if ($exists && $currentDeptId) {
                            // Update department if found in a specific department
                            if (!$personnel[$existingIdx]['department_id']) {
                                $personnel[$existingIdx]['department_id'] = $currentDeptId;
                            }
                        } elseif (!$exists) {
                            $personnel[] = [
                                'title' => $nameParts['title'],
                                'first_name' => $nameParts['first_name'],
                                'last_name' => $nameParts['last_name'],
                                'position' => $position,
                                'position_en' => '',
                                'department_id' => $currentDeptId,
                                'email' => '',
                                'phone' => '',
                                'image' => '',
                                'status' => 'active',
                                'sort_order' => count($personnel) + 1
                            ];
                            $personCount++;
                        }
                    }

                    if ($personCount > 0) {
                        echo "  Found $personCount personnel in $currentSection\n";
                    }
                    break;
                }
            }
        }
    }

    // Also look for personnel patterns in the entire page (fallback)
    $allText = $body->textContent;
    preg_match_all('/(ผู้ช่วยศาสตราจารย์(?:\s+ดร\.)?|รองศาสตราจารย์(?:\s+ดร\.)?|ศาสตราจารย์(?:\s+ดร\.)?|อาจารย์(?:\s+ดร\.)?|ดร\.)\s+([^\s]+(?:\s+[^\s]+)*?)\s+(คณบดี|รองคณบดี|ผู้ช่วยคณบดี|หัวหน้าหน่วยจัดการงานวิจัย)/u', $allText, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $title = trim($match[1]);
        $name = trim($match[2]);
        $position = trim($match[3]);

        $nameParts = parsePersonName($title . ' ' . $name);

        // Check if already exists
        $exists = false;
        foreach ($personnel as $p) {
            if ($p['first_name'] === $nameParts['first_name'] && $p['last_name'] === $nameParts['last_name']) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $personnel[] = [
                'title' => $nameParts['title'],
                'first_name' => $nameParts['first_name'],
                'last_name' => $nameParts['last_name'],
                'position' => $position,
                'position_en' => '',
                'department_id' => null, // Will be set to administration
                'email' => '',
                'phone' => '',
                'image' => '',
                'status' => 'active',
                'sort_order' => count($personnel) + 1
            ];
        }
    }

    // Also try to find in table format (and update image/email for existing personnel)
    $tables = $xpath->query("//table");
    foreach ($tables as $table) {
        $rows = $xpath->query(".//tr", $table);
        foreach ($rows as $row) {
            $cells = $xpath->query(".//td | .//th", $row);
            if ($cells->length >= 2) {
                $nameText = trim($cells->item(0)->textContent);
                $positionText = trim($cells->item(1)->textContent ?? '');

                if (strlen($nameText) > 5 && !in_array($nameText, ['ชื่อ', 'Name', 'ตำแหน่ง', 'Position'])) {
                    $nameParts = parsePersonName($nameText);

                    // Extract image from row (do this for both new and existing)
                    $img = $xpath->query(".//img", $row)->item(0);
                    $imageUrl = '';
                    if ($img) {
                        $src = $img->getAttribute('src') ?: $img->getAttribute('data-src');
                        if ($src) {
                            $imageUrl = strpos($src, 'http') === 0 ? $src : 'https://sci.uru.ac.th' . ltrim($src, '/');
                        }
                    }

                    $emailLink = $xpath->query(".//a[starts-with(@href, 'mailto:')]", $row)->item(0);
                    $email = $emailLink ? str_replace('mailto:', '', $emailLink->getAttribute('href')) : '';

                    $existingIdx = null;
                    foreach ($personnel as $idx => $p) {
                        if ($p['first_name'] === $nameParts['first_name'] && $p['last_name'] === $nameParts['last_name']) {
                            $existingIdx = $idx;
                            break;
                        }
                    }

                    if ($existingIdx !== null) {
                        // Update existing: set image and email from this row
                        if ($imageUrl) {
                            $personnel[$existingIdx]['image'] = $imageUrl;
                        }
                        if ($email) {
                            $personnel[$existingIdx]['email'] = $email;
                        }
                        if ($positionText && !$personnel[$existingIdx]['position']) {
                            $personnel[$existingIdx]['position'] = $positionText;
                        }
                    } else {
                        $personnel[] = [
                            'title' => $nameParts['title'],
                            'first_name' => $nameParts['first_name'],
                            'last_name' => $nameParts['last_name'],
                            'position' => $positionText ?: '',
                            'position_en' => '',
                            'department_id' => null,
                            'email' => $email,
                            'phone' => '',
                            'image' => $imageUrl,
                            'status' => 'active',
                            'sort_order' => count($personnel) + 1
                        ];
                    }
                }
            }
        }
    }

    // Pass 2: Find all img in page and match to personnel by nearby text
    $allImgs = $xpath->query("//img[contains(@src, 'http') or contains(@src, '/')]");
    foreach ($allImgs as $img) {
        $src = $img->getAttribute('src') ?: $img->getAttribute('data-src');
        if (!$src || strlen($src) < 10) continue;
        $imageUrl = strpos($src, 'http') === 0 ? $src : 'https://sci.uru.ac.th' . ltrim($src, '/');

        $parent = $img->parentNode;
        $walk = 0;
        while ($parent && $walk < 5) {
            $blockText = $parent->textContent ?? '';
            foreach ($personnel as $idx => $p) {
                $fullName = ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '');
                $fn = $p['first_name'] ?? '';
                $ln = $p['last_name'] ?? '';
                if (($fn && $ln && (strpos($blockText, $fn) !== false && strpos($blockText, $ln) !== false))
                    || (strpos($blockText, $fullName) !== false)
                ) {
                    if (empty($personnel[$idx]['image'])) {
                        $personnel[$idx]['image'] = $imageUrl;
                    }
                    break 2;
                }
            }
            $parent = $parent->parentNode;
            $walk++;
        }
    }
}

echo "Found " . count($personnel) . " personnel records\n\n";

// Import to database
$imported = 0;
$updated = 0;
$skipped = 0;

echo "\n=== Importing to Database ===\n";

foreach ($personnel as $person) {
    // Resolve image: download from URL to uploads/personnel if needed
    $imageValue = $person['image'] ?? '';
    if (!empty($imageValue) && strpos($imageValue, 'http') === 0) {
        $localFile = downloadPersonnelImage(
            $imageValue,
            $person['first_name'],
            $person['last_name'],
            null
        );
        if ($localFile) {
            $imageValue = $localFile;
            echo "  [Image saved] {$person['first_name']} {$person['last_name']} -> $localFile\n";
        }
    }

    // Check if exists (by name)
    $stmt = $mysqli->prepare("SELECT id FROM personnel WHERE first_name = ? AND last_name = ?");
    $stmt->bind_param('ss', $person['first_name'], $person['last_name']);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result->fetch_assoc();
    $stmt->close();

    if ($existing) {
        // If we had URL but failed above, try again with existing id
        if (($person['image'] ?? '') && strpos($person['image'], 'http') === 0 && empty($imageValue)) {
            $imageValue = downloadPersonnelImage(
                $person['image'],
                $person['first_name'],
                $person['last_name'],
                $existing['id']
            );
            if ($imageValue) {
                echo "  [Image saved] {$person['first_name']} {$person['last_name']} -> $imageValue\n";
            }
        }

        // Update existing
        $deptId = $person['department_id'];
        $stmt = $mysqli->prepare("UPDATE personnel SET title = ?, position = ?, position_en = ?, department_id = ?, email = ?, phone = ?, image = ?, status = ?, sort_order = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param(
            'sssissssii',
            $person['title'],
            $person['position'],
            $person['position_en'],
            $deptId,
            $person['email'],
            $person['phone'],
            $imageValue,
            $person['status'],
            $person['sort_order'],
            $existing['id']
        );
        $stmt->execute();
        $stmt->close();
        $updated++;
        echo "  ✓ Updated: {$person['title']} {$person['first_name']} {$person['last_name']}\n";
    } else {
        // Insert new
        $deptId = $person['department_id'];
        $firstNameEn = $person['first_name_en'] ?? '';
        $lastNameEn = $person['last_name_en'] ?? '';
        $stmt = $mysqli->prepare("INSERT INTO personnel (title, first_name, last_name, first_name_en, last_name_en, position, position_en, department_id, email, phone, image, status, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param(
            'sssssssissssi',
            $person['title'],
            $person['first_name'],
            $person['last_name'],
            $firstNameEn,
            $lastNameEn,
            $person['position'],
            $person['position_en'],
            $deptId,
            $person['email'],
            $person['phone'],
            $imageValue,
            $person['status'],
            $person['sort_order']
        );
        $stmt->execute();
        $stmt->close();
        $imported++;
        echo "  + Added: {$person['title']} {$person['first_name']} {$person['last_name']}\n";
    }
}

echo "\n=== Summary ===\n";
echo "Imported: $imported\n";
echo "Updated: $updated\n";
echo "Total processed: " . ($imported + $updated) . "\n";
echo "\nDone!\n";
