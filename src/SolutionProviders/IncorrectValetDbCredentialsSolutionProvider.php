<?php

namespace Facade\Ignition\SolutionProviders;

use Illuminate\Database\QueryException;
use Facade\Ignition\Solutions\GenerateAppKeySolution;
use Facade\Ignition\Solutions\UseDefaultValetDbCredentialsSolution;
use Facade\IgnitionContracts\HasSolutionsForThrowable;
use Facade\IgnitionContracts\Solution;
use Throwable;

class IncorrectValetDbCredentialsSolutionProvider implements HasSolutionsForThrowable
{
    const MYSQL_ACCESS_DENIED_CODE = 1045;

    public function canSolve(Throwable $throwable): bool
    {
        if (! PHP_OS === 'Darwin') {
            return false;
        }

        if (! $throwable instanceof QueryException) {
            return false;
        }

        if (! $this->isAccessDeniedCode($throwable->getCode())) {
            return false;
        }

        if (! $this->envFileExists()) {
            return false;
        }

        if (! $this->isValetInstalled()) {
            return false;
        };

        if ($this->usingCorrectDefaultCredentials()) {
            return false;
        }

        return true;
    }

    public function getSolutions(Throwable $throwable): array
    {
        return [new UseDefaultValetDbCredentialsSolution()];
    }

    protected function envFileExists(): bool
    {
        return file_exists(base_path('.env'));
    }

    protected function isAccessDeniedCode($code): bool
    {
        return $code === static::MYSQL_ACCESS_DENIED_CODE;
    }

    protected function isValetInstalled(): bool
    {
        return file_exists('/usr/local/bin/valet');
    }

    protected function usingCorrectDefaultCredentials(): bool
    {
        return env('DB_USERNAME') === 'root' && env('DB_PASSWORD') === '';
    }
}