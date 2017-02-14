<?php

namespace WP\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class WPUserBundle extends Bundle
{
    public function getParent() {
        return 'FOSUserBundle';
    }
}
