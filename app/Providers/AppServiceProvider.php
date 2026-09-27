<?php

namespace App\Providers;

use App\Domain\Kost\Models\Kost;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Route model binding for Super Admin kost submissions
        Route::model('submission', Kost::class);

        // Register custom pagination view
        Paginator::defaultView('vendor.pagination.pagination');
        Paginator::defaultSimpleView('vendor.pagination.pagination');

        $this->registerIndonesianValidationMessages();
    }

    /**
     * Register custom Indonesian validation messages.
     */
    protected function registerIndonesianValidationMessages(): void
    {
        Validator::replacer('required', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), 'Kolom :attribute wajib diisi.');
        });

        Validator::replacer('email', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), 'Kolom :attribute harus berupa alamat email yang valid.');
        });

        Validator::replacer('string', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), 'Kolom :attribute harus berupa teks.');
        });

        Validator::replacer('max', function ($message, $attribute, $rule, $parameters) {
            $max = $parameters[0] ?? '';

            return str_replace(
                [':attribute', ':max'],
                [$this->getAttributeName($attribute), $max],
                'Kolom :attribute tidak boleh lebih dari :max karakter.'
            );
        });

        Validator::replacer('min', function ($message, $attribute, $rule, $parameters) {
            $min = $parameters[0] ?? '';

            return str_replace(
                [':attribute', ':min'],
                [$this->getAttributeName($attribute), $min],
                'Kolom :attribute harus minimal :min karakter.'
            );
        });

        Validator::replacer('confirmed', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), 'Konfirmasi :attribute tidak cocok.');
        });

        Validator::replacer('unique', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), ':attribute tidak dapat digunakan.');
        });

        Validator::replacer('exists', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), ':attribute yang dipilih tidak valid.');
        });

        Validator::replacer('numeric', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), 'Kolom :attribute harus berupa angka.');
        });

        Validator::replacer('integer', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), 'Kolom :attribute harus berupa bilangan bulat.');
        });

        Validator::replacer('boolean', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), 'Kolom :attribute harus bernilai benar atau salah.');
        });

        Validator::replacer('url', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), 'Format :attribute tidak valid.');
        });

        Validator::replacer('date', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), 'Kolom :attribute harus berupa tanggal yang valid.');
        });

        Validator::replacer('after', function ($message, $attribute, $rule, $parameters) {
            $date = $parameters[0] ?? '';

            return str_replace(
                [':attribute', ':date'],
                [$this->getAttributeName($attribute), $date],
                'Kolom :attribute harus tanggal setelah :date.'
            );
        });

        Validator::replacer('before', function ($message, $attribute, $rule, $parameters) {
            $date = $parameters[0] ?? '';

            return str_replace(
                [':attribute', ':date'],
                [$this->getAttributeName($attribute), $date],
                'Kolom :attribute harus tanggal sebelum :date.'
            );
        });

        Validator::replacer('in', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), ':attribute yang dipilih tidak valid.');
        });

        Validator::replacer('mimes', function ($message, $attribute, $rule, $parameters) {
            $values = implode(', ', $parameters);

            return str_replace(
                [':attribute', ':values'],
                [$this->getAttributeName($attribute), $values],
                'Kolom :attribute harus berupa file dengan tipe: :values.'
            );
        });

        Validator::replacer('image', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), 'Kolom :attribute harus berupa gambar.');
        });

        Validator::replacer('regex', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), 'Format :attribute tidak valid.');
        });

        Validator::replacer('digits', function ($message, $attribute, $rule, $parameters) {
            $digits = $parameters[0] ?? '';

            return str_replace(
                [':attribute', ':digits'],
                [$this->getAttributeName($attribute), $digits],
                'Kolom :attribute harus terdiri dari :digits digit.'
            );
        });
    }

    /**
     * Get human-readable attribute name.
     */
    protected function getAttributeName(string $attribute): string
    {
        $attributeNames = [
            'email' => 'Email',
            'password' => 'Password',
            'first_name' => 'Nama depan',
            'last_name' => 'Nama belakang',
            'phone' => 'Nomor telepon',
        ];

        return $attributeNames[$attribute] ?? str_replace('_', ' ', $attribute);
    }
}
