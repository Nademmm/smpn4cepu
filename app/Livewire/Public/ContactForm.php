<?php

namespace App\Livewire\Public;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';
    public string $email_or_phone = '';
    public string $subject = '';
    public string $message = '';
    public string $honey = ''; // Honeypot anti-spam
    public bool $submitted = false;

    protected array $rules = [
        'name' => 'required|string|min:3|max:100',
        'email_or_phone' => 'required|string|min:5|max:100',
        'subject' => 'required|string|min:3|max:150',
        'message' => 'required|string|min:10|max:2000',
    ];

    protected array $messages = [
        'name.required' => 'Nama lengkap wajib diisi.',
        'name.min' => 'Nama lengkap minimal 3 karakter.',
        'email_or_phone.required' => 'Email atau nomor telepon wajib diisi.',
        'subject.required' => 'Perihal atau topik pesan wajib diisi.',
        'message.required' => 'Pesan lengkap wajib diisi.',
        'message.min' => 'Pesan minimal 10 karakter.',
    ];

    public function submitMessage(Request $request): void
    {
        // Proteksi bot spam via honeypot
        if (!empty($this->honey)) {
            $this->submitted = true;
            return;
        }

        $throttleKey = 'contact-message:' . ($request->ip() ?? '127.0.0.1');
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            $this->addError('message', "Terlalu banyak pengiriman pesan dalam waktu singkat. Silakan tunggu {$seconds} detik.");
            return;
        }

        $this->validate();

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 120);

        ContactMessage::create([
            'name' => trim($this->name),
            'email_or_phone' => trim($this->email_or_phone),
            'subject' => trim($this->subject),
            'message' => trim($this->message),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'is_read' => false,
        ]);

        $this->reset(['name', 'email_or_phone', 'subject', 'message', 'honey']);
        $this->submitted = true;
    }

    public function resetForm(): void
    {
        $this->submitted = false;
    }

    public function render()
    {
        return view('livewire.public.contact-form');
    }
}
