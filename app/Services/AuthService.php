<?php

namespace App\Services;

use App\Models\UserModel;
use App\Services\ActivityLogService;

class AuthService
{
    protected UserModel $userModel;
    protected ActivityLogService $logService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->logService = new ActivityLogService();
    }

    public function register(string $email, string $password)
    {
        $token = bin2hex(random_bytes(32));

        $this->userModel->insert([
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'email_verification_token' => $token
        ]);

        log_message('debug', 'DB token lookup: ' . $token);

        return $token;
    }


    public function attemptLogin(string $email, string $password): array
    {
        $user = $this->userModel
            ->where('email', $email)
            ->first();

        if (! $user) {
            return [
                'success' => false,
                'message' => 'Invalid email or password.'
            ];
        }

        if (! password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Invalid email or password.'
            ];
        }

        if (! $user['email_verified_at']) {
            return [
                'success' => false,
                'message' => 'Please verify your email before logging in.'
            ];
        }

        session()->set([
            'user_id'   => $user['id'],
            'email'     => $user['email'],
            'logged_in' => true
        ]);

        log_message('debug', 'Session user_id: ' . session()->get('user_id'));

        return [
            'success' => true
        ];
    }



    public function logout()
    {
        $this->logService->log(
        'logout',
        'user',
        session()->get('user_id'),
        'User logged out'
        );

        session()->destroy();
    }

    public function getUserByVerificationToken(string $token): ?array
    {
        log_message('debug', 'DB token lookup: ' . $token);

        return $this->userModel
            ->where('email_verification_token', $token)
            ->first();
    }

    public function markEmailAsVerified(int $userId): void
    {
        $this->userModel->update($userId, [
            'email_verified_at' => date('Y-m-d H:i:s'),
            'email_verification_token' => null
        ]);
    }

}
