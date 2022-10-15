<?php
declare(strict_types=1);

namespace Bilbofox\Latte\Formatters;

/**
 * Adds version for assets - for reloading by client from last modification of asset file
 *
 * @author Michal Kvita <Mikvt@seznam.cz>
 */
class FileVersionFormatter extends VersionFormatter
{

    /**
     * 
     * @param string $wwwDir www root directory - where webserver has access to
     */
    public function __construct(string $wwwDir)
    {
        parent::__construct(function (string $path) use ($wwwDir): ?string {
            $assetFile = $wwwDir . DIRECTORY_SEPARATOR . $path;
            return file_exists($assetFile) ? strval(filemtime($assetFile)) : null;
        });
    }
}