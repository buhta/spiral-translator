<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Translator;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Translator\Catalogue\CatalogueLoader;
use Spiral\Translator\Catalogue\CatalogueManager;
use Spiral\Translator\Catalogue\LoaderInterface;
use Spiral\Translator\CatalogueManagerInterface;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\PoFileLoader;

class TranslatorTest extends TestCase
{
    public function testIsMessage()
    {
        $this->assertTrue(Translator::isMessage('[[hello]]'));
        $this->assertFalse(Translator::isMessage('hello'));
    }

    public function testLocale()
    {
        $translator = $this->translator();
        $this->assertSame('en', $translator->getLocale());

        $translator2 = $translator->withLocale('ru');
        $this->assertSame('ru', $translator2->getLocale());
        $this->assertSame('en', $translator->getLocale());
    }

    /**
     * @expectedException \Spiral\Translator\Exception\LocaleException
     */
    public function testLocaleException()
    {
        $translator = $this->translator();
        $translator->withLocale('de');
    }

    public function testDomains()
    {
        $translator = $this->translator();

        $this->assertSame('spiral', $translator->getDomain('spiral-views'));
        $this->assertSame('messages', $translator->getDomain('vendor-views'));
    }

    public function testCatalogues()
    {
        $translator = $this->translator();
        $this->assertCount(2, $translator->getCatalogueManager()->getLocales());
    }

    public function testTrans()
    {
        $translator = $this->translator();
        $this->assertSame('message', $translator->trans('message'));

        $translator2 = $translator->withLocale('ru');
        $this->assertSame('translation', $translator2->trans('message'));

        $this->assertSame('message', $translator->trans('message'));
    }

    protected function translator(): Translator
    {
        $container = new Container();
        $container->bind(TranslatorConfig::class, new TranslatorConfig([
            'locale'    => 'en',
            'directory' => __DIR__ . '/fixtures/locales/',
            'loaders'   => [
                'php' => PhpFileLoader::class,
                'po'  => PoFileLoader::class,
            ],
            'domains'   => [
                'spiral'   => [
                    'spiral-*'
                ],
                'messages' => ['*']
            ]
        ]));

        $container->bindSingleton(TranslatorInterface::class, Translator::class);
        $container->bindSingleton(CatalogueManagerInterface::class, CatalogueManager::class);
        $container->bind(LoaderInterface::class, CatalogueLoader::class);

        return $container->get(TranslatorInterface::class);
    }
}