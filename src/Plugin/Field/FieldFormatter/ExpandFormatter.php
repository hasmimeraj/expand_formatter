<?php

namespace Drupal\expand_formatter\Plugin\Field\FieldFormatter;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
* Plugin implementation of the 'Expand' formatter
*
* @FieldFormatter(
*   id = "Expand",
*   label = @Translation("Expand Formatter"),
*   field_types = {
*     "text",
*     "text_long",
*     "text_with_summary",
*   },
* )
*/

class ExpandFormatter extends FormatterBase { 


/**
 * {@inheritdoc}
 */
 
public static function defaultSettings() {
	
	return array(
    'trim_length' => 200,
    'trim_ellipsis' => TRUE,
    'effect' => 'slide',
    'trigger_expanded_label' => t('Expand'),
    'trigger_collapsed_label' => '',
    'trigger_classes' => 'button',
    'inline' => TRUE,
    'css3' => TRUE,
    'js_duration' => 500,
  )+ parent::defaultSettings();
} 

/**
 * {@inheritdoc}
 */

public function settingsForm(array $form, FormStateInterface $form_state) {

    $element = array();
	
	$element['trim_length'] = array(
      '#type' => 'number',
      '#title' => t('Trim length'),
      '#size' => 10,
      '#default_value' => $this->getSetting('trim_length'),
      '#element_validate' => '',
      '#required' => TRUE,
    );
	
    $element['trim_ellipsis'] = array(
      '#type' => 'checkbox',
      '#title' => t('Append ellipsis') ,
      '#default_value' => $this->getSetting('trim_ellipsis'),
    );
	
    $element['effect'] = array(
      '#type' => 'select',
      '#title' => t('Animation effect'),
      '#default_value' => $this->getSetting('effect'),
      '#empty_value' => '',
      '#options' => array(
        'fade' => t('Fade'),
        'slide' => t('Slide'),
      ),
    );
	
    $element['css3'] = array(
      '#title' => t('Use CSS3 !transitions for animation effects'),
      '#type' => 'checkbox',
      '#description' => t('If you require support for IE 7/8, this option will need to be disabled to fallback to jQuery animations.'),
      '#default_value' => $this->getSetting('css3'),
      '#states' => array(
        'invisible' => array(
          ':input[name="effect"]' => array('value' => ''),
        ),
      ),
    );
	
    $element['trigger_expanded_label'] = array(
      '#type' => 'textfield',
      '#title' => t('Trigger expanded label'),
      '#default_value' => $this->getSetting('trigger_expanded_label'),
      '#required' => TRUE,
    );
    $element['trigger_collapsed_label'] = array(
      '#type' => 'textfield',
      '#title' => t('Trigger collapsed label'),
      '#description' => t('Enter text to make the content collapsible. If empty, content will only expand.'),
      '#default_value' => $this->getSetting('trigger_collapsed_label'),
    );
    $element['trigger_classes'] = array(
      '#type' => 'textfield',
      '#title' => t('Trigger classes'),
      '#description' => t('Provide additional CSS classes separated by spaces.'),
      '#default_value' => $this->getSetting('trigger_classes'),
    );
    $element['inline'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display elements as inline'),
      '#description' => t('If enabled, all elements inside the formatted display will appear as inline. Disable if needed or desired.'),
      '#default_value' => $this->getSetting('inline'),
    );
	
    $element['js_duration'] = array(
      '#title' => t('jQuery animation duration'),
      '#type' => 'textfield',
      '#description' => t('Milliseconds'),
      '#size' => 5,
      '#default_value' => $this->getSetting('js_duration'),
      '#states' => array(
        'invisible' => array(
          ':input[name="css3"]' => array('checked' => TRUE),
        ),
      ),
    );

    return $element;

}

 /**
 * {@inheritdoc}
 */

public function settingsSummary() {

  $summary = array();
  $summary['trim_length'] = t('Trim lengths: @trim_length', array('@trim_length' => $this->getSetting('trim_length')));
  
  $summary['effect'] = t('Effect: @effect', array('@effect' => $this->getSetting('effect')));
  
  $summary['trigger_expanded_label'] = t('Expand Label: @trigger_expanded_label', array('@trigger_expanded_label' => $this->getSetting('trigger_expanded_label')));
  
  $summary['trigger_classes'] = t('Trigger Class: @trigger_classes', array('@trigger_classes' => $this->getSetting('trigger_classes')));
  
  return $summary;
}
 /**
 * {@inheritdoc}
 */
public function viewElements(FieldItemListInterface $items, $langcode) {
     
   $elements = array();
   
    $attributes = array();
    $attributes['class'] = array('expanding-formatter');
	
    if (!empty($this->getSetting('inline'))) {
      $attributes['data-inline'] = $this->getSetting('inline');
    }
    if (!empty($this->getSetting('css3'))) {
      $attributes['data-css3'] = $this->getSetting('css3');
    }
    if (!empty($this->getSetting('effect'))) {
      $attributes['data-effect'] = $this->getSetting('effect');
    }
    if (!empty($this->getSetting('trigger_collapsed_label'))) {
      	  
      $attributes['data-collapsed-label'] = $this->getSetting('trigger_collapsed_label');
    }
	
	if(!empty($this->getSetting('trigger_expanded_label'))){
	 $attributes['data-expanded-label'] = $this->getSetting('trigger_expanded_label');
	}
	
    
	
	if (!empty($this->getSetting('js_duration'))) {
      $attributes['data-js-duration'] = $this->getSetting('js_duration');
    }
	
	if(!empty($this->getSetting('trim_ellipsis'))){
	$attributes['data-ellipsis'] = $this->getSetting('trim_ellipsis');
	}
	
	$trigger_class = '';
	if (!empty($this->getSetting('trigger_classes'))) {
       $trigger_class = $this->getSetting('trigger_classes');
    }   
		
    $text_processing = $this->getSetting('text_processing');
		
    foreach ($items as $delta => $item) {
		
	 $original = $item;	  
		
      if ($this->getPluginId() == 'text_summary_or_trimmed' && !empty($item->summary)) {
        $output = $item->summary_processed;
		$summary_content = $output;	
		
      }
      else {
        $output = $item->processed;
        $output = text_summary($output, $text_processing ? $item->format : NULL, $this->getSetting('trim_length'));
		
		$summary_content = $output;		
      }  
	  
	   $elements[$delta] = array(
        '#theme' => 'expandformatter',
        '#attributes' => $attributes,
        '#contentdata' => $original,
        '#summarydata' => $summary_content,
		'#triggerclass' => $trigger_class,
		'#attached' => array('library'=> array('expand_formatter/expformet')),
       );  
	  
	 }
	
	
    return $elements;
  }
  
}