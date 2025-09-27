<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ErrorControllerTest extends WebTestCase
{
    public function testFileNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/test/file-not-found');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Fichier non trouvé');
        $this->assertSelectorTextContains('.alert-secondary', 'Chemin du fichier');
    }

    public function testFileDeleted(): void
    {
        $client = static::createClient();
        $client->request('GET', '/test/file-deleted');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Fichier supprimé');
        $this->assertSelectorTextContains('.alert-secondary', 'ID du fichier');
    }

    public function testFileNoPermission(): void
    {
        $client = static::createClient();
        $client->request('GET', '/test/file-no-permission');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Accès refusé');
        $this->assertSelectorTextContains('.alert-secondary', 'ID du fichier');
    }
}