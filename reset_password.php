<?php
// Include the Drupal bootstrap.
define('DRUPAL_ROOT', 'C:\\xampp\\htdocs\\newradio\\cms\\web'); // Change this to your Drupal root path.
require_once DRUPAL_ROOT . '/core/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

// Load the user by their UID or username.
$uid = 1; // Change this to the UID of the user you want to reset.
$user = \Drupal\user\Entity\User::load($uid);

// Set a new password.
$new_password = 'ensenadabc'; // Change this to the new password.
$user->setPassword($new_password);
$user->save();

echo "Password updated successfully.";
