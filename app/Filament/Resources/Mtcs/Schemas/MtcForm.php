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
                ->label('Created By')
                ->options(fn () => User::pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->native(false)
                ->required(),

            TextInput::make('title')
                ->label('Title')
                ->required()->unique(ignoreRecord: true)
                ->maxLength(150),

            Textarea::make('deskripsi')
                ->label('Deskripsi')
                ->required()
                ->rows(3),

            // Type → 5 opsi
            Select::make('type')
                ->label('Type')
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
                ->label('Solusi')
                ->rows(3),

            // Aplikasi → dropdown sesuai Excel
            Select::make('application')
                ->label('Aplikasi')
                ->options(Mtc::APP_OPTIONS)
                ->searchable()
                ->native(false)
                ->required(),

            DatePicker::make('tanggal'),

            // Attachments = ANGKA
            FileUpload::make('attachments')
            ->label('Attachments')
            ->multiple() // kalau boleh upload banyak file
            ->directory('attachments') // simpan di storage/app/attachments
            ->maxSize(10240) // max 10 MB
            ->acceptedFileTypes(['application/pdf', 'image/*']) // file yang diterima
            ->required(),
        ]);
    }
}
