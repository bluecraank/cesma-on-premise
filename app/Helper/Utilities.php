<?php

namespace App\Helper;

class Utilities
{
    public static function CheckSSHCommand($command)
    {
        $blacklisted = array('sh ru', 'aaa', 'no ip', 'no rest-interface', 'no ip ssh');
        foreach ($blacklisted as $blacklistedCommand) {
            if (str_contains($command, $blacklistedCommand)) {
                return false;
            }
        }

        return true;
    }
}
