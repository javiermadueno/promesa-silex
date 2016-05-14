<?php

require_once __DIR__ . '/bootstrap.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;

$app = new Application();

$app['debug'] = false;

$app['enabled.locales'] = ['es', 'en', 'pt'];
$app['config.locales.regexp'] = 'es|en|pt';

$app->register(new \DerAlex\Silex\YamlConfigServiceProvider(__DIR__ .'/config.yml'));
$app->register(new Sorien\Provider\PimpleDumpProvider());
$app->register(new Silex\Provider\MonologServiceProvider(), [
    'monolog.logfile' => __DIR__ . '/../logs/development.log',
]);
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\HttpFragmentServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/../views'
]);

$app->register(new Silex\Provider\TranslationServiceProvider(), [
    'locale' => 'es',
    'locale_fallbacks'    => ['es'],
]);
$app->register(new Silex\Provider\SwiftmailerServiceProvider(), [
    'swiftmailer.options' => [
        'host'       => $app['config']['email']['host'],
        'port'       => $app['config']['email']['port'],
        'username'   => $app['config']['email']['username'],
        'password'   => $app['config']['email']['pass'],
        'encryption' => $app['config']['email']['encryption'],
        'auth_mode'  => $app['config']['email']['auth_mode']
    ]
]);

$pp['monolog'] = $app->share($app->extend('monolog', function($monolog, $app) {
    $monolog->pushHandler(new RotatingFileHandler($app['monolog.logfile'], 10, Logger::DEBUG));
    return $monolog;
}));

$app['translator'] = $app->share($app->extend('translator', function ($translator, $app) {
    $translator->addLoader('yaml', new YamlFileLoader());
    $translator->addResource('yaml', __DIR__ . '/../locales/es.yml', 'es');
    $translator->addResource('yaml', __DIR__ . '/../locales/en.yml', 'en');
    $translator->addResource('yaml', __DIR__ . '/../locales/pt.yml', 'pt');
    $translator->addResource('yaml', __DIR__ . '/../locales/validation.es.yml', 'es', 'validators');
    $translator->addResource('yaml', __DIR__ . '/../locales/validation.en.yml', 'en', 'validators');
    $translator->addResource('yaml', __DIR__ . '/../locales/validation.pt.yml', 'pt', 'validators');

    return $translator;
}));

$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
    $twig->addFunction(new \Twig_SimpleFunction('asset', function ($asset) use ($app) {
        $base_path = $app['request']->getBasePath();
        return sprintf('%s/%s', $base_path, ltrim($asset, '/'));
    }));

    return $twig;
}));

$call_back_locale = function (Request $request, Application $app) {
    $monolog = $app['monolog'];

    if( null === ($locale = $request->cookies->get('locale'))) {
        $locale = $request->getPreferredLanguage($app['enabled.locales']);
    }
    $app['translator']->setLocale($locale);
    $monolog->addDebug('Accepted-Languages: ' . implode(', ', $request->getLanguages()));
    $monolog->addDebug('Lengua preferida: ' . $locale);
    $request->setLocale($locale);
};

//$app->before($call_back_locale);

/**
 * Index URL, automatic redirect to preferred user locale
 */
$app->get('/', function (Application $app) {
    $locale = $app['request']->getPreferredLanguage($app['enabled.locales']);
    return $app->redirect(
        $app['url_generator']->generate('homepage', array('_locale' => $locale))
    );
});


$app
    ->get('/{_locale}', 'Promesa\Front\Controller\FrontController::indexAction')
    ->bind('homepage')
    ->assert('_locale', $app['config.locales.regexp']);
    //->value("_locale", 'es')
;

$app
    ->match('/{_locale}/contact', 'Promesa\Front\Controller\FrontController::contactAction')
    ->bind('contact')
;

$app->get('{_locale}/file', 'Promesa\Front\Controller\FrontController::sendFileAction')
    ->bind('file');

$app->get('/{_locale}/cookie', function($_locale){
    $cookie = new Cookie('locale', $_locale, time() + (1 * 365 * 24 * 60 * 60) );
    $response  = new RedirectResponse('/');
    $response->headers->setCookie($cookie);
    return $response;
})->bind('cookie');

return $app;

