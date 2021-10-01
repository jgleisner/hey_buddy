<?php
namespace Drupal\hey_buddy\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block for the greeting of logged-in buddies.
 *
 * @Block(
 *   id = "hey_buddy_block",
 *   admin_label = @Translation("Hey Buddy block"),
 *   category = @Translation("Hello Forum One"),
 * )
 */
class HeyBuddyBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    $markup = "";

    if ($logged_in) {
      $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

      $name = $user->getDisplayName();

      $last_login = date('F jS, Y g:i a', $user->getLastLoginTime());

      $markup = $this->t('Hello @Name!<br />', ['@Name' => $name]);
      $markup .= $this->t('Your last log in was @LastLogin <br />', ['@LastLogin' => $last_login]);
      $markup .= $this->t('<a href="/user">Visit your profile</a><br /><br />');
    }

    if ($config['hey_buddy_showhide'] === 1 || $logged_in) {
      $markup .= $this->t($config['hey_buddy_message']);
    }

    if ($markup != "") {
      return [
        '#markup' => $markup,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['hey_buddy_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Message'),
      '#description' => $this->t('Global Hey Buddy Messaging'),
      '#default_value' => !empty($config['hey_buddy_message']) ? $config['hey_buddy_message'] : '',
    ];

    $form['hey_buddy_showhide'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show for anonymous users'),
      '#description' => $this->t('Uncheck this to hide from anonymous users'),
      '#default_value' => !empty($config['hey_buddy_showhide']) ? $config['hey_buddy_showhide'] : ''
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['hey_buddy_message'] = $values['hey_buddy_message'];
    $this->configuration['hey_buddy_showhide'] = $values['hey_buddy_showhide'];
  }
}
