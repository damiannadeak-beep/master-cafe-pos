<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorageFallbackTest extends TestCase
{
    public function test_storage_fallback_returns_logo_or_file()
    {
        $response = $this->get('/storage/test-non-existent-image.png');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));
    }
}
