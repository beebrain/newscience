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
$routes->get('/news/(:segment)', 'Pages::newsDetail/$1');
$routes->get('/events', 'Pages::events');
$routes->get('/contact', 'Pages::contact');
$routes->get('/personnel', 'Pages::personnel');

// Admin Auth Routes (no filter)
$routes->get('/admin/login', 'Admin\Auth::login');
$routes->post('/admin/login', 'Admin\Auth::attemptLogin');
$routes->get('/admin/logout', 'Admin\Auth::logout');

// Admin Routes (protected by adminauth filter)
$routes->group('admin', ['filter' => 'adminauth'], function ($routes) {
    // News Management
    $routes->get('news', 'Admin\News::index');
    $routes->get('news/create', 'Admin\News::create');
    $routes->post('news/store', 'Admin\News::store');
    $routes->get('news/edit/(:num)', 'Admin\News::edit/$1');
    $routes->post('news/update/(:num)', 'Admin\News::update/$1');
    $routes->get('news/delete/(:num)', 'Admin\News::delete/$1');
});

// Utility Routes
$routes->group('utility', function ($routes) {
    $routes->post('upload/image', 'Utility\Upload::uploadImage');
    $routes->post('upload/multiple', 'Utility\Upload::uploadMultiple');
    $routes->post('upload/delete/(:num)', 'Utility\Upload::deleteImage/$1');
    $routes->post('upload/delete-file', 'Utility\Upload::deleteFile');
    $routes->get('import-data', 'Utility\ImportData::index');
});

// API Routes for AJAX
$routes->group('api', function ($routes) {
    // News
    $routes->get('news', 'Api::news');
    $routes->get('news/featured', 'Api::newsFeatured');
    $routes->get('news/search', 'Api::newsSearch');
    $routes->get('news/(:num)', 'Api::newsDetail/$1');
    
    // Static Content
    $routes->get('personnel', 'Api::personnel');
    $routes->get('personnel/dean', 'Api::dean');
    $routes->get('departments', 'Api::departments');
    $routes->get('programs', 'Api::programs');
    $routes->get('settings', 'Api::settings');
    $routes->get('stats', 'Api::stats');
});

