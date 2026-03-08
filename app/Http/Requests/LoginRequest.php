<?php

namespace App\Http\Requests;

use App\Models\LoginLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
        
        if (config('services.recaptcha.site_key') && config('services.recaptcha.secret_key')) {
            $rules['g-recaptcha-response'] = ['required', 'string'];
        }
        
        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'username.required' => 'Please enter your username.',
            'password.required' => 'Please enter your password.',
        ];
        
        if (config('services.recaptcha.site_key') && config('services.recaptcha.secret_key')) {
            $messages['g-recaptcha-response.required'] = 'reCAPTCHA verification failed. Please try again.';
        }
        
        return $messages;
    }

    public function authenticate(): void
    {
        // Verify reCAPTCHA token (only if both keys are configured)
        $recaptchaSecret = config('services.recaptcha.secret_key');
        $recaptchaSiteKey = config('services.recaptcha.site_key');
        
        if ($recaptchaSecret && $recaptchaSiteKey) {
            $token = $this->input('g-recaptcha-response');
            
            if ($token) {
                try {
                    $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                        'secret' => $recaptchaSecret,
                        'response' => $token,
                        'remoteip' => $this->ip(),
                    ]);

                    $recaptchaData = $response->json();

                    $isSuccess = (bool) ($recaptchaData['success'] ?? false);
                    $score = $recaptchaData['score'] ?? null;
                    $action = (string) ($recaptchaData['action'] ?? '');
                    $errorCodes = $recaptchaData['error-codes'] ?? null;

                    // v2: only `success` is guaranteed (no `score` / `action`)
                    // v3: validate action + score
                    $isV3 = $score !== null;
                    $scoreValue = $isV3 ? (float) $score : null;

                    if (! $isSuccess || ($isV3 && ($action !== 'login' || ($scoreValue ?? 0) < 0.5))) {
                        LoginLog::query()->create([
                            'username' => $this->input('username'),
                            'status' => 'Failed',
                            'failure_reason' => 'reCAPTCHA verification failed'.($errorCodes ? ' (' . json_encode($errorCodes) . ')' : ''),
                        ]);

                        throw ValidationException::withMessages([
                            'username' => 'reCAPTCHA verification failed. Please try again.',
                        ]);
                    }
                } catch (\Exception $e) {
                    LoginLog::query()->create([
                        'username' => $this->input('username'),
                        'status' => 'Failed',
                        'failure_reason' => 'reCAPTCHA error: ' . $e->getMessage(),
                    ]);

                    throw ValidationException::withMessages([
                        'username' => 'Security verification error. Please try again.',
                    ]);
                }
            }
        }

        // Proceed with standard authentication
        if (! Auth::attempt($this->only('username', 'password'), $this->boolean('remember'))) {
            LoginLog::query()->create([
                'username' => $this->input('username'),
                'status' => 'Failed',
                'failure_reason' => 'Invalid credentials',
            ]);

            throw ValidationException::withMessages([
                'username' => __('These credentials do not match our records.'),
            ]);
        }
    }
}
