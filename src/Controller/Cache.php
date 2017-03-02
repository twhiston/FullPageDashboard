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
class Cache {

  /**
   * @var string
   */
  protected $cachePath;

  /**
   * Urls constructor.
   *
   * @param string        $cachePath
   */
  public function __construct(string $cachePath) {
    $this->cachePath = $cachePath;
  }

  /**
   * @param \Silex\Application                        $app
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function clear(Application $app, Request $request) {

    foreach (new \DirectoryIterator($this->cachePath) as $fileInfo) {
      if(!$fileInfo->isDot()) {
        unlink($fileInfo->getPathname());
      }
    }

    return new JsonResponse([
                              'status'   => 'success'
                            ]);
  }

}