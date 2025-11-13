<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Seed the languages table with supported languages.
     */
    public function run(): void
    {
        $languages = [
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'flag_emoji' => '🇬🇧',
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'de',
                'name' => 'German',
                'native_name' => 'Deutsch',
                'flag_emoji' => '🇩🇪',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
            ],
            [
                'code' => 'es',
                'name' => 'Spanish',
                'native_name' => 'Español',
                'flag_emoji' => '🇪🇸',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 3,
            ],
            [
                'code' => 'fr',
                'name' => 'French',
                'native_name' => 'Français',
                'flag_emoji' => '🇫🇷',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 4,
            ],
            [
                'code' => 'si',
                'name' => 'Slovenian',
                'native_name' => 'Slovenščina',
                'flag_emoji' => '🇸🇮',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }

        $this->command->info('Languages seeded successfully!');
    }
}
