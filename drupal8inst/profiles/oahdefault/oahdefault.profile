<?php
/**
 * @file
 * Enables modules and site configuration for a standard site installation.
 */

// Add any custom code here like hook implementations.
function oahdefault_form_install_configure_form_alter(&$form, $form_state) {
  // Pre-populate the site name with the server name.
  $form['site_information']['site_name']['#default_value'] = $_SERVER['SERVER_NAME'];
}
