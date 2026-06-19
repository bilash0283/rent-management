<?php
    ob_start();
    session_name("rant_manager");
    $custom_timeout = 3600 * 24 * 30; // 30 days

    // Start the session with custom parameters
    session_start([
        'cookie_lifetime' => $custom_timeout, // Time the browser cookie persists
        'gc_maxlifetime'   => $custom_timeout, // Time the server data stays active
    ]);
?>