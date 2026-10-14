<?php
use App\{Auth, Audit, Helper};
Audit::log('user.logout', 'user', $_SESSION['user_id'] ?? null);
Auth::logout();
Helper::redirect('/login');