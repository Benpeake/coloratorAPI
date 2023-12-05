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
        $response = $this->getJson('/api/palettes');

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->hasAll(['data', 'message'])
                    ->has('data', 1, function (AssertableJson $json) {
                        $json->hasAll([
                            'id',
                            'name',
                            'hex_colors',
                            'public',
                            'votes',
                            'user_id',
                        ])
                            ->whereAllType([
                                'id' => 'integer',
                                'name' => 'string',
                                'hex_colors' => 'array',
                                'public' => ['boolean', 'integer'],
                                'votes' => 'integer',
                                'user_id' => 'integer',
                            ]);
                    });
            });
    }

    public function test_getAllPalettes_WithSearch(): void
    {
        $palette1 = Palette::factory(['name' => 'palette 1'])->create();
        $palette2 = Palette::factory(['name' => 'palette 2'])->create();

        $response = $this->getJson('/api/palettes?search='.$palette1->name);

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->hasAll(['data', 'message'])
                    ->has('data', 1, function (AssertableJson $json) {
                        $json->hasAll([
                            'id',
                            'name',
                            'hex_colors',
                            'public',
                            'votes',
                            'user_id',
                        ])
                            ->whereAllType([
                                'id' => 'integer',
                                'name' => 'string',
                                'hex_colors' => 'array',
                                'public' => ['boolean', 'integer'],
                                'votes' => 'integer',
                                'user_id' => 'integer',
                            ]);
                    });
            });
    }

    public function test_getAllPalettes_sortByVotes(): void
    {
        Palette::factory(['name' => 'palette 1', 'votes' => 1])->create();
        Palette::factory(['name' => 'palette 2', 'votes' => 2])->create();
        Palette::factory(['name' => 'palette 3', 'votes' => 3])->create();
    
        $response = $this->getJson('/api/palettes?order_by=most_voted');
    
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($response) {
                $json->hasAll(['data', 'message'])
                    ->has('data', 3, function (AssertableJson $json) {
                        $json->hasAll([
                            'id',
                            'name',
                            'hex_colors',
                            'public',
                            'votes',
                            'user_id',
                        ])
                            ->whereAllType([
                                'id' => 'integer',
                                'name' => 'string',
                                'hex_colors' => 'array',
                                'public' => ['boolean', 'integer'],
                                'votes' => 'integer',
                                'user_id' => 'integer',
                            ]);
                    });
    
                $paletteData = $response->json('data');
                $this->assertTrue($paletteData[0]['votes'] >= $paletteData[1]['votes']);
            });
    }
    
    public function test_getAllPalettesByUserID_success(): void
    {
        $user = User::factory()->create();
        Palette::factory(['user_id' => $user->id, 'name' => 'Palette 1'])->create();
        Palette::factory(['user_id' => $user->id, 'name' => 'Palette 2'])->create();

        $response = $this->getJson("/api/palettes/{$user->id}");

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->hasAll(['data', 'message'])
                    ->has('data', 2, function (AssertableJson $json) {
                        $json->whereAllType([
                            'id' => 'integer',
                            'name' => 'string',
                            'hex_colors' => 'array',
                            'public' => ['boolean', 'integer'],
                            'votes' => 'integer',
                            'user_id' => 'integer',
                        ]);
                    });
            });
    }

    public function test_getAllPalettesByUserID_invalidUserID(): void
    {
        $response = $this->getJson("/api/palettes/999"); 

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Invalid user ID',
            ]);
    }


    public function test_getAllPalettesByUserID_withSearch(): void
    {
        $user = User::factory()->create();
        Palette::factory(['user_id' => $user->id, 'name' => 'Palette1'])->create();
        Palette::factory(['user_id' => $user->id, 'name' => 'Palette2'])->create();
    
        $response = $this->getJson("/api/palettes/{$user->id}?search=Palette1");
    
        $response->assertStatus(200)
        ->assertJson(function (AssertableJson $json) {
            $json->hasAll(['data', 'message'])
                ->has('data', 1, function (AssertableJson $json) {
                    $json->whereAllType([
                        'id' => 'integer',
                        'name' => 'string',
                        'hex_colors' => 'array',
                        'public' => ['boolean', 'integer'],
                        'votes' => 'integer',
                        'user_id' => 'integer',
                    ])
                    ->where('name', 'Palette1');
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
            'Authorization' => 'Bearer ' . $token,
        ]);
            
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->hasAll('message');
            });
    
        $this->assertDatabaseHas('palettes', [
            'name' => 'palette',
            'hex_colors' => '["#C2DB3C","#461ABD"]',
            'public' => 1,
            'votes' => 0,
            'user_id' => $user->id,
        ]);
    }
    
    

    // public function test_addPalette_InvalidData(): void
    // {
    //     $response = $this->postJson('/api/palettes/', [
    //         'name' => 'palette with name longer than fourteen characters',
    //         'hex_colors' => 1,
    //     ]);

    //     $response->assertStatus(422)
    //         ->assertInvalid([
    //             'name',
    //             'hex_colors',
    //         ]);
    // }

    // public function test_addPalette_NoData(): void
    // {
    //     $response = $this->postJson('/api/palettes/', [
    //         'name' => '',
    //         'hex_colors' => '',
    //     ]);

    //     $response->assertStatus(422)
    //         ->assertInvalid([
    //             'name',
    //             'hex_colors',
    //         ]);
    // }

    // public function test_softDeletePalette(): void
    // {
    //     $palette = Palette::factory()->create();
    //     $response = $this->putJson('api/palettes/delete/'.$palette->id);

    //     $response->assertStatus(200)
    //         ->assertJson(function (AssertableJson $json) {
    //             $json->hasAll('message');
    //         });

    //     $this->assertSoftDeleted('palettes', [
    //         'id' => $palette->id,
    //     ]);
    // }
}
