<?php
/**
 * Some kind of localization is possible.
 * You can define the Error/Success Information texts here.
 */

define('NO_DB', 'Connection to database failed.');
define('EMPTY_FORMS', 'Please provide all required information.');

/* Registration-specific */
define('BAD_NAME', 'Your username should be between 3 and 30 characters long.');
define('NAME_TAKEN', 'This username is already taken.');
define('BAD_PASS', 'Your password should be between 5 and 75 characters long.');
define('MAIL_TAKEN', 'Somebody with this email is already registered.');
define('REG_SUCCESS', 'Welcome, registration was successful. Please login now!');

/* Login-specific */
define('INVALID_LOGIN', 'Invalid username/password combination.');
define('LOGIN_SUCCESS', 'Welcome, login was successful!');


/* Login-specific */
define('EMPTY_SESSION', 'Session-Cookies are missing.');
define('INVALID_SESSION', 'Invalid session, login again.');
define('SESSION_SUCCESS', 'Session is valid!');

/* Logout-specific */
define('LOGOUT_SUCCESS', 'You are logged out!');

/* Logout-specific */
define('CHANGE_SUCCESS', 'Your data was changed!');


?>