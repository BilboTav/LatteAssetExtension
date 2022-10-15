<?php
declare(strict_types=1);

namespace Bilbofox\Latte\Formatters;

/**
 * Tries to use min version of asset path - in various usuall forms by trying file existence
 *
 * @author Michal Kvita <Mikvt@seznam.cz>
 */
class TryMinFormatter
{

    /**
     * 
     * @param string $wwwDir www root directory - where webserver has access to
     */
    public function __construct(private string $wwwDir)
    {
        
    }

    public function __invoke(string $path): string
    {
        $assetFile = $this->wwwDir . DIRECTORY_SEPARATOR . $path;
        if (file_exists($assetFile)) {
            $assetFileExt = pathinfo($assetFile, PATHINFO_EXTENSION);
            $assetFilePath = pathinfo($assetFile, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($assetFile, PATHINFO_FILENAME);
            $pathWithoutExt = pathinfo($path, PATHINFO_DIRNAME) . '/' . pathinfo($path, PATHINFO_FILENAME);

            if (file_exists($assetFilePath . '.min.' . $assetFileExt)) {
                $path = $pathWithoutExt . '.min.' . $assetFileExt;
            } elseif (file_exists($assetFilePath . '-min.' . $assetFileExt)) {
                $path = $pathWithoutExt . '-min.' . $assetFileExt;
            }
        }

        return $path;
    }
}