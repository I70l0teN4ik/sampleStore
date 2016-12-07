<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Client;


class ProductControllerTest extends WebTestCase
{
    /** @var  Client */
    private $client;
    private $username;
    private $password;
    private $title;
    private $price;
    private $description;

    protected function setUp()
    {
        parent::setUp();
        $this->client = static::createClient();

        $this->username = 'admin';
        $this->password = 'pswd';
        $this->title = 'Test Product';
        $this->price = 12.34;
        $this->description = 'Curabitur posuere est at tempor hendrerit. Curabitur nibh neque, suscipit at lorem
                vitae, ultricies interdum lectus. Nullam varius urna vel nibh rutrum, at pretium purus placerat.
                Maecenas tempus vestibulum mi, in sagittis est pulvinar ac. Aenean pulvinar eleifend metus, ac aliquet';
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            'Sample Store',
            $crawler->filter('a.navbar-brand')->text(),
            'Homepage reached successfully.'
            );
    }

    public function testSecuredUrl()
    {
        $this->client->followRedirects();

        // logout in case user exist in session
        $this->client->request('GET', '/logout');

        $crawler = $this->client->request('GET', '/admin/new-product');

        $this->assertEquals(
            'http://localhost/login',
            $crawler->getBaseHref(),
            'The /admin/new-product secure URL redirects to the login form.'
        );
        $this->client->followRedirects(false);
    }

    public function testCreateNewProductWorkflow()
    {
        // login as admin
        $crawler = $this->client->request('GET', '/login');

        $this->assertContains('Login', $crawler->filter('form legend')->text());
        $this->assertTrue($crawler->filter("form")->count() > 0, "Login form exist");

        $form = $crawler->filter("form")->form();
        $this->client->submit($form, array(
            '_username' => $this->username,
            '_password' => $this->password,
        ));
        if ($this->client->getResponse()->isRedirect()) {
            $crawler = $this->client->followRedirect();
        }
        $this->assertContains('Logout', $crawler->filter('ul.navbar-nav')->text());

        $crawler = $this->client->request('GET', '/admin/new-product');

        $this->assertEquals(
            'http://localhost/admin/new-product',
            $crawler->getBaseHref(),
            'Logged in as admin successfully. New Product view reached.'
        );


        $this->assertContains('Add new Product', $crawler->filter('h1')->text());
        $this->assertTrue($crawler->filter("form")->count() > 0, "New prod form exist");

        $form = $crawler->filter("form")->form();
        $this->client->submit($form, array(
            'product[name]' => $this->title,
            'product[price]' => $this->price,
            'product[description]' => $this->description,
        ));
        if ($this->client->getResponse()->isRedirect()) {
            $crawler = $this->client->followRedirect();
        }
        $this->assertContains('New Product created successfully.', $crawler->filter('body')->text());

        $link = $crawler->selectLink('Test Product')->link();
        $crawler = $this->client->click($link);

        $this->assertTrue($crawler->filter("form")->count() > 0, "Delete form exist");

        $form = $crawler->filter("form")->form();
        $this->client->submit($form, array());
        if ($this->client->getResponse()->isRedirect()) {
            $crawler = $this->client->followRedirect();
        }

        $this->assertContains('Product deleted successfully', $crawler->filter('body')->text());
    }
}
