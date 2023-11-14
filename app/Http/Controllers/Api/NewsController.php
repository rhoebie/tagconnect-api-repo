<?php

namespace App\Http\Controllers\Api;

use SimplePie;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;

class NewsController extends Controller
{
    public function getNews(Request $request)
    {
        $feedUrl = 'https://www.manilatimes.net/news/feed/';

        $feed = new SimplePie();
        $feed->set_feed_url($feedUrl);
        $feed->enable_cache(false);
        $feed->init();

        $perPage = 10; // Set a default value for per_page
        $currentPage = $request->input('page', 1);

        // Get the total number of items
        $totalItems = count($feed->get_items());

        // Use Laravel's Paginator to paginate the array
        $items = array_slice($feed->get_items(), ($currentPage - 1) * $perPage, $perPage);

        $news = [];

        foreach ($items as $item) {
            $news[] = [
                'title' => $item->get_title(),
                'author' => $item->get_author()->get_name(),
                'link' => $item->get_permalink(),
                'description' => $item->get_description(),
                'date' => $item->get_date('Y-m-d H:i:s'),
                'image' => $item->get_enclosure()->get_link(),
            ];
        }

        // Create a paginator instance
        $paginator = new Paginator($news, $perPage, $currentPage);

        return response()->json([
            'meta' => [
                'total_page' => ceil($totalItems / $perPage),
                'current_page' => $currentPage,
            ],
            'data' => $paginator->items(),
        ]);
    }
}