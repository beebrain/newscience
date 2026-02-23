-- Content Builder Testing Setup SQL
-- This script creates test users for content builder access testing

-- Super Admin user
INSERT INTO user (login_uid, email, password, role, thai_name, thai_lastname, status, created_at, updated_at) 
VALUES ('test_super_admin', 'super_admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'ทดสอบ', 'ซูเปอร์แอดมิน', 'active', NOW(), NOW());

-- Admin user  
INSERT INTO user (login_uid, email, password, role, thai_name, thai_lastname, status, created_at, updated_at)
VALUES ('test_admin', 'admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'ทดสอบ', 'แอดมิน', 'active', NOW(), NOW());

-- Faculty Chair user
INSERT INTO user (login_uid, email, password, role, thai_name, thai_lastname, status, created_at, updated_at)
VALUES ('test_faculty_chair', 'faculty_chair@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', 'ทดสอบ', 'อาจารย์ประธาน', 'active', NOW(), NOW());

-- Faculty Regular user
INSERT INTO user (login_uid, email, password, role, thai_name, thai_lastname, status, created_at, updated_at)
VALUES ('test_faculty_regular', 'faculty_regular@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', 'ทดสอบ', 'อาจารย์ทั่วไป', 'active', NOW(), NOW());

-- Regular user
INSERT INTO user (login_uid, email, password, role, thai_name, thai_lastname, status, created_at, updated_at)
VALUES ('test_user', 'user@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'ทดสอบ', 'ผู้ใช้ทั่วไป', 'active', NOW(), NOW());

-- Set up Faculty Chair as Program Chair for program ID 1
INSERT INTO personnel_programs (personnel_uid, program_id, role_in_curriculum, created_at, updated_at)
VALUES ((SELECT uid FROM user WHERE login_uid = 'test_faculty_chair'), 1, 'ประธานหลักสูตร', NOW(), NOW());

-- Display created users
SELECT uid, login_uid, email, role, thai_name, thai_lastname, status 
FROM user 
WHERE login_uid LIKE 'test_%'
ORDER BY role, login_uid;
