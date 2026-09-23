<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class UploadServeTest extends TestCase
{
    public function test_serve_upload_returns_file_when_exists(): void
    {
        $testDir = public_path('uploads/ppdb_payments');
        if (!File::isDirectory($testDir)) {
            File::makeDirectory($testDir, 0755, true);
        }

        $testFile = $testDir . '/test_sample_proof.jpg';
        file_put_contents($testFile, 'fake-image-content');

        $response = $this->get('/uploads/ppdb_payments/test_sample_proof.jpg');
        $response->assertStatus(200);

        @unlink($testFile);
    }

    public function test_serve_upload_aborts_on_invalid_folder(): void
    {
        $response = $this->get('/uploads/secret_folder/sample.jpg');
        $response->assertStatus(404);
    }

    public function test_sync_uploads_route_renders_successfully(): void
    {
        $response = $this->get('/sync-uploads');
        $response->assertStatus(200);
        $response->assertSee('Sinkronisasi Berkas Upload PPDB');
    }
}
