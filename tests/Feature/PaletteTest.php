<?php

namespace Tests\Feature;

use App\Models\Palette;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PaletteTest extends TestCase
{
    use DatabaseMigrations;

    public function test_getAllPalettes(): void
    {
        Palette::factory()->create();
        $response = $this->getJson('/api/palettes/all');

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->hasAll(['data', 'message'])
                    ->has('data', 1, function (AssertableJson $json) {
                        $json->hasAll([
                            'id',
                            'name',
                            'hex_colors',
                            'public',
                            'likes',
                            'user_id',
                        ])
                            ->whereAllType([
                                'id' => 'integer',
                                'name' => 'string',
                                'hex_colors' => 'array',
                                'public' => ['boolean', 'integer'],
                                'likes' => 'integer',
                                'user_id' => 'integer',
                            ]);
                    });
            });
    }

    public function test_getAllPalettes_WithSearch(): void
    {
        $palette1 = Palette::factory(['name' => 'palette 1'])->create();
        $palette2 = Palette::factory(['name' => 'palette 2'])->create();

        $response = $this->getJson('/api/palettes/all?search='.$palette1->name);

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->hasAll(['data', 'message'])
                    ->has('data', 1, function (AssertableJson $json) {
                        $json->hasAll([
                            'id',
                            'name',
                            'hex_colors',
                            'public',
                            'likes',
                            'user_id',
                        ])
                            ->whereAllType([
                                'id' => 'integer',
                                'name' => 'string',
                                'hex_colors' => 'array',
                                'public' => ['boolean', 'integer'],
                                'likes' => 'integer',
                                'user_id' => 'integer',
                            ]);
                    });
            });
    }

    public function test_getAllPalettes_sortByLikes(): void
    {
        Palette::factory(['name' => 'palette 1', 'likes' => 1])->create();
        Palette::factory(['name' => 'palette 2', 'likes' => 2])->create();
        Palette::factory(['name' => 'palette 3', 'likes' => 3])->create();

        $response = $this->getJson('/api/palettes/all?order_by=most_likes');

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($response) {
                $json->hasAll(['data', 'message'])
                    ->has('data', 3, function (AssertableJson $json) {
                        $json->hasAll([
                            'id',
                            'name',
                            'hex_colors',
                            'public',
                            'likes',
                            'user_id',
                        ])
                            ->whereAllType([
                                'id' => 'integer',
                                'name' => 'string',
                                'hex_colors' => 'array',
                                'public' => ['boolean', 'integer'],
                                'likes' => 'integer',
                                'user_id' => 'integer',
                            ]);
                    });

                $paletteData = $response->json('data');
                $this->assertTrue($paletteData[0]['likes'] >= $paletteData[1]['likes']);
            });
    }

    public function test_getAllUsersPalettes_success(): void
    {
        $user = User::factory()->create();

        // Authenticate the user
        $this->actingAs($user);

        Palette::factory(['user_id' => $user->id, 'name' => 'Palette 1'])->create();
        Palette::factory(['user_id' => $user->id, 'name' => 'Palette 2'])->create();

        $response = $this->getJson('/api/palettes');

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->hasAll(['data', 'message'])
                    ->has('data', 2, function (AssertableJson $json) {
                        $json->whereAllType([
                            'id' => 'integer',
                            'name' => 'string',
                            'hex_colors' => 'array',
                            'public' => ['boolean', 'integer'],
                            'likes' => 'integer',
                            'user_id' => 'integer',
                        ]);
                    });
            });
    }

    public function test_getAllUsersPalettes_Unauthenticated(): void
    {
        $response = $this->getJson('/api/palettes');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_getAllUsersPalettesWithSearch(): void
    {
        $user = User::factory()->create();
        Palette::factory(['user_id' => $user->id, 'name' => 'Palette1'])->create();
        Palette::factory(['user_id' => $user->id, 'name' => 'Palette2'])->create();

        // Authenticate the user
        $this->actingAs($user);

        $response = $this->getJson('/api/palettes?search=Palette1');

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->hasAll(['data', 'message'])
                    ->has('data', 1, function (AssertableJson $json) {
                        $json->whereAllType([
                            'id' => 'integer',
                            'name' => 'string',
                            'hex_colors' => 'array',
                            'public' => ['boolean', 'integer'],
                            'likes' => 'integer',
                            'user_id' => 'integer',
                        ])
                            ->where('name', 'Palette1');
                    });
            });
    }

    public function test_getAllUsersLikedPalettes_success(): void
    {
        $user = User::factory()->create();
        $palette = Palette::factory(['name' => 'Palette1'])->create();
        $user->likedPalettes()->attach($palette);
        $this->actingAs($user);

        $response = $this->getJson('/api/palettes/liked');

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($palette) {
                $json->hasAll(['data', 'message'])
                    ->has('data', 1, function (AssertableJson $json) use ($palette) {
                        $json->whereAllType([
                            'id' => 'integer',
                            'name' => 'string',
                            'hex_colors' => 'array',
                            'public' => ['boolean', 'integer'],
                            'likes' => 'integer',
                            'user_id' => 'integer',
                        ])
                            ->where('name', $palette->name);
                    });
            });
    }

    public function test_addPalette_ValidData(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->postJson('/api/palettes/', [
            'name' => 'palette',
            'hex_colors' => ['#C2DB3C', '#461ABD'],
            'user_id' => $user->id,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(201)
            ->assertJson(function (AssertableJson $json) {
                $json->hasAll('message');
            });

        $this->assertDatabaseHas('palettes', [
            'name' => 'palette',
            'hex_colors' => '["#C2DB3C","#461ABD"]',
            'public' => 1,
            'likes' => 0,
            'user_id' => $user->id,
        ]);
    }

    public function test_addPalette_invalidData(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->postJson('/api/palettes/', [
            'name' => 'palette with name longer than fourteen characters',
            'hex_colors' => [1],
            'user_id' => $user->id,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(422)
            ->assertInvalid([
                'name',
                'hex_colors',
            ]);
    }

    public function test_softDeletePalette_success(): void
    {
        $user = User::factory()->create();
        $palette = Palette::factory(['user_id' => $user->id])->create();
        $this->actingAs($user);
        $response = $this->deleteJson('api/palettes/delete/'.$palette->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Palette '.$palette->id.' removed',
            ]);

        $this->assertSoftDeleted('palettes', [
            'id' => $palette->id,
        ]);
    }

    public function test_softDeletePalette_unauthenticated(): void
    {
        $user = User::factory()->create();
        $palette = Palette::factory(['user_id' => $user->id])->create();
        $palette2 = Palette::factory()->create();
        $this->actingAs($user);
        $response = $this->deleteJson('api/palettes/delete/'. $palette2->id);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Unauthorized. You do not have permission to delete this palette.'
            ]);

    }

    public function test_softDeletePalette_invalidPaletteID(): void
    {
        $user = User::factory()->create();
        $palette = Palette::factory(['user_id' => $user->id])->create();
        $this->actingAs($user);
        $response = $this->deleteJson('api/palettes/delete/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Palette not found'
            ]);
    }

    public function testAddLikeToPalette()
    {
        $user = User::factory()->create();
        $palette = Palette::factory()->create(['likes' => 0]);
        $this->actingAs($user);
        $response = $this->put("api/palettes/like/{$palette->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Palette successfully updated',
            ]);

        $this->assertEquals(1, $palette->fresh()->likes);
        $this->assertTrue($user->likedPalettes->contains($palette));
    }

    public function testAddLikeToPalette_alreadyLiked()
    {
        $user = User::factory()->create();
        $palette = Palette::factory()->create(['likes' => 1]);
        $user->likedPalettes()->attach($palette->id);

        $this->actingAs($user);
        $response = $this->put("api/palettes/like/{$palette->id}");

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'You have already liked this palette',
            ]);

        $this->assertEquals(1, $palette->fresh()->likes);
        $this->assertTrue($user->likedPalettes->contains($palette));
    }
}
