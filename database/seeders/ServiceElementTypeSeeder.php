<?php

namespace Modules\Worship\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Worship\Models\ServiceElementType;

class ServiceElementTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['key' => 'welcome', 'label' => 'Welcome'],
            ['key' => 'call_to_worship', 'label' => 'Call to Worship'],
            ['key' => 'prayer', 'label' => 'Prayer', 'expects_content' => true, 'content_kind' => 'prayer'],
            ['key' => 'offering', 'label' => 'Offering'],
            ['key' => 'notices', 'label' => 'Notices'],
            ['key' => 'reading', 'label' => 'Bible Reading', 'expects_content' => true, 'content_kind' => 'reading'],
            ['key' => 'sermon', 'label' => 'Sermon', 'expects_content' => true, 'content_kind' => 'preacher'],
            ['key' => 'benediction', 'label' => 'Benediction'],
        ] as $item) {
            ServiceElementType::updateOrCreate(
                ['key' => $item['key']], // match on unique key
                $item                     // insert or update all fields
            );
        }
    }
}
