<?php

namespace App\Http\Controllers;

use App\Models\Design;
use Illuminate\Http\Response;

class DesignPreviewController extends Controller
{
    /**
     * Serve the stored preview image for a design.
     */
    public function __invoke(Design $design): Response
    {
        $preview = $design->preview()->first(['data', 'mime']);

        abort_if($preview === null, 404);

        $binary = $preview->binary();

        abort_if($binary === null, 404);

        return response($binary, 200, [
            'Content-Type' => in_array($preview->mime, ['image/webp', 'image/jpeg', 'image/png'], true)
                ? $preview->mime
                : 'image/webp',
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
