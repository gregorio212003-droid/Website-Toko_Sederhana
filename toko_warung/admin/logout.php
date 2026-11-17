<?php

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}


/*
|--------------------------------------------------------------------------
| HAPUS SESSION ADMIN
|--------------------------------------------------------------------------
*/

unset(
    $_SESSION['admin_id']
);

unset(
    $_SESSION['admin_username']
);

unset(
    $_SESSION['admin_name']
);


/*
|--------------------------------------------------------------------------
| REDIRECT KE LOGIN
|--------------------------------------------------------------------------
*/

header(
    "Location: login.php"
);

exit;

?>