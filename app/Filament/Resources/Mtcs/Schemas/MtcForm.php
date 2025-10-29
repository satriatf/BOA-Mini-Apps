<?php

namespace App\Filament\Resources\Mtcs\Schemas;

use App\Models\Mtc;
use App\Models\User;
use App\Models\MasterNonProjectType;
use App\Models\MasterApplication;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class MtcForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('created_by_id')
                ->label('Created By')
                ->options(fn() => User::where('is_active', 'Active')->whereIn('level', ['Staff', 'Section Head'])->pluck('employee_name', 'sk_user'))
                ->searchable()->preload()->native(false)->required(),

            TextInput::make('no_tiket')
                ->label('No. Ticket')
                ->required()
                ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule) => $rule->whereNull('deleted_at'))
                ->maxLength(50),

            Textarea::make('deskripsi')
                ->label('Description')
                ->required()
                ->rows(3),

            Select::make('type')
                ->label('Type')
                ->options(fn() => MasterNonProjectType::pluck('name', 'name'))
                ->searchable()->native(false)->required(),

            Select::make('resolver_id')
                ->label('Resolver PIC')
                ->options(fn() => User::where('is_active', 'Active')->whereIn('level', ['Staff', 'Section Head'])->pluck('employee_name', 'sk_user'))
                ->searchable()->preload()->native(false),

            Textarea::make('solusi')
                ->label('Solution')
                ->rows(3),

            Select::make('application')
                ->label('Application')
                ->options(fn() => MasterApplication::pluck('name', 'name'))
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
                // Max size per file: 10 MB (in KB)
                ->maxSize(10240)
                ->preserveFilenames()
                ->reorderable()
                ->helperText('Multi-file. Allowed: PDF/PNG/JPG/JPEG/XLS/XLSX/DOC/DOCX/ZIP/HEIC/TXT. If not listed, compress to ZIP first. Preview: PDF/images/TXT only. Max 10 MB/file.')
                // Frontend accept/mime filter
                ->acceptedFileTypes([
                    'application/pdf',
                    'image/png',
                    'image/jpeg',
                    'image/heic',
                    'image/heif',
                    'application/msword', // .doc
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                    'application/vnd.ms-excel', // .xls
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                    'application/zip',
                    'application/x-zip-compressed',
                    'text/plain', // .txt
                ])
                // Server-side validation (per file)
                ->rules(['mimes:pdf,png,jpg,jpeg,xls,xlsx,doc,docx,zip,heic,txt', 'max:10240'])
                ->validationMessages([
                    'mimes' => 'Tipe file tidak sesuai. Gunakan: pdf, png, jpg, jpeg, xls, xlsx, doc, docx, zip, heic, txt. Jika berbeda, mohon zip dulu.',
                    'max' => 'File terlalu besar. Maksimal 10 MB per file.',
                ])
                ->previewable(true)
                ->downloadable()
                ->openable()
                ->visibility('public'),
        ]);
    }
}
