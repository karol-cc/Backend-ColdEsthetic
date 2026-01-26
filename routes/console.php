<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('user:create {email} {password} {--first_name=} {--last_name=} {--cellphone=} {--name=}', function () {
    $email = (string) $this->argument('email');
    $password = (string) $this->argument('password');

    $firstName = (string) ($this->option('first_name') ?? '');
    $lastName = (string) ($this->option('last_name') ?? '');
    $cellphone = (string) ($this->option('cellphone') ?? '');
    $name = (string) ($this->option('name') ?? '');

    if ($firstName === '' || $lastName === '' || $cellphone === '') {
        $this->error('Missing required options: --first_name, --last_name, --cellphone');
        return self::FAILURE;
    }

    if ($name === '') {
        $name = trim($firstName.' '.$lastName);
    }

    $user = User::updateOrCreate(
        ['email' => $email],
        [
            'name' => $name,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'cellphone' => $cellphone,
            'password' => Hash::make($password),
        ]
    );

    $this->info("OK user_id={$user->id} email={$user->email}");

    return self::SUCCESS;
})->purpose('Create or update a user by email');
