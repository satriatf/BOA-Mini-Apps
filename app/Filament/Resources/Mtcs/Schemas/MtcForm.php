<?php

namespace App\Filament\Resources\Mtcs\Schemas;

use App\Models\Mtc;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class MtcForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // Created By → dropdown User
            Select::make('created_by_id')
                ->label('Created by who?')
                ->options(fn () => User::pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->native(false)
                ->required(),

            TextInput::make('title')
                ->label('Ticket Number')
                ->required()->unique(ignoreRecord: true)
                ->maxLength(15),

            Textarea::make('deskripsi')
                ->label('Description')
                ->required()
                ->rows(3),

            // Type → 5 opsi
            Select::make('type')
                ->label('Ticket Type')
                ->options(Mtc::TYPE_OPTIONS)
                ->searchable()
                ->native(false)
                ->required(),

            // Resolver PIC → dropdown User
            Select::make('resolver_id')
                ->label('Resolver PIC')
                ->options(fn () => User::pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->native(false),

            Textarea::make('solusi')
                ->label('Solution')
                ->rows(3),

            // Aplikasi → dropdown sesuai Excel
            Select::make('application')
                ->label('Application')
                ->options(Mtc::APP_OPTIONS)
                ->searchable()
                ->native(false)
                ->required(),

            DatePicker::make('Date')
            ->displayFormat('d/m/Y'),

            // Attachments = ANGKA
            FileUpload::make('attachments')
            ->label('Attachments')
            ->multiple() // kalau boleh upload banyak file
            ->directory('attachments') // simpan di storage/app/attachments
            ->maxSize(10240) // max 10 MB
            ->acceptedFileTypes(['application/pdf', 'image/*', 'excel/xlc']) // file yang diterima
            ->required(),
        ]);
    }
}
