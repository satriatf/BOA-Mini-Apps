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
            Select::make('created_by_id')
                ->label('Created By')
                ->options(fn () => User::where('is_active', 'Active')->whereIn('level', ['Staff', 'SH'])->pluck('employee_name', 'sk_user'))
                ->searchable()->preload()->native(false)->required(),

            TextInput::make('no_tiket')
                ->label('No. Ticket')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(50),

            Textarea::make('deskripsi')
                ->label('Description')
                ->required()
                ->rows(3),

            Select::make('type')
                ->label('Type')
                ->options(Mtc::TYPE_OPTIONS)
                ->searchable()->native(false)->required(),

            Select::make('resolver_id')
                ->label('Resolver PIC')
                ->options(fn () => User::where('is_active', 'Active')->whereIn('level', ['Staff', 'SH'])->pluck('employee_name', 'sk_user'))
                ->searchable()->preload()->native(false),

            Textarea::make('solusi')
                ->label('Solution')
                ->rows(3),

            Select::make('application')
                ->label('Application')
                ->options(Mtc::APP_OPTIONS)
                ->searchable()->native(false)->required(),

            DatePicker::make('tanggal')
                ->label('Date')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->required(),

            FileUpload::make('attachments')
                ->label('Attachments')
                ->multiple()
                ->disk('public')
                ->directory('mtc_attachments')
                ->maxSize(10240)
                ->preserveFilenames()
                ->reorderable()
                ->helperText('Upload multiple files (PDF, images, docs).')
                ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                ->previewable(false)
                ->downloadable()
                ->openable(),
        ]);
    }
}
