<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class FetchArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from external news APIs';

    /**
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $this->fetchFromNewsAPI();
        $this->fetchFromTheGuardian();
        $this->fetchFromNYTimes();

        $this->info('Articles fetched successfully!');
    }

    private function fetchFromNewsAPI()
    {
        try {
            $response = Http::get('https://newsapi.org/v2/top-headlines', [
                'apiKey' => env('NEWS_API_KEY'),
                'country' => 'us',
            ]);

            $articles = $response->json()['articles'] ?? [];

            foreach ($articles as $data) {
                $this->saveArticle(
                    $data['title'],
                    $data['description'],
                    $data['author'],
                    $data['source']['name'],
                    'general', // Assuming a general category
                    Carbon::parse($data['publishedAt'])->format('Y-m-d')
                );
            }
        } catch (\Exception $e) {
            $this->error('Failed to fetch articles from News API');
            throw new \Exception('Failed to fetch articles from News API: ' . $e->getMessage());
        }
    }

    private function fetchFromTheGuardian()
    {
        try {
            $response = Http::get('https://content.guardianapis.com/search', [
                'api-key' => env('GUARDIAN_API_KEY'),
                'show-fields' => 'body,byline',
            ]);

            $articles = $response->json()['response']['results'] ?? [];

            foreach ($articles as $data) {
                $this->saveArticle(
                    $data['webTitle'],
                    $data['fields']['body'] ?? '',
                    $data['fields']['byline'] ?? 'Unknown',
                    'The Guardian',
                    'general',
                    Carbon::parse($data['webPublicationDate'])->format('Y-m-d')
                );
            }
        } catch (\Exception $e) {
            $this->error('Failed to fetch articles from The Guardian');
            throw new \Exception('Failed to fetch articles from The Guardian: ' . $e->getMessage());
        }
    }

    private function fetchFromNYTimes()
    {
        try {
            $response = Http::get('https://api.nytimes.com/svc/topstories/v2/home.json', [
                'api-key' => env('NYTIMES_API_KEY'),
            ]);

            $articles = $response->json()['results'] ?? [];

            foreach ($articles as $data) {
                $this->saveArticle(
                    $data['title'],
                    $data['abstract'],
                    $data['byline'] ?? '',
                    'New York Times',
                    $data['section'] ?? 'general',
                    Carbon::parse($data['published_date'])->format('Y-m-d')
                );
            }
        } catch (\Exception $e) {
            $this->error('Failed to fetch articles from New York Times');
            throw new \Exception('Failed to fetch articles from New York Times: ' . $e->getMessage());
        }
    }

    private function saveArticle($title, $content, $author, $source, $category, $publishedAt)
    {
        $maxContentLength = 1024 * 1024; // 1 MB limit for content
        if (strlen($content) > $maxContentLength) {
            $content = substr($content, 0, $maxContentLength);
        }

        Article::updateOrCreate(
            ['title' => $title],
            [
                'content' => $content,
                'author' => $author,
                'source' => $source,
                'category' => $category,
                'published_at' => $publishedAt,
            ]
        );
    }
}
