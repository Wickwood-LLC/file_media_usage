<?php

namespace Drupal\file_media_usage\Plugin\views\field;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\field\Standard;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Template\Attribute;

/**
 * Default implementation of the base field plugin.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("file_display")
 */
class FileDisplay extends Standard {
  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    $this->additional_fields['filemime'] = 'filemime';
    $this->additional_fields['fid'] = 'fid';
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['display_as'] = ['default' => 'link'];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    // Do here.
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $this->addAdditionalFields();
    //$this->field_alias = $this->table . '_' . $this->field;
  }

  public function render(ResultRow $values) {
    $filemime = $this->getValue($values, 'filemime');
    $file = $values->_entity;
    $uri = $file->uri->get(0)->get('value')->getValue();

    if (preg_match('~^image/.+~', $filemime)) {
      return [
        '#theme' => 'image_style',
        // '#width' => $variables['width'],
        // '#height' => $variables['height'],
        '#style_name' => 'thumbnail',
        '#uri' => $uri,
      ];
    }
    else if (preg_match('~^video/.+~', $filemime)) {
      return [
        '#theme' => 'file_video',
        '#attributes' => (new Attribute())->setAttribute('controls', 'controls'),
        '#files' => [
          ['source_attributes' => (new Attribute())->setAttribute('src', file_create_url($uri))->setAttribute('type', $file->getMimeType()),],
        ],
      ];
    }
    else  {
      return array(
        '#type' => 'link',
        '#title' => $file->filename,
        '#url' => file_create_url($url),
      );
    }
  }
}
