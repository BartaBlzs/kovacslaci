<?php

namespace Tests\Feature;

use App\Models\PostalCode;
use App\Models\City;
use App\Models\County;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostalCodeControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test index returns all postal codes with relations
     */
    public function test_index_returns_all_postal_codes()
    {
        $county = County::factory()->create(['name' => 'Pest']);
        $city = City::factory()->create(['name' => 'Budapest', 'county_id' => $county->id]);
        
        PostalCode::factory()->create(['code' => '1011', 'city_id' => $city->id]);
        PostalCode::factory()->create(['code' => '1012', 'city_id' => $city->id]);

        $response = $this->getJson('/api/postal-codes');

        $response->assertStatus(200)
            ->assertJsonFragment(['code' => '1011'])
            ->assertJsonFragment(['code' => '1012'])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'code',
                        'city_id',
                        'city' => [
                            'id',
                            'name',
                            'county' => [
                                'id',
                                'name'
                            ]
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test index filters by postal code
     */
    public function test_index_filters_by_code()
    {
        $county = County::factory()->create();
        $city = City::factory()->create(['county_id' => $county->id]);
        
        PostalCode::factory()->create(['code' => '1011', 'city_id' => $city->id]);
        PostalCode::factory()->create(['code' => '2000', 'city_id' => $city->id]);

        $response = $this->getJson('/api/postal-codes?code=101');

        $response->assertStatus(200)
            ->assertJsonFragment(['code' => '1011'])
            ->assertJsonMissing(['code' => '2000']);
    }

    /**
     * Test index filters by city name
     */
    public function test_index_filters_by_city()
    {
        $county = County::factory()->create();
        $city1 = City::factory()->create(['name' => 'Budapest', 'county_id' => $county->id]);
        $city2 = City::factory()->create(['name' => 'Debrecen', 'county_id' => $county->id]);
        
        PostalCode::factory()->create(['code' => '1011', 'city_id' => $city1->id]);
        PostalCode::factory()->create(['code' => '4000', 'city_id' => $city2->id]);

        $response = $this->getJson('/api/postal-codes?city=Budapest');

        $response->assertStatus(200)
            ->assertJsonFragment(['code' => '1011'])
            ->assertJsonMissing(['code' => '4000']);
    }

    /**
     * Test index filters by county name
     */
    public function test_index_filters_by_county()
    {
        $county1 = County::factory()->create(['name' => 'Pest']);
        $county2 = County::factory()->create(['name' => 'Hajdú-Bihar']);
        
        $city1 = City::factory()->create(['county_id' => $county1->id]);
        $city2 = City::factory()->create(['county_id' => $county2->id]);
        
        PostalCode::factory()->create(['code' => '1011', 'city_id' => $city1->id]);
        PostalCode::factory()->create(['code' => '4000', 'city_id' => $city2->id]);

        $response = $this->getJson('/api/postal-codes?county=Pest');

        $response->assertStatus(200)
            ->assertJsonFragment(['code' => '1011'])
            ->assertJsonMissing(['code' => '4000']);
    }

    /**
     * Test store creates new postal code with authentication
     */
    public function test_store_creates_new_postal_code()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $county = County::factory()->create();
        $city = City::factory()->create(['county_id' => $county->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/postal-codes', [
            'code' => '1234',
            'city_id' => $city->id
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['code' => '1234'])
            ->assertJsonStructure([
                'id',
                'code',
                'city_id',
                'city' => [
                    'id',
                    'name',
                    'county'
                ]
            ]);

        $this->assertDatabaseHas('postal_codes', ['code' => '1234', 'city_id' => $city->id]);
    }

    /**
     * Test store requires authentication
     */
    public function test_store_requires_authentication()
    {
        $county = County::factory()->create();
        $city = City::factory()->create(['county_id' => $county->id]);

        $response = $this->postJson('/api/postal-codes', [
            'code' => '1234',
            'city_id' => $city->id
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test store validates postal code format
     */
    public function test_store_validates_code_format()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $county = County::factory()->create();
        $city = City::factory()->create(['county_id' => $county->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/postal-codes', [
            'code' => '123',  // Too short
            'city_id' => $city->id
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test store validates city_id exists
     */
    public function test_store_validates_city_exists()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/postal-codes', [
            'code' => '1234',
            'city_id' => 9999  // Non-existent city
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test show returns postal code details
     */
    public function test_show_returns_postal_code_details()
    {
        $county = County::factory()->create(['name' => 'Pest']);
        $city = City::factory()->create(['name' => 'Budapest', 'county_id' => $county->id]);
        $postalCode = PostalCode::factory()->create(['code' => '1011', 'city_id' => $city->id]);

        $response = $this->getJson("/api/postal-codes/{$postalCode->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['code' => '1011'])
            ->assertJsonFragment(['name' => 'Budapest'])
            ->assertJsonFragment(['name' => 'Pest']);
    }

    /**
     * Test show returns 404 for non-existent postal code
     */
    public function test_show_returns_404_for_missing_postal_code()
    {
        $response = $this->getJson('/api/postal-codes/9999');

        $response->assertStatus(404);
    }

    /**
     * Test update modifies existing postal code
     */
    public function test_update_modifies_existing_postal_code()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $county = County::factory()->create();
        $city1 = City::factory()->create(['county_id' => $county->id]);
        $city2 = City::factory()->create(['county_id' => $county->id]);
        
        $postalCode = PostalCode::factory()->create(['code' => '1011', 'city_id' => $city1->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/postal-codes/{$postalCode->id}", [
            'code' => '1012',
            'city_id' => $city2->id
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['code' => '1012']);

        $this->assertDatabaseHas('postal_codes', [
            'id' => $postalCode->id,
            'code' => '1012',
            'city_id' => $city2->id
        ]);
    }

    /**
     * Test update requires authentication
     */
    public function test_update_requires_authentication()
    {
        $county = County::factory()->create();
        $city = City::factory()->create(['county_id' => $county->id]);
        $postalCode = PostalCode::factory()->create(['city_id' => $city->id]);

        $response = $this->putJson("/api/postal-codes/{$postalCode->id}", [
            'code' => '1012'
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test update returns 404 for non-existent postal code
     */
    public function test_update_returns_404_for_missing_postal_code()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/postal-codes/9999', [
            'code' => '1012'
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test delete removes postal code
     */
    public function test_delete_removes_postal_code()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $county = County::factory()->create();
        $city = City::factory()->create(['county_id' => $county->id]);
        $postalCode = PostalCode::factory()->create(['city_id' => $city->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/postal-codes/{$postalCode->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Postal code deleted successfully']);

        $this->assertDatabaseMissing('postal_codes', ['id' => $postalCode->id]);
    }

    /**
     * Test delete requires authentication
     */
    public function test_delete_requires_authentication()
    {
        $county = County::factory()->create();
        $city = City::factory()->create(['county_id' => $county->id]);
        $postalCode = PostalCode::factory()->create(['city_id' => $city->id]);

        $response = $this->deleteJson("/api/postal-codes/{$postalCode->id}");

        $response->assertStatus(401);
    }

    /**
     * Test delete returns 404 for non-existent postal code
     */
    public function test_delete_returns_404_for_missing_postal_code()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/postal-codes/9999');

        $response->assertStatus(404);
    }
}