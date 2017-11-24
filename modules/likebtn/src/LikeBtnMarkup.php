<?php

namespace Drupal\likebtn;

use Drupal\Core\Entity\Entity;
use Drupal\likebtn\Controller\LikeBtnController;

class LikeBtnMarkup {

  public function likebtn_get_markup($element_name, $element_id, $values = NULL, $wrap = TRUE, $include_entity_data = TRUE) {
    $prepared_settings = array();
    $config = \Drupal::config('likebtn.settings');
    $controller = new LikeBtnController();

    $likebtn = new LikeBtn();
    $likebtn->runSyncVotes();

    $settings = unserialize(LIKEBTN_SETTINGS);

    $data = '';
    if ($element_name && $element_id) {
      $data .= 'data-identifier="' . $element_name . '_' . $element_id . '"';
    }

    $site_id = $config->get('settings.likebtn_account_data_site_id');
    if ($site_id) {
      $data .= ' data-site_id="' . $site_id . '" ';
    }

    // Website subdirectory.
    if ($config->get('settings.likebtn_settings_subdirectory')) {
      $data .= ' data-subdirectory="' . $config->get('settings.likebtn_settings.subdirectory') . '" ';
    }

    $data .= ' data-engine="drupal" data-engine_v="' .\Drupal::VERSION . '"';
    $data .= ' data-plugin_v="' . LikebtnInterface::LIKEBTN_VERSION . '" ';

    foreach ($settings as $option_name => $option_info) {
      if ($values) {
        if (isset($values[$option_name])) {
          $option_value = $values['settings.likebtn_settings.' . $option_name];
        }
        elseif (isset($values[$option_name])) {
          $option_value = $values[$option_name];
        }
        else {
          $option_value = '';
        }
      }
      else {
        $option_value = $config->get('settings.likebtn_settings.' . $option_name) ?: '';
      }

      $option_value_prepared = $controller->likebtn_prepare_option($option_name, $option_value);
      $prepared_settings[$option_name] = $option_value_prepared;

      // Do not add option if it has default value.
      if (!is_array($option_value) && $option_value !== '' && $option_value != $settings[$option_name]['default']) {
        $data .= ' data-' . $option_name . '="' . $option_value_prepared . '" ';
      }
    }

    // Add item options.
    if ($include_entity_data) {
      if (empty($prepared_settings['item_url']) || empty($prepared_settings['item_title'])) {
        $entity_list = array();
        $entity = NULL;
        $entity_url = '';
        $entity_title = '';
        $entity_date = '';

        if (!empty($entity_list)) {
          $entity = array_shift($entity_list);
        }
        if ($entity && (isset($entity->title) || isset($entity->subject))) {
          // URL.
          if (empty($prepared_settings['item_url'])) {
            $entity_url_object = Entity::uri($element_name, $entity);

            if (!empty($entity_url_object['path'])) {
              global $base_url;
              $entity_url = $base_url . '/' . $entity_url_object['path'];
            }
          }

          // Title.
          if (empty($prepared_settings['item_title'])) {
            if (isset($entity->title)) {
              $entity_title = $entity->title;
            }
            elseif (isset($entity->subject)) {
              $entity_title = $entity->subject;
            }
          }

          // Date.
          if (empty($prepared_settings['item_date'])) {
            if (isset($entity->created)) {
              $entity_date = date("c", $entity->created);
            }
          }
        }

        if ($entity_url) {
          $data .= ' data-item_url="' . $entity_url . '" ';
        }
        if ($entity_title) {
          $entity_title = htmlspecialchars($entity_title);
          $data .= ' data-item_title="' . $entity_title . '" ';
        }
        if ($entity_date) {
          $data .= ' data-item_date="' . $entity_date . '" ';
        }
      }
    }

    $public_url = _likebtn_public_url();

    if (isset($values['likebtn_html_before'])) {
      $html_before = $values['likebtn_html_before'];
    }
    else {
      $html_before = $config->get('settings.likebtn_html_before');
    }

    if (isset($values['likebtn_html_after'])) {
      $html_after = $values['likebtn_html_after'];
    }
    else {
      $html_after = $config->get('settings.likebtn_html_after');
    }

    $alignment = '';
    if ($wrap) {
      if (isset($values['likebtn_alignment'])) {
        $alignment = $values['likebtn_alignment'];
      }
      else {
        $alignment = $config->get('settings.likebtn_alignment');
      }
    }

    return array(
      '#theme' => 'likebtn_markup',
      '#data' => $data,
      '#aligment' => $alignment,
      '#html_before' => $html_before,
      '#html_after' => $html_after
    );
  }
}
