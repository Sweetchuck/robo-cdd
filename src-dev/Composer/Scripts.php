<?php

namespace Sweetchuck\Robo\cdd\Composer;

use Sweetchuck\GitHooks\Composer\Scripts as GitHooks;
use Composer\Script\Event;

class Scripts
{
    public static function postInstallCmd(Event $event): bool
    {
        GitHooks::deploy($event);

        return true;
    }

    public static function postUpdateCmd(Event $event): bool
    {
        GitHooks::deploy($event);

        return true;
    }
}
