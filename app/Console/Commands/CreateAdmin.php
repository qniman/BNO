<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    protected $signature = 'bno:admin {email} {--name=Регистратор} {--password= : Если не указан — будет сгенерирован}';

    protected $description = 'Создать администратора или сменить ему пароль';

    public function handle(): int
    {
        $email = $this->argument('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Некорректная почта.');

            return self::FAILURE;
        }

        $password = $this->option('password') ?: Str::password(16, symbols: false);

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $this->option('name'), 'password' => Hash::make($password), 'role' => 'admin'],
        );

        $this->info(($user->wasRecentlyCreated ? 'Администратор создан' : 'Пароль обновлён').": {$email}");
        if (! $this->option('password')) {
            $this->line("Пароль: {$password}");
            $this->warn('Сохраните его — повторно он показан не будет.');
        }

        return self::SUCCESS;
    }
}
