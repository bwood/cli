<?php

namespace Terminus\Models;

use Terminus\Request;

abstract class TerminusModel {
  protected $id;
  protected $request;
  private $attributes;

  /**
   * Object constructor
   *
   * @param [stdClass] $attributes Attributes of this model
   * @param [array]    $options    Options to set as $this->key
   * @return [TerminusModel] $this
   */
  public function __construct($attributes = null, $options = array()) {
    if ($attributes == null) {
      $attributes = new \stdClass();
    }
    if (isset($attributes->id)) {
      $this->id = $attributes->id;
    }
    foreach ($options as $var_name => $value) {
      $this->$var_name = $value;
    }
    $this->attributes = $attributes;
    $this->request    = new Request();
  }

  /**
   * Handles requests for inaccessable properties
   *
   * @param [string] $property Name of property being requested
   * @return [mixed] $this->$property
   */
  public function __get($property) {
    if (property_exists($this, $property)) {
      return $this->$property;
    }

    $trace = debug_backtrace();
    trigger_error(
      sprintf(
        'Undefined property $var->$%s in %s on line %s',
        $property,
        $trace[0]['file'],
        $trace[0]['line']
      ),
      E_USER_NOTICE
    );
    return null;
  }

  /**
   * Fetches this object from Pantheon
   *
   * @param [array] $options Params to pass to url request
   * @return [TerminusModel] $this
   */
  public function fetch($options = array()) {
    $fetch_args = array();
    if (isset($options['fetch_args'])) {
      $fetch_args = $options['fetch_args'];
    }

    $options = array_merge(
      array('options' => array('method' => 'get')),
      $this->getFetchArgs(),
      $fetch_args
    );

    $results = $this->request->simpleRequest(
      $this->getFetchUrl(),
      $options
    );

    $this->attributes = $results['data'];
    return $this;
  }

  /**
   * Retrieves attribute of given name
   *
   * @param [string] $attribute Name of the key of the desired attribute
   * @return [mixed] $this->attributes->$attribute
   */
  public function get($attribute) {
    if ($this->has($attribute)) {
      return $this->attributes->$attribute;
    }
    return null;
  }

  /**
   * Checks whether the model has an attribute
   *
   * @param [string] $attribute Name of the attribute key
   * @return [boolean] $isset whether the attribute is set
   */
  public function has($attribute) {
    $isset = isset($this->attributes->$attribute);
    return $isset;
  }

  /**
   * Give necessary args for collection data fetching
   *
   * @return [array]
   */
  protected function getFetchArgs() {
    return array();
  }

  /**
   * Give the URL for collection data fetching
   *
   * @return [string] $url URL to use in fetch query
   */
  protected function getFetchUrl() {
    return '';
  }

}
