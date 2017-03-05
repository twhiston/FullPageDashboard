<?php
/**
 * Created by PhpStorm.
 * User: twhiston
 * Date: 02.03.17
 * Time: 22:04
 */

namespace twhiston\FullPageDashboard\Controller;

use M1\Vars\Vars;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Settings
 *
 * @package twhiston\FullPageDashboard\Controller
 */
class Settings {

  /**
   * @var string
   */
  protected $configPath;

  /**
   * Urls constructor.
   *
   * @param \M1\Vars\Vars $vars
   * @param string        $configPath
   */
  public function __construct($configPath) {
    $this->configPath = $configPath;
  }

  /**
   * @param \Silex\Application                        $app
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function get(Application $app, Request $request) {
    return new JsonResponse([
                              'status'   => 'success',
                              'settings' => $app['vars']['config']['settings'],
                            ]);
  }

  /**
   * @param \Silex\Application                        $app
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function set(Application $app, Request $request) {
    $data = $request->request->all();
    $merged = array_merge($app['vars']['config']['settings'], $data);

    $app['vars']->set('config.settings', $merged);

    $output = Yaml::dump(['config' => $app['vars']->get('config')], 3);

    if (file_put_contents($this->configPath . '/config.yml', $output) === FALSE) {
      return new JsonResponse(['status' => 'fail', 'message' => 'Failed to write output file']);
    }
    return new JsonResponse([
                              'status'   => 'success',
                              'settings' => $app['vars']['config']['settings'],
                            ]);
  }
}