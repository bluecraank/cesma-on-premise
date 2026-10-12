<?php

namespace App\Helper;

class Utilities
{
    public static function CheckSSHCommand($command)
    {
        if (!is_string($command) || trim($command) === '') {
            return false;
        }

        // Only a single command line is allowed, otherwise further commands could be chained
        if (preg_match('/[\r\n\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $command)) {
            return false;
        }

        // Normalize case and whitespace so "SH  RU" or "no   ip" are caught as well
        $normalized = strtolower(preg_replace('/\s+/', ' ', trim($command)));

        $blacklisted = array(
            '/\bsh(o|ow)? ru/',             // show running-config (and abbreviations)
            '/\baaa\b/',
            '/\bno ip\b/',                  // includes "no ip ssh"
            '/\bno rest-interface\b/',
        );

        foreach ($blacklisted as $blacklistedCommand) {
            if (preg_match($blacklistedCommand, $normalized)) {
                return false;
            }
        }

        return true;
    }
}
