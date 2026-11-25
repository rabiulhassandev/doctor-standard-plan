<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('doctor:owner {email} {name}')]
#[Description('Create a practice owner using a hidden password prompt')]
class ProvisionOwner extends Command
{
    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Run interactively to enter the password securely.');

            return self::FAILURE;
        }

        $data = [
            'email' => strtolower(trim($this->argument('email'))),
            'name' => $this->argument('name'),
            'password' => $this->secret('Password (at least 12 characters, mixed case and numbers)'),
            'password_confirmation' => $this->secret('Confirm password'),
        ];
        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $owner = new User;
        $owner->name = $data['name'];
        $owner->email = $data['email'];
        $owner->password = $data['password'];
        $owner->is_admin = true;
        $owner->save();
        $this->info('Owner created. Sign in at /admin/login.');

        return self::SUCCESS;
    }
}
