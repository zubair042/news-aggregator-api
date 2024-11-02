<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Article::factory()->count(20)->create();
    }

    /**
     * @return void
     */
    private function authenticateUser()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    #[Test]
    public function it_can_retrieve_articles_with_pagination()
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',  // Paginated data
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]
            ]);
    }

    #[Test]
    public function it_can_filter_articles_by_keyword()
    {
        $this->authenticateUser();

        $article = Article::factory()->create(['title' => 'Unique Article Title']);

        $response = $this->getJson('/api/articles?keyword=Unique');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Unique Article Title']);
    }

    #[Test]
    public function it_can_filter_articles_by_category()
    {
        $this->authenticateUser();

        $category = 'Tech';
        $article = Article::factory()->create(['category' => $category]);

        $response = $this->getJson("/api/articles?category={$category}");

        $response->assertStatus(200)
            ->assertJsonFragment(['category' => $category]);
    }

    #[Test]
    public function it_can_filter_articles_by_source()
    {
        $this->authenticateUser();

        $source = 'New York Times';
        $article = Article::factory()->create(['source' => $source]);

        $response = $this->getJson("/api/articles?source={$source}");

        $response->assertStatus(200)
            ->assertJsonFragment(['source' => $source]);
    }

    #[Test]
    public function it_can_filter_articles_by_date()
    {
        $this->authenticateUser();

        $date = '2024-11-01';
        $article = Article::factory()->create(['published_at' => $date]);

        $response = $this->getJson("/api/articles?date={$date}");

        $response->assertStatus(200)
            ->assertJsonFragment(['published_at' => $date]);
    }

    #[Test]
    public function it_retrieves_an_article_by_id()
    {
        $this->authenticateUser();

        $article = Article::factory()->create();

        $response = $this->getJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Article retrieved successfully',
                'data' => [
                    'id' => $article->id,
                    'title' => $article->title,
                ]
            ]);
    }

    #[Test]
    public function it_returns_404_if_article_is_not_found()
    {
        $this->authenticateUser();

        $invalidId = 9999;  // Assuming this ID doesn't exist
        $response = $this->getJson("/api/articles/{$invalidId}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Article not found',
            ]);
    }

    #[Test]
    public function it_returns_unauthorized_if_not_authenticated()
    {
        $response = $this->getJson('/api/articles');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }
}
