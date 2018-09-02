<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Translator;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Spiral\Core\MemoryInterface;
use Spiral\Translator\CatalogueInterface;
use Spiral\Translator\Catalogues\Manager;
use Spiral\Translator\Configs\TranslatorConfig;
use Spiral\Translator\Loaders\PhpFileLoader;
use Symfony\Component\Translation\Loader\PoFileLoader;

class ManagerTest extends TestCase
{
    public function testLocalesFromLoader()
    {
        $memory = m::mock(MemoryInterface::class);
        $memory->shouldReceive('loadData')->andReturn(null);
        $memory->shouldReceive('saveData')->andReturn(null);

        $manager = new Manager(new TranslatorConfig([
                'directory' => __DIR__ . '/fixtures/locales/',
                'loaders'   => [
                    'php' => PhpFileLoader::class,
                    'po'  => PoFileLoader::class,
                ]
            ]
        ), $memory);

        $this->assertTrue($manager->has('ru'));
        $this->assertTrue($manager->has('en'));
    }

    public function testLocalesFromMemory()
    {
        $memory = m::mock(MemoryInterface::class);
        $memory->shouldReceive('loadData')->andReturn(['en', 'ru']);
        $memory->shouldNotReceive('saveData')->andReturn(null);

        $manager = new Manager(new TranslatorConfig([
                'directory' => __DIR__ . '/fixtures/locales/',
                'loaders'   => [
                    'php' => PhpFileLoader::class,
                    'po'  => PoFileLoader::class,
                ]
            ]
        ), $memory);

        $this->assertTrue($manager->has('ru'));
        $this->assertTrue($manager->has('en'));
    }

    public function testCatalogue()
    {
        $memory = m::mock(MemoryInterface::class);
        $memory->shouldReceive('loadData')->with(
            Manager::MEMORY
        )->andReturn(['en', 'ru']);

        $manager = new Manager(new TranslatorConfig([
                'directory' => __DIR__ . '/fixtures/locales/',
                'loaders'   => [
                    'php' => PhpFileLoader::class,
                    'po'  => PoFileLoader::class,
                ]
            ]
        ), $memory);

        $memory->shouldReceive('loadData')->with(
            Manager::MEMORY . '/ru'
        )->andReturn([]);

        $catalogue = $manager->get("ru");
        $this->assertInstanceOf(CatalogueInterface::class, $catalogue);

        $this->assertTrue($catalogue->has('messages', 'message'));
        $this->assertSame('translation', $catalogue->get('messages', 'message'));

        $memory->shouldReceive('saveData')->with(
            'locales/ru',
            [
                'messages' => [
                    'message' => 'translation'
                ],
                'views'    => [
                    'Welcome To Spiral' => 'Добро пожаловать в Spiral Framework',
                    'Twig Version'      => 'Twig версия'
                ]
            ]
        )->andReturn(null);

        $memory->shouldReceive('saveData')->with(
            'locales/ru',
            [
                'messages' => [
                    'message' => 'new message'
                ],
                'views'    => [
                    'Welcome To Spiral' => 'Добро пожаловать в Spiral Framework',
                    'Twig Version'      => 'Twig версия'
                ]
            ]
        )->andReturn(null);

        $catalogue->set('messages', 'message', 'new message');
        $manager->save('ru');
    }

    public function testFromMemory()
    {
        $memory = m::mock(MemoryInterface::class);
        $memory->shouldReceive('loadData')->with(
            Manager::MEMORY
        )->andReturn(['en', 'ru']);

        $memory->shouldReceive('loadData')->with(
            Manager::MEMORY . '/ru'
        )->andReturn([
            'messages' => [
                'message' => 'new message'
            ],
            'views'    => [
                'Welcome To Spiral' => 'Добро пожаловать в Spiral Framework',
                'Twig Version'      => 'Twig версия'
            ]
        ]);

        $manager = new Manager(new TranslatorConfig([
                'directory' => __DIR__ . '/fixtures/locales/',
                'loaders'   => [
                    'php' => PhpFileLoader::class,
                    'po'  => PoFileLoader::class,
                ]
            ]
        ), $memory);

        $memory->shouldReceive('loadData')->with(
            Manager::MEMORY . '/ru'
        )->andReturn([]);

        $catalogue = $manager->get("ru");
        $this->assertInstanceOf(CatalogueInterface::class, $catalogue);

        $this->assertTrue($catalogue->has('messages', 'message'));
        $this->assertSame('new message', $catalogue->get('messages', 'message'));
    }

    /**
     * @expectedException \Spiral\Translator\Exceptions\LocaleException
     */
    public function testException()
    {
        $memory = m::mock(MemoryInterface::class);
        $memory->shouldReceive('loadData')->with(
            Manager::MEMORY
        )->andReturn(['en']);

        $memory->shouldReceive('saveData')->with()->andReturn(null);

        $manager = new Manager(new TranslatorConfig([
                'directory' => __DIR__ . '/fixtures/locales/',
                'loaders'   => [
                    'php' => PhpFileLoader::class,
                    'po'  => PoFileLoader::class,
                ]
            ]
        ), $memory);

        $manager->load('ru');
    }
}