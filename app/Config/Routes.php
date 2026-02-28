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
$routes->get('/contact', 'Pages::contact');
$routes->get('/personnel', 'Pages::personnel');
$routes->get('/executives', 'Pages::executives');
$routes->get('/support-documents', 'Pages::supportDocuments');
$routes->get('/official-documents', 'Pages::officialDocuments');
$routes->get('/promotion-criteria', 'Pages::promotionCriteria');

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
$routes->get('/official-documents', 'Pages::officialDocuments');
$routes->get('/promotion-criteria', 'Pages::promotionCriteria');

// Local only: ข้าม Authen สำหรับทดสอบ (ทำงานเฉพาะ ENVIRONMENT === 'development')
$routes->get('/dev/login-as-admin', 'Dev::loginAsAdmin');
$routes->get('/dev/login-as-student', 'Dev::loginAsStudent');
$routes->get('/dev/login-as-student-admin', 'Dev::loginAsStudentAdmin');
$routes->get('/dev/test-content-builder', 'Dev::testContentBuilder');
// Mock URU Portal OAuth (ทดสอบ OAuth flow โดยไม่ต้องผ่าน Portal จริง)
$routes->get('/dev/mock-oauth-student', 'Dev::mockOAuthStudent');
$routes->get('/dev/mock-oauth-personnel', 'Dev::mockOAuthPersonnel');

// Admin Auth Routes (no filter)
$routes->get('/admin/login', 'Admin\Auth::login');
$routes->post('/admin/login', 'Admin\Auth::attemptLogin');
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
$routes->get('/go-research-record', 'Admin\Auth::goResearchRecord', ['filter' => 'loggedin']);

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
    // จัดการผู้ใช้ (Super_admin และ Faculty_admin)
    $routes->get('users', 'Admin\UserManagement::index');

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

    // Certificate Templates Management
    $routes->get('cert-templates', 'Admin\CertTemplates::index');
    $routes->get('cert-templates/create', 'Admin\CertTemplates::create');
    $routes->post('cert-templates/store', 'Admin\CertTemplates::store');
    $routes->get('cert-templates/edit/(:num)', 'Admin\CertTemplates::edit/$1');
    $routes->post('cert-templates/update/(:num)', 'Admin\CertTemplates::update/$1');
    $routes->get('cert-templates/delete/(:num)', 'Admin\CertTemplates::delete/$1');

    // Certificate Template Preview (AJAX)
    $routes->post('cert-templates/preview-upload', 'Admin\CertTemplatePreview::uploadPreview');
    $routes->post('cert-templates/extract-text', 'Admin\CertTemplatePreview::extractText');
    $routes->post('cert-templates/clear-temp', 'Admin\CertTemplatePreview::clearTemp');

    // Certificate Events (กิจกรรม/อบรมที่จะออก Certificate - ระบบใหม่)
    $routes->get('cert-events', 'Admin\CertEvents::index');
    $routes->get('cert-events/create', 'Admin\CertEvents::create');
    $routes->post('cert-events/store', 'Admin\CertEvents::store');
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

    // Certificates Management (Staff verification - ระบบเดิม เก็บไว้สำหรับ backward compat)
    $routes->get('certificates', 'Admin\Certificates::index');
    $routes->get('certificates/pending', 'Admin\Certificates::pending');
    $routes->get('certificates/(:num)', 'Admin\Certificates::show/$1');
    $routes->post('certificates/verify/(:num)', 'Admin\Certificates::verify/$1');
    $routes->post('certificates/reject/(:num)', 'Admin\Certificates::reject/$1');

    // Events (กิจกรรมที่จะมาถึง)
    $routes->get('events', 'Admin\Events::index');
    $routes->get('events/create', 'Admin\Events::create');
    $routes->post('events/store', 'Admin\Events::store');
    $routes->get('events/edit/(:num)', 'Admin\Events::edit/$1');
    $routes->post('events/update/(:num)', 'Admin\Events::update/$1');
    $routes->get('events/delete/(:num)', 'Admin\Events::delete/$1');

    // Site Settings Management
    $routes->get('settings', 'Admin\Settings::index');
    $routes->post('settings/store', 'Admin\Settings::store');
    $routes->get('settings/create', 'Admin\Settings::create');
    $routes->post('settings/store-new', 'Admin\Settings::storeNew');
    $routes->get('settings/delete/(:num)', 'Admin\Settings::delete/$1');
    $routes->get('settings/init-defaults', 'Admin\Settings::initDefaults');

    // ไป Research Record โดยไม่ต้อง login ซ้ำ (ส่ง signed token จาก email)
    $routes->get('go-research-record', 'Admin\Auth::goResearchRecord');
});

// Approver Routes (Program Chairs & Dean)
$routes->group('approve', ['filter' => 'certapprover'], function ($routes) {
    $routes->get('certificates', 'Approve\Certificate::index');
    $routes->get('certificates/history', 'Approve\Certificate::history');
    $routes->get('certificates/(:num)', 'Approve\Certificate::show/$1');
    $routes->post('certificates/approve/(:num)', 'Approve\Certificate::approve/$1');
    $routes->post('certificates/reject/(:num)', 'Approve\Certificate::reject/$1');
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
    $routes->post('upload-hero/(:num)', 'Admin\ProgramAdmin\Dashboard::uploadHero/$1');
    $routes->post('upload-alumni-photo/(:num)', 'Admin\ProgramAdmin\Dashboard::uploadAlumniPhoto/$1');
    $routes->get('downloads/(:num)', 'Admin\ProgramAdmin\Dashboard::downloads/$1');
    $routes->post('upload-download/(:num)', 'Admin\ProgramAdmin\Dashboard::uploadDownload/$1');
    $routes->post('delete-download/(:num)', 'Admin\ProgramAdmin\Dashboard::deleteDownload/$1');
    $routes->post('update-order/(:num)', 'Admin\ProgramAdmin\Dashboard::updateOrder/$1');
    $routes->get('preview/(:num)', 'Admin\ProgramAdmin\Dashboard::preview/$1');
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
    $routes->get('viewPDF/(:any)', 'Edoc\EdocController::viewPDF/$1');

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

    // Notifications
    $routes->get('notifications', 'Edoc\GeneralController::getDocumentNotificationsData');
    $routes->get('notifications/(:segment)', 'Edoc\GeneralController::getDocumentNotificationsData/$1');
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
