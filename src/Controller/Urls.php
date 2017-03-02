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
 * Class Urls
 *
 * @package twhiston\FullPageDashboard\Controller
 */
class Urls {

  protected $configPath;

  /**
   * Urls constructor.
   *
   * @param string $configPath
   */
  public function __construct(string $configPath) {
    $this->configPath = $configPath;
  }

  /**
   * @param \Silex\Application                        $app
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function get(Application $app, Request $request) {
    return new JsonResponse(['urls' => $app['vars']->get('urls')]);
  }

  /**
   * Add a url
   */
  public function create(Application $app, Request $request) {
    $data = $request->request->all();

    //TODO - Validate urls
    $merged = array_merge($app['vars']->get('urls'), $data['urls']);
    $app['vars']->set('urls', $merged);
    $output = Yaml::dump(['urls' => $app['vars']->get('urls')], 3);
    if (file_put_contents($this->configPath . '/urls.yml', $output) === FALSE) {
      return new JsonResponse(['status' => 'fail', 'message' => 'Failed to write output file']);
    }
    return new JsonResponse([
                              'status' => 'success',
                              'urls'   => $app['vars']->get('urls'),
                            ]);
  }

  /**
   * Delete a url
   */
  public function delete(Application $app, Request $request, string $title) {
    /** @var Vars $vars */
    $vars = $app['vars'];

    $urls = $vars->get('urls');
    $id = $this->inArray($title, $urls);
    if (!$id) {
      return new JsonResponse(['status' => 'fail', 'message' => $title . ' does not exist']);
    }
    unset($urls[$id]);
    $vars->set('urls', array_values($urls));

    $output = Yaml::dump(['urls' => $vars->get('urls')]);
    if (file_put_contents($this->configPath . '/urls.yml', $output) === FALSE) {
      return new JsonResponse(['status' => 'fail', 'message' => 'Failed to write output file']);
    }
    return new JsonResponse([
                              'status' => 'success',
                              'urls'   =>  $app['vars']['urls'],
                            ]);

  }

  /**
   * @param string $needle
   * @param array  $haystack
   *
   * @return int|null|string
   */
  public function inArray(string $needle, array &$haystack) {
    $found = NULL;
    foreach ($haystack as $key => $item) {
      if ($item === $needle) {
        $found = $key;
        break;
      } elseif (is_array($item)) {
        $found = $this->inArray($needle, $item);
        if ($found !== NULL) {
          $found = $key;
          break;
        }
      }
    }
    return $found;
  }
}