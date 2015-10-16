Feature: site

  Scenario: Site Info
    @vcr site-info
    Given I am authenticated
    And a site named "[[test_site_name]]"
    When I run "terminus site info --site=[[test_site_name]]"
    Then I should get:
    """
    Service Level
    """

  Scenario: Site Workflows
    @vcr site-workflows
    Given I am authenticated
    And a site named "[[test_site_name]]"
    When I run "terminus site workflows --site=[[test_site_name]]"
    Then I should get:
    """
    Converge "dev"
    """
