<?php

/**
 * Implements hook_views_data_alter().
 */
function file_media_usage_usage_views_data_alter(&$data) {

  $data['file_managed']['display'] = [
    'title' => t('Display file'),
    'help' => t('Display file'),
    'field' => [
      'id' => 'file_display',
    ],
  ];
}