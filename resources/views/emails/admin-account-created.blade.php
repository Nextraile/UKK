@component('mail::message')
# Selamat Datang di SewaKost!

Halo {{ $admin->first_name }},

Akun Admin Anda telah berhasil dibuat oleh Super Administrator. Anda sekarang dapat mengakses dashboard admin SewaKost.

## Informasi Login

**Email:** {{ $admin->email }}  
**Password:** {{ $password }}

@component('mail::button', ['url' => route('login')])
Login Sekarang
@endcomponent

---

**Penting untuk Keamanan Akun Anda:**

Untuk melindungi akun Anda, kami sangat menyarankan untuk **mengganti password ini** setelah login pertama kali melalui menu Profile Anda.

Jika Anda memiliki pertanyaan atau membutuhkan bantuan, silakan hubungi Super Administrator.

Terima kasih,  
{{ config('app.name') }}
@endcomponent
