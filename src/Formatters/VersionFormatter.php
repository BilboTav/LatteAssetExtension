<?php
declare(strict_types=1);

namespace Bilbofox\Latte\Formatters;

/**
 * Adds version for assets - for reloading by client
 *
 * @author Michal Kvita <Mikvt@seznam.cz>
 */
class VersionFormatter
{
    /* @var int|string|callable */
    private $version;

    public function __construct(int|string|callable $version)
    {
        $this->version = $version;
    }

    public function __invoke(string $path): string
    {
        if (is_callable($this->version)) {
            $version = strval(($this->version)($path));
        } else {
            $version = $this->version;
        }

        if (isset($version)) {
            $path .= '?v=' . $version;
        }

        return $path;
    }
}