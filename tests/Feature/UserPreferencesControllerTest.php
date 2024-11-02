<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserPreferencesControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();


        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    #[Test]
    public function it_can_set_preferences()
    {
        $data = [
            'preferred_sources' => ['The Guardian', 'New York Times'],
            'preferred_categories' => ['general', 'technology'],
            'preferred_authors' => ['Author 1', 'Author 2'],
        ];

        $response = $this->actingAs($this->user)->postJson('/api/preferences', $data);
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Preferences saved successfully',
            ]);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $this->user->id,
            'preferred_sources' => json_encode($data['preferred_sources']),
            'preferred_categories' => json_encode($data['preferred_categories']),
            'preferred_authors' => json_encode($data['preferred_authors']),
        ]);
    }

    #[Test]
    public function it_can_get_preferences()
    {
        UserPreference::factory()->create([
            'user_id' => $this->user->id,
            'preferred_sources' => ['The Guardian', 'New York Times'],
            'preferred_categories' => ['general', 'technology'],
            'preferred_authors' => ['Author 1', 'Author 2'],
        ]);

        $response = $this->getJson('/api/preferences');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Preferences retrieved successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'preferred_sources',
                    'preferred_categories',
                    'preferred_authors',
                ],
            ]);
    }

    #[Test]
    public function it_returns_error_if_no_preferences_found()
    {
        $response = $this->getJson('/api/preferences');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No preferences found for this user',
            ]);
    }

    #[Test]
    public function it_can_get_personalized_feed_based_on_preferences()
    {
        // Create user preferences
        UserPreference::factory()->create([
            'user_id' => $this->user->id,
            'preferred_sources' => ['The Guardian'],
            'preferred_categories' => ['technology'],
            'preferred_authors' => ['Author 1'],
        ]);

        // Create matching and non-matching articles
        Article::factory()->create([
            'title' => 'Tech News from The Guardian',
            'source' => 'The Guardian',
            'category' => 'technology',
            'author' => 'Author 1',
        ]);

        Article::factory()->create([
            'title' => 'General News',
            'source' => 'Another Source',
            'category' => 'general',
            'author' => 'Author 2',
        ]);

        $response = $this->getJson('/api/personalized-feed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Personalized feed retrieved successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['title', 'source', 'category', 'author'],
                    ],
                ],
            ]);

        $this->assertCount(1, $response->json('data.data'));
    }

    #[Test]
    public function it_returns_error_if_no_personalized_feed_found()
    {
        $response = $this->getJson('/api/personalized-feed');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No preferences set for personalized feed.',
            ]);
    }
}
