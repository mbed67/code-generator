<?php

namespace Mbed67\GeneratorBundle\Builder;

use Ablett\TwigFaker\TwigFakerExtension;
use Twig_Extension_Debug;
use TwigGenerator\Builder\BaseBuilder;

class EventBuilder extends BaseBuilder
{
    public function __construct()
    {
        parent::__construct();
        $this->twigExtensions = [
            Twig_Extension_Debug::class,
            TwigFakerExtension::class
        ];
    }
}
