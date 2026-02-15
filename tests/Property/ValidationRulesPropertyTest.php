<?php

namespace Tests\Property;

use Tests\TestCase;
use App\Models\Plugin;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ValidationRulesPropertyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 2: Rating Value Validation
     * 
     * Validates: Requirements 1.5
     * 
     * For any review submission, if the rating value is not an integer between 1 and 5 (inclusive),
     * the system should reject the submission with a validation error.
     */
    public function test_rating_validation_rejects_invalid_values()
    {
        $user = User::factory()->create();
        
        // Test invalid rating values
        $invalidRatings = [0, -1, 6, 10, 'string', null, 1.5, 4.7];
        
        for ($i = 0; $i < 20; $i++) {
            $plugin = Plugin::factory()->create();
            $invalidRating = $invalidRatings[array_rand($invalidRatings)];
            
            $response = $this->actingAs($user)->postJson(route('reviews.store', $plugin), [
                'rating' => $invalidRating,
                'comment' => 'Test comment',
            ]);
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors('rating');
            
            // Clean up
            $plugin->forceDelete();
        }
        
        // Test valid rating values
        $validRatings = [1, 2, 3, 4, 5];
        
        foreach ($validRatings as $validRating) {
            $testUser = User::factory()->create();
            $testPlugin = Plugin::factory()->create();
            
            $response = $this->actingAs($testUser)->postJson(route('reviews.store', $testPlugin), [
                'rating' => $validRating,
                'comment' => 'Valid test comment',
            ]);
            
            // Should not have validation errors for rating
            if ($response->status() === 422) {
                $errors = $response->json('errors');
                $this->assertArrayNotHasKey('rating', $errors, 
                    "Valid rating {$validRating} was rejected");
            }
            
            // Clean up
            $testPlugin->reviews()->delete();
            $testPlugin->forceDelete();
            $testUser->delete();
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 7: File Upload Validation
     * 
     * Validates: Requirements 2.4
     * 
     * For any file upload for plugin logo, if the file does not meet requirements
     * (type: jpeg/png/jpg/svg, size: ≤2MB, dimensions: ≥200x200),
     * the upload should be rejected with a descriptive validation error.
     */
    public function test_file_upload_validation_enforces_requirements()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        for ($i = 0; $i < 20; $i++) {
            // Test invalid file types
            $invalidExtensions = ['txt', 'pdf', 'doc', 'exe', 'php'];
            $invalidExt = $invalidExtensions[array_rand($invalidExtensions)];
            
            $invalidFile = UploadedFile::fake()->create("logo.{$invalidExt}", 100);
            
            $response = $this->actingAs($user)->postJson(route('plugins.store'), [
                'name' => 'Test Plugin ' . uniqid(),
                'description' => 'Test description',
                'category_id' => $category->id,
                'version' => '1.0.0',
                'compatibility' => 'Laravel 11',
                'license_type' => 'MIT',
                'requirements' => ['php' => '8.2', 'laravel' => '11.0'],
                'logo' => $invalidFile,
            ]);
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors('logo');
        }
        
        // Test oversized file (> 2MB)
        $oversizedFile = UploadedFile::fake()->image('logo.jpg')->size(3000); // 3MB
        
        $response = $this->actingAs($user)->postJson(route('plugins.store'), [
            'name' => 'Test Plugin Oversized',
            'description' => 'Test description',
            'category_id' => $category->id,
            'version' => '1.0.0',
            'compatibility' => 'Laravel 11',
            'license_type' => 'MIT',
            'requirements' => ['php' => '8.2', 'laravel' => '11.0'],
            'logo' => $oversizedFile,
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('logo');
        
        // Test undersized dimensions (< 200x200)
        $undersizedFile = UploadedFile::fake()->image('logo.jpg', 100, 100);
        
        $response = $this->actingAs($user)->postJson(route('plugins.store'), [
            'name' => 'Test Plugin Undersized',
            'description' => 'Test description',
            'category_id' => $category->id,
            'version' => '1.0.0',
            'compatibility' => 'Laravel 11',
            'license_type' => 'MIT',
            'requirements' => ['php' => '8.2', 'laravel' => '11.0'],
            'logo' => $undersizedFile,
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('logo');
        
        // Test valid file
        $validFile = UploadedFile::fake()->image('logo.jpg', 300, 300)->size(1000); // 1MB
        
        $response = $this->actingAs($user)->postJson(route('plugins.store'), [
            'name' => 'Test Plugin Valid',
            'description' => 'Test description',
            'category_id' => $category->id,
            'version' => '1.0.0',
            'compatibility' => 'Laravel 11',
            'license_type' => 'MIT',
            'requirements' => ['php' => '8.2', 'laravel' => '11.0'],
            'logo' => $validFile,
        ]);
        
        // Should not have validation errors for logo
        if ($response->status() === 422) {
            $errors = $response->json('errors');
            $this->assertArrayNotHasKey('logo', $errors, 
                "Valid logo file was rejected");
        }
        
        // Clean up
        Plugin::whereIn('name', [
            'Test Plugin Oversized', 'Test Plugin Undersized', 'Test Plugin Valid'
        ])->forceDelete();
        $category->delete();
        $user->delete();
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 9: Validation Error Structure
     * 
     * Validates: Requirements 2.6
     * 
     * For any invalid request, the validation error response should include
     * field-specific error messages in a structured format (JSON with field names as keys).
     */
    public function test_validation_errors_have_structured_format()
    {
        $user = User::factory()->create();
        
        for ($i = 0; $i < 20; $i++) {
            // Submit invalid plugin data with multiple errors
            $response = $this->actingAs($user)->postJson(route('plugins.store'), [
                'name' => '', // Required field missing
                'description' => '', // Required field missing
                'category_id' => 99999, // Non-existent category
                'version' => '',
                'compatibility' => '',
                'license_type' => 'InvalidLicense', // Invalid enum value
                'requirements' => 'not-an-array', // Should be array
            ]);
            
            $response->assertStatus(422);
            
            // Verify response has errors key
            $response->assertJsonStructure(['message', 'errors']);
            
            $errors = $response->json('errors');
            
            // Verify errors is an object/array with field names as keys
            $this->assertIsArray($errors);
            $this->assertNotEmpty($errors);
            
            // Verify each error field has an array of messages
            foreach ($errors as $field => $messages) {
                $this->assertIsString($field, "Error key should be a string field name");
                $this->assertIsArray($messages, "Error messages should be an array");
                $this->assertNotEmpty($messages, "Error messages array should not be empty");
                
                foreach ($messages as $message) {
                    $this->assertIsString($message, "Each error message should be a string");
                    $this->assertNotEmpty($message, "Error message should not be empty");
                }
            }
            
            // Verify specific fields have errors
            $this->assertArrayHasKey('name', $errors);
            $this->assertArrayHasKey('description', $errors);
        }
        
        // Clean up
        $user->delete();
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 13: Review Comment Length Validation
     * 
     * Validates: Requirements 3.6
     * 
     * For any review submission with a comment exceeding 1000 characters,
     * the system should reject the submission with a validation error.
     */
    public function test_review_comment_length_validation()
    {
        $user = User::factory()->create();
        
        for ($i = 0; $i < 20; $i++) {
            $plugin = Plugin::factory()->create();
            // Generate comment exceeding 1000 characters
            $longComment = str_repeat('a', rand(1001, 2000));
            
            $response = $this->actingAs($user)->postJson(route('reviews.store', $plugin), [
                'rating' => rand(1, 5),
                'comment' => $longComment,
            ]);
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors('comment');
            
            // Verify error message mentions the max length
            $errors = $response->json('errors.comment');
            $this->assertIsArray($errors);
            $errorMessage = implode(' ', $errors);
            $this->assertStringContainsStringIgnoringCase('1000', $errorMessage);
            
            // Clean up
            $plugin->forceDelete();
        }
        
        // Test valid comment lengths
        $validLengths = [0, 100, 500, 999, 1000];
        
        foreach ($validLengths as $length) {
            $testUser = User::factory()->create();
            $testPlugin = Plugin::factory()->create();
            $validComment = $length > 0 ? str_repeat('a', $length) : '';
            
            $response = $this->actingAs($testUser)->postJson("/plugins/{$testPlugin->id}/reviews", [
                'rating' => rand(1, 5),
                'comment' => $validComment,
            ]);
            
            // Should not have validation errors for comment
            if ($response->status() === 422) {
                $errors = $response->json('errors');
                $this->assertArrayNotHasKey('comment', $errors, 
                    "Valid comment length {$length} was rejected");
            }
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 32: Required Field Validation
     * 
     * Validates: Requirements 7.6
     * 
     * For any request with required fields, if any required field is missing or empty,
     * the system should reject the request with a validation error specifying which fields are required.
     */
    public function test_required_fields_are_validated()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        $requiredFields = [
            'name', 'description', 'category_id', 'version', 
            'compatibility', 'license_type', 'requirements'
        ];
        
        for ($i = 0; $i < 20; $i++) {
            // Test each required field individually
            $fieldToOmit = $requiredFields[array_rand($requiredFields)];
            
            $data = [
                'name' => 'Test Plugin',
                'description' => 'Test description',
                'category_id' => $category->id,
                'version' => '1.0.0',
                'compatibility' => 'Laravel 11',
                'license_type' => 'MIT',
                'requirements' => ['php' => '8.2', 'laravel' => '11.0'],
            ];
            
            // Remove the field to test
            unset($data[$fieldToOmit]);
            
            $response = $this->actingAs($user)->postJson('/plugins', $data);
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors($fieldToOmit);
            
            // Verify error message mentions "required"
            $errors = $response->json("errors.{$fieldToOmit}");
            $this->assertIsArray($errors);
            $errorMessage = implode(' ', $errors);
            $this->assertStringContainsStringIgnoringCase('required', $errorMessage);
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 33: Type Validation
     * 
     * Validates: Requirements 7.7
     * 
     * For any request field with a specific type requirement, if the provided value
     * does not match the expected type, the system should reject the request with a type validation error.
     */
    public function test_field_types_are_validated()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        for ($i = 0; $i < 20; $i++) {
            // Test category_id with non-integer value
            $response = $this->actingAs($user)->postJson('/plugins', [
                'name' => 'Test Plugin',
                'description' => 'Test description',
                'category_id' => 'not-an-integer',
                'version' => '1.0.0',
                'compatibility' => 'Laravel 11',
                'license_type' => 'MIT',
                'requirements' => ['php' => '8.2', 'laravel' => '11.0'],
            ]);
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors('category_id');
            
            // Test requirements with non-array value
            $response = $this->actingAs($user)->postJson('/plugins', [
                'name' => 'Test Plugin 2',
                'description' => 'Test description',
                'category_id' => $category->id,
                'version' => '1.0.0',
                'compatibility' => 'Laravel 11',
                'license_type' => 'MIT',
                'requirements' => 'not-an-array',
            ]);
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors('requirements');
        }
        
        // Test review rating with non-integer
        $plugin = Plugin::factory()->create();
        
        $response = $this->actingAs($user)->postJson("/plugins/{$plugin->id}/reviews", [
            'rating' => 'not-an-integer',
            'comment' => 'Test comment',
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('rating');
    }
}
