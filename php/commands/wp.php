<?php

use Terminus\CommandWithSSH;
use Terminus\Exceptions\TerminusException;
use Terminus\Helpers\Input;
use Terminus\Models\Collections\Sites;

class WPCLI_Command extends CommandWithSSH {

  /**
   * Invoke `wp` commands on a Pantheon development site
   *
   * <commands>...
   * : The WP-CLI commands you intend to run.
   *
   * [--<flag>=<value>]
   * : Additional WP-CLI flag(s) to pass in to the command.
   *
   * [--site=<site>]
   * : The name (DNS shortname) of your site on Pantheon.
   *
   * [--env=<environment>]
   * : Your Pantheon environment. Default: dev
   *
   */
  function __invoke( $args, $assoc_args ) {
    $environment = Input::env($assoc_args);
    $sites = new Sites();
    $site = $sites->get(Input::sitename($assoc_args));
    if (!$site) {
      throw new TerminusException("Command could not be completed. Unknown site specified.");
    }

    # see https://github.com/pantheon-systems/titan-mt/blob/master/dashboardng/app/workshops/site/models/environment.coffee
    $server = Array(
      'user' => "$environment.{$site->get('id')}",
      'host' => "appserver.$environment.{$site->get('id')}.drush.in",
      'port' => '2222'
    );

    if (strpos(TERMINUS_HOST, 'onebox') !== FALSE) {
      $server['user'] = "appserver.$environment.{$site->get('id')}";
      $server['host'] = TERMINUS_HOST;
    }

    # Sanitize assoc args so we don't try to pass our own flags.
    # TODO: DRY this out?
    unset($assoc_args['site']);
    if (isset($assoc_args['env'])) {
      unset($assoc_args['env']);
    }
    # Create user-friendly output
    $command = implode( $args, ' ' );
    $flags = '';
    foreach ( $assoc_args as $k => $v ) {
      if (isset($v) && (string) $v != '') {
        $flags .= "--$k=" . escapeshellarg($v) . ' ';
      }
      else {
        $flags .= "--$k ";
      }
    }
    $this->log()->info(
      "Running wp {cmd} {flags} on {site}-{env}",
      array(
        'cmd' => $command,
        'flags' => $flags,
        'site' => $site->get('name'),
        'env' => $environment
      )
    );
    $result = $this->send_command($server, 'wp', $args, $assoc_args);
    if (Terminus::getConfig('format') != 'normal') {
      $this->output()->outputRecordList($result);
    }
  }

}

Terminus::addCommand( 'wp', 'WPCLI_Command' );
