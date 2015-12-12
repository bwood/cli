Feature: Set a site's service level
  In order to ensure the level of service my site requires
  As a user
  I need to be able to change the service level on my site.

  @vcr site_set-service-level
  Scenario: Changing the service level
    Given I am authenticated
    And a site named "[[test_site_name]]"
    When I run "terminus site set-service-level --site=[[test_site_name]] --level=pro"
    Then I should get:
    """
    Service level has been updated to 'pro'
    """

  @vcr site_set-service-level_fail
  Scenario: Changing service level without payment method
    Given I am authenticated
    And a site named "[[test_site_name]]"
    When I run "terminus site set-service-level --site=[[test_site_name]] --level=pro"
    Then I should get:
    """
    Instrument required to increase service level
    """

  @vcr site_set-service-level_wrong
  Scenario: Changing to incorrect service level
    Given I am authenticated
    And a site named "[[test_site_name]]"
    When I run "terminus site set-service-level --site=[[test_site_name]] --level=professional"
    Then I should get:
    """
    Service level "professional" is invalid.
    """
