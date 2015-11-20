<?php

require_once __DIR__ . '/bootstrap.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

$app = new Application();

$app['debug'] = true;


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
    'locale_fallbacks'    => ['es'],
]);
$app->register(new Silex\Provider\SwiftmailerServiceProvider(), [
    'swiftmailer.options' => [
        'host'       => 'smtp.promesadelavirgendefatima.com',
        'port'       => '587',
        'username'   => 'info%promesadelavirgendefatima.com',
        'password'   => 'pJPIqsq86',
        'encryption' => null,
        'auth_mode'  => null
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
    $translator->addResource('yaml', __DIR__ . '/../locales/validation.es.yml', 'es', 'validators');
    $translator->addResource('yaml', __DIR__ . '/../locales/validation.en.yml', 'en', 'validators');

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

    $locale = $request->getPreferredLanguage(['es', 'en']);
    $app['translator']->setLocale($locale);
    $app['monolog']->addDebug('Lengua preferida: ' . $locale);
    $request->setLocale($locale);
};

$app->before($call_back_locale);

$app
    ->get('/', 'Promesa\Front\Controller\FrontController::indexAction')
    ->bind('homepage');

$app
    ->match('/contact', 'Promesa\Front\Controller\FrontController::contactAction')
    ->bind('contact')
    ->before($call_back_locale);

$app->get('/file', 'Promesa\Front\Controller\FrontController::sendFileAction')
    ->bind('file');

return $app;

