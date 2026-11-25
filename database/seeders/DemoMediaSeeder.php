<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MediaItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoMediaSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        DB::transaction(function (): void {
            foreach ($this->categories() as $categoryIndex => $definition) {
                $category = Category::query()->firstOrCreate([
                    'kind' => 'media',
                    'slug' => $definition['slug'],
                ], [
                    'title' => $definition['title'],
                    'is_active' => true,
                    'sort_order' => $categoryIndex,
                ]);

                foreach ($definition['items'] as $itemIndex => $item) {
                    $media = MediaItem::query()->firstOrCreate(['slug' => $item['slug']], [
                        'category_id' => $category->id,
                        'kind' => $item['kind'],
                        'provider' => 'youtube',
                        'provider_id' => $item['youtube_id'],
                        'status' => 'published',
                        'featured' => $item['featured'] ?? false,
                        'sort_order' => $itemIndex,
                        'published_at' => now(),
                    ]);

                    foreach ($item['translations'] as $locale => $translation) {
                        $media->translations()->firstOrCreate(['locale' => $locale], [
                            'status' => 'published',
                            'title' => $translation['title'],
                            'description' => $translation['description'],
                            'seo_title' => $translation['title'],
                            'seo_description' => $translation['description'],
                        ]);
                    }
                }
            }
        });
    }

    /**
     * @return list<array{
     *     slug: string,
     *     title: array{en: string, bn: string},
     *     items: list<array{
     *         slug: string,
     *         kind: 'video'|'reel',
     *         youtube_id: string,
     *         featured?: bool,
     *         translations: array<string, array{title: string, description: string}>
     *     }>
     * }>
     */
    private function categories(): array
    {
        return [
            [
                'slug' => 'health-discussions',
                'title' => ['en' => 'Health discussions', 'bn' => 'স্বাস্থ্য আলোচনা'],
                'items' => [
                    [
                        'slug' => 'demo-understanding-high-blood-pressure',
                        'kind' => 'video',
                        'youtube_id' => 'RIXWEMQpP9Q',
                        'featured' => true,
                        'translations' => [
                            'en' => ['title' => 'Understanding high blood pressure', 'description' => 'An expert explains hypertension in the World Health Organization’s Science in 5 series.'],
                            'bn' => ['title' => 'উচ্চ রক্তচাপ সম্পর্কে জানুন', 'description' => 'উচ্চ রক্তচাপ নিয়ে বিশ্ব স্বাস্থ্য সংস্থার সায়েন্স ইন ফাইভ সিরিজের ইংরেজি আলোচনা।'],
                        ],
                    ],
                    [
                        'slug' => 'demo-blood-pressure-in-brief',
                        'kind' => 'reel',
                        'youtube_id' => '48OolwRxRyQ',
                        'translations' => [
                            'en' => ['title' => 'Blood pressure in brief', 'description' => 'Cleveland Clinic explains what blood pressure measures and why the reading can change.'],
                            'bn' => ['title' => 'সংক্ষেপে রক্তচাপ', 'description' => 'রক্তচাপের মাপ কী বোঝায় এবং কেন তা বদলায়, তা নিয়ে ক্লিভল্যান্ড ক্লিনিকের ইংরেজি শর্ট।'],
                        ],
                    ],
                    [
                        'slug' => 'demo-understanding-dementia',
                        'kind' => 'video',
                        'youtube_id' => 'oym8gSySu-A',
                        'translations' => [
                            'en' => ['title' => 'Understanding dementia', 'description' => 'An episode of the World Health Organization’s Science in 5 about dementia.'],
                            'bn' => ['title' => 'ডিমেনশিয়া সম্পর্কে জানুন', 'description' => 'ডিমেনশিয়া নিয়ে বিশ্ব স্বাস্থ্য সংস্থার সায়েন্স ইন ফাইভ সিরিজের ইংরেজি পর্ব।'],
                        ],
                    ],
                    [
                        'slug' => 'demo-air-pollution-and-health',
                        'kind' => 'video',
                        'youtube_id' => '8zktuV75u4U',
                        'translations' => [
                            'en' => ['title' => 'Air pollution and public health', 'description' => 'The World Health Organization explains why air pollution is a public health concern.'],
                            'bn' => ['title' => 'বায়ুদূষণ ও জনস্বাস্থ্য', 'description' => 'বায়ুদূষণ কেন জনস্বাস্থ্যের জন্য গুরুত্বপূর্ণ, তা নিয়ে বিশ্ব স্বাস্থ্য সংস্থার ইংরেজি আলোচনা।'],
                        ],
                    ],
                    [
                        'slug' => 'demo-caffeine-in-brief',
                        'kind' => 'reel',
                        'youtube_id' => 'X_8Xw_Plvt4',
                        'translations' => [
                            'en' => ['title' => 'Caffeine in brief', 'description' => 'A Cleveland Clinic short about caffeine’s effects and cutting back.'],
                            'bn' => ['title' => 'সংক্ষেপে ক্যাফেইন', 'description' => 'ক্যাফেইনের প্রভাব ও গ্রহণের পরিমাণ কমানো নিয়ে ক্লিভল্যান্ড ক্লিনিকের ইংরেজি শর্ট।'],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'patient-stories',
                'title' => ['en' => 'Patient stories', 'bn' => 'রোগীর অভিজ্ঞতা'],
                'items' => [
                    [
                        'slug' => 'demo-justins-heart-care-story',
                        'kind' => 'video',
                        'youtube_id' => 'xFbgTq4VzLc',
                        'translations' => [
                            'en' => ['title' => 'Justin’s heart care story', 'description' => 'Justin Vigile and his surgeon discuss his experience with heart disease and recovery on the Mayo Clinic Q&A podcast.'],
                            'bn' => ['title' => 'হৃদ্‌রোগের চিকিৎসায় জাস্টিনের অভিজ্ঞতা', 'description' => 'মেয়ো ক্লিনিকের ইংরেজি পডকাস্টে জাস্টিন ভিজিল ও তাঁর সার্জন চিকিৎসা এবং সুস্থ হয়ে ওঠার অভিজ্ঞতা নিয়ে কথা বলেছেন।'],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'everyday-health',
                'title' => ['en' => 'Everyday health', 'bn' => 'দৈনন্দিন স্বাস্থ্য'],
                'items' => [
                    [
                        'slug' => 'demo-meena-and-handwashing',
                        'kind' => 'video',
                        'youtube_id' => '1aJ-09TaqKg',
                        'translations' => [
                            'en' => ['title' => 'Learn about handwashing with Meena', 'description' => 'A Bengali animated episode from UNICEF Bangladesh about handwashing and everyday hygiene.'],
                            'bn' => ['title' => 'মীনার সঙ্গে হাত ধোয়া শিখি', 'description' => 'হাত ধোয়া ও দৈনন্দিন পরিচ্ছন্নতা নিয়ে ইউনিসেফ বাংলাদেশের বাংলা অ্যানিমেশন।'],
                        ],
                    ],
                    [
                        'slug' => 'demo-light-and-sleep',
                        'kind' => 'reel',
                        'youtube_id' => '7jJjhhfcnWI',
                        'translations' => [
                            'en' => ['title' => 'How light affects sleep', 'description' => 'A short Cleveland Clinic video about bedroom lighting and sleep.'],
                            'bn' => ['title' => 'ঘুমের ওপর আলোর প্রভাব', 'description' => 'শোবার ঘরের আলো ও ঘুম নিয়ে ক্লিভল্যান্ড ক্লিনিকের ইংরেজি শর্ট।'],
                        ],
                    ],
                    [
                        'slug' => 'demo-hydration-and-mental-wellbeing',
                        'kind' => 'reel',
                        'youtube_id' => 'XxvjKJZJcNs',
                        'translations' => [
                            'en' => ['title' => 'Hydration and mental wellbeing', 'description' => 'A Cleveland Clinic short about hydration as one part of looking after your wellbeing.'],
                            'bn' => ['title' => 'পানি পান ও মানসিক সুস্থতা', 'description' => 'সুস্থ থাকার দৈনন্দিন অভ্যাসে পর্যাপ্ত পানি পানের ভূমিকা নিয়ে ক্লিভল্যান্ড ক্লিনিকের ইংরেজি শর্ট।'],
                        ],
                    ],
                    [
                        'slug' => 'demo-meena-and-staying-hydrated',
                        'kind' => 'video',
                        'youtube_id' => 'P-FPfodTun8',
                        'translations' => [
                            'en' => ['title' => 'Meena and childhood dehydration', 'description' => 'A Bengali animated episode from UNICEF Bangladesh about childhood diarrhea and dehydration.'],
                            'bn' => ['title' => 'শিশুদের পানিশূন্যতা নিয়ে মীনার গল্প', 'description' => 'শিশুদের ডায়রিয়া ও পানিশূন্যতা নিয়ে ইউনিসেফ বাংলাদেশের বাংলা অ্যানিমেশন।'],
                        ],
                    ],
                    [
                        'slug' => 'demo-fiber-and-digestion',
                        'kind' => 'reel',
                        'youtube_id' => 'SAqMtkTyh60',
                        'translations' => [
                            'en' => ['title' => 'Fiber and digestion', 'description' => 'A Cleveland Clinic short about dietary fiber and digestive health.'],
                            'bn' => ['title' => 'খাদ্য আঁশ ও হজম', 'description' => 'খাদ্য আঁশ ও হজমের স্বাস্থ্য নিয়ে ক্লিভল্যান্ড ক্লিনিকের ইংরেজি শর্ট।'],
                        ],
                    ],
                    [
                        'slug' => 'demo-flexibility-and-stretching',
                        'kind' => 'reel',
                        'youtube_id' => '9jLQwZwDqDY',
                        'translations' => [
                            'en' => ['title' => 'Flexibility and stretching', 'description' => 'A Cleveland Clinic exercise physiologist explains flexibility and stretching in this short video.'],
                            'bn' => ['title' => 'নমনীয়তা ও স্ট্রেচিং', 'description' => 'শরীরের নমনীয়তা ও স্ট্রেচিং নিয়ে ক্লিভল্যান্ড ক্লিনিকের ইংরেজি শর্ট।'],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'special-programs',
                'title' => ['en' => 'Live and special programs', 'bn' => 'লাইভ ও বিশেষ অনুষ্ঠান'],
                'items' => [
                    [
                        'slug' => 'demo-lets-talk-mental-health',
                        'kind' => 'video',
                        'youtube_id' => 'rqP0uNskF0Q',
                        'translations' => [
                            'en' => ['title' => 'Let’s talk about mental health', 'description' => 'A recorded World Health Organization live discussion with mental health experts.'],
                            'bn' => ['title' => 'মানসিক স্বাস্থ্য নিয়ে কথা বলি', 'description' => 'মানসিক স্বাস্থ্য বিশেষজ্ঞদের সঙ্গে বিশ্ব স্বাস্থ্য সংস্থার ইংরেজি লাইভ আলোচনার রেকর্ডিং।'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
