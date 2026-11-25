<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Chamber;
use App\Models\GalleryItem;
use App\Models\HomeSlide;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        DB::transaction(function (): void {
            $this->seedChamber();
            $this->seedServicesAndArticles();
            $this->seedGalleryAndSlider();
        });
    }

    private function seedChamber(): void
    {
        if (Chamber::query()->where('slug', '!=', 'demo-dhaka-chamber')->exists()) {
            return;
        }

        $chamber = Chamber::query()->firstOrCreate(['slug' => 'demo-dhaka-chamber'], [
            'name' => ['en' => 'Dhaka chamber · Example', 'bn' => 'ঢাকা চেম্বার · নমুনা'],
            'hospital_name' => ['en' => 'Example medical centre', 'bn' => 'নমুনা মেডিকেল সেন্টার'],
            'address' => ['en' => 'Dhaka, Bangladesh · replace with the actual chamber address', 'bn' => 'ঢাকা, বাংলাদেশ · চেম্বারের প্রকৃত ঠিকানা যুক্ত করুন'],
            'notice' => ['en' => 'Example visiting hours. Contact details must be configured before accepting requests.', 'bn' => 'এটি নমুনা সময়সূচি। অনুরোধ নেওয়ার আগে যোগাযোগের তথ্য যুক্ত করুন।'],
            'is_active' => true,
        ]);

        foreach ([0, 2, 4] as $weekday) {
            $chamber->visitingWindows()->firstOrCreate(['weekday' => $weekday, 'starts_at' => '17:00:00'], ['ends_at' => '20:00:00', 'is_active' => true]);
        }
    }

    private function seedServicesAndArticles(): void
    {
        $articleCategory = Category::query()->firstOrCreate(['kind' => 'article', 'slug' => 'practice-guides'], [
            'title' => ['en' => 'Practice guides', 'bn' => 'রোগীর সহায়িকা'], 'is_active' => true,
        ]);

        $services = [
            ['general-consultation', 'General consultation', 'সাধারণ পরামর্শ', 'Talk with the doctor about your concerns and the next appropriate steps.', 'আপনার সমস্যার কথা চিকিৎসককে জানান এবং পরবর্তী করণীয় নিয়ে আলোচনা করুন।'],
            ['follow-up-visit', 'Follow-up visits', 'ফলোআপ পরামর্শ', 'Bring previous prescriptions and relevant records to discuss your ongoing care.', 'চলমান চিকিৎসা নিয়ে আলোচনার জন্য আগের ব্যবস্থাপত্র ও প্রয়োজনীয় নথি সঙ্গে আনুন।'],
            ['preventive-care', 'Preventive care', 'প্রতিরোধমূলক যত্ন', 'Discuss individual health needs and appropriate preventive care during a visit.', 'সাক্ষাতের সময় আপনার প্রয়োজন অনুযায়ী প্রতিরোধমূলক যত্ন নিয়ে আলোচনা করুন।'],
            ['health-education', 'Health education', 'স্বাস্থ্য শিক্ষা', 'Clear explanations to help you prepare questions for your consultation.', 'পরামর্শের আগে আপনার প্রশ্নগুলো গুছিয়ে নিতে সহজ ভাষায় স্বাস্থ্যবিষয়ক তথ্য।'],
            ['demo-medication-review', 'Medication review', 'ওষুধের তালিকা পর্যালোচনা', 'Bring your current medication list and discuss any questions about your prescriptions during a consultation.', 'বর্তমান ওষুধের তালিকা সঙ্গে আনুন এবং সাক্ষাতে ব্যবস্থাপত্র সম্পর্কে আপনার প্রশ্নগুলো আলোচনা করুন।'],
            ['demo-routine-checkup', 'Routine health checkup', 'নিয়মিত স্বাস্থ্য পরীক্ষা', 'Discuss your health history, concerns, and whether any routine checks are appropriate for you.', 'আপনার স্বাস্থ্য ইতিহাস ও উদ্বেগ জানিয়ে কোন নিয়মিত পরীক্ষা আপনার জন্য প্রয়োজন হতে পারে, তা নিয়ে আলোচনা করুন।'],
        ];

        foreach ($services as $index => [$slug, $englishTitle, $banglaTitle, $englishText, $banglaText]) {
            $service = Service::query()->firstOrCreate(['slug' => $slug], ['status' => 'published', 'featured' => $index < 3, 'sort_order' => $index]);
            $this->translate($service, 'en', $englishTitle, $englishText, $englishText);
            $this->translate($service, 'bn', $banglaTitle, $banglaText, $banglaText);
        }

        $articles = [
            ['prepare-for-your-visit', 'Preparing for your visit', 'চিকিৎসকের সঙ্গে সাক্ষাতের প্রস্তুতি', 'A little preparation helps you make the most of your consultation.', 'সামান্য প্রস্তুতি চিকিৎসকের সঙ্গে আপনার আলোচনাকে আরও ফলপ্রসূ করতে পারে।', 'Write down the questions you would like to discuss. Bring relevant previous prescriptions and records. Contact the chamber for its current visiting arrangements.\n\nThis article is replaceable demo content and should be reviewed by the doctor before launch.', 'আপনি যে প্রশ্নগুলো আলোচনা করতে চান সেগুলো লিখে রাখুন। প্রয়োজনীয় আগের ব্যবস্থাপত্র ও নথি সঙ্গে আনুন। চেম্বারের বর্তমান সময়সূচি জানতে যোগাযোগ করুন।\n\nএটি পরিবর্তনযোগ্য ডেমো লেখা। ওয়েবসাইট চালুর আগে চিকিৎসকের অনুমোদন নিতে হবে।'],
            ['understand-appointment-requests', 'How appointment requests work', 'সাক্ষাতের অনুরোধ যেভাবে কাজ করে', 'Choose a chamber and tell the team when you would prefer to visit.', 'চেম্বার বেছে নিয়ে আপনার পছন্দের সাক্ষাতের দিন জানান।', 'Submitting a request helps the chamber team contact you. It does not reserve a time or serial number. Staff will confirm the arrangements directly. For urgent concerns, seek appropriate immediate care.\n\nThis is an example practice guide.', 'অনুরোধ পাঠালে চেম্বারের কর্মীরা আপনার সঙ্গে যোগাযোগ করতে পারবেন। এতে কোনো নির্দিষ্ট সময় বা সিরিয়াল সংরক্ষিত হয় না। কর্মীরা সরাসরি সময় নিশ্চিত করবেন। জরুরি প্রয়োজনে দ্রুত উপযুক্ত চিকিৎসা নিন।\n\nএটি রোগীর সহায়িকার একটি নমুনা।'],
            ['your-health-questions', 'Make space for your health questions', 'স্বাস্থ্য নিয়ে আপনার প্রশ্নগুলো করুন', 'A consultation is an opportunity to understand the plan for your care.', 'সাক্ষাতের সময় আপনার চিকিৎসার পরিকল্পনা ভালোভাবে বুঝে নিন।', 'Ask about anything you do not understand during your consultation. Keep a note of the instructions provided by your doctor and clarify how to arrange a follow-up.\n\nThis educational demo text is not individual medical advice.', 'পরামর্শের সময় কোনো বিষয় বুঝতে অসুবিধা হলে প্রশ্ন করুন। চিকিৎসকের দেওয়া নির্দেশনা লিখে রাখুন এবং ফলোআপের ব্যবস্থা কীভাবে করবেন তা জেনে নিন।\n\nএটি শিক্ষামূলক ডেমো লেখা, ব্যক্তিগত চিকিৎসা পরামর্শ নয়।'],
        ];

        foreach ($articles as $index => [$slug, $englishTitle, $banglaTitle, $englishText, $banglaText, $englishBody, $banglaBody]) {
            $article = Article::query()->firstOrCreate(['slug' => $slug], ['category_id' => $articleCategory->id, 'status' => 'published', 'featured' => true, 'sort_order' => $index, 'published_at' => now()]);
            $this->translate($article, 'en', $englishTitle, $englishText, str_replace('\\n', "\n", $englishBody));
            $this->translate($article, 'bn', $banglaTitle, $banglaText, str_replace('\\n', "\n", $banglaBody));
        }

        $recordsCategory = Category::query()->firstOrCreate(['kind' => 'article', 'slug' => 'visit-planning'], [
            'title' => ['en' => 'Visit planning', 'bn' => 'সাক্ষাতের পরিকল্পনা'],
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $articles = [
            ['demo-organize-visit-records', 'Organizing records for your visit', 'সাক্ষাতের জন্য নথি গুছিয়ে রাখুন', 'Keep the most relevant documents together before a consultation.', 'সাক্ষাতের আগে প্রয়োজনীয় নথিগুলো একসঙ্গে রাখুন।', 'Put recent prescriptions and relevant test results in one folder. Write the date on any paper without a clear date and bring a list of your questions.', 'সাম্প্রতিক ব্যবস্থাপত্র ও প্রয়োজনীয় পরীক্ষার ফল এক ফোল্ডারে রাখুন। কোনো কাগজে তারিখ স্পষ্ট না থাকলে লিখে রাখুন এবং প্রশ্নের তালিকা সঙ্গে আনুন।'],
            ['demo-plan-a-follow-up', 'Planning your next follow-up', 'পরবর্তী ফলোআপের পরিকল্পনা', 'Write down the next steps before leaving the chamber.', 'চেম্বার ছাড়ার আগে পরবর্তী করণীয় লিখে রাখুন।', 'Ask when to return and which records to bring. Check whether you need to call the chamber in advance to arrange the next visit.', 'কখন আবার আসবেন এবং কোন নথি আনবেন, তা জেনে নিন। পরের সাক্ষাতের জন্য আগে চেম্বারে ফোন করতে হবে কি না, সেটিও নিশ্চিত করুন।'],
        ];
        foreach ($articles as $index => [$slug, $englishTitle, $banglaTitle, $englishText, $banglaText, $englishBody, $banglaBody]) {
            $article = Article::query()->firstOrCreate(['slug' => $slug], ['category_id' => $recordsCategory->id, 'status' => 'published', 'sort_order' => $index, 'published_at' => now()]);
            $this->translate($article, 'en', $englishTitle, $englishText, $englishBody);
            $this->translate($article, 'bn', $banglaTitle, $banglaText, $banglaBody);
        }
    }

    private function seedGalleryAndSlider(): void
    {
        $galleryPath = 'media/demo-gallery-reception.webp';
        $slidePath = 'media/demo-slide-reception.webp';
        $videoPath = 'slides/demo-reception.mp4';
        $posterPath = 'media/demo-slide-video-cover.webp';
        $disk = Storage::disk('local');

        foreach ([$galleryPath => 'clinic-reception.jpg', $slidePath => 'clinic-reception.jpg', $posterPath => 'reception-poster.jpg'] as $path => $filename) {
            if ($disk->missing($path)) {
                $image = imagecreatefromjpeg(public_path('images/practice-demo/'.$filename));
                if ($image === false) {
                    throw new RuntimeException('Could not load the demo image.');
                }
                ob_start();
                imagewebp($image, null, 85);
                if (! $disk->put($path, (string) ob_get_clean())) {
                    throw new RuntimeException('Could not store the demo image.');
                }
            }
        }
        if ($disk->missing($videoPath) && ! $disk->putFileAs('slides', new File(public_path('images/practice-demo/reception-video.mp4')), 'demo-reception.mp4')) {
            throw new RuntimeException('Could not store the demo video.');
        }

        $galleryItem = GalleryItem::query()->firstOrCreate(['slug' => 'demo-clinic-reception'], [
            'category' => 'practice', 'image_path' => $galleryPath, 'status' => 'published', 'featured' => true,
        ]);
        $this->translate($galleryItem, 'en', 'Clinic reception preview', 'Stock photograph showing a clinic reception.', '');
        $this->translate($galleryItem, 'bn', 'চেম্বারের রিসেপশনের নমুনা', 'চেম্বারের রিসেপশন দেখানো একটি নমুনা ছবি।', '');

        HomeSlide::query()->firstOrCreate(['file_path' => $slidePath], [
            'title' => 'Demo clinic reception photo', 'kind' => 'image', 'is_active' => false, 'sort_order' => 1,
            'alt_text' => ['en' => 'Stock photograph of a clinic reception', 'bn' => 'চেম্বারের রিসেপশনের নমুনা ছবি'],
        ]);
        HomeSlide::query()->firstOrCreate(['file_path' => $videoPath], [
            'title' => 'Demo clinic reception video', 'kind' => 'video', 'poster_path' => $posterPath, 'is_active' => false, 'sort_order' => 2,
            'alt_text' => ['en' => 'Stock video of a clinic reception', 'bn' => 'চেম্বারের রিসেপশনের নমুনা ভিডিও'],
        ]);
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
            'alt_text' => $model instanceof GalleryItem ? $title : null,
        ]);
    }
}
