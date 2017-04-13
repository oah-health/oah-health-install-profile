<?php

/**
 * @file
 * Contains \Drupal\merci\Plugin\views\argument_default\CurrentUserRoles.
 */

namespace Drupal\merci\Plugin\views\argument_default;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;

/**
 * Default argument plugin to extract the current user
 *
 * This plugin actually has no options so it odes not need to do a great deal.
 *
 * @ViewsArgumentDefault(
 *   id = "current_user_roles",
 *   title = @Translation("User roles from logged in user")
 * )
 */
class CurrentUserRoles extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    $roles = \Drupal::currentUser()->getRoles();

    return implode(',', $roles);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['user'];
  }

}
