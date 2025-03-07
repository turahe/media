<?php

namespace Turahe\Media\Tests;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Turahe\Media\Models\Media as BaseMedia;
use Turahe\Media\Tests\Models\Media;
use Turahe\Media\Tests\Models\Subject;

class HasMediaTest extends TestCase
{
    use RefreshDatabase;

    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = Subject::create();
    }

    public function test_registers_the_media_relationship()
    {
        $this->assertInstanceOf(MorphToMany::class, $this->subject->media());
    }

    public function test_can_attach_media_to_the_default_group()
    {
        $media = Media::factory()->create();

        $this->subject->attachMedia($media);

        $attachedMedia = $this->subject->media()->first();

        $this->assertEquals($attachedMedia->id, $media->id);
        $this->assertEquals('default', $attachedMedia->pivot->group);
    }

    public function test_can_attach_media_to_a_named_group()
    {
        $media = Media::factory()->create();

        $this->subject->attachMedia($media, $group = 'custom');

        $attachedMedia = $this->subject->media()->first();

        $this->assertEquals($media->id, $attachedMedia->id);
        $this->assertEquals($group, $attachedMedia->pivot->group);
    }

    public function test_can_attach_a_collection_of_media()
    {
        $media = Media::factory(2)->create();

        $this->subject->attachMedia($media);

        $attachedMedia = $this->subject->media()->get();

        $this->assertCount(2, $attachedMedia);
        $this->assertEmpty($media->diff($attachedMedia));

        $attachedMedia->each(
            function ($media) {
                $this->assertEquals('default', $media->pivot->group);
            }
        );
    }

    public function test_can_get_all_the_media_in_the_default_group()
    {
        $media = Media::factory(2)->create();

        $this->subject->attachMedia($media);

        $defaultMedia = $this->subject->getMedia();

        $this->assertEquals(2, $defaultMedia->count());
        $this->assertEmpty($media->diff($defaultMedia));
    }

    public function test_can_get_all_the_media_in_a_specified_group()
    {
        $media = Media::factory(2)->create();

        $this->subject->attachMedia($media, 'gallery');

        $galleryMedia = $this->subject->getMedia('gallery');

        $this->assertEquals(2, $galleryMedia->count());
        $this->assertEmpty($media->diff($galleryMedia));
    }

    public function test_can_handle_attempts_to_get_media_from_an_empty_group()
    {
        $media = $this->subject->getMedia();

        $this->assertInstanceOf(EloquentCollection::class, $media);
        $this->assertTrue($media->isEmpty());
    }

    public function test_can_get_the_first_media_item_in_the_default_group()
    {
        $media = Media::factory()->create();

        $this->subject->attachMedia($media);

        $firstMedia = $this->subject->getFirstMedia();

        $this->assertInstanceOf(BaseMedia::class, $firstMedia);
        $this->assertEquals($media->id, $firstMedia->id);
    }

    public function test_can_get_the_first_media_item_in_a_specified_group()
    {
        $media = Media::factory()->create();

        $this->subject->attachMedia($media, 'gallery');

        $firstMedia = $this->subject->getFirstMedia('gallery');

        $this->assertInstanceOf(BaseMedia::class, $firstMedia);
        $this->assertEquals($media->id, $firstMedia->id);
    }

    public function test_will_only_get_media_in_the_specified_group()
    {
        $defaultMedia = Media::factory()->create();
        $galleryMedia = Media::factory()->create();

        // Attach media to the default group...
        $this->subject->attachMedia($defaultMedia->id);

        // Attach media to the gallery group...
        $this->subject->attachMedia($galleryMedia->id, 'gallery');

        $allDefaultMedia = $this->subject->getMedia();
        $allGalleryMedia = $this->subject->getMedia('gallery');
        $firstGalleryMedia = $this->subject->getFirstMedia('gallery');

        $this->assertCount(1, $allDefaultMedia);
        $this->assertEquals($defaultMedia->getKey(), $allDefaultMedia->first()->id);

        $this->assertCount(1, $allGalleryMedia);
        $this->assertEquals($galleryMedia->getKey(), $allGalleryMedia->first()->id);
        $this->assertEquals($galleryMedia->getKey(), $firstGalleryMedia->id);
    }

    public function test_can_get_the_url_of_the_first_media_item_in_the_default_group()
    {
        $media = Media::factory()->create();

        $this->subject->attachMedia($media);

        $url = $this->subject->getFirstMediaUrl();

        $this->assertEquals($media->getUrl(), $url);
    }

    public function test_can_get_the_url_of_the_first_media_item_in_a_specified_group()
    {
        $media = Media::factory()->create();

        $this->subject->attachMedia($media, 'gallery');

        $url = $this->subject->getFirstMediaUrl('gallery');

        $this->assertEquals($media->getUrl(), $url);
    }

    public function test_can_get_the_converted_image_url_of_the_first_media_item_in_a_specified_group()
    {
        $media = Media::factory()->create();

        $this->subject->attachMedia($media, 'gallery');

        $url = $this->subject->getFirstMediaUrl('gallery', 'conversion-name');

        $this->assertEquals($media->getUrl('conversion-name'), $url);
    }

    public function test_can_determine_if_there_is_media_in_the_default_group()
    {
        $media = Media::factory()->create();

        $this->subject->attachMedia($media);

        $this->assertTrue($this->subject->hasMedia());
        $this->assertFalse($this->subject->hasMedia('empty'));
    }

    public function test_can_determine_if_there_is_media_in_a_specified_group()
    {
        $media = Media::factory()->create();

        $this->subject->attachMedia($media, 'gallery');

        $this->assertTrue($this->subject->hasMedia('gallery'));
        $this->assertFalse($this->subject->hasMedia());
    }

    public function test_can_detach_all_the_media()
    {
        $mediaOne = Media::factory()->create();
        $mediaTwo = Media::factory()->create();

        $this->subject->attachMedia($mediaOne);
        $this->subject->attachMedia($mediaTwo, 'gallery');

        $this->subject->detachMedia();

        $this->assertFalse($this->subject->media()->exists());
    }

    public function test_can_detach_specific_media_items()
    {
        $mediaOne = Media::factory()->create();
        $mediaTwo = Media::factory()->create();

        $this->subject->attachMedia([
            $mediaOne->id, $mediaTwo->id,
        ]);

        $this->subject->detachMedia($mediaOne);

        $this->assertCount(1, $this->subject->getMedia());
        $this->assertEquals($mediaTwo->id, $this->subject->getFirstMedia()->id);
    }

    public function test_can_detach_all_the_media_in_a_specified_group()
    {
        $mediaOne = Media::factory()->create();
        $mediaTwo = Media::factory()->create();

        $this->subject->attachMedia($mediaOne, 'one');
        $this->subject->attachMedia($mediaTwo, 'two');

        $this->subject->clearMediaGroup('one');

        $this->assertFalse($this->subject->hasMedia('one'));
        $this->assertCount(1, $this->subject->getMedia('two'));
        $this->assertEquals($mediaTwo->id, $this->subject->getFirstMedia('two')->id);
    }
}
