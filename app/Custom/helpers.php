<?php

if (!function_exists('getImageUrl')) {
    function getImageUrl($image)
    {
        $baseUrl = config('app.base_url');

        if ($image !== null) {
            return $baseUrl . $image;
        } else {
            return null;
        }
    }
}