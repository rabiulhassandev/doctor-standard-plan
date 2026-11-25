<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentRequestForm extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('phone'))) {
            return;
        }

        $phone = strtr($this->input('phone'), array_combine(mb_str_split('০১২৩৪৫৬৭৮৯'), range(0, 9)));
        $phone = preg_replace('/[\s()\-]/u', '', $phone);
        if (preg_match('/^01[3-9][0-9]{8}$/', $phone)) {
            $phone = '+88'.$phone;
        }
        $this->merge(['phone' => $phone]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['bail', 'required', 'string', 'regex:/^\+[1-9][0-9]{7,14}$/'],
            'chamber_id' => ['nullable', 'integer', Rule::exists('chambers', 'id')->where('is_active', true)],
            'preferred_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Dhaka')->toDateString(), 'before_or_equal:'.now('Asia/Dhaka')->addMonths(6)->toDateString()],
            'preferred_period' => ['required', Rule::in(['no_preference', 'morning', 'afternoon', 'evening'])],
            'privacy' => ['accepted'], 'website' => ['nullable', 'string', 'max:0'],
            'submission_token' => ['required', 'uuid', Rule::in($this->session()->get('appointment_tokens', []))],
        ];
    }

    public function messages(): array
    {
        $bn = app()->getLocale() === 'bn';

        return [
            'name.required' => $bn ? 'আপনার নাম লিখুন।' : 'Please enter your name.',
            'phone.required' => $bn ? 'যোগাযোগের ফোন নম্বর লিখুন।' : 'Please enter a callback number.',
            'phone.regex' => $bn ? 'সঠিক মোবাইল নম্বর লিখুন (যেমন ০১৭১২৩৪৫৬৭৮)।' : 'Enter a valid mobile number, including the country code for international numbers.',
            'chamber_id.exists' => $bn ? 'চেম্বারটি এখন উপলব্ধ নয়। অন্য চেম্বার বেছে নিন।' : 'This chamber is no longer active. Please choose another.',
            'preferred_date.*' => $bn ? 'আজ থেকে আগামী ছয় মাসের মধ্যে একটি তারিখ বেছে নিন।' : 'Choose a date from today through the next six months.',
            'privacy.accepted' => $bn ? 'গোপনীয়তার তথ্য পড়ে সম্মতি দিন।' : 'Please acknowledge the privacy information.',
            'submission_token.*' => $bn ? 'ফর্মটি আবার খুলে চেষ্টা করুন।' : 'Please reopen the form and try again.',
            'website.*' => $bn ? 'ফর্মটি আবার পূরণ করুন।' : 'Please fill out the form again.',
        ];
    }
}
