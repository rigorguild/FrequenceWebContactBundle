<?php

namespace FrequenceWeb\Bundle\ContactBundle\Tests\Controller;

use FrequenceWeb\Bundle\ContactBundle\Tests\Functional\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->enableProfiler();
        $crawler = $client->request('GET', '/contact.html');

        file_put_contents(__DIR__ . '/../Functional/result.html', $client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());


        $form = $crawler->selectButton('contact_message_submit')->form();
        $client->submit($form, [
            'contact[name]'    => 'John Doe',
            'contact[subject]' => 'I have a message for you.',
            'contact[body]'    => 'This is my message body.',
        ]);

        $this->assertNotInstanceOf(RedirectResponse::class, $client->getResponse());

        $form = $crawler->selectButton('contact_message_submit')->form();
        $client->submit($form, [
            'contact[name]'    => 'John Doe',
            'contact[email]'   => 'john.doe@gmail.com',
            'contact[subject]' => 'I have a message for you.',
            'contact[body]'    => 'This is my message body.',
        ]);
        $client->submit($form);

        $collector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertCount(1, $collector->getMessages());

        $this->assertInstanceOf(RedirectResponse::class, $client->getResponse());
        $client->followRedirect();
    }
}
