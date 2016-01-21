<?php

namespace Terminus\Commands;

use Terminus;
use Terminus\Auth;
use Terminus\Endpoint;
use Terminus\Request;
use Terminus\Session;
use Terminus\Utils;
use Terminus\Outputters\OutputterInterface;
use Terminus\Exceptions\TerminusException;
use Terminus\Models\Workflow;
use Terminus\Loggers\Logger;

/**
 * The base class for Terminus commands
 */
abstract class TerminusCommand {
  public $cache;
  public $session;
  public $sites;

  protected $func;

  /**
   * @var Logger
   */
  protected $logger;

  /**
   * @var OutputterInterface
   */
  protected $outputter;

  /**
   * @var Request
   */
  protected $request;

  /**
   * Instantiates object, sets cache and session
   */
  public function __construct() {
    //Load commonly used data from cache
    $this->cache     = Terminus::getCache();
    $this->logger    = Terminus::getLogger();
    $this->outputter = Terminus::getOutputter();
    $this->session   = Session::instance();
    $this->request   = new Request();
    if (!Terminus::isTest()) {
      Utils\checkForUpdate();
    }
  }

  /**
   * Sends the given message to logger as an error and exits with -1
   *
   * @param string $message   Message to log as error before exit
   * @param array  $context   Vars to interpolate in message
   * @param int    $exit_code Code to exit with
   * @return void
   * @throws TerminusException
   */
  protected function failure(
    $message       = 'Command failed',
    array $context = array(),
    $exit_code     = 1
  ) {
    throw new TerminusException($message, $context, $exit_code);
  }

  /**
   * Retrieves the logger for use
   *
   * @return Logger
   */
  protected function log() {
    return $this->logger;
  }

  /**
   * Retrieves the outputter for use
   *
   * @return OutputterInterface
   */
  protected function output() {
    return $this->outputter;
  }

  /**
   * Saves the logger object as a class property
   *
   * @param Logger $logger Logger object to save
   * @return void
   */
  protected function setLogger(Logger $logger) {
    $this->logger = $logger;
  }

  /**
   * Saves the outputter object as a class property
   *
   * @param OutputterInterface $outputter Outputter object to save
   * @return void
   */
  protected function setOutputter(OutputterInterface $outputter) {
    $this->outputter = $outputter;
  }

  /**
   * Outputs basic workflow success/failure messages
   *
   * @param Workflow $workflow Workflow to output message about
   * @return void
   */
  protected function workflowOutput($workflow) {
    if ($workflow->get('result') == 'succeeded') {
      $this->log()->info($workflow->get('active_description'));
    } else {
      $final_task = $workflow->get('final_task');
      $this->log()->error($final_task->reason);
    }
  }

}
