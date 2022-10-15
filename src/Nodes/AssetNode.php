<?php
declare(strict_types=1);

namespace Bilbofox\Latte\Nodes;

use Latte\CompileException;
use Latte\Compiler\Nodes\Html\AttributeNode;
use Latte\Compiler\Nodes\Html\ElementNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\Nodes\TextNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Latte\Compiler\TemplateParser;

/**
 * 
 *
 * @author Michal Kvita <Mikvt@seznam.cz>
 */
class AssetNode extends StatementNode
{
    public string $tagName;
    public array $usedAttrs = [];
    public ExpressionNode $value;

    /**
     * @var array tagName => main URL location attribute
     * @internal
     * 
     * @link https://www.w3schools.com/tags/tag_link.asp
     * @link https://www.w3schools.com/tags/tag_script.asp
     */
    private static array $validTags = [
        'script' => 'src',
        'link' => 'href',
    ];

    private static function findUsedAttrs(ElementNode $el): array
    {
        $res = [];
        foreach ($el->attributes?->children as $child) {
            if ($child instanceof AttributeNode && $child->name instanceof TextNode) {
                $res[$child->name->content] = true;
            }
        }

        return $res;
    }

    public static function create(Tag $tag, TemplateParser $templateParser, callable $pathFormatter = null)
    {
        $node = new static;

        // Check valid tag use...
        $tagName = $tag->htmlElement->name;
        if (!isset(self::$validTags[$tagName])) {
            throw new CompileException(sprintf('Invalid usage on tag <%s> - n:asset macro can be used only on tags %s',
                                    $tagName,
                                    implode(',', array_map(fn($tag) => '<' . $tag . '>', array_keys(self::$validTags)))
                            ), $tag->position);
        }
        $node->tagName = $tagName;

        // Check existing attributes...
        $usedAttrs = self::findUsedAttrs($tag->htmlElement);
        if (isset($usedAttrs[self::$validTags[$tagName]])) {
            throw new CompileException(sprintf('Tag <%s> already has main location attribute %s="", can not be used together with n:asset macro',
                                    $tagName,
                                    self::$validTags[$tagName]
                            ), $tag->position);
        }
        $node->usedAttrs = $usedAttrs;

        $tag->expectArguments();
        $node->value = $tag->parser->parseUnquotedStringOrExpression();

        return $node;
    }

    /**
     * @internal
     */
    public static function output(string $path, string $tagName, array $usedAttrs, string $basePath, callable $formatter): string
    {
        $output = '';
        // Default attributes for assets if needed...
        if ($tagName === 'link' && str_ends_with($path, '.css')) {
            if (!isset($usedAttrs['rel'])) {
                $output .= ' rel="stylesheet"';
            }
            if (!isset($usedAttrs['type'])) {
                $output .= ' type="text/css"';
            }
        }

        if ($tagName === 'script' && str_ends_with($path, '.js')) {
            if (!isset($usedAttrs['type'])) {
                $output .= ' type="text/javascript"';
            }
        }

        $urlAttr = self::$validTags[$tagName];

        $pathFormatted = htmlspecialchars((!str_starts_with($path, '/') ? $basePath . '/' : '') . $formatter($path), ENT_QUOTES);
        $output .= ' ' . $urlAttr . '="' . $pathFormatted . '"';

        return $output;
    }

    public function print(PrintContext $context): string
    {
        $basePathPass = '$this->params["basePath"] ?? ""';
        return $context->format(
                        'echo ' . self::class . '::output(%args, %dump, %dump, ' . $basePathPass . ', $this->global->_nAssetFormatter);',
                        [$this->value], $this->tagName, $this->usedAttrs
        );
    }
}