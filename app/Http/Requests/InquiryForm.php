<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InquiryForm extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'reply_to' => ['bail', 'required', 'string', 'max:255', function (string $attribute, mixed $value, \Closure $fail): void {
                $normalized = strtr($value, array_combine(mb_str_split('০১২৩৪৫৬৭৮৯'), range(0, 9)));
                if (! filter_var($value, FILTER_VALIDATE_EMAIL) && ! preg_match('/^\+?[0-9 ()-]{8,20}$/', $normalized)) {
                    $fail(app()->getLocale() === 'bn' ? 'সঠিক ফোন নম্বর বা ইমেইল লিখুন।' : 'Enter a valid phone number or email address.');
                }
            }],
            'message' => ['required', 'string', 'max:1500'],
            'privacy' => ['accepted'], 'website' => ['nullable', 'string', 'max:0'],
            'submission_token' => ['required', 'uuid', Rule::in($this->session()->get('inquiry_tokens', []))],
        ];
    }

    public function messages(): array
    {
        return app()->getLocale() === 'bn' ? [
            'required' => 'এই তথ্যটি লিখুন।', 'max' => 'লেখাটি সংক্ষিপ্ত করুন।',
            'privacy.accepted' => 'গোপনীয়তার তথ্য পড়ে সম্মতি দিন।',
            'submission_token.*' => 'ফর্মটি আবার খুলে চেষ্টা করুন।',
        ] : [];
    }
}
