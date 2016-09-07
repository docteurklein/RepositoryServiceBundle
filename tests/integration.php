<?php

namespace App;

use Symfony\Component\HttpKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Definition;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;

require __DIR__.'/../vendor/autoload.php';

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

/** @ORM\Entity */
class Product {
    /** @ORM\Id @ORM\Column */
    private $id;
}

class Products extends EntityRepository {
    public function test() { return 'yay'; }
}

class Kernel extends HttpKernel\Kernel
{
    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle,
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle,
            new \DocteurKlein\RepositoryServiceBundle,
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function($container) {
            $container->loadFromExtension('framework', [
                'secret' => 'test',
                'validation' => ['enable_annotations' => true],
            ]);
            $container->loadFromExtension('doctrine', [
                'dbal' => [
                    'driver' => 'pdo_sqlite',
                    'dbname' => 'memory',
                    'path' => __DIR__.'/knp_rad.sqlite',
                    'memory' => true,
                ],
                'orm' => [
                    'mappings' => [
                        'app' => [
                            'type' => 'annotation',
                            'dir' => __DIR__,
                            'prefix' => 'App',
                        ],
                    ],
                ],
            ]);
            $def = new Definition(Products::class);
            $def->addtag('repository', ['for' => Product::class]);
            $container->setDefinition('products', $def);
        });
    }
}

$kernel = new \App\Kernel('test', true);
$kernel->boot();
$container = $kernel->getContainer();
assert($container->get('products')->test() == 'yay');
