<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/about', 'Pages::about');
$routes->get('/academics', 'Pages::academics');
$routes->get('/research', 'Pages::research');
$routes->get('/campus-life', 'Pages::campusLife');
$routes->get('/admission', 'Pages::admission');
$routes->get('/news', 'Pages::news');
$routes->get('/news/(:num)', 'Pages::newsDetail/$1');
$routes->get('/events', 'Pages::events');
$routes->get('/events/(:num)', 'Pages::eventDetail/$1');
$routes->get('/calendar', 'Pages::calendar');
$routes->get('/contact', 'Pages::contact');
$routes->get('/complaints', 'ComplaintController::index');
$routes->post('/complaints/submit', 'ComplaintController::submit', ['filter' => 'csrf']);
$routes->get('/personnel', 'Pages::personnel');
$routes->get('/executives', 'Pages::executives');
$routes->get('/documents', 'Pages::documents');
$routes->get('/support-documents', 'Pages::supportDocuments');
$routes->get('/official-documents', 'Pages::officialDocuments');
$routes->get('/promotion-criteria', 'Pages::promotionCriteria');
$routes->get('/internal-documents', 'Pages::internalDocuments');

// Program Website Routes (Public) — /program/{id} redirects to new SPA /p/{id}
$routes->get('program/(:num)', 'ProgramSpaController::showByProgramId/$1');
$routes->get('program-site/(:num)', 'ProgramWebsite::index/$1');
$routes->get('program-detail', 'ProgramDetailController::index');

// Program SPA (Modern Luxury): ใช้ program id
$routes->get('p/(:num)', 'ProgramSpaController::index/$1');
$routes->get('p/(:num)/check', 'ProgramSpaController::checkSystem/$1');
$routes->get('p/(:num)/data', 'ProgramSpaController::getData/$1');
$routes->get('p/(:num)/main', 'ProgramSpaController::main/$1');

// Serve uploaded files from writable (fallback to public)
// ใส่ path เต็มก่อน (programs/8/hero/xxx.jpg) ไป fileByPath — เหลือ 2 segments ค่อยไป file()
$routes->get('serve/uploads/(.+)', 'Serve::fileByPath/$1');
$routes->get('serve/uploads/(:segment)/(:segment)', 'Serve::file/$1/$2');
$routes->get('serve/thumb/(:segment)/(:segment)', 'Serve::thumb/$1/$2');

// Faculty personnel from external Research Record API (see doc_api.rd)
$routes->group('personnel-api', static function ($routes) {
    $routes->get('faculty', 'FacultyPersonnelController::index');
    $routes->get('faculty/status', 'FacultyPersonnelController::status');
});

// Personnel CV page (public)
$routes->get('personnel-cv/(:segment)', 'PersonnelCvController::show/$1');

// Local only: ข้าม Authen สำหรับทดสอบ (ทำงานเฉพาะ ENVIRONMENT === 'development')
$routes->get('/dev/login-as-admin', 'Dev::loginAsAdmin');
$routes->get('/dev/login-as-student', 'Dev::loginAsStudent');
$routes->get('/dev/login-as-student-admin', 'Dev::loginAsStudentAdmin');
$routes->get('/dev/test-content-builder', 'Dev::testContentBuilder');
// Mock URU Portal OAuth (ทดสอบ OAuth flow โดยไม่ต้องผ่าน Portal จริง)
$routes->get('/dev/mock-oauth-student', 'Dev::mockOAuthStudent');
$routes->get('/dev/mock-oauth-personnel', 'Dev::mockOAuthPersonnel');
// นักศึกษา dummy สำหรับทดสอบ (u59=สโมสร, u69=นักศึกษาปกติ) — ENVIRONMENT === development เท่านั้น
$routes->get('/dev/student-test', 'Dev::studentTest');
$routes->get('/dev/seed-student-dummies', 'Dev::seedStudentDummies');
$routes->get('/dev/login-dummy-club', 'Dev::loginDummyStudentClub');
$routes->get('/dev/login-dummy-student', 'Dev::loginDummyStudentRegular');

// Admin Auth Routes (no filter)
$routes->get('/admin/login', 'Admin\Auth::login');
$routes->post('/admin/login', 'Admin\Auth::attemptLogin'); // ปิด login email/password — redirect ไป OAuth แทน
$routes->get('/admin/logout', 'Admin\Auth::logout');
$routes->get('/admin/clear-session', 'Admin\Auth::clearSession');
// รับ redirect หลัง logout จาก Edoc — ให้ Edoc ตั้ง redirect หลัง signout มาที่ URL นี้เพื่อเด้งกลับ newScience
$routes->get('/admin/edoc-logout-return', 'Admin\Auth::edocLogoutReturn');
// SSO ผ่าน Edoc (URU Portal เดียว)
$routes->get('/admin/portal-login', 'Admin\Auth::portalLogin');
$routes->get('/admin/oauth-callback', 'Admin\Auth::oauthCallback');

// -------------------------------------------------------------------------
// URU Portal OAuth 2.0 Routes (ใช้ร่วมกันทั้ง นักศึกษา และ บุคลากร)
// Callback URL: https://sci.uru.ac.th/index.php/oauth
// -------------------------------------------------------------------------
// redirect ผู้ใช้ไปล็อกอินที่ URU Portal
$routes->get('/oauth/login', 'OAuthController::login');
// callback จาก URU Portal (รับ ?code=xxx&state=xxx) — ต้องตรงกับ redirect_uri ที่ลงทะเบียนไว้
$routes->get('/oauth', 'OAuthController::callback');
// ออกจากระบบ (ล้าง session ฝั่ง newScience)
$routes->get('/oauth/logout', 'OAuthController::logout');

// หน้า Dashboard ผู้ใช้งาน — หลัง login มาที่หน้านี้ (ทุก role); หน้าตาเหมือน Admin, เมนู Edoc + หน้าการจัดการงานวิจัย; หน้าแรก = ข้อมูลผู้ใช้คนนั้น
$routes->get('/dashboard', 'User\Dashboard::index', ['filter' => 'loggedin']);
$routes->get('/dashboard/calendar', 'User\CalendarController::index', ['filter' => 'loggedin']);
$routes->get('/dashboard/profile', 'User\ProfileCv::index', ['filter' => 'loggedin']);
$routes->get('/dashboard/profile/cv', 'User\ProfileCv::cv', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/cv/sync-from-rr', 'User\ProfileCv::syncFromResearchRecord', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/cv/photo', 'User\ProfileCv::saveCvPhoto', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/cv/photo/remove', 'User\ProfileCv::removeCvPhoto', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/cv/narrative', 'User\ProfileCv::saveCvNarrative', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/cv/section/save', 'User\ProfileCv::saveCvSection', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/cv/section/reorder', 'User\ProfileCv::reorderCvSections', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/cv/section/toggle/(:num)', 'User\ProfileCv::toggleCvSectionPublic/$1', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/cv/section/delete/(:num)', 'User\ProfileCv::deleteCvSection/$1', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/cv/entry/save', 'User\ProfileCv::saveCvEntry', ['filter' => 'loggedin']);
$routes->get('/dashboard/profile/cv/entry/(:num)', 'User\ProfileCv::getCvEntry/$1', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/cv/entry/delete/(:num)', 'User\ProfileCv::deleteCvEntry/$1', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/cv/entry/toggle/(:num)', 'User\ProfileCv::toggleCvEntryPublic/$1', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/cv/entry/reorder', 'User\ProfileCv::reorderCvEntries', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/cv/orcid/import', 'User\ProfileCv::importOrcidCv', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/cv/orcid/save', 'User\ProfileCv::saveOrcidId', ['filter' => 'loggedin']);
$routes->get('/dashboard/profile/research-record-sync', 'User\ResearchRecordSync::index', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/research-record-sync/compare', 'User\ResearchRecordSync::compare', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/research-record-sync/apply', 'User\ResearchRecordSync::apply', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/research-record-sync/pull-all', 'User\ResearchRecordSync::pullAll', ['filter' => 'loggedin']);
$routes->post('/dashboard/profile/research-record-sync/push-all', 'User\ResearchRecordSync::pushAll', ['filter' => 'loggedin']);
$routes->get('/go-research-record', 'Admin\Auth::goResearchRecord', ['filter' => 'loggedin']);

// E-Certificate กิจกรรม (อาจารย์/บุคลากร — Dashboard)
$routes->group('dashboard/cert-events', ['filter' => 'certorganizer'], static function ($routes) {
    $routes->get('/', 'User\CertEvents::index');
    $routes->get('issued-report', 'User\CertEvents::issuedReport');
    $routes->get('create', 'User\CertEvents::create');
    $routes->post('store', 'User\CertEvents::store');
    $routes->get('(:num)/background-preview', 'User\CertEvents::backgroundPreview/$1');
    $routes->get('(:num)', 'User\CertEvents::show/$1');
    $routes->get('(:num)/edit', 'User\CertEvents::edit/$1');
    $routes->post('(:num)/update', 'User\CertEvents::update/$1');
    $routes->get('(:num)/delete', 'User\CertEvents::delete/$1');
    $routes->post('(:num)/add-recipient', 'User\CertEvents::addRecipient/$1');
    $routes->get('recipient/(:num)/remove', 'User\CertEvents::removeRecipient/$1');
    $routes->get('(:num)/import', 'User\CertEvents::importForm/$1');
    $routes->post('(:num)/import', 'User\CertEvents::processImport/$1');
    $routes->get('(:num)/export', 'User\CertEvents::exportRecipients/$1');
    $routes->get('(:num)/issue', 'User\CertEvents::issueCertificates/$1');
});

// Teaching evaluation (ประเมินผลการสอน) — lecture submit + admin ต้อง login; แบบฟอร์มผู้ประเมินเข้าจาก link
$routes->group('evaluate', ['filter' => 'loggedin'], static function ($routes) {
    $routes->get('/', 'Evaluate\LectureEvaluateController::submitEvaluate');
    $routes->post('lecture-evaluate/save', 'Evaluate\LectureEvaluateController::save');
    $routes->get('admin', 'Evaluate\AdminEvaluateController::index');
    $routes->get('admin/search', 'Evaluate\AdminEvaluateController::searchByEmail');
    $routes->post('admin/getResult', 'Evaluate\AdminEvaluateController::getResult');
    $routes->post('admin/getEvaluateInfo', 'Evaluate\AdminEvaluateController::getEvaluateInfo');
    $routes->post('admin/printRefAndSave', 'Evaluate\AdminEvaluateController::printRefAndSave');
    $routes->post('admin/sendmailEvaluate', 'Evaluate\AdminEvaluateController::sendmailEvaluate');
    $routes->post('admin/saveDate', 'Evaluate\AdminEvaluateController::saveDate');
    $routes->post('admin/delete', 'Evaluate\AdminEvaluateController::delete');
    // Admin CRUD ผู้ทรงคุณวุฒิ
    $routes->get('admin/referees', 'Evaluate\AdminRefereeController::index');
    $routes->post('admin/referees/save', 'Evaluate\AdminRefereeController::save');
    $routes->get('admin/referees/get/(:num)', 'Evaluate\AdminRefereeController::get/$1');
    $routes->post('admin/referees/delete', 'Evaluate\AdminRefereeController::delete');
    $routes->post('admin/referees/toggleStatus', 'Evaluate\AdminRefereeController::toggleStatus');

    // Admin ตั้งค่าระบบการประเมิน
    $routes->get('admin/settings', 'Admin\EvaluateSettingsController::index');
    $routes->post('admin/settings/save', 'Admin\EvaluateSettingsController::save');
});

$routes->get('evaluate/evaluate/(:segment)', 'Evaluate\EvaluateController::index/$1');
$routes->post('evaluate/saveEvaluate', 'Evaluate\EvaluateController::saveEvaluate');

// DevTools - สำหรับทดสอบโดยไม่ต้องผ่าน Authen (development only)
$routes->group('dev', function ($routes) {
    $routes->get('/', 'DevTools::index');
    $routes->get('login-as-student/(:num)', 'DevTools::loginAsStudent/$1');
    $routes->get('login-as-student', 'DevTools::loginAsStudent');
    $routes->get('login-as-staff/(:num)', 'DevTools::loginAsStaff/$1');
    $routes->get('login-as-staff', 'DevTools::loginAsStaff');
    $routes->get('login-as-approver/(:segment)/(:num)', 'DevTools::loginAsApprover/$1/$2');
    $routes->get('login-as-approver/(:segment)', 'DevTools::loginAsApprover/$1');
    $routes->get('logout', 'DevTools::logout');
    $routes->get('create-test-request/(:num)', 'DevTools::createTestRequest/$1');
    $routes->get('create-test-request', 'DevTools::createTestRequest');

    // Upload Test Routes
    $routes->get('upload-test', 'Dev\UploadTest::index');
    $routes->post('upload-test/test-pdf', 'Dev\UploadTest::testPdfUpload');
    $routes->post('upload-test/test-csv', 'Dev\UploadTest::testCsvUpload');
    $routes->post('upload-test/cleanup-temp', 'Dev\UploadTest::cleanupTemp');
    $routes->get('upload-test/folder-info', 'Dev\UploadTest::getFolderInfo');

    // Field Mapping Test Routes
    $routes->get('field-test', 'Dev\FieldTest::index');
    $routes->post('field-test/test-pdf-generation', 'Dev\FieldTest::testPdfGeneration');
});

// Student Portal Routesal หลัก = hub, บาร์โค้ด, ข่าว/Event)
$routes->get('/student/login', 'Student\Auth::login');
$routes->post('/student/login', 'Student\Auth::attemptLogin');
$routes->get('/student/logout', 'Student\Auth::logout');
$routes->get('/student', 'Student\Dashboard::index', ['filter' => 'studentauth']);
$routes->get('/student/dashboard', 'Student\Dashboard::index', ['filter' => 'studentauth']);
$routes->get('/student/barcodes', 'Student\Dashboard::barcodes', ['filter' => 'studentauth']);
$routes->get('/student/barcodes/event/(:num)', 'Student\Dashboard::barcodeEvent/$1', ['filter' => 'studentauth']);
$routes->post('/student/barcodes/claim/(:num)', 'Student\Dashboard::claimBarcode/$1', ['filter' => 'studentauth']);
$routes->post('/student/barcodes/claim-from-event/(:num)', 'Student\Dashboard::claimFromEvent/$1', ['filter' => 'studentauth']);
$routes->get('/student/events', 'Student\Dashboard::events', ['filter' => 'studentauth']);
// Student Certificates (ระบบใหม่: นักศึกษาดู/ดาวน์โหลดได้อย่างเดียว ไม่สามารถขอเอง)
$routes->get('/student/certificates', 'Student\Certificate::index', ['filter' => 'studentauth']);
$routes->get('/student/certificates/(:num)', 'Student\Certificate::show/$1', ['filter' => 'studentauth']);
$routes->get('/student/certificates/(:num)/download', 'Student\Certificate::download/$1', ['filter' => 'studentauth']);

// Student Admin (จัดการบาร์โค้ด — แยกจาก Admin หลัก; เข้าได้ทั้ง admin ระบบ และนักศึกษาสโมสร)
$routes->group('student-admin', ['filter' => 'studentadmin'], function ($routes) {
    $routes->get('barcode-events', 'StudentAdmin\BarcodeEvents::index');
    $routes->get('barcode-events/create', 'StudentAdmin\BarcodeEvents::create');
    $routes->post('barcode-events/store', 'StudentAdmin\BarcodeEvents::store');
    // Ajax (ต้องอยู่ก่อน route barcode-events/(:num) เพื่อไม่ให้ถูกจับเป็น id)
    $routes->get('barcode-events/ajax/events-table', 'StudentAdmin\BarcodeEvents::ajaxEventsTable');
    $routes->get('barcode-events/ajax/event/(:num)', 'StudentAdmin\BarcodeEvents::ajaxEventDetail/$1');
    $routes->get('barcode-events/ajax/barcodes/(:num)', 'StudentAdmin\BarcodeEvents::ajaxBarcodes/$1');
    $routes->get('barcode-events/ajax/eligibles-data/(:num)', 'StudentAdmin\BarcodeEvents::ajaxEligiblesData/$1');
    $routes->post('barcode-events/ajax/delete-event/(:num)', 'StudentAdmin\BarcodeEvents::ajaxDeleteEvent/$1');
    $routes->get('barcode-events/(:num)', 'StudentAdmin\BarcodeEvents::show/$1');
    $routes->get('barcode-events/edit/(:num)', 'StudentAdmin\BarcodeEvents::edit/$1');
    $routes->post('barcode-events/update/(:num)', 'StudentAdmin\BarcodeEvents::update/$1');
    $routes->get('barcode-events/delete/(:num)', 'StudentAdmin\BarcodeEvents::delete/$1');
    $routes->post('barcode-events/import/(:num)', 'StudentAdmin\BarcodeEvents::import/$1');
    $routes->post('barcode-events/parse-file/(:num)', 'StudentAdmin\BarcodeEvents::parseFileUpload/$1');
    $routes->post('barcode-events/delete-barcode/(:num)/(:num)', 'StudentAdmin\BarcodeEvents::deleteBarcode/$1/$2');
    $routes->post('barcode-events/unassign/(:num)/(:num)', 'StudentAdmin\BarcodeEvents::unassign/$1/$2');
    $routes->get('barcode-events/eligibles/(:num)', 'StudentAdmin\BarcodeEvents::eligibles/$1');
    $routes->post('barcode-events/add-eligible/(:num)', 'StudentAdmin\BarcodeEvents::addEligible/$1');
    $routes->post('barcode-events/remove-eligible/(:num)/(:num)', 'StudentAdmin\BarcodeEvents::removeEligible/$1/$2');
});

// Admin Routes (protected by adminauth + ตรวจสิทธิ์แต่ละส่วนจาก user_system_access)
$routes->group('admin', ['filter' => ['adminauth', 'adminsystemaccess']], function ($routes) {
    // Executive Dashboard (คณบดี/รองคณบดี — ตรวจ role ใน controller)
    $routes->get('executive-dashboard', 'Admin\ExecutiveDashboard::index');

    // Website visit reports
    $routes->get('visit-reports', 'Admin\VisitReports::index');
    $routes->get('visit-reports/data', 'Admin\VisitReports::data');
    $routes->get('visit-reports/export', 'Admin\VisitReports::export');

    // ปฏิทินนัดหมายกิจกรรมผู้บริหาร
    $routes->get('calendar', 'Admin\Calendar::index');

    // จัดการผู้ใช้ (Super_admin และ Faculty_admin)
    $routes->get('users', 'Admin\UserManagement::index');

    // ตัวแทนนักศึกษาสโมสร (faculty_admin / admin ในหลักสูตร)
    $routes->get('club-representatives', 'Admin\ClubRepresentatives::index');
    $routes->post('club-representatives/set-role', 'Admin\ClubRepresentatives::setClubRole');

    // AJAX endpoints for user management
    $routes->get('users/get-users', 'Admin\UserManagement::getUsers');
    $routes->get('users/get-students', 'Admin\UserManagement::getStudents');
    $routes->get('users/get-user-data/(:num)', 'Admin\UserManagement::getUserData/$1');
    $routes->get('users/get-student-data/(:num)', 'Admin\UserManagement::getStudentData/$1');
    $routes->post('users/ajax-update-user/(:num)', 'Admin\UserManagement::ajaxUpdateUser/$1');
    $routes->post('users/ajax-update-student/(:num)', 'Admin\UserManagement::ajaxUpdateStudent/$1');
    $routes->post('users/toggle-user-status/(:num)', 'Admin\UserManagement::toggleUserStatus/$1');
    $routes->post('users/toggle-student-status/(:num)', 'Admin\UserManagement::toggleStudentStatus/$1');
    $routes->post('users/bulk-update', 'Admin\UserManagement::bulkUpdate');

    // System Access Management (for user permissions)
    $routes->get('users/system-access/(:num)', 'Admin\UserManagement::getUserSystemAccess/$1');
    $routes->post('users/system-access/(:num)', 'Admin\UserManagement::updateUserSystemAccess/$1');

    // News Management
    $routes->get('news', 'Admin\News::index');
    $routes->get('news/get-paginated', 'Admin\News::getPaginated');
    $routes->get('news/create', 'Admin\News::create');
    $routes->post('news/store', 'Admin\News::store');
    $routes->get('news/edit/(:num)', 'Admin\News::edit/$1');
    $routes->post('news/update/(:num)', 'Admin\News::update/$1');
    $routes->get('news/delete/(:num)', 'Admin\News::delete/$1');

    // Organization (โครงสร้างองค์กร)
    $routes->get('organization', 'Admin\Organization::index');
    $routes->get('organization/create', 'Admin\Organization::create');
    $routes->post('organization/store', 'Admin\Organization::store');
    $routes->get('organization/edit/(:num)', 'Admin\Organization::edit/$1');
    $routes->post('organization/update/(:num)', 'Admin\Organization::update/$1');
    $routes->get('organization/delete/(:num)', 'Admin\Organization::delete/$1');

    // Programs (หลักสูตร)
    $routes->get('programs', 'Admin\Programs::index');
    $routes->get('programs/create', 'Admin\Programs::create');
    $routes->post('programs/store', 'Admin\Programs::store');
    $routes->get('programs/edit/(:num)', 'Admin\Programs::edit/$1');
    $routes->post('programs/update/(:num)', 'Admin\Programs::update/$1');
    $routes->get('programs/delete/(:num)', 'Admin\Programs::delete/$1');

    // Hero Slides Management
    $routes->get('hero-slides', 'Admin\HeroSlides::index');
    $routes->get('hero-slides/create', 'Admin\HeroSlides::create');
    $routes->post('hero-slides/store', 'Admin\HeroSlides::store');
    $routes->get('hero-slides/edit/(:num)', 'Admin\HeroSlides::edit/$1');
    $routes->post('hero-slides/update/(:num)', 'Admin\HeroSlides::update/$1');
    $routes->get('hero-slides/delete/(:num)', 'Admin\HeroSlides::delete/$1');
    $routes->post('hero-slides/toggle-active/(:num)', 'Admin\HeroSlides::toggleActive/$1');
    $routes->post('hero-slides/update-order', 'Admin\HeroSlides::updateOrder');

    // Urgent Popups (ประกาศด่วน ป๊อปอัปหน้าแรก สูงสุด 3 รายการ)
    $routes->get('urgent-popups', 'Admin\UrgentPopups::index');
    $routes->get('urgent-popups/create', 'Admin\UrgentPopups::create');
    $routes->post('urgent-popups/store', 'Admin\UrgentPopups::store');
    $routes->get('urgent-popups/edit/(:num)', 'Admin\UrgentPopups::edit/$1');
    $routes->post('urgent-popups/update/(:num)', 'Admin\UrgentPopups::update/$1');
    $routes->get('urgent-popups/delete/(:num)', 'Admin\UrgentPopups::delete/$1');
    $routes->post('urgent-popups/toggle-active/(:num)', 'Admin\UrgentPopups::toggleActive/$1');

    // Executive Posters (โปสเตอร์ผู้บริหาร — สไลด์หน้า About)
    $routes->get('executive-posters', 'Admin\ExecutivePosters::index');
    $routes->get('executive-posters/create', 'Admin\ExecutivePosters::create');
    $routes->post('executive-posters/store', 'Admin\ExecutivePosters::store');
    $routes->get('executive-posters/edit/(:num)', 'Admin\ExecutivePosters::edit/$1');
    $routes->post('executive-posters/update/(:num)', 'Admin\ExecutivePosters::update/$1');
    $routes->get('executive-posters/delete/(:num)', 'Admin\ExecutivePosters::delete/$1');
    $routes->post('executive-posters/toggle-active/(:num)', 'Admin\ExecutivePosters::toggleActive/$1');

    // Certificate Events (กิจกรรม/อบรมที่จะออก Certificate)
    $routes->get('cert-events/issued-report', 'Admin\CertEvents::issuedReport');
    $routes->get('cert-events', 'Admin\CertEvents::index');
    $routes->get('cert-events/create', 'Admin\CertEvents::create');
    $routes->post('cert-events/store', 'Admin\CertEvents::store');
    $routes->get('cert-events/(:num)/background-preview', 'Admin\CertEvents::backgroundPreview/$1');
    $routes->get('cert-events/(:num)', 'Admin\CertEvents::show/$1');
    $routes->get('cert-events/(:num)/edit', 'Admin\CertEvents::edit/$1');
    $routes->post('cert-events/(:num)/update', 'Admin\CertEvents::update/$1');
    $routes->get('cert-events/(:num)/delete', 'Admin\CertEvents::delete/$1');
    $routes->post('cert-events/(:num)/add-recipient', 'Admin\CertEvents::addRecipient/$1');
    $routes->get('cert-events/recipient/(:num)/remove', 'Admin\CertEvents::removeRecipient/$1');
    $routes->get('cert-events/(:num)/import', 'Admin\CertEvents::importForm/$1');
    $routes->post('cert-events/(:num)/import', 'Admin\CertEvents::processImport/$1');
    $routes->get('cert-events/(:num)/export', 'Admin\CertEvents::exportRecipients/$1');
    $routes->get('cert-events/(:num)/issue', 'Admin\CertEvents::issueCertificates/$1');

    // Events (กิจกรรมที่จะมาถึง)
    $routes->get('events', 'Admin\Events::index');
    $routes->get('events/create', 'Admin\Events::create');
    $routes->post('events/store', 'Admin\Events::store');
    $routes->get('events/edit/(:num)', 'Admin\Events::edit/$1');
    $routes->post('events/update/(:num)', 'Admin\Events::update/$1');
    $routes->get('events/delete/(:num)', 'Admin\Events::delete/$1');

    // Faculty Download Management (หมวดดาวน์โหลด + เอกสาร)
    $routes->get('downloads', 'Admin\Downloads::index');
    $routes->post('downloads/store-category', 'Admin\Downloads::storeCategory');
    $routes->post('downloads/update-category/(:num)', 'Admin\Downloads::updateCategory/$1');
    $routes->get('downloads/delete-category/(:num)', 'Admin\Downloads::deleteCategory/$1');
    $routes->post('downloads/update-category-order', 'Admin\Downloads::updateCategoryOrder');
    $routes->get('downloads/documents/(:num)', 'Admin\Downloads::documents/$1');
    $routes->post('downloads/upload/(:num)', 'Admin\Downloads::upload/$1');
    $routes->get('downloads/edit/(:num)', 'Admin\Downloads::edit/$1');
    $routes->post('downloads/update/(:num)', 'Admin\Downloads::update/$1');
    $routes->get('downloads/delete/(:num)', 'Admin\Downloads::delete/$1');
    $routes->post('downloads/update-doc-order', 'Admin\Downloads::updateDocOrder');

    // Academic Service (ข้อมูลการบริการวิชาการ)
    $routes->get('academic-services', 'Admin\AcademicServices::index');
    $routes->get('academic-services/create', 'Admin\AcademicServices::create');
    $routes->get('academic-services/form-view/(:num)', 'Admin\AcademicServices::formView/$1');
    $routes->get('academic-services/form-view', 'Admin\AcademicServices::formView');
    $routes->get('academic-services/detail-view/(:num)', 'Admin\AcademicServices::detailView/$1');
    $routes->post('academic-services/store', 'Admin\AcademicServices::store');
    $routes->get('academic-services/edit/(:num)', 'Admin\AcademicServices::edit/$1');
    $routes->post('academic-services/update/(:num)', 'Admin\AcademicServices::update/$1');
    $routes->post('academic-services/delete-attachment/(:num)', 'Admin\AcademicServices::deleteAttachment/$1');
    $routes->get('academic-services/delete/(:num)', 'Admin\AcademicServices::delete/$1');
    $routes->get('academic-services/search-users', 'Admin\AcademicServices::searchUsers');
    $routes->get('academic-services/report', 'Admin\AcademicServices::report');
    $routes->get('academic-services/report-data', 'Admin\AcademicServices::reportData');
    $routes->get('academic-services/report/export', 'Admin\AcademicServices::reportExport');

    // Site Settings Management
    $routes->get('settings', 'Admin\Settings::index');
    $routes->post('settings/store', 'Admin\Settings::store');
    $routes->get('settings/create', 'Admin\Settings::create');
    $routes->post('settings/store-new', 'Admin\Settings::storeNew');
    $routes->get('settings/delete/(:num)', 'Admin\Settings::delete/$1');
    $routes->get('settings/init-defaults', 'Admin\Settings::initDefaults');

    // Complaints inbox (Super Admin only - enforced in controller)
    $routes->get('complaints', 'Admin\Complaints::index');
    $routes->post('complaints/update-status/(:num)', 'Admin\Complaints::updateStatus/$1', ['filter' => 'csrf']);

    // ไป Research Record โดยไม่ต้อง login ซ้ำ (ส่ง signed token จาก email)
    $routes->get('go-research-record', 'Admin\Auth::goResearchRecord');
});

// Public Certificate Verification (no login required)
$routes->get('verify/(:segment)', 'CertVerify::verify/$1');
$routes->post('verify/check-hash', 'CertVerify::checkHash');

// Utility Routes (adminauth + ตรวจสิทธิ์ระบบ utility)
$routes->group('utility', ['filter' => ['adminauth', 'adminsystemaccess']], function ($routes) {
    $routes->post('upload/image', 'Utility\Upload::uploadImage');
    $routes->post('upload/multiple', 'Utility\Upload::uploadMultiple');
    $routes->post('upload/delete/(:num)', 'Utility\Upload::deleteImage/$1');
    $routes->post('upload/delete-file', 'Utility\Upload::deleteFile');
    $routes->get('import-data', 'Utility\ImportData::index');
    $routes->get('categorize-news', 'Utility\CategorizeNews::index');
    $routes->post('categorize-news/run', 'Utility\CategorizeNews::run');
    $routes->get('categorize-news/suggest/(:num)', 'Utility\CategorizeNews::suggest/$1');
});

// API Routes for AJAX
$routes->group('api', function ($routes) {
    // ปฏิทินสาธารณะ — ใครก็ดูได้ (ไม่ต้องล็อกอิน)
    $routes->get('calendar/public/events', 'Api\CalendarApi::publicEvents');
    $routes->get('calendar/public/feed', 'Api\CalendarApi::publicFeedIcs'); // ฟีด .ics สำหรับสมัครรับในแอปปฏิทินมือถือ

    // Calendar (ปฏิทินนัดหมาย — ต้องล็อกอิน)
    $routes->group('calendar', ['filter' => 'loggedin'], function ($routes) {
        $routes->get('events', 'Api\CalendarApi::events');
        $routes->get('users', 'Api\CalendarApi::users');
        $routes->get('event/(:num)', 'Api\CalendarApi::getEvent/$1');
        $routes->get('export-ics', 'Api\CalendarApi::exportIcs');
        $routes->post('store', 'Api\CalendarApi::store');
        $routes->post('update/(:num)', 'Api\CalendarApi::update/$1');
        $routes->post('delete/(:num)', 'Api\CalendarApi::delete/$1');
    });

    // Executive Dashboard stats (admin/super_admin only — ตรวจใน controller)
    $routes->group('executive', ['filter' => 'adminauth'], function ($routes) {
        $routes->get('overview', 'Api\ExecutiveStats::overview');
        $routes->get('personnel', 'Api\ExecutiveStats::personnel');
        $routes->get('programs', 'Api\ExecutiveStats::programs');
        $routes->get('news', 'Api\ExecutiveStats::news');
        $routes->get('edoc', 'Api\ExecutiveStats::edoc');
        $routes->get('certificates', 'Api\ExecutiveStats::certificates');
        $routes->get('research', 'Api\ExecutiveStats::research');
        $routes->get('pageviews', 'Api\ExecutiveStats::pageviews');
        $routes->get('program-summary', 'Api\ExecutiveStats::programSummary');
        $routes->get('academic-services', 'Api\ExecutiveStats::academicServices');
    });

    // News
    $routes->get('news', 'Api::news');
    $routes->get('news/featured', 'Api::newsFeatured');
    $routes->get('news/search', 'Api::newsSearch');
    $routes->get('news/tag/(:segment)', 'Api::newsByTag/$1');
    $routes->get('news/research', 'Api::newsResearch');
    $routes->get('news-tags', 'Api::newsTags');
    $routes->get('news/(:num)', 'Api::newsDetail/$1');

    // Hero Slides
    $routes->get('hero-slides', 'Api::heroSlides');

    // Events (upcoming for home & events page)
    $routes->get('events/upcoming', 'Api::eventsUpcoming');
    $routes->get('events', 'Api::events');

    // Static Content
    $routes->get('personnel', 'Api::personnel');
    $routes->get('personnel/dean', 'Api::dean');
    $routes->get('executives', 'Api::executives');
    $routes->get('departments', 'Api::departments');
    $routes->get('programs', 'Api::programs');
    $routes->get('program/(:num)', 'Api::programDetail/$1');
    $routes->get('settings', 'Api::settings');
    $routes->get('stats', 'Api::stats');

    // Barcode dummy JSON สำหรับทดสอบนำเข้า (รหัสเฉยๆ BC0001, BC0002, ...)
    $routes->get('barcode-dummy', 'Api::barcodeDummy');
});

// Program Admin Routes (Program Chairs)
$routes->group('program-admin', ['filter' => 'programadmin'], function ($routes) {
    $routes->get('/', 'Admin\ProgramAdmin\Dashboard::index');
    $routes->get('edit/(:num)', 'Admin\ProgramAdmin\Dashboard::edit/$1');
    $routes->post('update/(:num)', 'Admin\ProgramAdmin\Dashboard::update/$1');
    $routes->post('update-page/(:num)', 'Admin\ProgramAdmin\Dashboard::updatePage/$1');
    $routes->post('update-page-json/(:num)', 'Admin\ProgramAdmin\Dashboard::updatePageJson/$1');
    $routes->post('update-website/(:num)', 'Admin\ProgramAdmin\Dashboard::updateWebsite/$1');
    $routes->post('update-admission/(:num)', 'Admin\ProgramAdmin\Dashboard::updateAdmission/$1');
    $routes->post('upload-hero/(:num)', 'Admin\ProgramAdmin\Dashboard::uploadHero/$1');
    $routes->post('upload-page-media/(:num)', 'Admin\ProgramAdmin\Dashboard::uploadPageMedia/$1');
    $routes->post('upload-alumni-photo/(:num)', 'Admin\ProgramAdmin\Dashboard::uploadAlumniPhoto/$1');
    $routes->get('downloads/(:num)', 'Admin\ProgramAdmin\Dashboard::downloads/$1');
    $routes->post('upload-download/(:num)', 'Admin\ProgramAdmin\Dashboard::uploadDownload/$1');
    $routes->post('delete-download/(:num)', 'Admin\ProgramAdmin\Dashboard::deleteDownload/$1');
    $routes->post('update-order/(:num)', 'Admin\ProgramAdmin\Dashboard::updateOrder/$1');
    $routes->get('preview/(:num)', 'Admin\ProgramAdmin\Dashboard::preview/$1');
    $routes->get('bundle-export/(:num)', 'Admin\ProgramAdmin\Dashboard::exportContentBundle/$1');
    $routes->get('bundle-template/(:num)', 'Admin\ProgramAdmin\Dashboard::exportContentBundleTemplate/$1');
    $routes->get('bundle-preview/(:num)', 'Admin\ProgramAdmin\Dashboard::currentBundlePreview/$1');
    $routes->post('bundle-import-preview/(:num)', 'Admin\ProgramAdmin\Dashboard::importContentBundlePreview/$1');
    $routes->post('bundle-import-commit/(:num)', 'Admin\ProgramAdmin\Dashboard::importContentBundleCommit/$1');
    $routes->post('toggle-publish/(:num)', 'Admin\ProgramAdmin\Dashboard::togglePublish/$1');

    // Program News (tag program_{id} by default)
    $routes->get('news/(:num)', 'Admin\ProgramAdmin\Dashboard::programNews/$1');
    $routes->post('news/(:num)/create', 'Admin\ProgramAdmin\Dashboard::createProgramNews/$1');

    // Activities (replace Content Builder)
    $routes->get('activities/(:num)', 'Admin\ProgramAdmin\Activities::index/$1');
    $routes->get('activities/(:num)/create', 'Admin\ProgramAdmin\Activities::createActivity/$1');
    $routes->post('activities/(:num)/store', 'Admin\ProgramAdmin\Activities::storeActivity/$1');
    $routes->get('activity/(:num)/edit', 'Admin\ProgramAdmin\Activities::editActivity/$1');
    $routes->post('activity/(:num)/update', 'Admin\ProgramAdmin\Activities::updateActivity/$1');
    $routes->post('activity/(:num)/delete', 'Admin\ProgramAdmin\Activities::deleteActivity/$1');
    $routes->post('activity/(:num)/upload-image', 'Admin\ProgramAdmin\Activities::uploadActivityImage/$1');
    $routes->post('activity-image/(:num)/delete', 'Admin\ProgramAdmin\Activities::deleteActivityImage/$1');
});

// ================================================================
// Edoc Sub-App Routes (prefix: /edoc)
// ================================================================
$routes->group('edoc', ['filter' => 'edocauth'], function ($routes) {
    // E-Document (User)
    $routes->get('/', 'Edoc\EdocController::index');
    $routes->post('getdocinfo', 'Edoc\EdocController::getDocInfo');
    $routes->post('getdoc', 'Edoc\EdocController::getDoc');
    $routes->post('getallviewers', 'Edoc\EdocController::getAllViewers');
    $routes->get('volumes', 'Edoc\EdocController::getVolumes');
    $routes->get('viewPDF/(:any)', 'Edoc\EdocController::viewPDF/$1');
    $routes->post('update-thai-name', 'Edoc\EdocController::updateThaiName');

    // E-Document (Admin)
    $routes->get('admin', 'Edoc\AdminEdocController::index');
    $routes->post('admin/getdoc', 'Edoc\AdminEdocController::getDoc');
    $routes->post('admin/getdocinfo', 'Edoc\AdminEdocController::getDocInfo');
    $routes->post('admin/savedoc', 'Edoc\AdminEdocController::saveDoc');
    $routes->get('admin/gettaggroups', 'Edoc\AdminEdocController::getTagGroups');
    $routes->post('admin/savetaggroup', 'Edoc\AdminEdocController::saveTagGroup');
    $routes->post('admin/deletetaggroup', 'Edoc\AdminEdocController::deleteTagGroup');

    // Volume Management (Admin)
    $routes->get('admin/volumes', 'Edoc\AdminEdocController::getVolumes');
    $routes->get('admin/volumes/years', 'Edoc\AdminEdocController::getAvailableYears');
    $routes->post('admin/volumes/create-year', 'Edoc\AdminEdocController::createYearVolumes');
    $routes->post('admin/volumes/toggle', 'Edoc\AdminEdocController::toggleVolume');

    // Email Tag Suggest (Admin)
    $routes->get('admin/suggest-emails', 'Edoc\AdminEdocController::suggestEmails');
    $routes->get('admin/document-tags', 'Edoc\AdminEdocController::getDocumentTags');

    // Document Analysis
    $routes->get('analysis', 'Edoc\DocumentAnalysisController::index');
    $routes->get('api/summary-metrics', 'Edoc\DocumentAnalysisController::getSummaryMetrics');
    $routes->get('api/doc-type-distribution', 'Edoc\DocumentAnalysisController::getDocTypeDistribution');
    $routes->get('api/monthly-trend', 'Edoc\DocumentAnalysisController::getMonthlyTrend');
    $routes->get('api/top-owners', 'Edoc\DocumentAnalysisController::getTopOwners');
    $routes->get('api/page-distribution', 'Edoc\DocumentAnalysisController::getPageDistribution');
    $routes->get('api/advanced-analytics', 'Edoc\DocumentAnalysisController::getAdvancedAnalytics');
    $routes->get('api/export-report', 'Edoc\DocumentAnalysisController::exportAnalysisReport');

    // Notifications (send-notifications ยังต้อง login)
    $routes->get('send-notifications', 'Edoc\GeneralController::sendTodayDocumentNotifications');

    // File Upload (Edoc-specific)
    $routes->post('upload/edoc', 'Edoc\EdocUploadController::uploadFileEdoc');

    // Diagnostic (dev)
    $routes->get('diagnostic/checkfile/(:any)', 'Edoc\DiagnosticController::checkFile/$1');
    $routes->get('diagnostic/checkfile', 'Edoc\DiagnosticController::checkFile');
    $routes->get('diagnostic/listfiles', 'Edoc\DiagnosticController::listFiles');
});

// Edoc public routes (no auth required — accessed via email link)
$routes->get('edoc/public/secure-access', 'Edoc\GeneralController::secureAccess');
$routes->get('edoc/public/view-file/(:any)', 'Edoc\GeneralController::publicViewFile/$1');
// Edoc notifications — ดึงข้อมูลข่าว/เอกสารประจำวัน (ไม่ต้อง login)
$routes->get('edoc/notifications', 'Edoc\GeneralController::getDocumentNotificationsData');
$routes->get('edoc/notifications/(:segment)', 'Edoc\GeneralController::getDocumentNotificationsData/$1');

// Exam Routes (ตารางคุมสอบ) - JSON Based
$routes->group('exam', ['filter' => 'loggedin'], function ($routes) {
    $routes->get('/', 'ExamJsonController::index');
    $routes->get('get-semesters', 'ExamJsonController::getSemesters');
    $routes->get('get-schedules', 'ExamJsonController::getSchedules');
});

// Admin Exam Routes - JSON Based
$routes->group('admin/exam', ['filter' => ['adminauth', 'adminsystemaccess']], function ($routes) {
    $routes->get('/', 'Admin\ExamJsonAdminController::index');
    $routes->get('upload', 'Admin\ExamJsonAdminController::uploadForm');
    $routes->post('upload', 'Admin\ExamJsonAdminController::upload');
    $routes->get('preview/(:num)/(:num)/(:any)', 'Admin\ExamJsonAdminController::preview/$1/$2/$3');
    $routes->post('publish/(:num)/(:num)/(:any)', 'Admin\ExamJsonAdminController::publish/$1/$2/$3');
    $routes->post('delete/(:num)/(:num)/(:any)', 'Admin\ExamJsonAdminController::delete/$1/$2/$3');
    $routes->get('get-semesters', 'Admin\ExamJsonAdminController::getAvailableSemesters');
    $routes->get('load-data', 'Admin\ExamJsonAdminController::loadData');
});

// Admin User Faculty Routes
$routes->group('admin/user-faculty', ['filter' => ['adminauth', 'adminsystemaccess']], function ($routes) {
    $routes->get('/', 'Admin\UserFacultyController::index');
    $routes->post('update-faculty', 'Admin\UserFacultyController::updateFaculty');
    $routes->post('bulk-update', 'Admin\UserFacultyController::bulkUpdate');
    $routes->get('get-user/(:num)', 'Admin\UserFacultyController::getUser/$1');
});
