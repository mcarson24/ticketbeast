<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\ProcessPosterImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessPosterImageTest extends TestCase
{
	use RefreshDatabase;

    /** @test */
    public function it_resizes_poster_image_to_600px_wide()
    {
    	Storage::fake('public');

    	$concert = \ConcertFactory::createUnpublished([
    		'poster_image_path' => 'posters/example-poster.png'
    	]);

    	Storage::disk('public')->put(
    		'posters/example-poster.png',
    		file_get_contents(base_path('tests/__fixtures__/full-size-poster.png'))
    	);

        ProcessPosterImage::dispatch($concert);

        $resizedImage = Storage::disk('public')->get('posters/example-poster.png');
        list($width, $height) = getimagesizefromstring($resizedImage);

        $this->assertEquals(600, $width);
        $this->assertEquals(776, $height);
    }
}
