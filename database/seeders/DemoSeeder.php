<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DoctorProfile;
use App\Models\MediaItem;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('Demo content may only be seeded outside production.');
        }

        if (DoctorProfile::query()->where('is_demo', false)->exists() || SiteSetting::query()->where('is_demo', false)->exists()) {
            throw new RuntimeException('Demo seeding cannot replace an existing practice identity.');
        }

        DB::transaction(function (): void {
            DoctorProfile::query()->firstOrCreate(['id' => 1], [
                'name' => ['en' => 'Dr. Sayra Rahman · Demo', 'bn' => 'ডা. সায়রা রহমান · ডেমো'],
                'designation' => ['en' => 'A personal approach to your wellbeing', 'bn' => 'আপনার সুস্থতায় আন্তরিক সেবা'],
                'specialty' => ['en' => 'General medicine', 'bn' => 'মেডিসিন'],
                'qualifications' => ['en' => 'Demo profile · verified credentials to be added', 'bn' => 'নমুনা প্রোফাইল · যাচাইকৃত যোগ্যতা যুক্ত করুন'],
                'introduction' => ['en' => 'Thoughtful care starts with a conversation. Discover the practice, explore health resources, and request a convenient visit.', 'bn' => 'আন্তরিক একটি কথোপকথন থেকেই যত্নের শুরু। চিকিৎসক ও চেম্বারের তথ্য জানুন, স্বাস্থ্যবিষয়ক লেখা পড়ুন এবং সাক্ষাতের অনুরোধ করুন।'],
                'biography' => ['en' => 'This demonstration shows how a doctor can introduce their approach to care. Replace this text with an approved professional biography before publishing.', 'bn' => 'এই নমুনায় একজন চিকিৎসকের পরিচয় ও চিকিৎসাদর্শন তুলে ধরার ধরন দেখানো হয়েছে। প্রকাশের আগে অনুমোদিত পেশাগত পরিচিতি দিয়ে লেখাটি পরিবর্তন করুন।'],
                'is_demo' => true,
            ]);

            SiteSetting::query()->firstOrCreate(['id' => 1], [
                'site_name' => ['en' => 'Sayra Rahman · Demo Practice', 'bn' => 'সায়রা রহমান · ডেমো চেম্বার'],
                'tagline' => ['en' => 'Care that begins with listening', 'bn' => 'আপনার কথা শুনেই যত্নের শুরু'],
                'footer_text' => ['en' => 'Demonstration website. Replace all example practice details before launch.', 'bn' => 'এটি একটি ডেমো ওয়েবসাইট। চালুর আগে চেম্বারের সব নমুনা তথ্য পরিবর্তন করুন।'],
                'seo_title' => ['en' => 'Doctor portfolio · Demo practice', 'bn' => 'চিকিৎসকের পরিচিতি · ডেমো চেম্বার'],
                'seo_description' => ['en' => 'Explore a bilingual doctor portfolio with chamber information, health resources, and appointment requests.', 'bn' => 'চিকিৎসকের পরিচিতি, চেম্বারের তথ্য, স্বাস্থ্যবিষয়ক লেখা ও সাক্ষাতের অনুরোধের দ্বিভাষিক ওয়েবসাইট।'],
                'appointment_notice' => ['en' => 'Your preferred date is a request. The chamber team will contact you to confirm arrangements. This form is not monitored for emergencies.', 'bn' => 'আপনার পছন্দের তারিখটি একটি অনুরোধ। সময় নিশ্চিত করতে চেম্বার থেকে যোগাযোগ করা হবে। জরুরি চিকিৎসার জন্য এই ফর্ম ব্যবহার করবেন না।'],
                'contact_notice' => ['en' => 'Please use this form for general inquiries. Avoid sharing medical reports or sensitive health information.', 'bn' => 'সাধারণ যোগাযোগের জন্য ফর্মটি ব্যবহার করুন। চিকিৎসার রিপোর্ট বা সংবেদনশীল স্বাস্থ্যতথ্য পাঠাবেন না।'],
                'privacy_text' => ['en' => 'Appointment requests and inquiries are available only to authorized practice staff. Contact the practice to ask about correction or deletion. The practice must approve its retention period and final privacy notice before launch.', 'bn' => 'সাক্ষাতের অনুরোধ ও যোগাযোগের তথ্য কেবল অনুমোদিত কর্মীরা দেখতে পারেন। তথ্য সংশোধন বা মুছে ফেলার অনুরোধে চেম্বারে যোগাযোগ করুন। চালুর আগে তথ্য সংরক্ষণের সময়সীমা ও চূড়ান্ত গোপনীয়তা নীতি অনুমোদন করতে হবে।'],
                'notification_email' => 'demo@example.com',
                'default_locale' => 'bn',
                'is_demo' => true,
            ]);

            $this->call(DemoContentSeeder::class);

            $mediaCategory = Category::query()->firstOrCreate(['kind' => 'media', 'slug' => 'health-discussions'], [
                'title' => ['en' => 'Health discussions', 'bn' => 'স্বাস্থ্য আলোচনা'], 'is_active' => true,
            ]);

            $media = MediaItem::query()->firstOrCreate(['slug' => 'replace-with-approved-health-video'], [
                'category_id' => $mediaCategory->id, 'kind' => 'video', 'provider' => 'youtube', 'provider_id' => 'aqz-KE-bpKQ', 'status' => 'draft',
            ]);
            $this->translate($media, 'en', 'Add an approved health video', 'Replace this draft with a video approved by the practice.', '', 'draft');
            $this->translate($media, 'bn', 'অনুমোদিত স্বাস্থ্য ভিডিও যুক্ত করুন', 'চেম্বার অনুমোদিত ভিডিও দিয়ে এই খসড়াটি পরিবর্তন করুন।', '', 'draft');
        });
    }

    private function translate(Model $model, string $locale, string $title, string $description, string $body, string $status = 'published'): void
    {
        $model->translations()->firstOrCreate(['locale' => $locale], [
            'status' => $status,
            'title' => $title,
            'description' => $description,
            'body' => $body,
            'seo_title' => $title,
            'seo_description' => $description,
        ]);
    }
}
