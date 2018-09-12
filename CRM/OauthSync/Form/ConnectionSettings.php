<?php

use CRM_OauthSync_ExtensionUtil as E;

/**
 * Form controller class for managing connection settings. Connection specific plugins should extend this to configure
 * oauth client secret/client id.
 * Lots of inspiration drawn from https://github.com/eileenmcnaughton/nz.co.fuzion.civixero/blob/master/CRM/Civixero/Form/XeroSettings.php
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
abstract class CRM_OauthSync_Form_ConnectionSettings extends CRM_Core_Form {


  private $_submittedValues = array();
  private $_settings = array();

  /**
   * @return string the prefix for the settings used
   */
  abstract protected function getConnectionSettingsPrefix();

  abstract protected function getHumanReadableConnectionName();

  public function buildQuickForm() {
    $settings = $this->getFormSettings();
    foreach ($settings as $name => $setting) {
      if (isset($setting['quick_form_type'])) {
        $add = 'add' . $setting['quick_form_type'];
        if ($add == 'addElement') {
          $this->$add($setting['html_type'], $name, ts($setting['title']), CRM_Utils_Array::value('html_attributes', $setting, array()));
        } elseif ($setting['html_type'] == 'Select') {
          $optionValues = array();
          if (!empty($setting['pseudoconstant'])) {
            if (!empty($setting['pseudoconstant']['optionGroupName'])) {
              $optionValues = CRM_Core_OptionGroup::values($setting['pseudoconstant']['optionGroupName'], FALSE, FALSE, FALSE, NULL, 'name');
            } elseif (!empty($setting['pseudoconstant']['callback'])) {
              $cb = Civi\Core\Resolver::singleton()->get($setting['pseudoconstant']['callback']);
              $optionValues = call_user_func_array($cb, $optionValues);
            }
          }
          $this->add('select', $setting['name'], $setting['title'], $optionValues, FALSE, $setting['html_attributes']);
        } else {
          $this->$add($name, ts($setting['title']));
        }
        $this->assign("{$setting['description']}_description", ts('description'));
      }
    }
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      )
    ));
    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());

    parent::buildQuickForm();
  }

  public function postProcess() {
    $this->_submittedValues = $this->exportValues();
    $this->saveSettings();
    parent::postProcess();
  }


  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons". These
    // items don't have labels. We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  function getFormSettings() {
    if (empty($this->_settings)) {
      $settings = civicrm_api3('setting', 'getfields', array('filters' => array('group' => $this->getConnectionSettingsPrefix())));
    }
    return $settings['values'];
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  function saveSettings() {
    $settings = $this->getFormSettings();
    $values = array_intersect_key($this->_submittedValues, $settings);
    civicrm_api3('setting', 'create', $values);
  }

  /**
   * Set defaults for form.
   *
   * @see CRM_Core_Form::setDefaultValues()
   */
  function setDefaultValues() {
    $existing = civicrm_api3('setting', 'get', array('return' => array_keys($this->getFormSettings())));
    $defaults = array();
    $domainID = CRM_Core_Config::domainID();
    foreach ($existing['values'][$domainID] as $name => $value) {
      $defaults[$name] = $value;
    }
    return $defaults;
  }

  function getTemplateFileName() {



//    $ext = CRM_Extension_System::singleton()->getMapper();
//
//    $filename = $ext->gettemplatename(self::class);
//    $tplname = $ext->gettemplatepath(self::class) . directory_separator . $filename;

    $tplname = __DIR__ . '/../../../templates/CRM/OauthSync/Form/ConnectionSettings.tpl';
    return $tplname;
  }
}
