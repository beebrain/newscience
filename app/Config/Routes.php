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

// Program Website Routes (Public)
$routes->get('program/(:num)', 'ProgramController::show/$1');

// Serve uploaded files from writable (fallback to public)
$routes->get('serve/uploads/(:segment)/(:segment)', 'Serve::file/$1/$2');
$routes->get('serve/thumb/(:segment)/(:segment)', 'Serve::thumb/$1/$2');

// Faculty personnel from external Research Record API (see doc_api.rd)
$routes->group('personnel-api', static function ($routes) {
    $routes->get('faculty', 'FacultyPersonnelController::index');
    $routes->get('faculty/status', 'FacultyPersonnelController::status');
});
$routes->get('/official-documents', 'Pages::officialDocuments');
$routes->get('/promotion-criteria', 'Pages::promotionCriteria');

// Local only: ข้าม Authen สำหรับทดสอบ (ทำงานเฉพาะ ENVIRONMENT === 'development')
$routes->get('/dev/login-as-admin', 'Dev::loginAsAdmin');
$routes->get('/dev/login-as-student', 'Dev::loginAsStudent');
$routes->get('/dev/login-as-student-admin', 'Dev::loginAsStudentAdmin');

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

// หน้า Dashboard ผู้ใช้งาน — หลัง login มาที่หน้านี้ (ทุก role); หน้าตาเหมือน Admin, เมนู Edoc + หน้าการจัดการงานวิจัย; หน้าแรก = ข้อมูลผู้ใช้คนนั้น
$routes->get('/dashboard', 'User\Dashboard::index', ['filter' => 'loggedin']);
$routes->get('/go-research-record', 'Admin\Auth::goResearchRecord', ['filter' => 'loggedin']);

// Student Portal (Portal หลัก = hub, บาร์โค้ด, ข่าว/Event)
$routes->get('/student/login', 'Student\Auth::login');
$routes->post('/student/login', 'Student\Auth::attemptLogin');
$routes->get('/student/logout', 'Student\Auth::logout');
$routes->get('/student', 'Student\Dashboard::index', ['filter' => 'studentauth']);
$routes->get('/student/dashboard', 'Student\Dashboard::index', ['filter' => 'studentauth']);
$routes->get('/student/barcodes', 'Student\Dashboard::barcodes', ['filter' => 'studentauth']);
$routes->post('/student/barcodes/claim/(:num)', 'Student\Dashboard::claimBarcode/$1', ['filter' => 'studentauth']);
$routes->post('/student/barcodes/claim-from-event/(:num)', 'Student\Dashboard::claimFromEvent/$1', ['filter' => 'studentauth']);
$routes->get('/student/events', 'Student\Dashboard::events', ['filter' => 'studentauth']);

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

// Admin Routes (protected by adminauth filter)
$routes->group('admin', ['filter' => 'adminauth'], function ($routes) {
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

    // News Management
    $routes->get('news', 'Admin\News::index');
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

// Utility Routes (admin only – ใช้ adminauth เหมือน admin)
$routes->group('utility', ['filter' => 'adminauth'], function ($routes) {
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
    $routes->get('downloads/(:num)', 'Admin\ProgramAdmin\Dashboard::downloads/$1');
    $routes->post('upload-download/(:num)', 'Admin\ProgramAdmin\Dashboard::uploadDownload/$1');
    $routes->post('delete-download/(:num)', 'Admin\ProgramAdmin\Dashboard::deleteDownload/$1');
    $routes->post('update-order/(:num)', 'Admin\ProgramAdmin\Dashboard::updateOrder/$1');
    $routes->get('preview/(:num)', 'Admin\ProgramAdmin\Dashboard::preview/$1');
    $routes->post('toggle-publish/(:num)', 'Admin\ProgramAdmin\Dashboard::togglePublish/$1');
});
