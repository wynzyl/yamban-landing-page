@props([
    'title' => null,
    'description' => null,
])

@php
    $siteName = config('site.name');
    $pageTitle = filled($title) ? $title.' · '.$siteName : $siteName.' · Custom Sportswear & Uniforms';
    $metaDescription = $description ?? config('site.tagline');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $metaDescription }}" />
        <link rel="canonical" href="{{ url()->current() }}" />

        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="{{ $siteName }}" />
        <meta property="og:title" content="{{ $pageTitle }}" />
        <meta property="og:description" content="{{ $metaDescription }}" />
        <meta property="og:url" content="{{ url()->current() }}" />
        <meta property="og:image" content="{{ asset('images/brand/hero-main.jpg') }}" />
        <meta name="twitter:card" content="summary_large_image" />

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-bone font-sans text-ink antialiased">
        <x-site-header />

        <main>
            {{ $slot }}
        </main>

        <x-site-footer />
    </body>
</html>
