<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can register successfully', function () {
      $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
      ];

      $response = $this->postJson('/api/auth/register', $userData);

      $response->assertStatus(201)
            ->assertJsonStructure([
                  'success',
                  'message',
                  'data' => [
                        'user' => [
                              'id',
                              'name',
                              'email',
                              'level',
                              'xp',
                              'hp',
                              'title'
                        ],
                        'token'
                  ]
            ]);

      $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User'
      ]);
});

test('createToken method exists on User model', function () {
      $user = new User();
      expect(method_exists($user, 'createToken'))->toBeTrue();
});
