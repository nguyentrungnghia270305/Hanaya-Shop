<?php

namespace Tests\Integration\Database;

use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function transaction_commits_on_success()
    {
        DB::beginTransaction();

        $user = User::factory()->create(['name' => 'Test User']);

        DB::commit();

        $this->assertDatabaseHas('users', ['name' => 'Test User']);
    }

    /**
     * @test
     */
    public function transaction_rolls_back_on_failure()
    {
        try {
            DB::beginTransaction();

            User::factory()->create(['name' => 'User To Rollback']);

            throw new \Exception('Simulated error');
        } catch (\Exception $e) {
            DB::rollBack();
        }

        $this->assertDatabaseMissing('users', ['name' => 'User To Rollback']);
    }

    /**
     * @test
     */
    public function nested_transactions_work_correctly()
    {
        DB::beginTransaction();

        $user = User::factory()->create(['name' => 'Outer Transaction']);

        DB::beginTransaction();

        $product = Product::factory()->create(['name' => 'Inner Transaction Product']);

        DB::commit();

        DB::commit();

        $this->assertDatabaseHas('users', ['name' => 'Outer Transaction']);
        $this->assertDatabaseHas('products', ['name' => 'Inner Transaction Product']);
    }

    /**
     * @test
     */
    public function transaction_callback_handles_success()
    {
        $result = DB::transaction(function () {
            $user = User::factory()->create(['name' => 'Callback User']);

            return $user;
        });

        $this->assertInstanceOf(User::class, $result);
        $this->assertDatabaseHas('users', ['name' => 'Callback User']);
    }

    /**
     * @test
     */
    public function transaction_callback_handles_failure()
    {
        try {
            DB::transaction(function () {
                User::factory()->create(['name' => 'Failed User']);

                throw new \Exception('Transaction error');
            });
        } catch (\Exception $e) {
            // Expected exception
        }

        $this->assertDatabaseMissing('users', ['name' => 'Failed User']);
    }
}
