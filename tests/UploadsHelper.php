<?php

namespace Froala\NovaFroalaField\Tests;

use function Froala\NovaFroalaField\nova_version_at_least;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

trait UploadsHelper
{
    protected $file;

    protected $draftId;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake(TestCase::DISK);

        $this->draftId = Str::uuid();

        $this->regenerateUpload();
    }

    protected function regenerateUpload()
    {
        $this->file = UploadedFile::fake()->image('picture'.random_int(1, 100).'.jpg');
    }

    protected function uploadPendingFile(): TestResponse
    {
        $url = config('nova.froala-field.attachments_driver') === 'trix'
            ? '/nova-api/articles/trix-attachment/content'
            : 'nova-vendor/froala-field/articles/attachments/content';

        return $this->json('POST', $url, [
            'draftId' => $this->draftId,
            'attachment' => $this->file,
        ]);
    }

    protected function storeArticle(): TestResponse
    {
        return $this->json('POST', 'nova-api/articles', [
            'title' => 'Some title',
            'content' => 'Some content',
            'contentDraftId' => $this->draftId,
        ]);
    }

    protected function getAttachmentLocation($preserveFilename = false): string
    {
        $filename = $preserveFilename ? $this->file->getClientOriginalName() : $this->file->hashName();

        return nova_version_at_least('2.7.0') ? rtrim(TestCase::PATH, '/').'/'.$filename : $filename;
    }
}
