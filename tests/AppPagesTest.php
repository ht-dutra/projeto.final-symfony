<?php

namespace App\Tests;

use App\Entity\Categoria;
use App\Entity\Produto;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppPagesTest extends WebTestCase
{
    public function testAuthenticatedPagesRender(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $suffix = bin2hex(random_bytes(4));

        $user = (new Usuario())
            ->setEmail(sprintf('phpunit-%s@nerddev.test', $suffix))
            ->setRoles([])
            ->setPassword(password_hash('senha', PASSWORD_BCRYPT));

        $categoria = (new Categoria())->setNome('Categoria '.$suffix);

        $produto = (new Produto())
            ->setNome('Produto '.$suffix)
            ->setDescricao('Produto criado pelo teste')
            ->setQuantidade(3)
            ->setValor('10.00')
            ->setCategoria($categoria);

        $em->persist($user);
        $em->persist($categoria);
        $em->persist($produto);
        $em->flush();

        $produtoId = $produto->getId();
        $categoriaId = $categoria->getId();
        $userId = $user->getId();

        try {
            $client->loginUser($user);

            $client->request('GET', '/');
            self::assertSelectorTextContains('.nd-nav__right', 'Sair');

            $urls = [
                '/',
                '/app/produtos',
                '/app/categorias',
                '/app/produtos/cadastrar',
                '/app/produtos/'.$produtoId,
            ];

            foreach ($urls as $url) {
                $client->request('GET', $url);
                self::assertResponseIsSuccessful($url);
            }
        } finally {
            $connection = static::getContainer()
                ->get(EntityManagerInterface::class)
                ->getConnection();

            $connection->executeStatement('DELETE FROM produto WHERE id = ?', [$produtoId]);
            $connection->executeStatement('DELETE FROM categoria WHERE id = ?', [$categoriaId]);
            $connection->executeStatement('DELETE FROM usuario WHERE id = ?', [$userId]);
        }
    }

    public function testStorefrontIsPublic(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.nd-nav__right', 'Entrar');
        self::assertSelectorTextNotContains('.nd-nav__right', 'Sair');
    }

    public function testAdminAreaRequiresLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/app/produtos');

        self::assertResponseRedirects('/login');
    }

    public function testLoginPageRenders(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[method="post"]');
    }
}
