<?php

namespace Tests\Feature;

use App\Exceptions\ServerException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Define some temporary routes to test exceptions
        Route::get('/_test_error/validation', function () {
            throw \Illuminate\Validation\ValidationException::withMessages(['field' => ['Invalid field']]);
        });

        Route::get('/_test_error/server', function () {
            throw new ServerException('Secret DB error', 'Custom server error occurred');
        });

        Route::get('/_test_error/unexpected', function () {
            throw new \Exception('Unexpected runtime exception');
        });

        Route::post('/api/_test_error/malformed_json', function (\Illuminate\Http\Request $request) {
            if ($request->isJson() && json_decode($request->getContent()) === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Invalid JSON payload');
            }
            return response()->json(['status' => true]);
        });
    }

    public function test_validation_error_format()
    {
        $response = $this->getJson('/_test_error/validation');

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'status',
                     'error' => [
                         'type',
                         'message',
                         'details',
                         'reference_id'
                     ]
                 ]);
                 
        $this->assertEquals('validation_error', $response->json('error.type'));
        $this->assertFalse($response->json('status'));
    }

    public function test_custom_server_exception_format()
    {
        $response = $this->getJson('/_test_error/server');

        $response->assertStatus(500)
                 ->assertJsonStructure([
                     'status',
                     'error' => [
                         'type',
                         'message',
                         'reference_id'
                     ]
                 ]);

        $this->assertEquals('server_error', $response->json('error.type'));
        $this->assertEquals('Custom server error occurred', $response->json('error.message'));
    }

    public function test_unexpected_exception_format()
    {
        $response = $this->getJson('/_test_error/unexpected');

        $response->assertStatus(500)
                 ->assertJsonStructure([
                     'status',
                     'error' => [
                         'type',
                         'message',
                         'reference_id'
                     ]
                 ]);

        $this->assertEquals('server_error', $response->json('error.type'));
        $this->assertEquals(__('errors.server_error'), $response->json('error.message'));
    }

    public function test_malformed_json_request()
    {
        $response = $this->call('POST', '/api/_test_error/malformed_json', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], '{invalid_json:');

        $response->assertStatus(400)
                 ->assertJsonStructure([
                     'status',
                     'error' => [
                         'type',
                         'message'
                     ]
                 ]);

        $this->assertEquals('invalid_json', $response->json('error.type'));
        $this->assertEquals(__('errors.invalid_json'), $response->json('error.message'));
    }

    public function test_correlation_id_middleware()
    {
        $response = $this->getJson('/_test_error/validation');
        
        $this->assertNotNull($response->headers->get('X-Correlation-ID'));
        $this->assertNotNull($response->json('error.reference_id'));
        $this->assertEquals($response->headers->get('X-Correlation-ID'), $response->json('error.reference_id'));
    }

    public function test_service_not_configured_returns_html_even_on_api()
    {
        // Setup the API route temporarily
        Route::get('/api/_test_error_not_configured', function () {
            throw new \App\Exceptions\ServiceNotConfiguredException('This service is not configured properly.');
        });

        $response = $this->getJson('/api/_test_error_not_configured');

        $response->assertStatus(503);
        $response->assertHeader('Content-Type', 'text/html; charset=utf-8');
        $response->assertSee('Service Not Configured');
    }
}
