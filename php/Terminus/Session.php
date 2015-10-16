<?php

namespace Terminus;

use Terminus;

class Session {
  static $instance;
  protected $data;


  public function __construct() {

    $cache = Terminus::getCache();
    $session = $cache->get_data('session');


    if (empty($session)) {
      $this->data = new \stdClass();;
    } else {
      $this->data = $session;
    }

    self::$instance = $this;
    return $this;
  }

  public function get($key = 'session', $default = false) {
    if ( isset($this->data) AND isset($this->data->$key) )
      return $this->data->$key;
    return $default;
  }

  public function set($key, $value) {
    $this->data->$key = !empty($value) ? $value : null;
    return $this;
  }

  public static function instance( $session = null ) {
    if (!self::$instance) {
      self::$instance = new self($session);
    }
    return self::$instance;
  }

  public static function getValue( $key, $default = false ) {
    $session = Session::instance();
    return $session->get($key);
  }

  public static function getData() {
    $session = Session::instance();
    return $session->data;
  }

  public static function setData( $data ) {
    $cache = Terminus::getCache();
    Terminus::getLogger()->info('Saving session data');
    $cache->put_data('session', $data);
    $session = self::instance();
    $session->set('data',$data);
    if (empty($data)) return false;
    foreach ($data as $k=>$v) {
        $session->set($k,$v);
    }
  }

}
