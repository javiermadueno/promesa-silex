<?php
/**
 * Created by PhpStorm.
 * User: javi
 * Date: 25/09/15
 * Time: 20:59
 */
namespace Promesa\Front\Controller;

use Silex\Application;
use Swift_Message;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Validator\Constraints\NotBlank;

class FrontController
{

    /**
     * @param Request     $request
     * @param Application $app
     *
     * @return string
     */
    public function indexAction(Request $request, Application $app)
    {
        return $app['twig']->render('index.html.twig');
    }

    /**
     * @param Request     $request
     * @param Application $app
     *
     * @return string
     */
    public function contactAction(Request $request, Application $app)
    {
        $data = [];

        /** @var Form $form */
        $form = $app['form.factory']->createBuilder('form', $data, [
            'action' => $app['url_generator']->generate('contact'),
            'method' => Request::METHOD_POST
        ])
            ->add('nombre', 'text', [
                'required'    => false,
                'label'       => false,
                'attr'        => [
                    'placeholder' => 'form.contacto.nombre.placeholder'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'form.contacto.nombre.notblank'])
                ]
            ])
            ->add('apellidos', 'text', [
                'required' => false,
                'label'    => false,
                'attr'     => [
                    'placeholder' => 'form.contacto.apellidos.placeholder'
                ]
            ])
            ->add('email', 'email', [
                'required'    => false,
                'label'       => false,
                'attr'        => [
                    'placeholder' => 'form.contacto.email.placeholder'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'form.contacto.email.notblank'])
                ]
            ])
            ->add('mensaje', 'textarea', [
                'required'    => false,
                'label'       => false,
                'attr'        => [
                    'placeholder' => 'form.contacto.mensaje.placeholder',
                    'cols'        => 30,
                    'rows'        => 10
                ],
                'constraints' => [
                    new NotBlank(['message' => 'form.contacto.mensaje.notblank'])
                ]
            ])
            ->add('submit', 'submit', [
                'label' => 'form.contacto.submit',
                'attr'  => [
                    'class' => 'btn',
                ]
            ])
            ->getForm();

        if ($request->isMethod(Request::METHOD_GET)) {
            return $app['twig']->render('contacto.html.twig', [
                'form' => $form->createView()
            ]);
        }

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();

            $message = \Swift_Message::newInstance()
                ->setSubject('Contacto desde web "Promesa de la Virgen de Fatima"')
                ->setFrom([$data['email'] => sprintf("%s %s", $data['nombre'], $data['apellidos'])])
                ->setTo('javiermadueno@gmail.com')
                ->setBcc('javiermadueno@gmail.com')
                ->setBody($app['twig']->render('email/template.html.twig', ['data' => $data]), 'text/html');

            $app['mailer']->send($message);

            return JsonResponse::create([
                'alerta' => $app['twig']->render('alerta.html.twig', ['error' => !$form->isValid()]),
                'form'   => $app['twig']->render('form_contact.html.twig', ['form' => $form->createView()])
            ], Response::HTTP_OK);
        }

        return JsonResponse::create([
            'alerta' => $app['twig']->render('alerta.html.twig', ['error' => !$form->isValid()]),
            'form'   => $app['twig']->render('form_contact.html.twig', ['form' => $form->createView()])
        ], Response::HTTP_BAD_REQUEST);
    }

    public function sendFileAction(Request $request, Application $app)
    {
        $file = $request->query->get('file', '');
        $base_path = $request->getBasePath();
        $file_path = $base_path . 'download/' . $file;

        $log = $app['monolog'];

        $log->addInfo(sprintf('Se ha solicitado el archivo: %s', $file_path));

        if (!file_exists($file_path)) {
            $log->addError('No se ha encontrado el fichero');
            $app->abort(Response::HTTP_NO_CONTENT, 'No se ha encontrado el fichero solicitado');
        }

        return $app->sendFile($file_path)
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file);
    }

} 