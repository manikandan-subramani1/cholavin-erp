<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_homepage_only_shows_active_products_selected_for_homepage(): void
    {
        Product::create([
            'name' => 'Second Featured Rice',
            'slug' => 'second-featured-rice',
            'is_active' => true,
            'show_on_homepage' => true,
            'sort_order' => 20,
        ]);

        Product::create([
            'name' => 'First Featured Rice',
            'slug' => 'first-featured-rice',
            'is_active' => true,
            'show_on_homepage' => true,
            'sort_order' => 10,
        ]);

        Product::create([
            'name' => 'Products Page Only Rice',
            'slug' => 'products-page-only-rice',
            'is_active' => true,
            'show_on_homepage' => false,
        ]);

        Product::create([
            'name' => 'Inactive Featured Rice',
            'slug' => 'inactive-featured-rice',
            'is_active' => false,
            'show_on_homepage' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Quality Rice for Every Meal')
            ->assertSeeInOrder(['First Featured Rice', 'Second Featured Rice'])
            ->assertDontSee('Products Page Only Rice')
            ->assertDontSee('Inactive Featured Rice')
            ->assertDontSee('Popular Products From Our Store');
    }
}
