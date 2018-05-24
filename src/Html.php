<?php

namespace ipl\Html;

use Exception;
use InvalidArgumentException;

class Html
{
    /** @var bool */
    protected static $showTraces = true;

    /**
     * Convert special characters to HTML5 entities using the UTF-8 character set for encoding
     *
     * This method internally uses {@link htmlspecialchars} with the following flags:
     * * Single quotes are not escaped (ENT_COMPAT)
     * * Uses HTML5 entities, disallowing &#013; (ENT_HTML5)
     * * Invalid characters are replaced with � (ENT_SUBSTITUTE)
     *
     * Already existing HTML entities will be encoded as well.
     *
     * @param   string  $content        The content to encode
     *
     * @return  string  The encoded content
     */
    public static function escape($content)
    {
        return htmlspecialchars($content, ENT_COMPAT | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * @deprecated Use {@link Html::encode()} instead
     */
    public static function escapeForHtml($content)
    {
        return static::escape($content);
    }

    /**
     * Create a HTML element from the given tag, attributes and content
     *
     * This method does not render the HTML element but creates a {@link HtmlElement}
     * instance from the given tag, attributes and content
     *
     * @param   string $name       The desired HTML tag name
     * @param   mixed  $attributes HTML attributes or content for the element
     * @param   mixed  $content    The content of the element if no attributes have been given
     *
     * @return  HtmlElement The created element
     */
    public static function tag($name, $attributes = null, $content = null)
    {
        if ($attributes instanceof ValidHtml || is_string($attributes)) {
            $content = $attributes;
            $attributes = null;
        } elseif (is_array($attributes)) {
            reset($attributes);
            if (is_int(key($attributes))) {
                $content = $attributes;
                $attributes = null;
            }
        }

        return new HtmlElement($name, $attributes, $content);
    }

    /**
     * @param $string
     * @return FormattedString
     */
    public static function sprintf($string)
    {
        $args = func_get_args();
        array_shift($args);

        return new FormattedString($string, $args);
    }

    /**
     * @param $any
     * @return ValidHtml
     * @throws InvalidArgumentException
     */
    public static function wantHtml($any)
    {
        if ($any instanceof ValidHtml) {
            return $any;
        } elseif (static::canBeRenderedAsString($any)) {
            return new Text($any);
        } elseif (is_array($any)) {
            $html = new HtmlDocument();
            foreach ($any as $el) {
                $html->add(static::wantHtml($el));
            }

            return $html;
        } else {
            // TODO: Should we add a dedicated Exception class?
            throw new InvalidArgumentException(sprintf(
                'String, Html Element or Array of such expected, got "%s"',
                Html::getPhpTypeName($any)
            ));
        }
    }

    public static function canBeRenderedAsString($any)
    {
        return is_string($any) || is_int($any) || is_null($any) || is_float($any);
    }

    /**
     * @param $any
     * @return string
     */
    public static function getPhpTypeName($any)
    {
        if (is_object($any)) {
            return get_class($any);
        } else {
            return gettype($any);
        }
    }

    /**
     * @param $name
     * @param $arguments
     * @return HtmlElement
     */
    public static function __callStatic($name, $arguments)
    {
        $attributes = array_shift($arguments);
        $content = array_shift($arguments);

        return static::tag($name, $attributes, $content);
    }

    /**
     * TODO: Allow to (statically) inject an error renderer. This will allow
     *       us to satisfy "Show exceptions" settings and/or preferences
     *
     * @param Exception|string $error
     * @return string
     */
    public static function renderError($error)
    {
        if ($error instanceof Exception) {
            $file = preg_split('/[\/\\\]/', $error->getFile(), -1, PREG_SPLIT_NO_EMPTY);
            $file = array_pop($file);
            $msg = sprintf(
                '%s (%s:%d)',
                $error->getMessage(),
                $file,
                $error->getLine()
            );
        } elseif (is_string($error)) {
            $msg = $error;
        } else {
            $msg = 'Got an invalid error'; // TODO: translate?
        }

        $output = sprintf(
            // TODO: translate? Be careful when doing so, it must be failsafe!
            "<div class=\"exception\">\n<h1><i class=\"icon-bug\">"
            . "</i>Oops, an error occurred!</h1>\n<pre>%s</pre>\n",
            static::escape($msg)
        );

        if (static::showTraces()) {
            $output .= sprintf(
                "<pre>%s</pre>\n",
                static::escape($error->getTraceAsString())
            );
        }
        $output .= "</div>\n";
        return $output;
    }

    /**
     * @param null $show
     * @return bool|null
     */
    public static function showTraces($show = null)
    {
        if ($show !== null) {
            self::$showTraces = $show;
        }

        return self::$showTraces;
    }
}
