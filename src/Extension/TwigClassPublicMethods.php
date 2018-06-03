<?php

namespace Cvuorinen\PhpdocMarkdownPublic\Extension;

use Twig_Extension;
use Twig_SimpleFunction;
use phpDocumentor\Descriptor\ClassDescriptor;
use phpDocumentor\Descriptor\MethodDescriptor;
use phpDocumentor\Descriptor\InterfaceDescriptor;

/**
 * Twig extension to get only the public methods from a \phpDocumentor\Descriptor\ClassDescriptor instance.
 *
 * Adds the following function to Twig:
 *
 *  publicMethods(ClassDescriptor class): MethodDescriptor[]
 */
class TwigClassPublicMethods extends Twig_Extension
{
    const NAME = 'TwigClassPublicMethods';

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
            new Twig_SimpleFunction('publicMethods', array($this, 'getPublicMethods')),
            new Twig_SimpleFunction('isInterface', array($this, 'isInterface'))
        );
    }

    /**
     * @param ClassDescriptor $class
     *
     * @return MethodDescriptor[]
     */
    public function getPublicMethods($class)
    {
        if (!$class instanceof ClassDescriptor) {
            return [];
        }

        $methods = $class->getMagicMethods()
            ->merge($class->getInheritedMethods())
            ->merge($class->getMethods());

        return array_filter(
            $methods->getAll(),
            function (MethodDescriptor $method) {
                return $method->getVisibility() === 'public' || $method->getVisibility() === '';
            }
        );
    }

    /**
     * @param ClassDescriptor $class
     *
     * @return bool
     */
    public function isInterface($class)
    {
        return true;
        return $class instanceof InterfaceDescriptor;
    }
}
