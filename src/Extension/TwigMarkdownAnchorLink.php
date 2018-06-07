<?php

namespace Cvuorinen\PhpdocMarkdownPublic\Extension;

use Twig_Extension;
use Twig\TwigFilter;
use Twig_SimpleFunction;
use phpDocumentor\Descriptor\DescriptorAbstract;

/**
 * Twig extension to create Markdown anchor links (within a single page).
 *
 * Links need to be created in the same order as the anchors appear in the document, so that links with
 * same title will be correctly suffixed with a numeric index.
 *
 * Adds the following function:
 *
 *  anchorLink(string title): string
 */
class TwigMarkdownAnchorLink extends Twig_Extension
{
    const NAME = 'TwigMarkdownAnchorLink';

    /**
     * Keep track of the created links so we can check if a link with the same title already exists.
     *
     * @var array
     */
    private static $links = array();

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('anchorLink', array($this, 'createAnchorLink')),
            new Twig_SimpleFunction('routeToMethod', array($this, 'routeToMethod')),
            new Twig_SimpleFunction('routeToClass', array($this, 'routeToClass')),
            new Twig_SimpleFunction('routeTo', array($this, 'routeTo')),
        );
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('className', array($this, 'classNameFilter')),
        );
    }

    /**
     * @param string $title
     *
     * @return string
     */
    public function createAnchorLink($title)
    {
        $anchor = strtolower($title);

        // Check if we already have link to an anchor with the same name, and add count suffix
        $linkCounts = array_count_values(self::$links);
        $anchorSuffix = array_key_exists($anchor, $linkCounts)
            ? '-' . $linkCounts[$anchor] : '';
        array_push(self::$links, $anchor);

        return sprintf("[%s](%s)", $title, '#' . $anchor . $anchorSuffix);
    }

    public function routeToMethod(DescriptorAbstract $class, DescriptorAbstract $method)
    {
        $path = str_replace("\\", "/",$class->getFullyQualifiedStructuralElementName());
        $anchor = strtolower($method->getName());

        return sprintf('[%s]({{< ref "%s" >}})', $method->getName(), 'api' . $path . '/index.md#' . $anchor);
    }

    public function routeToClass(DescriptorAbstract $class)
    {
        $path = str_replace("\\", "/",$class->getFullyQualifiedStructuralElementName());

        return sprintf('[%s]({{< ref "%s" >}})', $class->getName(), 'api' . $path . '/index.md');
    }

    public function routeTo($path)
    {
        $name = substr(strrchr($path, "\\"), 1);
        $anchor = substr(strrchr($path, ":"), 1);
        $path = str_replace("\\", "/", strchr($path, ':', true));

        return sprintf('[%s]({{< ref "%s" >}})', $name, 'api' . $path . '/index.md#' .$anchor );
    }

    public function classNameFilter($value, $chr = "\\")
    {
        if (is_array($value)) {
            return array_map(function($item) {
                return substr(strrchr(strval($item), $chr), 1);
            }, $value);
        } else {
            return substr(strrchr($value, $chr), 1);
        }
    }
}
