<?php
/** Admin logout. */
require __DIR__ . '/includes/guard.php';
// $CURRENT_ADMIN is required by guard, so we know a session exists.
auth_logout();
start_secure_session();
flash('success', 'You have been signed out.');
redirect(admin_url('login.php'));
