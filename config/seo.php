<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kode Verifikasi Mesin Pencari
    |--------------------------------------------------------------------------
    | Isi lewat .env setelah situs didaftarkan di Google Search Console dan
    | Bing Webmaster Tools. Cukup isi nilai "content" dari meta tag verifikasi.
    */
    'google_site_verification' => env('GOOGLE_SITE_VERIFICATION'),
    'bing_site_verification' => env('BING_SITE_VERIFICATION'),

    /*
    |--------------------------------------------------------------------------
    | Identitas Entitas Sekolah (untuk JSON-LD)
    |--------------------------------------------------------------------------
    */
    'npsn' => '20314928',
    'kemdikbud_profile_url' => 'https://referensi.data.kemdikbud.go.id/pendidikan/npsn/20314928',

    'latitude' => -7.1576081,
    'longitude' => 111.5662531,

];
