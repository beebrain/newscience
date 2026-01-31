-- Update news dates from actual website sci.uru.ac.th
-- Dates converted from Buddhist Era (พ.ศ.) to Christian Era (ค.ศ.)
SET NAMES utf8mb4;

-- ID 723: 12 ม.ค. 69 = 2026-01-12
UPDATE news SET published_at = '2026-01-12' WHERE id = 723 OR slug LIKE '%รอบที่-2%' OR title LIKE '%รอบที่ 2%สอบคัดเลือก%';

-- ID 722: 6 ม.ค. 69 = 2026-01-06  
UPDATE news SET published_at = '2026-01-06' WHERE id = 722 OR title LIKE '%9 มกราคม 2569%';

-- ID 721: 16 ธ.ค. 68 = 2025-12-16
UPDATE news SET published_at = '2025-12-16' WHERE id = 721 OR title LIKE '%URU AMBASSADOR 2025%';

-- ID 710: 21 ต.ค. 68 = 2025-10-21
UPDATE news SET published_at = '2025-10-21' WHERE id = 710 OR title LIKE '%URU RUN 2025%';

-- ID 709: 17 ต.ค. 68 = 2025-10-17
UPDATE news SET published_at = '2025-10-17' WHERE id = 709 OR title LIKE '%รอบที่ 1%2569%';

-- ID 708: 2 ต.ค. 68 = 2025-10-02
UPDATE news SET published_at = '2025-10-02' WHERE id = 708 OR title LIKE '%Freshy Boy%Freshy Girl%';

-- ID 707: 2 ต.ค. 68 = 2025-10-02
UPDATE news SET published_at = '2025-10-02' WHERE id = 707 OR title LIKE '%ฝึกซ้อมพิธีพระราชทานปริญญาบัตร%2566%';

-- ID 682: 24 ก.ย. 68 = 2025-09-24
UPDATE news SET published_at = '2025-09-24' WHERE id = 682 OR title LIKE '%Freshy Science%Technology 2025%';

-- ID 680: 24 ก.ย. 68 = 2025-09-24
UPDATE news SET published_at = '2025-09-24' WHERE id = 680 OR title LIKE '%เลื่อนวันสอบคัดเลือก%รอบที่ 1%';

-- ID 679: 8 ก.ย. 68 = 2025-09-08
UPDATE news SET published_at = '2025-09-08' WHERE id = 679 OR title LIKE '%ปริญญาเอก%วิทยาศาสตร์ประยุกต์%';

-- ID 678: 8 ก.ย. 68 = 2025-09-08
UPDATE news SET published_at = '2025-09-08' WHERE id = 678 OR title LIKE '%ปริญญาโท%วิทยาศาสตร์ประยุกต์%';

-- ID 671: 8 ก.ย. 68 = 2025-09-08
UPDATE news SET published_at = '2025-09-08' WHERE id = 671 OR title LIKE '%รับสมัครนักศึกษาใหม่%2569%';

-- ID 670: 8 ก.ย. 68 = 2025-09-08
UPDATE news SET published_at = '2025-09-08' WHERE id = 670 OR title LIKE '%บทความตีพิมพ์%วารสาร%2025%';

-- ID 669: 7 ก.ย. 68 = 2025-09-07
UPDATE news SET published_at = '2025-09-07' WHERE id = 669 OR title LIKE '%บริจาคโลหิต%กันยายน%';

-- ID 668: 8 ก.ย. 68 = 2025-09-08
UPDATE news SET published_at = '2025-09-08' WHERE id = 668 OR title LIKE '%ลงทะเบียนบัณฑิต%2566%รอบที่ 2%';

-- ID 667: 29 ส.ค. 68 = 2025-08-29
UPDATE news SET published_at = '2025-08-29' WHERE id = 667 OR title LIKE '%สมเด็จพระกนิษฐาธิราชเจ้า%เสด็จ%';

-- ID 666: 28 ส.ค. 68 = 2025-08-28
UPDATE news SET published_at = '2025-08-28' WHERE id = 666 OR title LIKE '%เฝ้ารับเสด็จ%29 สิงหาคม%';

-- ID 665: 26 ส.ค. 68 = 2025-08-26
UPDATE news SET published_at = '2025-08-26' WHERE id = 665 OR title LIKE '%มาตรฐานฝีมือแรงงาน%อาหารไทย%';

-- ID 633: 4 ส.ค. 68 = 2025-08-04
UPDATE news SET published_at = '2025-08-04' WHERE id = 633 OR title LIKE '%สัปดาห์วิทยาศาสตร์แห่งชาติ%2568%';

-- ID 632: 4 ส.ค. 68 = 2025-08-04
UPDATE news SET published_at = '2025-08-04' WHERE id = 632 OR title LIKE '%พิธีพระราชทานปริญญาบัตร%2566%';

-- ID 631: 31 ก.ค. 68 = 2025-07-31
UPDATE news SET published_at = '2025-07-31' WHERE id = 631 OR title LIKE '%วันสถาปนา%ราชภัฎอุตรดิตถ์%';

-- ID 630: 29 ก.ค. 68 = 2025-07-29
UPDATE news SET published_at = '2025-07-29' WHERE id = 630 OR title LIKE '%รับสมัครนักศึกษา%2569%เคมี%';

SELECT 'News dates updated!' AS status;
