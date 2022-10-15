<?php
declare(strict_types=1);

namespace Bilbofox\Latte;

use Bilbofox\Latte\Nodes\AssetNode;
use Latte;
use Latte\Compiler\Tag;
use Latte\Compiler\TemplateParser;

/**
 * Latte extension for n:asset="" macro
 *
 * @author Michal Kvita <Mikvt@seznam.cz>
 */
class AssetExtension extends Latte\Extension
{
    private array $formatters = [];

    public function addFormatter(callable $formatter)
    {
        $this->formatters[] = $formatter;
        return $this;
    }

    public function getTags(): array
    {
        return [
            'n:asset' => fn(Tag $tag, TemplateParser $templateParser) => AssetNode::create($tag, $templateParser),
        ];
    }

    public function getProviders(): array
    {
        return [
            '_nAssetFormatter' => function (string $path): string {
                foreach ($this->formatters as $formatter) {
                    $path = $formatter($path);
                }

                return $path;
            },
        ];
    }
}