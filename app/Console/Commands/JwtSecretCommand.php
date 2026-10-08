<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('jwt:secret {--show : Only display the secret instead of writing it to .env} {--force : Overwrite an existing secret}')]
#[Description('Generate the HS256 signing secret (JWT_SECRET) for access tokens')]
class JwtSecretCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $secret = bin2hex(random_bytes(32));

        if ($this->option('show')) {
            $this->line($secret);

            return self::SUCCESS;
        }

        $path = $this->laravel->environmentFilePath();

        if (! file_exists($path)) {
            $this->error('.env file not found. Use --show and set JWT_SECRET yourself.');

            return self::FAILURE;
        }

        $contents = file_get_contents($path);

        if (preg_match('/^JWT_SECRET=.+$/m', $contents) && ! $this->option('force')) {
            $this->warn('JWT_SECRET is already set. Use --force to replace it (all issued tokens become invalid).');

            return self::SUCCESS;
        }

        $contents = preg_match('/^JWT_SECRET=.*$/m', $contents)
            ? preg_replace('/^JWT_SECRET=.*$/m', 'JWT_SECRET='.$secret, $contents)
            : rtrim($contents).PHP_EOL.PHP_EOL.'JWT_SECRET='.$secret.PHP_EOL;

        file_put_contents($path, $contents);

        $this->info('JWT_SECRET written to .env.');

        return self::SUCCESS;
    }
}
