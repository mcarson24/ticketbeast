<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\AttendeeMessage;
use App\Jobs\SendAttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SendAttendeeMessageTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_sends_the_message_to_all_concert_attendees()
    {
        Mail::fake();
        $concert = \ConcertFactory::createPublished();
        $otherConcert = \ConcertFactory::createPublished();
        $message = AttendeeMessage::create([
            'concert_id' => $concert->id,
            'subject'    => 'My Subject',
            'message'     => 'My Message'
        ]);
        $orderA = \OrderFactory::createForConcert($concert, ['email' => 'alex@example.com']);
        $orderForOtherConcert = \OrderFactory::createForConcert($otherConcert, ['email' => 'joe@example.com']);
        $orderB = \OrderFactory::createForConcert($concert, ['email' => 'sam@example.com']);
        $orderC = \OrderFactory::createForConcert($concert, ['email' => 'taylor@example.com']);

        SendAttendeeMessage::dispatch($message);

        // In 5.5, will need to use Mail::assertQueued
        Mail::assertQueued(AttendeeMessageEmail::class, function ($mail) use ($message) {
            return $mail->hasTo('alex@example.com') &&
                   $mail->attendeeMessage->is($message);
        });
        Mail::assertQueued(AttendeeMessageEmail::class, function ($mail) use ($message) {
            return $mail->hasTo('sam@example.com') &&
                   $mail->attendeeMessage->is($message);
        });
        Mail::assertQueued(AttendeeMessageEmail::class, function ($mail) use ($message) {
            return $mail->hasTo('taylor@example.com') &&
                   $mail->attendeeMessage->is($message);
        });
        Mail::assertNotQueued(AttendeeMessageEmail::class, function ($mail) use ($message) {
            return $mail->hasTo('joe@example.com');
        });
    }
}
