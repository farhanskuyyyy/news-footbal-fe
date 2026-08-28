<?php

namespace App\Http\Controllers;

use App\Services\RabbitMQService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function __construct(private readonly RabbitMQService $rabbitMQService) {}

    /**
     * Show the image upload form.
     */
    public function create()
    {
        return view('upload.create');
    }

    /**
     * Store uploaded image and publish event to RabbitMQ.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ], [
            'image.required' => 'Pilih gambar terlebih dahulu.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, webp, gif.',
            'image.max' => 'Ukuran gambar maksimal adalah 5MB.',
        ]);

        $file = $request->file('image');
        $path = $file->store('uploads', 'public');
        $url = Storage::disk('public')->url($path);

        $payload = [
            'filename' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'url' => $url,
            'size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
            'uploaded_at' => now()->toIso8601String(),
        ];

        $mqPublished = $this->rabbitMQService->publishImageUpload($payload);

        $message = 'Gambar berhasil di-upload!';
        if (! $mqPublished) {
            $message .= ' (Catatan: Event RabbitMQ gagal dikirim / RabbitMQ offline)';
        }

        return redirect()
            ->route('upload.create')
            ->with('status', $message)
            ->with('uploaded_image', [
                'url' => $url,
                'filename' => basename($path),
                'original_name' => $file->getClientOriginalName(),
                'size_formatted' => round($file->getSize() / 1024, 2).' KB',
                'mq_status' => $mqPublished ? 'Terkirim ke RabbitMQ' : 'Gagal terkirim ke RabbitMQ',
            ]);
    }
}
