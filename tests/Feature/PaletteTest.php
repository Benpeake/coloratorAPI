<?php

namespace Tests\Feature;

use App\Models\Palette;
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
                        ])
                            ->whereAllType([
                                'id' => 'integer',
                                'name' => 'string',
                                'hex_colors' => 'array',
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
                        ])
                            ->whereAllType([
                                'id' => 'integer',
                                'name' => 'string',
                                'hex_colors' => 'array',
                            ]);
                    });
            });
    }

    public function test_addPalette_ValidData(): void
    {
        $response = $this->postJson('/api/palettes/', [
            'name' => 'palette',
            'hex_colors' => ['#C2DB3C', '#461ABD'],
        ]);

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->hasAll('message');
            });

        $this->assertDatabaseHas('palettes', [
            'name' => 'palette',
            'hex_colors' => '["#C2DB3C","#461ABD"]',
        ]);
    }

    public function test_addPalette_InvalidData(): void
    {
        $response = $this->postJson('/api/palettes/', [
            'name' => 'palette with name longer than fourteen characters',
            'hex_colors' => 1,
        ]);

        $response->assertStatus(422)
            ->assertInvalid([
                'name',
                'hex_colors',
            ]);
    }

    public function test_addPalette_NoData(): void
    {
        $response = $this->postJson('/api/palettes/', [
            'name' => '',
            'hex_colors' => '',
        ]);

        $response->assertStatus(422)
            ->assertInvalid([
                'name',
                'hex_colors',
            ]);
    }

    public function test_softDeletePalette(): void
    {
        $palette = Palette::factory()->create();
        $response = $this->putJson('api/palettes/delete/'.$palette->id);

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->hasAll('message');
            });

        $this->assertSoftDeleted('palettes', [
            'id' => $palette->id,
        ]);
    }
}
