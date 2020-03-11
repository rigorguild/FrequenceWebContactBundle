<?php

namespace FrequenceWeb\Bundle\ContactBundle\Controller;

use FrequenceWeb\Bundle\ContactBundle\EventDispatcher\ContactEvents;
use FrequenceWeb\Bundle\ContactBundle\EventDispatcher\Event\MessageSubmitEvent;
use FrequenceWeb\Bundle\ContactBundle\Model\Contact;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Contact controller
 *
 * @author Yohan Giarelli <yohan@giarel.li>
 */
class DefaultController extends AbstractController
{
    /**
     * Action that displays the contact form
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        $this->container->get('session')->set('_fw_contact_referer', $request->getUri());

        return $this->renderFormResponse($this->getForm());
    }

    /**
     * Action that handles the submitted contact form
     *
     * @param  Request $request
     *
     * @return Response
     */
    public function submitAction(Request $request): Response
    {
        $form = $this->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Send the event for message handling (send mail, add to DB, don't care)
            $event = new MessageSubmitEvent($form->getData());
            /** @var EventDispatcherInterface $eventDispatcher */
            $eventDispatcher = $this->container->get('event_dispatcher');
            $eventDispatcher->dispatch($event, ContactEvents::onMessageSubmit);

            // Let say the user it's ok
            $message = $this->container->get('translator')->trans('contact.submit.success', [], 'FrequenceWebContactBundle');
            $this->container->get('session')->getFlashBag()->add('success', $message);

            // Redirect somewhere
            return new RedirectResponse($this->container->get('session')->get('_fw_contact_referer'));
        }

        // Let say the user there's a problem
        $message = $this->container->get('translator')->trans('contact.submit.failure', [], 'FrequenceWebContactBundle');
        $this->container->get('session')->getFlashBag()->add('error', $message);

        // Errors ? Re-render the form
        return $this->renderFormResponse($form);
    }

    /**
     * Returns the rendered form response
     *
     * @param  FormInterface $form
     *
     * @return Response
     */
    protected function renderFormResponse(FormInterface $form)
    {
        return $this->render(
            '@FrequenceWebContactBundle/Default/index.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Returns the contact form instance
     *
     * @return FormInterface
     */
    protected function getForm(): FormInterface
    {
        return $this->container->get('form.factory')->create(
            $this->container->getParameter('frequence_web_contact.type'),
            $this->container->get('frequence_web_contact.model')
        );
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedServices()
    {
        $dependentServices = parent::getSubscribedServices();

        return array_merge($dependentServices, [
            'event_dispatcher' => EventDispatcherInterface::class,
            'translator' => TranslatorInterface::class,
        ]);
    }
}
