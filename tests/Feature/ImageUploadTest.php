<?php

namespace Tests\Feature;

use App\Services\RabbitMQService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    public function test_upload_page_can_be_rendered(): void
    {
        $this->get('/upload')
            ->assertOk()
            ->assertSee('Upload Gambar');
    }

    public function test_image_upload_saves_file_and_publishes_event(): void
    {
        Storage::fake('public');

        $this->mock(RabbitMQService::class, function (MockInterface $mock) {
            $mock->shouldReceive('publishImageUpload')
                ->once()
                ->andReturn(true);
        });

        $file = UploadedFile::fake()->image('test_banner.jpg', 600, 400);

        $response = $this->post('/upload', [
            'image' => $file,
        ]);

        $response->assertRedirect('/upload');
        $response->assertSessionHas('status', 'Gambar berhasil di-upload!');
        $response->assertSessionHas('uploaded_image');

        $uploadedImage = session('uploaded_image');
        $this->assertEquals('test_banner.jpg', $uploadedImage['original_name']);
        $this->assertEquals('Terkirim ke RabbitMQ', $uploadedImage['mq_status']);

        Storage::disk('public')->assertExists('uploads/'.$file->hashName());
    }

    public function test_image_upload_validation_fails_for_non_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->post('/upload', [
            'image' => $file,
        ]);

        $response->assertSessionHasErrors('image');
    }
}
