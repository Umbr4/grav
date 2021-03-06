<?php
/**
 * @package    Grav.Common.Twig
 *
 * @copyright  Copyright (C) 2015 - 2018 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Common\Twig\TokenParser;

use Grav\Common\Twig\Node\TwigNodeStyle;

/**
 * Adds a style to the document.
 *
 * {% style 'theme://css/foo.css' priority: 20 %}

 * {% style priority: 20 with { media: 'screen' } %}
 *     a { color: red; }
 * {% endstyle %}
 */
class TwigTokenParserStyle extends \Twig_TokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param \Twig_Token $token A Twig_Token instance
     *
     * @return \Twig_Node A Twig_Node instance
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        list ($file, $group, $priority, $attributes) = $this->parseArguments($token);

        $content = null;
        if (!$file) {
            $content = $this->parser->subparse([$this, 'decideBlockEnd'], true);
            $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        }

        return new TwigNodeStyle($content, $file, $group, $priority, $attributes, $lineno, $this->getTag());
    }

    /**
     * @param \Twig_Token $token
     * @return array
     */
    protected function parseArguments(\Twig_Token $token)
    {
        $stream = $this->parser->getStream();

        $file = null;
        if (!$stream->test(\Twig_Token::NAME_TYPE) && !$stream->test(\Twig_Token::OPERATOR_TYPE) && !$stream->test(\Twig_Token::BLOCK_END_TYPE)) {
            $file = $this->parser->getExpressionParser()->parseExpression();
        }

        $group = null;
        if ($stream->nextIf(\Twig_Token::OPERATOR_TYPE, 'in')) {
            $group = $this->parser->getExpressionParser()->parseExpression();
        }

        $priority = null;
        if ($stream->nextIf(\Twig_Token::NAME_TYPE, 'priority')) {
            $stream->expect(\Twig_Token::PUNCTUATION_TYPE, ':');
            $priority = $this->parser->getExpressionParser()->parseExpression();
        }

        $attributes = null;
        if ($stream->nextIf(\Twig_Token::NAME_TYPE, 'with')) {
            $attributes = $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return [$file, $group, $priority, $attributes];
    }

    /**
     * @param \Twig_Token $token
     * @return bool
     */
    public function decideBlockEnd(\Twig_Token $token)
    {
        return $token->test('endstyle');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'style';
    }
}
