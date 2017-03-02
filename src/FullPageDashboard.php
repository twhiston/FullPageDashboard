<?php
/**
 * Created by PhpStorm.
 * User: twhiston
 * Date: 25.02.17
 * Time: 23:58
 */

namespace twhiston\FullPageDashboard;

use M1\Vars\Provider\Silex\VarsServiceProvider;
use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use twhiston\FullPageDashboard\Controller\Settings;
use twhiston\FullPageDashboard\Controller\Urls;

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
   * @var bool
   */
  protected $booted = FALSE;

  /**
   * FullPageDashboard constructor.
   *
   * @param bool $debug
   *
   * @throws \LogicException
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
   * Register all app services and controllers
   */
  public function registerServices() {

    $app = $this->app;
    $configPath = $this->configPath;
    $app->register(new VarsServiceProvider($this->configPath),
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
    $app->register(new ServiceControllerServiceProvider());

    $app['api.urls'] = function () use ($configPath) {
      return new Urls($configPath);
    };
    $app['api.settings'] = function () use ($configPath) {
      return new Settings($configPath);
    };
  }

  /**
   * Register all the app routes
   */
  public function registerRoutes() {

    $app = $this->app;

    /**
     * API Routes
     */
    //Settings
    $app->get('/api/settings', 'api.settings:get');
    $app->post('/api/settings', 'api.settings:set')->when("request.headers.get('Content-Type') === 'application/json'");

    //Urls
    $app->get('/api/urls', 'api.urls:get');
    $app->post('/api/urls/add', 'api.urls:create')
        ->when("request.headers.get('Content-Type') === 'application/json'");//Deprecated
    $app->post('/api/urls/create', 'api.urls:create')
        ->when("request.headers.get('Content-Type') === 'application/json'");
    $app->delete('/api/urls/delete/{title}', 'api.urls:delete');
  }

  /**
   * Run boot commands to register everything.
   */
  public function boot() {

    $this->registerServices();
    $this->registerRoutes();
    $this->booted = TRUE;

  }

  /**
   * Proxy to run the app
   */
  public function run() {
    if (!$this->booted) {
      $this->boot();
    }
    return $this->app->run();
  }

}