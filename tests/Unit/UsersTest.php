<?php

namespace Tests\Unit;

use App\Exceptions\RouteConflictException;
use App\Models\Product;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Closure;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     *
     * @return void
     */
    public function it_does_not_allow_conflict_with_existing_system_routes()
    {
        $allowed = User::factory()->create();

        $this->assertModelExists($allowed);

        $this->expectException(RouteConflictException::class);

        // This creation call should throw an exception because of conflict with system route.
        $forbidden = User::factory()->create(['username' => 'contact']);
    }

    /**
     * @test
     *
     * @return void
     */
    public function it_orders_users_by_products_total_value()
    {
        $userProducts = $this->generateUserProductSample();

        $users = User::orderByProductsTotalValue()->get()->keyBy('id')->map(function ($user) {
            return $user->total_value;
        })->toArray();

        $userProducts = $userProducts->map(function ($productList) {
            return array_sum($productList);
        })->sortDesc()->toArray();

        $this->assertEquals($userProducts, $users);
    }

    /**
     * @test
     *
     * @return void
     */
    public function it_caches_users_ordered_by_products_total_value()
    {
        $this->withoutExceptionHandling();

        $this->generateUserProductSample();

        $expected = app(User::class)->orderByProductsTotalValue()->get();

        Cache::shouldReceive('remember')
            ->once()
            ->with(
                app(User::class)->getOrderByProductsTotalValueCacheKey(),
                60*45,
                Closure::class
            )->andReturn($expected);

        $cached = app(User::class)->orderByProductsTotalValueFromCache();
    }

    protected function generateUserProductSample () {
        $this->artisan('migrate:refresh');

        Model::unguard();

        $users = User::factory()->count(3)->create()->keyBy('id');

        $userProducts = collect([
            1 => [500, 1000],
            2 => [2000],
            3 => [150, 250, 350]
        ]);

        foreach ($userProducts as $userId => $products) {
            foreach ($products as $price) {
                Product::factory()->create([
                    'user_id' => $userId,
                    'price' => $price
                ]);
            }
        }

        Model::reguard();

        return $userProducts;
    }
}
