<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Tests\TestCase;
use App\AttendeeMessage;
use App\Jobs\SendAttendeeMessage;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class MessageAttendeesTest extends TestCase
{
	use DatabaseMigrations;

    /** @test */
    public function a_promoter_can_view_the_message_form_for_their_concert()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);

		$response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

		$response->assertStatus(200);
		$response->assertViewIs('backstage.concert-messages.create');
		$this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    public function a_promoter_cannot_view_the_message_form_for_another_promoters_concert()
    {
        $user = factory(User::class)->create();
        $otherUsersConcert = factory(Concert::class)->create(['user_id' => factory(User::class)->create()]);

		$response = $this->actingAs($user)->get("/backstage/concerts/{$otherUsersConcert->id}/messages/new");

		$response->assertStatus(404);
    }

    /** @test */
    public function a_guest_cannot_view_the_message_form_for_any_concert()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertRedirect('login');
    }

    /** @test */
    public function a_promoter_can_send_a_new_message()
    {
    	$this->disableExceptionHandling();

    	Queue::fake();
        $user = factory(User::class)->create();
        $concert = \ConcertFactory::createPublished([
        	'user_id' => $user->id
    	]);

    	$response = $this->actingAs($user)->post("/backstage/concerts/{$concert->id}/messages", [
    		'subject' => 'My Subject',
    		'message' => 'My Message'
		]);

		$response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
		$response->assertSessionHas('flash');

		$message = AttendeeMessage::first();

		$this->assertEquals($concert->id, $message->concert_id);
		$this->assertEquals("My Subject", $message->subject);
		$this->assertEquals("My Message", $message->message);

		Queue::assertPushed(SendAttendeeMessage::class, function($job) use ($message) {
			return $job->attendeeMessage->is($message);
		});
    }

    /** @test */
    public function a_promoter_cannot_send_messages_for_another_promoters_concerts()
    {
        $user = factory(User::class)->create();
        $otherUsersConcert = \ConcertFactory::createPublished([
        	'user_id' => factory(User::class)->create()
    	]);
    	Queue::fake();

    	$response = $this->actingAs($user)->post("/backstage/concerts/{$otherUsersConcert->id}/messages", [
    		'subject' => 'My Subject',
    		'message' => 'My Message'
		]);

		$response->assertStatus(404);
		$this->assertEquals(0, AttendeeMessage::count());
		Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    /** @test */
    public function a_guest_cannot_send_messages_for_any_concert()
    {
        $otherUsersConcert = \ConcertFactory::createPublished();
        Queue::fake();

    	$response = $this->post("/backstage/concerts/{$otherUsersConcert->id}/messages", [
    		'subject' => 'My Subject',
    		'message' => 'My Message'
		]);

		$response->assertRedirect('login');        
		$this->assertEquals(0, AttendeeMessage::count());
		Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    /** @test */
    public function a_subject_is_required()
    {
        $user = factory(User::class)->create();
        $concert = \ConcertFactory::createPublished([
        	'user_id' => $user->id
    	]);
    	Queue::fake();

    	$response = $this->actingAs($user)
    					 ->from("/backstage/concerts/{$concert->id}/messages/new")
						 ->post("/backstage/concerts/{$concert->id}/messages", [
							'message' => 'My Message'
		]);

		$response->assertStatus(302);
    	$response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
    	$this->assertEquals(0, AttendeeMessage::count());
    	$response->assertSessionHasErrors('subject');
    	Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    /** @test */
    public function a_message_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
        	'user_id' => $user->id
    	]);
    	Queue::fake();

    	$response = $this->actingAs($user)
    				     ->from("/backstage/concerts/{$concert->id}/messages/new")
    				     ->post("/backstage/concerts/{$concert->id}/messages", [
    				     	'subject' => 'My Subject'
     	]);

	    $response->assertStatus(302);
	    $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
	    $this->assertEquals(0, AttendeeMessage::count());
	    $response->assertSessionHasErrors('message');
	    Queue::assertNotPushed(SendAttendeeMessage::class);
    }
}
