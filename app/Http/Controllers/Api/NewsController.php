<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use SimplePie;

class NewsController extends Controller
{
    public function getNews()
    {
        $feedUrl = 'https://www.manilatimes.net/news/feed/';
        $maxItems = 20;

        $feed = new SimplePie();
        $feed->set_feed_url($feedUrl);
        $feed->enable_cache(false); // Disable caching for simplicity
        $feed->init();

        $news = [];

        foreach ($feed->get_items(0, $maxItems) as $item) {
            $news[] = [
                'title' => $item->get_title(),
                'author' => $item->get_author()->get_name(),
                // Get the author's name
                'link' => $item->get_permalink(),
                'description' => $item->get_description(),
                'date' => $item->get_date('Y-m-d H:i:s'),
                'image' => $item->get_enclosure()->get_link(),
                // Get the URL of the image or thumbnail
            ];
        }

        return response()->json($news);
    }
}