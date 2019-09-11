<?php

namespace CodeMade\WuiBundle;

use CodeMade\WuiBundle\DependencyInjection\WuiExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class WuiBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new WuiExtension();
    }
}