<?php

namespace Tests\Unit;

use App\Exceptions\RouteConflictException;
use App\Models\Product;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Router;
use Tests\TestCase;

class ProductsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     *
     * @return void
     */
    public function it_filters_products_within_a_specified_price_range()
    {
        $this->artisan('migrate:refresh');

        $user = User::factory()->create();

        $products = [
            10, 25, 100, 123, 172, 200, 233, 500
        ];

        Model::unguard();
        foreach ($products as $price) {
            Product::factory()->create([
                'user_id' => $user->getKey(),
                'price' => $price
            ]);
        }
        Model::reguard();


        $products = Product::withinPriceRange(100, 200)->get()->map(function ($product) {
            return $product->price;
        })->toArray();

        $this->assertEquals([100, 123, 172, 200], $products);

        // Reverse order and make sure it doesn't match product output.
        $this->assertNotEquals(array_reverse([100, 123, 172, 200]), $products);

        // Make sure that if we sent decending order it actually adjust order correctly on top of filtering.
        $products = Product::withinPriceRange(100, 200, 'desc')->get()->map(function ($product) {
            return $product->price;
        })->toArray();

        $this->assertEquals(array_reverse([100, 123, 172, 200]), $products);
    }
}
