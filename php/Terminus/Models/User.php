<?php

namespace Terminus\Models;

use Terminus\Models\Collections\UserOrganizationMemberships;
use Terminus\Models\TerminusModel;
use Terminus\Models\Collections\Instruments;
use Terminus\Models\Collections\Workflows;
use Terminus\Session;

class User extends TerminusModel {
  public $organizations;

  protected $instruments;
  protected $workflows;

  private $aliases;
  private $profile;

  /**
   * Object constructor
   *
   * @param [stdClass] $attributes Attributes of this model
   * @param [array]    $options    Options to set as $this->key
   * @return [User] $this
   */
  public function __construct($attributes = null, $options = array()) {
    if (!isset($options['id'])) {
      $options['id'] = Session::getValue('user_uuid');
    }
    parent::__construct($attributes, $options);

    if (isset($attributes->profile)) {
      $this->profile = $attributes->profile;
    }
    $this->workflows     = new Workflows(
      array('owner' => $this)
    );
    $this->instruments   = new Instruments(array('user' => $this));
    $this->organizations = new UserOrganizationMemberships(
      array('user' => $this)
    );
  }

  /**
   * Retrieves drush aliases for this user
   *
   * @return [stdClass] $this->aliases
   */
  public function getAliases() {
    if (!$this->aliases) {
      $this->setAliases();
    }
    return $this->aliases;
  }

  /**
   * Retrieves organization data for this user
   *
   * @return [stdClass] $organizations
   */
  public function getOrganizations() {
    $organizations = $this->organizations->all();
    return $organizations;
  }

  /**
   * Requests API data and returns an object of user site data
   *
   * @param [string] $organization UUID of organization to requests sites from
   * @return [stdClass] $response['data']
   */
  public function getSites($organization = null) {
    if ($organization) {
      $path = sprintf('organizations/%s/memberships/sites', $organization);
    } else {
      $path = 'sites';
    }
    $method   = 'GET';
    $response = $this->request->request('users', $this->id, $path, $method);
    return $response['data'];
  }

  /**
   * Requests API data and populates $this->aliases
   *
   * @return [void]
   */
  private function setAliases() {
    $path     = 'drush_aliases';
    $method   = 'GET';
    $response = $this->request->request('users', $this->id, $path, $method);

    $this->aliases = $response['data']->drush_aliases;
  }

}
