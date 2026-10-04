<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Building;
use App\Entity\Location;
use App\Entity\User;
use App\Kernel;
use App\Service\Inventory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class InventoryTest extends WebTestCase
{
    private KernelBrowser $client;
    private string $database;

    protected static function getKernelClass(): string { return Kernel::class; }

    protected function setUp(): void
    {
        $this->database = tempnam(sys_get_temp_dir(), 'tree-inventory-');
        $_ENV['DATABASE_URL'] = $_SERVER['DATABASE_URL'] = 'sqlite:///'.$this->database;
        $this->client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        (new SchemaTool($em))->createSchema(array_map($em->getClassMetadata(...), [User::class, Building::class, Location::class]));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        @unlink($this->database);
    }

    private function user(string $email): array
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = (new User())->setEmail($email);
        $user->setPassword(self::getContainer()->get('security.user_password_hasher')->hashPassword($user, 'Demo-test-password-123!'));
        $em->persist($user);
        $em->flush();
        $building = self::getContainer()->get(Inventory::class)->forUser($user);
        return [$user, $building->getRootLocation()->getId(), $building->getCode()];
    }

    private function api(string $method, string $uri, ?array $body = null): array
    {
        $this->client->request($method, $uri, server: [
            'CONTENT_TYPE' => $method === 'PATCH' ? 'application/merge-patch+json' : 'application/ld+json',
            'HTTP_ACCEPT' => 'application/ld+json',
        ], content: $body === null ? null : json_encode($body, JSON_THROW_ON_ERROR));
        return json_decode($this->client->getResponse()->getContent(), true) ?? [];
    }

    public function testRegistrationLoginLogoutAndPrivateRoot(): void
    {
        $this->client->request('GET', '/inventory');
        self::assertResponseRedirects('/login');
        $crawler = $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();
        $this->client->submit($crawler->selectButton('Register')->form([
            'registration_form[email]' => 'demo@example.test',
            'registration_form[plainPassword]' => 'Demo-test-password-123!',
        ]));
        self::assertResponseRedirects('/inventory');
        $crawler = $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'My inventory');
        $data = $this->api('GET', '/api/locations');
        self::assertResponseIsSuccessful();
        self::assertCount(1, $data['member']);
        self::assertSame('demo@example.test', $data['member'][0]['name']);
        $this->client->click($crawler->selectLink('Sign out')->link());
        self::assertResponseRedirects('/login');
        $crawler = $this->client->followRedirect();
        $this->client->submit($crawler->selectButton('Sign in')->form(['email' => 'demo@example.test', 'password' => 'wrong-password']));
        self::assertResponseRedirects('/login');
        $crawler = $this->client->followRedirect();
        self::assertSelectorExists('.alert-danger');
        $this->client->submit($crawler->selectButton('Sign in')->form(['email' => 'demo@example.test', 'password' => 'Demo-test-password-123!']));
        self::assertResponseRedirects('/inventory');
    }

    public function testApiEditingAndTenantIsolation(): void
    {
        [$alice, $aliceRoot, $aliceBuilding] = $this->user('alice@example.test');
        [$bob, $bobRoot, $bobBuilding] = $this->user('bob@example.test');
        $this->client->loginUser($alice);
        $list = $this->api('GET', '/api/locations');
        self::assertResponseIsSuccessful();
        self::assertCount(1, $list['member']);
        self::assertSame($aliceRoot, $list['member'][0]['id']);
        $this->api('GET', '/api/locations/'.$bobRoot);
        self::assertResponseStatusCodeSame(404);
        $this->client->request('GET', '/building/show/'.$bobBuilding);
        self::assertResponseStatusCodeSame(404);
        $list = $this->api('GET', '/api/building/'.$bobBuilding.'/locations.jsonld');
        self::assertResponseIsSuccessful();
        self::assertEmpty($list['member']);

        $item = $this->api('POST', '/api/locations', ['name' => 'Kitchen', 'parent' => '/api/locations/'.$aliceRoot]);
        self::assertResponseStatusCodeSame(201);
        self::assertSame($aliceRoot, $item['parentId']);
        $iri = $item['@id'];
        $item = $this->api('PATCH', $iri, ['name' => 'Kitchen cupboard']);
        self::assertResponseIsSuccessful();
        self::assertSame('Kitchen cupboard', $item['name']);
        $child = $this->api('POST', '/api/locations', ['name' => 'Cup', 'parent' => $iri]);
        self::assertResponseStatusCodeSame(201);
        $this->api('PATCH', $iri, ['parent' => $child['@id']]);
        self::assertResponseStatusCodeSame(422);
        $this->api('PATCH', $iri, ['parent' => '/api/locations/'.$bobRoot]);
        self::assertContains($this->client->getResponse()->getStatusCode(), [400, 403, 422]);
        $this->api('POST', '/api/locations', ['name' => 'Intruder', 'parent' => '/api/locations/'.$bobRoot]);
        self::assertContains($this->client->getResponse()->getStatusCode(), [400, 403, 422]);
        $this->api('PATCH', '/api/locations/'.$bobRoot, ['name' => 'Intruder']);
        self::assertResponseStatusCodeSame(404);
        $this->api('DELETE', '/api/locations/'.$bobRoot);
        self::assertResponseStatusCodeSame(404);
        $this->api('PATCH', '/api/locations/'.$aliceRoot, ['parent' => $iri]);
        self::assertResponseStatusCodeSame(422);
        $this->api('DELETE', '/api/locations/'.$aliceRoot);
        self::assertResponseStatusCodeSame(422);
        $this->api('PATCH', $child['@id'], ['parent' => '/api/locations/'.$aliceRoot]);
        self::assertResponseIsSuccessful();
        $this->api('DELETE', $child['@id']);
        self::assertResponseStatusCodeSame(204);
        $this->api('DELETE', $iri);
        self::assertResponseStatusCodeSame(204);
        $this->api('GET', $iri);
        self::assertResponseStatusCodeSame(404);
    }
}
