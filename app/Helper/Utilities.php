<?php

namespace App\Helper;

class Utilities
{
    /**
     * Search term for a "like" query: % and _ in the input are searched literally
     * instead of acting as wildcards, surrounding whitespace is ignored.
     */
    public static function like($value): string
    {
        return '%' . addcslashes(trim((string) $value), '%_\\') . '%';
    }

    /**
     * Natural sort ("Room 2" before "Room 10") of the current page of a paginator.
     */
    public static function sortPageNaturally($paginator, string $attribute)
    {
        $paginator->setCollection($paginator->getCollection()->sort(function ($a, $b) use ($attribute) {
            return strnatcasecmp((string) $a[$attribute], (string) $b[$attribute]);
        })->values());

        return $paginator;
    }

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
