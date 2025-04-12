<?php

namespace Prismaticoder\LaravelPromptManager\Exceptions;

class VersionNotFound extends \Exception
{
    public function __construct(string $version, array $availableVersions = [])
    {
        $message = "Version '{$version}' not found for the selected prompt.";

        if (! empty($availableVersions)) {
            $message .= " Available versions are: " . implode(', ', $availableVersions);
        }
        
        parent::__construct($message);
    }
} 