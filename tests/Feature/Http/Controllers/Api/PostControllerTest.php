<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_store()
    {
        $user = \App\Models\User::factory()->create();
        $response = $this->actingAs($user, 'api')->json('POST', '/api/posts', [ //acceder a la ruta
            'title' => 'El post de prueba' //Enviamos el dato
        ]);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at']) //Verificar que se retornen estos datos
            ->assertJson(['title' => 'El post de prueba']) //Afirmar de nuevo que se guarda correctamente lo que enviamos
            ->assertStatus(201); //Comprobar el status HTTP correcto

        $this->assertDatabaseHas('posts', ['title' => 'El post de prueba']); //Confirmar que se guarde en la base de datos
    }

    public function test_validate_title()
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user, 'api')->json('POST', '/api/posts', [ //acceder a la ruta
            'title' => '' //Enviamos el dato
        ]);

        $response->assertStatus(422) //Solicitud bien hecha pero no fue posible completarla HTTP 422
            ->assertJsonValidationErrors('title');
    }

    public function test_show()
    {
        $user = \App\Models\User::factory()->create();

        $this->withoutExceptionHandling();

        $post = \App\Models\Post::factory()->create();

        $response = $this->actingAs($user, 'api')->json('GET', "/api/posts/$post->id");
        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => $post->title])
            ->assertStatus(200);
    }

    public function test_update()
    {
        $user = \App\Models\User::factory()->create();

        $this->withoutExceptionHandling();

        $post = \App\Models\Post::factory()->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "/api/posts/$post->id", [ //acceder a la ruta
            'title' => 'Nuevo' //Enviamos el dato
        ]);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at']) //Verificar que se retornen estos datos
            ->assertJson(['title' => 'Nuevo']) //Afirmar de nuevo que se guarda correctamente lo que enviamos
            ->assertStatus(200); //Comprobar el status HTTP correcto

        $this->assertDatabaseHas('posts', ['title' => 'Nuevo']); //Confirmar que se actualice en la base de datos
    }

    public function test_delete()
    {
        $user = \App\Models\User::factory()->create();

        $this->withoutExceptionHandling();

        $post = \App\Models\Post::factory()->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', "/api/posts/$post->id");

        $response->assertSee(null)
            ->assertStatus(204);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_index()
    {
        $user = \App\Models\User::factory()->create();

        $this->withoutExceptionHandling();

        $post = \App\Models\Post::factory()->count(5)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/posts');
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'created_at', 'updated_at']
            ]
        ])->assertStatus(200);
    }

    public function test_guest()
    {
        $this->json('GET', '/api/posts')->assertStatus(401);
        $this->json('POST', '/api/posts')->assertStatus(401);
        $this->json('GET', '/api/posts/1000')->assertStatus(401);
        $this->json('PUT', '/api/posts/1000')->assertStatus(401);
        $this->json('DELETE', '/api/posts/1000')->assertStatus(401);
    }
}
