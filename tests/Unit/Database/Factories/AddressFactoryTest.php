<?php

namespace Tests\Unit\Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function address_factory_creates_address()
    {
        $address = Address::factory()->create();

        $this->assertInstanceOf(Address::class, $address);
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }

    /**
     * @test
     */
    public function address_factory_sets_required_attributes()
    {
        $address = Address::factory()->create();

        $this->assertNotNull($address->user_id);
        $this->assertNotNull($address->phone_number);
        $this->assertNotNull($address->address);
    }

    /**
     * @test
     */
    public function address_factory_can_override_attributes()
    {
        $user = User::factory()->create();

        $address = Address::factory()->create([
            'user_id' => $user->id,
            'phone_number' => '0123456789',
            'address' => '123 Test Street',
        ]);

        $this->assertEquals($user->id, $address->user_id);
        $this->assertEquals('0123456789', $address->phone_number);
        $this->assertEquals('123 Test Street', $address->address);
    }

    /**
     * @test
     */
    public function address_factory_can_set_coordinates()
    {
        $address = Address::factory()->create([
            'latitude' => 10.7756,
            'longitude' => 106.7019,
        ]);

        $this->assertEquals(10.7756, $address->latitude);
        $this->assertEquals(106.7019, $address->longitude);
    }

    /**
     * @test
     */
    public function address_factory_can_create_multiple_addresses()
    {
        $user = User::factory()->create();
        $addresses = Address::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $addresses);
        foreach ($addresses as $address) {
            $this->assertEquals($user->id, $address->user_id);
        }
    }

    /**
     * @test
     */
    public function address_factory_associates_with_user()
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $address->user_id);
        $this->assertInstanceOf(User::class, $address->user);
    }

    /**
     * @test
     */
    public function address_factory_generates_valid_phone_number()
    {
        $address = Address::factory()->create();

        $this->assertNotEmpty($address->phone_number);
        $this->assertMatchesRegularExpression('/^[0-9+\-\s()]+$/', $address->phone_number);
    }
}
