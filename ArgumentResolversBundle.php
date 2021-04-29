<?php

namespace Prokl\ArgumentResolversBundle;

use Prokl\ArgumentResolversBundle\DependencyInjection\ArgumentResolversExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ArgumentResolversBundle
 * @package Prokl\ArgumentResolversBundle
 *
 * @since 29.04.2021
 */
class ArgumentResolversBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new ArgumentResolversExtension();
        }

        return $this->extension;
    }
}
