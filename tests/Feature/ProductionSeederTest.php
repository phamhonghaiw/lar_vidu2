<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class ProductionSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['seeding.admin' => [
            'name' => 'Test Shop Admin',
            'email' => 'seed-admin@example.com',
            'password' => 'test-only-password-123',
        ]]);
    }

    public function test_seeded_admin_can_sign_in_without_email_delivery(): void
    {
        Notification::fake();
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('categories', 3);
        $this->assertDatabaseCount('products', 10);
        $admin = User::where('email', 'seed-admin@example.com')->firstOrFail();
        $this->assertTrue($admin->hasVerifiedEmail());
        $this->assertSame('admin', $admin->role);
        Notification::assertNothingSent();

        $this->post('/login', [
            'email' => 'seed-admin@example.com',
            'password' => 'test-only-password-123',
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->get('/admin/dashboard')->assertOk();
    }

    public function test_repeated_seeding_preserves_changes_and_uses_actual_category_ids(): void
    {
        Category::create(['name' => 'Existing category']);
        $this->seed(DatabaseSeeder::class);
        $product = Product::where('name', 'Quần jean nam')->firstOrFail();
        $this->assertSame('Quần', $product->category->name);
        $this->assertNotEquals(1, $product->category_id);
        $product->update(['price' => 123000, 'quantity' => 7]);

        $admin = User::where('email', 'seed-admin@example.com')->firstOrFail();
        $newHash = Hash::make('changed-password-123');
        $admin->update(['password' => $newHash]);
        config(['seeding.admin.password' => null]);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('categories', 4);
        $this->assertDatabaseCount('products', 10);
        $this->assertSame($newHash, $admin->fresh()->password);
        $this->assertSame(7, $product->fresh()->quantity);
        $this->assertEquals(123000, $product->fresh()->price);
    }

    public function test_seed_does_not_promote_an_existing_regular_user(): void
    {
        $user = User::create([
            'name' => 'Existing customer',
            'email' => 'seed-admin@example.com',
            'password' => Hash::make('customer-password-123'),
            'role' => 'user',
        ]);

        try {
            $this->seed(DatabaseSeeder::class);
            $this->fail('An existing customer must not be promoted by seeding.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('non-admin', $exception->getMessage());
        }

        $this->assertSame('user', $user->fresh()->role);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_seed_requires_a_password_and_leaves_no_partial_data(): void
    {
        config(['seeding.admin.password' => null]);

        try {
            $this->seed(DatabaseSeeder::class);
            $this->fail('Seeding must require admin credentials.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('SEED_ADMIN_PASSWORD', $exception->getMessage());
        }

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('categories', 0);
        $this->assertDatabaseCount('products', 0);
    }
}
