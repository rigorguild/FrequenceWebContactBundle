<?php

namespace FrequenceWeb\Bundle\ContactBundle\EventDispatcher\Listener;

use FrequenceWeb\Bundle\ContactBundle\EventDispatcher\Event\MessageSubmitEvent;
use Swift_Mailer;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Listener for contact events, that sends emails
 *
 * @author Yohan Giarelli <yohan@giarel.li>
 */
class EmailContactListener
{
    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var array
     */
    protected $config;

    public function __construct(Swift_Mailer $mailer, Environment $twig, TranslatorInterface $translator, array $config)
    {
        $this->mailer     = $mailer;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->config     = $config;
    }

    public function onMessageSubmit(MessageSubmitEvent $event): void
    {
        $contact = $event->getContact();

        $message = new \Swift_Message($this->translator->trans(
            $this->config['subject'],
            $contact->toTranslateArray(),
            'FrequenceWebContactBundle'
        ));

        $message->addFrom($this->config['from']);
        $message->addReplyTo($contact->getEmail(), $contact->getName());
        $message->addTo($this->config['to']);
        $message->addPart(
            $this->twig->render(
                '@FrequenceWebContactBundle/Mails/mail.html.twig',
                ['contact' => $contact]
            ),
            'text/html'
        );
        $message->addPart(
            $this->twig->render(
                '@FrequenceWebContactBundle/Mails/mail.txt.twig',
                ['contact' => $contact]
            ),
            'text/plain'
        );

        $this->mailer->send($message);
    }
}
