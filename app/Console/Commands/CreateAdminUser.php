<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

#[Signature('admin:create {email} {--name=Admin} {--password= : Leave empty to be prompted}')]
#[Description('Create an admin panel user, or reset the password of an existing one')]
class CreateAdminUser extends Command
{
    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $password = (string) ($this->option('password') ?: $this->secret('Password (min. 8 characters)'));

        $validator = Validator::make(compact('email', 'password'), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::updateOrCreate(['email' => $email], [
            'name' => (string) $this->option('name'),
            'password' => $password,
        ]);

        $this->info("Admin {$email} is ready. Sign in at ".route('admin.login'));

        return self::SUCCESS;
    }
}
