-- เพิ่ม comment กฎธุรกิจให้ตาราง personnel_programs (สำหรับ DB ที่มีตารางอยู่แล้ว)
-- กฎ: อาจารย์ 1 คน เป็นประธานได้ 1 หลักสูตร | เป็นอาจารย์ประจำได้หลายหลักสูตร | หลักสูตร 1 หลักสูตร มีประธานได้ 1 คน
-- การบังคับทำในแอป (PersonnelProgramModel::setProgramsForPersonnelWithPrimary)
-- Run: mysql -u root -p newscience < database/enforce_chair_rules_comment.sql

SET NAMES utf8mb4;

ALTER TABLE `personnel_programs`
COMMENT = 'Pivot บุคลากร–หลักสูตร. กฎ: 1 คนเป็นประธานได้1หลักสูตร, 1หลักสูตรมีประธานได้1คน. Enforce ในแอป';
