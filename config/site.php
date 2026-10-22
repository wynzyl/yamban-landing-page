<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storefront details
    |--------------------------------------------------------------------------
    |
    | These values feed the public landing page, the contact details and the
    | notification email sent when a customer submits an inquiry. Override
    | them from the .env file rather than editing this file.
    |
    */

    'name' => env('SITE_NAME', 'Yamban Enterprise'),

    'tagline' => env('SITE_TAGLINE', 'Custom sportswear, uniforms and team gear made in the Philippines.'),

    'email' => env('SITE_EMAIL', 'yambanenterprise@gmail.com'),

    'phone' => env('SITE_PHONE'),

    'address' => env('SITE_ADDRESS', 'Philippines · Nationwide shipping'),

    'hours' => env('SITE_HOURS', 'Monday to Saturday, 8:00 AM – 6:00 PM'),

    'facebook' => env('SITE_FACEBOOK', 'https://www.facebook.com/yambanEnterprise'),

    'messenger' => env('SITE_MESSENGER', 'https://m.me/yambanEnterprise'),

    'inquiry_subject' => env('SITE_INQUIRY_SUBJECT', 'New inquiry from the website'),

];
