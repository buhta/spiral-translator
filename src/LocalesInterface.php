<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator;

/**
 * Manages list of locales and associated catalogues.
 */
interface LocalesInterface
{
    /**
     * Get list of all existed locales.
     *
     * @return array
     */
    public function getNames(): array;

    /**
     * Load catalogue.
     *
     * @param string $locale
     *
     * @return CatalogueInterface
     * @throws \Spiral\Translator\Exceptions\LocaleException
     */
    public function load(string $locale): CatalogueInterface;

    /**
     * Save catalogue changes.
     *
     * @param string $locale
     */
    public function save(string $locale);

    /**
     * Check if locale exists.
     *
     * @param string $locale
     *
     * @return bool
     */
    public function has(string $locale): bool;

    /**
     * Get catalogue associated with the locale.
     *
     * @param string $locale
     *
     * @return CatalogueInterface
     * @throws \Spiral\Translator\Exceptions\LocaleException
     */
    public function get(string $locale): CatalogueInterface;
}