<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\AuthService;
use CodeIgniter\Exceptions\PageNotFoundException;

class AuthController extends BaseController
{
    protected AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function registerForm()
    {
        return view('auth/register');
    }

    public function register()
    {
        $throttler = \Config\Services::throttler();

        // Allow 3 registrations per hour per IP
        // if ($throttler->check($this->request->getIPAddress(), 3, HOUR) === false) {
        //     return redirect()->back()
        //         ->with('error', 'Too many registration attempts. Please try again later.');
        // }

        $rules = [
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => [
                'rules' => 'required|min_length[6]|regex_match[/^(?=.*[A-Z])(?=.*\d).+$/]',
                'errors' => [
                    'regex_match' => 'Password must contain at least one uppercase letter and one number.'
                ]
            ],
            'password_confirm' => 'required|matches[password]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validation failed');
        }

        $token = $this->authService->register(
            $this->request->getPost('email'),
            $this->request->getPost('password')
        );

        // 2️⃣ SEND VERIFICATION EMAIL (THIS WAS MISSING)
        $email = \Config\Services::email();

        $email->setTo($this->request->getPost('email'));
        $email->setSubject('Verify your email');
        $email->setMessage(
            'Click to verify your account: ' . base_url('/verify-email/' . $token)
        );

        if (! $email->send()) {
            log_message('error', 'Email send failed: ' . print_r($email->printDebugger(['headers']), true));
        }

        return redirect()->to('/login')->with('success', 'Account created. Please login.');
    }


    // show login form
    public function loginForm()
    {
        return view('auth/login');
    }

    // handle login
    public function login()
    {
        log_message('debug', 'Login method hit');

        $throttler = \Config\Services::throttler();
        if ($throttler->check($this->request->getIPAddress(), 5, MINUTE) === false) {
            return redirect()->back()
                ->with('error', 'Too many login attempts. Please try again later.');
        }

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required'
        ];

        if (! $this->validate($rules)) {
            log_message('debug', 'Login validation failed');
            return redirect()->back()->withInput()
                ->with('error', 'Invalid email or password.');
        }

        // 🔥 CORRECT VARIABLE
        $result = $this->authService->attemptLogin(
            $this->request->getPost('email'),
            $this->request->getPost('password')
        );

        log_message('debug', 'Login result: ' . json_encode($result));

        if (! $result['success']) {
            return redirect()->back()
                ->with('error', $result['message']);
        }

        log_message('debug', 'Login success, redirecting');

        return redirect()->to('/boards');
    }


    public function logout()
    {
        $this->authService->logout();
        return redirect()->to('/login');
    }

    public function verifyEmail(string $token)
    {
        log_message('debug', 'Verify token: ' . $token);

        $user = $this->authService->getUserByVerificationToken($token);

        if (! $user) {
            throw new PageNotFoundException('Invalid or expired verification link.');
        }

        $this->authService->markEmailAsVerified($user['id']);

        return redirect()->to('/login')
            ->with('success', 'Email verified successfully. You can now login.');
    }

}
