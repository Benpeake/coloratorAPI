<?php

namespace Tests\Feature;

use App\Models\Palette;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    public function test_RegisterUser()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'secret123',
        ];
    
        $response = $this->postJson('api/users/register', $userData);
    
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User registered successfully',
                'access_token' => true,
            ]);
    
        $user = User::where('email', $userData['email'])->first();
        $this->actingAs($user);
    
        $this->assertAuthenticatedAs($user);
        
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);
    }
    

    public function test_UpdateUser_validData()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $updatedUserData = [
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com',
            'password' => 'updatedpassword',
        ];

        $response = $this->putJson('api/users/update', $updatedUserData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User update successful',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $updatedUserData['name'],
            'email' => $updatedUserData['email'],
        ]);
    }

    public function test_UpdateUser_InvalidData()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $updatedUserData = [
            'name' => 'Updated Name',
            'email' => 1,
            'password' => 'a',
        ];

        $response = $this->putJson('api/users/update', $updatedUserData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'email' => 'The email field must be a valid email address.',
                'password' => 'The password field must be at least 6 characters.',
            ]);
    }

    public function test_SoftDeleteUser()
    {
        $user = User::factory()->create();
        $palette = Palette::factory(['user_id' => $user->id])->create();
        $response = $this->actingAs($user)->deleteJson('api/users/delete/');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User deleted successfully',
            ]);

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);

        $this->assertSoftDeleted('palettes', [
            'id' => $palette->id,
        ]);
    }

    public function test_SoftDeleteUser_Unauthenticated()
    {
        $user = User::factory()->create();
        $palette = Palette::factory(['user_id' => $user->id])->create();

        $response = $this->deleteJson('api/users/delete/');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);

        $this->assertDatabaseHas('palettes', [
            'id' => $palette->id,
        ]);
    }

    public function test_LoginUser()
    {
        $user = User::factory(['password' => 'testpassword'])->create();

        $credentials = [
            'email' => $user->email,
            'password' => 'testpassword',
        ];

        $response = $this->postJson('/api/users/login', $credentials);

        $token = $response['access_token']; 

        $response->assertStatus(200)
        ->assertJson([
            'message' => 'Login successful',
            'access_token' => $token,
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_LoginUser_invalidData()
    {
        $user = User::factory(['password' => 'testpassword'])->create();

        $credentials = [
            'email' => $user->email,
            'password' => 'testpassworddd',
        ];

        $response = $this->postJson('/api/users/login', $credentials);

        $response->assertStatus(401);
        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_UserCanLogout()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/api/users/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Logout successful',
                 ]);

        $this->assertEquals(0, $user->fresh()->tokens->count());
    }
    
}
