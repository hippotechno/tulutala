<?php namespace Cms\Classes;

use Winter\Storm\Halcyon\Datasource\FileDatasource;

/**
 * File datasource scoped to a single CMS theme.
 */
class ThemeFileDatasource extends FileDatasource
{
    /**
     * Include the theme path in cache keys so identical template names in
     * different themes cannot collide when using a shared cache store.
     */
    public function makeCacheKey(string $name = ''): string
    {
        return hash('crc32b', $this->basePath . '|' . $name);
    }
}
