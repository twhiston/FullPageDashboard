<?php
/**
 * Created by PhpStorm.
 * User: twhiston
 * Date: 25.02.17
 * Time: 23:58
 */

namespace twhiston\FullPageDashboard;

use M1\Vars\Provider\Silex\VarsServiceProvider;
use M1\Vars\Vars;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

/**
 * Class FullPageDashboard
 *
 * @package twhiston\FullPageDashboard
 */
class FullPageDashboard {

  /**
   * @var \Silex\Application
   */
  protected $app;

  /**
   * @var string
   */
  protected $configPath = __DIR__ . '/../config';

  /**
   * @var string
   */
  protected $cachePath = __DIR__ . '/../cache';

  /**
   * @var string
   */
  protected $themePath = __DIR__ . '/../theme';

  /**
   * FullPageDashboard constructor.
   *
   * @param $app
   */
  public function __construct($debug = FALSE) {
    $this->app = new Application();

    $this->app->before(function (Request $request) {
      if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), TRUE);
        $request->request->replace(is_array($data) ? $data : []);
      }
    });
    $this->app['debug'] = $debug;
  }

  /**
   * @param string $configPath
   */
  public function setConfigPath($configPath) {
    $this->configPath = $configPath;
  }

  /**
   * @param string $cachePath
   */
  public function setCachePath($cachePath) {
    $this->cachePath = $cachePath;
  }

  /**
   * @param string $themePath
   */
  public function setThemePath($themePath) {
    $this->themePath = $themePath;
  }

  /**
   *
   */
  public function registerServices() {
    $this->app->register(new VarsServiceProvider($this->configPath),
                         [
                           'vars.path'    => $this->configPath,
                           'vars.options' => [
                             'cache'         => TRUE,
                             'cache_path'    => $this->cachePath,
                             'cache_expire'  => 500,
                             'loaders'       => [
                               'yaml',
                             ],
                             'merge_globals' => TRUE,
                           ],
                         ]);

    $this->app->register(new TwigServiceProvider(),
                         [
                           'twig.path' => $this->themePath . '/views',
                         ]);
  }

  /**
   *
   */
  public function registerRoutes() {

    $app = $this->app;

    //Main view display
    $app->get('/',
      function () use ($app) {
        $vars = $app['vars']->getContent();
        return $app['twig']->render('index.html.twig',
                                    [
                                      'site'     => $vars['config']['site'],
                                      'settings' => $vars['config']['settings'],
                                      'urls'     => $vars['urls'],
                                    ]
        );
      });

    /**
     * API Routes
     */

    $app->get('/api/settings',
      function (Application $app, Request $request) {
        return new JsonResponse([
                                  'status'   => 'success',
                                  'settings' => $this->app['vars']['config']['settings'],
                                ]);
      });

    $app->post('/api/settings',
      function (Application $app, Request $request) {
        $data = $request->request->all();
        $merged = array_merge($this->app['vars']['config']['settings'], $data);

        /** @var Vars $vars */
        $vars = $this->app['vars'];
        $vars->set('config.settings', $merged);

        $output = Yaml::dump(['config' => $vars->get('config')], 3);

        if (file_put_contents($this->configPath . '/config.yml', $output) === FALSE) {
          return new JsonResponse(['status' => 'fail', 'message' => 'Failed to write output file']);
        }
        return new JsonResponse([
                                  'status'   => 'success',
                                  'settings' => $this->app['vars']['config']['settings'],
                                ]);
      })->when("request.headers.get('Content-Type') === 'application/json'");

    /**
     * Get all urls
     */
    $app->get('/api/urls',
      function (Application $app, Request $request) {
        return new JsonResponse(['urls' => $app['vars']['urls']]);
      });

    /**
     * Add a url
     */
    $app->post('/api/urls/add',
      function (Application $app, Request $request) {
        $data = $request->request->all();
        /** @var Vars $vars */
        $vars = $this->app['vars'];

        //TODO - Validate urls
        $merged = array_merge($vars->get('urls'), $data['urls']);
        $vars->set('urls', $merged);
        $output = Yaml::dump(['urls' => $vars->get('urls')], 3);
        if (file_put_contents($this->configPath . '/urls.yml', $output) === FALSE) {
          return new JsonResponse(['status' => 'fail', 'message' => 'Failed to write output file']);
        }
        return new JsonResponse([
                                  'status' => 'success',
                                  'urls'   => $this->app['vars']['urls'],
                                ]);
      })->when("request.headers.get('Content-Type') === 'application/json'");

    /**
     * Delete a url
     */
    $app->delete('/api/urls/delete/{title}',
      function (Application $app, Request $request, string $title) {
        /** @var Vars $vars */
        $vars = $app['vars'];

        $urls = $vars->get('urls');
        $id = self::inArray($title, $urls);
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
                                  'urls'   => $this->app['vars']['urls'],
                                ]);

      });
  }

  /**
   * @param $needle
   * @param $haystack
   *
   * @return int|null|string
   */
  public static function inArray($needle, &$haystack) {
    $found = NULL;
    foreach ($haystack as $key => $item) {
      if ($item === $needle) {
        $found = $key;
        break;
      } elseif (is_array($item)) {
        $found = self::inArray($needle, $item);
        if ($found !== NULL) {
          $found = $key;
          break;
        }
      }
    }
    return $found;
  }

  /**
   *
   */
  public function run() {
    return $this->app->run();
  }

}