<?php

namespace App\Filament\Resources\Mtcs\Pages;

use App\Filament\Resources\Mtcs\MtcResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMtc extends CreateRecord
{
    protected static string $resource = MtcResource::class;

    protected static ?string $title = 'New MTC';
}
