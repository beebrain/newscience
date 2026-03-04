-- Seed faculty download categories and documents (migrate from static data)
-- Run AFTER create_download_tables.sql
-- Run: mysql -u root newscience < database/seed_download_documents.sql

-- Support-documents page: 4 categories
INSERT INTO download_categories (name, slug, icon, page_type, sort_order, is_active) VALUES
('งานบริหารทั่วไป', 'general', 'folder', 'support', 10, 1),
('งานการเงินและพัสดุ', 'finance', 'banknotes', 'support', 20, 1),
('งานวิชาการ', 'academic', 'academic-cap', 'support', 30, 1),
('งานวิจัยและบริการวิชาการ', 'research', 'beaker', 'support', 40, 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), icon = VALUES(icon), sort_order = VALUES(sort_order);

-- Official-documents page: 3 categories
INSERT INTO download_categories (name, slug, icon, page_type, sort_order, is_active) VALUES
('คำสั่ง', 'orders', 'document', 'official', 10, 1),
('แผนพัฒนาและระเบียบ', 'plans', 'cube', 'official', 20, 1),
('สำหรับนักศึกษา', 'students', 'academic-cap', 'official', 30, 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), icon = VALUES(icon), sort_order = VALUES(sort_order);

-- Get category IDs (assume fixed order from inserts: 1=general, 2=finance, 3=academic, 4=research, 5=orders, 6=plans, 7=students)
-- Support: general (1)
INSERT INTO download_documents (category_id, title, external_url, file_type, file_size, sort_order, is_active) VALUES
(1, 'ขอใช้สถานที่-อุปกรณ์', 'https://sci.uru.ac.th/docs/download/0001.doc', 'doc', 0, 10, 1),
(1, 'ขอให้ออกหนังสือราชการ', 'https://sci.uru.ac.th/docs/download/0004.doc', 'doc', 0, 20, 1),
(1, 'ขออนุญาตไปราชการ (แบบ งค.๐๓)', 'https://sci.uru.ac.th/doctopic/254', 'link', 0, 30, 1),
(1, 'บันทึกขอใช้รถ 6 ล้อ', 'https://sci.uru.ac.th/docs/download/0076.pdf', 'pdf', 0, 40, 1),
(1, 'แบบขอใช้รถยนต์มหาวิทยาลัย', 'https://sci.uru.ac.th/docs/download/0007.doc', 'doc', 0, 50, 1),
(1, 'แบบขออนุญาตผู้ปกครองพานักศึกษาไปนอกสถานที่', 'https://sci.uru.ac.th/docs/download/0009.doc', 'doc', 0, 60, 1),
(1, 'แบบขออนุญาตพานักศึกษาไปนอกสถานที่', 'https://sci.uru.ac.th/docs/download/0010.doc', 'doc', 0, 70, 1),
(1, 'แบบขออนุญาตให้ผู้อื่นปฏิบัติหน้าที่เวรแทน', 'https://sci.uru.ac.th/docs/download/0052.doc', 'doc', 0, 80, 1),
(1, 'แบบใบลาพักผ่อน', 'https://sci.uru.ac.th/docs/download/0012.doc', 'doc', 0, 90, 1),
(1, 'แบบฟอร์มบันทึกข้อความ', 'https://sci.uru.ac.th/docs/download/0054.doc', 'doc', 0, 100, 1),
(1, 'แบบฟอร์มบันทึกขอไปราชการ/ขอใช้รถยนต์ส่วนตัว', 'https://sci.uru.ac.th/docs/download/0068.docx', 'docx', 0, 110, 1),
(1, 'แบบฟอร์มมอบหมายงานเพื่อลากิจ/ลาพักผ่อน/ไปราชการ', 'https://sci.uru.ac.th/docs/download/0069.pdf', 'pdf', 0, 120, 1),
(1, 'แบบฟอร์มหนังสือภายนอก', 'https://sci.uru.ac.th/docs/download/0055.doc', 'doc', 0, 130, 1),
(1, 'ใบมอบหมายงานเพื่อลากิจ', 'https://sci.uru.ac.th/docs/download/0024.doc', 'doc', 0, 140, 1),
(1, 'รายงานผลการไปราชการ', 'https://sci.uru.ac.th/docs/download/0027.doc', 'doc', 0, 150, 1),
(1, 'สัญญาจ้างเหมารถยนต์โดยสารพร้อมพนักงานขับรถ', 'https://sci.uru.ac.th/docs/download/0077_1.docx', 'docx', 0, 160, 1);

-- Support: finance (2)
INSERT INTO download_documents (category_id, title, external_url, file_type, file_size, sort_order, is_active) VALUES
(2, 'บันทึกข้อความ ขอโอนเงินผ่านธนาคาร', 'https://sci.uru.ac.th/docs/download/0050.doc', 'doc', 0, 10, 1),
(2, 'แบบฟอร์มขอเบิกใบเสร็จรับเงินเบ็ดเตล็ด', 'https://sci.uru.ac.th/docs/download/0067.docx', 'docx', 0, 20, 1),
(2, 'แบบฟอร์มใบส่งของ', 'https://sci.uru.ac.th/docs/download/0083.docx', 'docx', 0, 30, 1),
(2, 'แบบสำรวจ จัดซื้อ-จัดจ้าง', 'https://sci.uru.ac.th/docs/download/0017.doc', 'doc', 0, 40, 1),
(2, 'ใบขออนุมัติเบิกเงิน', 'https://sci.uru.ac.th/docs/download/0020.doc', 'doc', 0, 50, 1),
(2, 'ใบตรวจรับสินค้า', 'https://sci.uru.ac.th/docs/download/0021.doc', 'doc', 0, 60, 1),
(2, 'ใบเบิกค่าใช้จ่ายในการเดินทางไปราชการ', 'https://sci.uru.ac.th/docs/download/0022.doc', 'doc', 0, 70, 1),
(2, 'ใบเบิกค่าใช้จ่าย (บุคคลภายนอก)', 'https://sci.uru.ac.th/docs/download/0022_1.doc', 'doc', 0, 80, 1),
(2, 'ใบมอบฉันทะ', 'https://sci.uru.ac.th/docs/download/0023.doc', 'doc', 0, 90, 1),
(2, 'ใบสำคัญรับเงิน', 'https://sci.uru.ac.th/docs/download/0025.doc', 'doc', 0, 100, 1),
(2, 'ใบสำคัญรับเงินค่าอาหาร', 'https://sci.uru.ac.th/docs/download/0103.docx', 'docx', 0, 110, 1),
(2, 'ใบเสนอราคา', 'https://sci.uru.ac.th/docs/download/0026.doc', 'doc', 0, 120, 1),
(2, 'สัญญาค้ำประกันการยืมเงิน', 'https://sci.uru.ac.th/docs/download/0028.pdf', 'pdf', 0, 130, 1);

-- Support: academic (3)
INSERT INTO download_documents (category_id, title, external_url, file_type, file_size, sort_order, is_active) VALUES
(3, 'ขอเบิกค่าสอนการเปิดสอนกรณีพิเศษ', 'https://sci.uru.ac.th/docs/download/0002.doc', 'doc', 0, 10, 1),
(3, 'แบบขอเปลี่ยน "ร" หรือ "I"', 'https://sci.uru.ac.th/docs/download/0032.doc', 'doc', 0, 20, 1),
(3, 'แบบขอเปลี่ยนแปลงแผนการเรียนหลักสูตร', 'https://sci.uru.ac.th/docs/download/0074.docx', 'docx', 0, 30, 1),
(3, 'แบบขออนุญาตสอนชดเชย/ให้ผู้อื่นสอนแทน', 'https://sci.uru.ac.th/docs/download/0081.docx', 'docx', 0, 40, 1),
(3, 'แบบขออนุญาตไปราชการ ช่วงเวลาสอบ', 'https://sci.uru.ac.th/docs/download/0089.docx', 'docx', 0, 50, 1),
(3, 'แบบขออนุญาตสอบนอกตาราง', 'https://sci.uru.ac.th/docs/download/0090.docx', 'docx', 0, 60, 1),
(3, 'แบบฟอร์มการขอเปลี่ยนแปลงข้อมูลตารางเรียน', 'https://sci.uru.ac.th/doctopic/208', 'link', 0, 70, 1),
(3, 'แบบฟอร์มแก้ไขผลการเรียน', 'https://sci.uru.ac.th/docs/download/0075.docx', 'docx', 0, 80, 1),
(3, 'แบบฟอร์มขอแก้ไขตารางสอน', 'https://sci.uru.ac.th/doctopic/209', 'link', 0, 90, 1),
(3, 'แบบฟอร์มแนวการสอน', 'https://sci.uru.ac.th/docs/download/0043.doc', 'doc', 0, 100, 1),
(3, 'แบบรายงานการประเมิน ส่งเกรด', 'https://sci.uru.ac.th/docs/download/grade_2023_04_20.doc', 'doc', 0, 110, 1),
(3, 'แบบเสนอเพื่อแต่งตั้งอาจารย์พิเศษ', 'https://sci.uru.ac.th/docs/download/0066.docx', 'docx', 0, 120, 1),
(3, 'ฟอร์มแบบเสนอ มคอ.3', 'https://sci.uru.ac.th/docs/download/0044.doc', 'doc', 0, 130, 1);

-- Support: research (4)
INSERT INTO download_documents (category_id, title, external_url, file_type, file_size, sort_order, is_active) VALUES
(4, 'แบบประเมินการติดตามการนำความรู้ไปใช้ประโยชน์', 'https://sci.uru.ac.th/docs/download/0059.doc', 'doc', 0, 10, 1),
(4, 'แบบประเมินโครงการบริการวิชาการ', 'https://sci.uru.ac.th/docs/download/0062.doc', 'doc', 0, 20, 1),
(4, 'แบบฟอร์มขอจดลิขสิทธิ์', 'https://sci.uru.ac.th/docs/download/0056.doc', 'doc', 0, 30, 1),
(4, 'แบบฟอร์มสรุปโครงการทั่วไป', 'https://sci.uru.ac.th/docs/download/0060.doc', 'doc', 0, 40, 1),
(4, 'แบบฟอร์มสรุปโครงการบริการวิชาการ', 'https://sci.uru.ac.th/docs/download/0061.doc', 'doc', 0, 50, 1),
(4, 'แบบรายงานผลการดำเนินโครงการ', 'https://sci.uru.ac.th/docs/download/0030.doc', 'doc', 0, 60, 1),
(4, 'แบบเสนอขอรับทุนสาขาวิทยาศาสตร์ฯ', 'https://sci.uru.ac.th/docs/download/0049.doc', 'doc', 0, 70, 1),
(4, 'แบบเสนอโครงการวิจัย', 'https://sci.uru.ac.th/docs/download/0018.doc', 'doc', 0, 80, 1),
(4, 'แบบเสนอภาระงานวิจัย', 'https://sci.uru.ac.th/docs/download/0053.doc', 'doc', 0, 90, 1),
(4, 'หนังสือมอบอำนาจดำเนินการจดลิขสิทธิ์แทน', 'https://sci.uru.ac.th/docs/download/0057.doc', 'doc', 0, 100, 1),
(4, 'แบบรายงานการนำผลงานวิจัยไปสู่การใช้ประโยชน์', 'https://sci.uru.ac.th/docs/download/0038.doc', 'doc', 0, 110, 1),
(4, 'แบบฟอร์มขอรับรางวัลการเขียนบทความ (การนำเสนอ)', 'https://sci.uru.ac.th/docs/download/0045.doc', 'doc', 0, 120, 1),
(4, 'แบบฟอร์มขอรับรางวัลการเขียนบทความ (วารสาร)', 'https://sci.uru.ac.th/docs/download/0046.doc', 'doc', 0, 130, 1);

-- Official: orders (5)
INSERT INTO download_documents (category_id, title, external_url, file_type, file_size, sort_order, is_active) VALUES
(5, 'คำสั่งแต่งตั้งอาจารย์ที่ปรึกษานักศึกษาภาคปกติ ปีการศึกษา 2567', 'https://sci.uru.ac.th/doctopic/250', 'link', 0, 10, 1),
(5, 'คำสั่งแต่งตั้งอาจารย์ที่ปรึกษานักศึกษาภาคปกติ ปีการศึกษา 2566', 'https://sci.uru.ac.th/doctopic/200', 'link', 0, 20, 1),
(5, 'คำสั่งแต่งตั้งอาจารย์ที่ปรึกษานักศึกษาภาคปกติ ปีการศึกษา 2565', 'https://sci.uru.ac.th/docs/download/teacher2565.pdf', 'pdf', 0, 30, 1);

-- Official: plans (6)
INSERT INTO download_documents (category_id, title, external_url, file_type, file_size, sort_order, is_active) VALUES
(6, 'แผนพัฒนาบุคลากรระยะ 5 ปี (ปีงบประมาณ 2566-2569)', 'https://sci.uru.ac.th/doctopic/222', 'link', 0, 10, 1),
(6, 'หลักเกณฑ์ อัตราค่าใช้จ่าย และแนวทางการพิจารณางบประมาณ พ.ศ. 2561', 'https://sci.uru.ac.th/docs/download/6016.pdf', 'pdf', 0, 20, 1),
(6, 'หลักเกณฑ์และอัตราค่าใช้จ่ายในลักษณะค่าตอบแทน พ.ศ. 2561', 'https://sci.uru.ac.th/docs/download/6015.pdf', 'pdf', 0, 30, 1),
(6, 'ระเบียบกระทรวงการคลังว่าด้วยค่าใช้จ่ายในการฝึกอบรม', 'https://sci.uru.ac.th/docs/download/6014.pdf', 'pdf', 0, 40, 1);

-- Official: students (7)
INSERT INTO download_documents (category_id, title, external_url, file_type, file_size, sort_order, is_active) VALUES
(7, 'ปฏิทินกิจกรรมนักศึกษา ปีการศึกษา 2568', 'https://sci.uru.ac.th/doctopic/253', 'link', 0, 10, 1),
(7, 'คู่มือผู้ปกครองคณะวิทยาศาสตร์และเทคโนโลยี', 'https://sci.uru.ac.th/doctopic/252', 'link', 0, 20, 1),
(7, 'แนวปฏิบัติการแต่งกายนักศึกษา', 'https://sci.uru.ac.th/doctopic/205', 'link', 0, 30, 1);
