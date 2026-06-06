<?php
/**
 * CSRF Phase 1 includes
 *
 * Drop this partial into any view's <head>:
 *   <?= view('partials/csrf_head') ?>
 *
 * Renders the meta tag + csrf-setup.js which attaches X-CSRF-TOKEN to
 * every non-GET AJAX call (jQuery $.ajax AND native fetch) and rotates
 * the cached token from the response header.
 *
 * Backend enforcement is currently OFF — adding this partial is safe
 * and has no behavioral effect until Filters.php enables 'csrf'.
 */
?>
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<script src="<?= base_url('assets/js/csrf-setup.js') ?>"></script>
