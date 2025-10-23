<?php

namespace Modules\Worship\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class WorshipPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Worship';
    }

    public function getId(): string
    {
        return 'worship';
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
