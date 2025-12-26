<?php

namespace Tests\Unit\App\Models\Post;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function address_can_be_created_with_required_fields()
    {
        $user = User::factory()->create();

        $address = Address::factory()->create([
            'user_id' => $user->id,
            'phone_number' => '0123456789',
            'address' => '123 Test Street',
        ]);

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'phone_number' => '0123456789',
            'address' => '123 Test Street',
        ]);
    }

    /** @test */
    public function address_belongs_to_user()
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $address = Address::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $address->user);
        $this->assertEquals('Test User', $address->user->name);
    }

    /** @test */
    public function address_can_have_coordinates()
    {
        $address = Address::factory()->create([
            'latitude' => 10.762622,
            'longitude' => 106.660172,
        ]);

        $this->assertEquals(10.762622, $address->latitude);
        $this->assertEquals(106.660172, $address->longitude);
    }

    /** @test */
    public function address_has_fillable_attributes()
    {
        $user = User::factory()->create();
        $data = [
            'user_id' => $user->id,
            'phone_number' => '0987654321',
            'address' => '456 Another Street',
            'latitude' => 10.123,
            'longitude' => 106.456,
        ];

        $address = Address::create($data);

        $this->assertEquals('0987654321', $address->phone_number);
        $this->assertEquals('456 Another Street', $address->address);
    }

    /** @test */
    public function user_can_have_multiple_addresses()
    {
        $user = User::factory()->create();
        Address::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->addresses);
    }

    /** @test */
    public function address_can_be_updated()
    {
        $address = Address::factory()->create(['phone_number' => '0111111111']);

        $address->update(['phone_number' => '0999999999']);

        $this->assertEquals('0999999999', $address->fresh()->phone_number);
    }
}
