<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Pengirim')
                    ->disabled(),

                TextInput::make('email_or_phone')
                    ->label('Email / Nomor Telepon')
                    ->disabled(),

                TextInput::make('subject')
                    ->label('Perihal / Topik')
                    ->columnSpanFull()
                    ->disabled(),

                Textarea::make('message')
                    ->label('Isi Pesan')
                    ->rows(5)
                    ->columnSpanFull()
                    ->disabled(),

                TextInput::make('ip_address')
                    ->label('Alamat IP')
                    ->disabled(),

                Toggle::make('is_read')
                    ->label('Sudah Dibaca / Ditindaklanjuti')
                    ->default(false),

                DateTimePicker::make('read_at')
                    ->label('Waktu Dibaca')
                    ->nullable(),

                Textarea::make('admin_notes')
                    ->label('Catatan Tindak Lanjut Admin')
                    ->placeholder('Tuliskan catatan disposisi, tindak lanjut, atau respon staf tata usaha di sini...')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
