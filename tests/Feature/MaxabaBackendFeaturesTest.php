<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Amenity;
use App\Models\Property;
use App\Models\ReportedProperty;
use App\Models\RoommateRequest;
use App\Mail\VerificationTokenMail;
use App\Mail\RegistrationReminderMail;
use App\Notifications\MissedCallNotification;
use App\Notifications\NewMessageNotification;
use App\Notifications\PropertyLikedNotification;
use App\Notifications\NewFollowerNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaxabaBackendFeaturesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_strips_commas_from_property_price_and_enforces_word_limits()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Apartment', 'image' => 'http://example.com/image.png']);

        // 1. Test stripping commas and correct input
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/properties', [
            'occupant_type' => 'single',
            'category_id' => $category->id,
            'location' => json_encode(['lat' => 6.5, 'lng' => 3.3, 'address' => 'Lagos']),
            'title' => 'Short Beautiful Title',
            'price' => '1,500,000.50',
            'description' => 'A very short description that is well under three hundred words limit.',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'kitchens' => 1,
            'livingrooms' => 1,
            'amenities' => ['Wifi', 'AC'],
            'files' => [UploadedFile::fake()->image('property.jpg')],
            'offer_type' => 'rent',
            'charges' => [
                'agent_percentage' => 10,
                'caution_percentage' => 5,
                'legal_percentage' => 5
            ]
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('properties', [
            'title' => 'Short Beautiful Title',
            'price' => 1500000.50,
        ]);

        // 2. Test title exceeding 30 words validation
        $longTitle = implode(' ', array_fill(0, 35, 'word'));
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/properties', [
            'occupant_type' => 'single',
            'category_id' => $category->id,
            'location' => json_encode(['lat' => 6.5, 'lng' => 3.3]),
            'title' => $longTitle,
            'price' => '50000',
            'description' => 'Short description',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'kitchens' => 1,
            'livingrooms' => 1,
            'amenities' => ['Wifi'],
            'files' => [UploadedFile::fake()->image('property.jpg')],
            'offer_type' => 'sale',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);

        // 3. Test description exceeding 300 words validation
        $longDescription = implode(' ', array_fill(0, 310, 'word'));
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/properties', [
            'occupant_type' => 'single',
            'category_id' => $category->id,
            'location' => json_encode(['lat' => 6.5, 'lng' => 3.3]),
            'title' => 'Short Title',
            'price' => '50000',
            'description' => $longDescription,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'kitchens' => 1,
            'livingrooms' => 1,
            'amenities' => ['Wifi'],
            'files' => [UploadedFile::fake()->image('property.jpg')],
            'offer_type' => 'sale',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['description']);
    }

    /** @test */
    public function it_can_report_a_property()
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $category = Category::create(['name' => 'Apartment', 'image' => 'http://example.com/image.png']);
        
        $property = Property::create([
            'title' => 'Sample Property',
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'offer_type' => 'rent',
            'occupant_type' => 'single',
            'location' => ['lat' => 6.5, 'lng' => 3.3, 'address' => 'Lagos'],
            'price' => 120000.00,
            'description' => 'A nice place to live.',
            'images' => ['properties/sample.jpg'],
            'bedrooms' => 2,
            'bathrooms' => 2,
            'kitchens' => 1,
            'livingrooms' => 1,
            'amenities' => ['WiFi'],
            'status' => \App\Enums\Status::PENDING,
            'published' => false,
            'verified' => false,
            'rejection_reason' => '',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/properties/{$property->id}/report", [
            'reason' => 'Fraudulent listing description',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reported_properties', [
            'user_id' => $user->id,
            'property_id' => $property->id,
            'reason' => 'Fraudulent listing description',
        ]);
    }

    /** @test */
    public function it_persists_notifications_on_retrieval_and_correctly_counts_them()
    {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        $category = Category::create(['name' => 'Apartment', 'image' => 'http://example.com/image.png']);
        
        $property = Property::create([
            'title' => 'Sample Property',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'offer_type' => 'rent',
            'occupant_type' => 'single',
            'location' => ['lat' => 6.5, 'lng' => 3.3, 'address' => 'Lagos'],
            'price' => 120000.00,
            'description' => 'A nice place to live.',
            'images' => ['properties/sample.jpg'],
            'bedrooms' => 2,
            'bathrooms' => 2,
            'kitchens' => 1,
            'livingrooms' => 1,
            'amenities' => ['WiFi'],
            'status' => \App\Enums\Status::PENDING,
            'published' => false,
            'verified' => false,
            'rejection_reason' => '',
        ]);

        // Send a PropertyLikedNotification to user
        $user->notify(new PropertyLikedNotification($sender, $property));

        // Ensure notification is in DB
        $this->assertEquals(1, $user->unreadNotifications()->count());

        // Call notification index (this marks them as read, but DOES NOT delete them)
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/notifications');
        $response->assertStatus(200);

        // Ensure notification is marked read, but still exists in the DB
        $this->assertEquals(0, $user->unreadNotifications()->count());
        $this->assertEquals(1, $user->notifications()->count());
    }

    /** @test */
    public function it_dispatches_missed_call_notification_when_offline_or_busy()
    {
        Notification::fake();

        $caller = User::factory()->create();
        $receiver = User::factory()->create(); // offline by default

        // Call initiate to offline user
        $response = $this->actingAs($caller, 'sanctum')->postJson('/api/calls/initiate', [
            'receiver_id' => $receiver->id,
            'call_type' => 'voice',
        ]);

        $response->assertStatus(400);

        Notification::assertSentTo(
            $receiver,
            MissedCallNotification::class,
            function ($notification) use ($caller) {
                return $notification->user->id === $caller->id;
            }
        );
    }

    /** @test */
    public function it_has_a_queueable_otp_email()
    {
        Mail::fake();

        $email = 'test@example.com';
        $token = '123456';

        Mail::to($email)->send(new VerificationTokenMail($token));

        Mail::assertQueued(VerificationTokenMail::class, function ($mail) use ($token) {
            return $mail->token === $token;
        });
    }

    /** @test */
    public function it_sends_registration_reminders_via_artisan_command()
    {
        Mail::fake();

        // 1. User created >24h ago with incomplete profile
        $incompleteUser = User::factory()->create([
            'first_name' => null,
            'last_name' => null,
            'created_at' => now()->subHours(25),
            'registration_reminder_sent' => false,
        ]);

        // 2. User created <24h ago with incomplete profile
        $recentIncompleteUser = User::factory()->create([
            'first_name' => null,
            'last_name' => null,
            'created_at' => now()->subHours(10),
            'registration_reminder_sent' => false,
        ]);

        // 3. User created >24h ago with completed profile
        $completedUser = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'created_at' => now()->subHours(25),
            'registration_reminder_sent' => false,
        ]);

        // Run the artisan command
        $this->artisan('app:send-registration-reminder')->assertExitCode(0);

        // Assert reminder mail was queued for incompleteUser
        Mail::assertQueued(RegistrationReminderMail::class, function ($mail) use ($incompleteUser) {
            return $mail->hasTo($incompleteUser->email);
        });

        // Assert reminder mail was NOT queued for others
        Mail::assertNotQueued(RegistrationReminderMail::class, function ($mail) use ($recentIncompleteUser, $completedUser) {
            return $mail->hasTo($recentIncompleteUser->email) || $mail->hasTo($completedUser->email);
        });

        // Assert incompleteUser marked as sent in database
        $this->assertTrue((bool) $incompleteUser->fresh()->registration_reminder_sent);
    }

    /** @test */
    public function it_handles_amenities_and_categories_crud_actions()
    {
        Storage::fake('public');
        $admin = \App\Models\Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@maxaba.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // 1. Create Category with uploaded file
        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/admin/categories', [
            'name' => 'Villa',
            'image' => UploadedFile::fake()->image('villa.jpg'),
        ]);
        $response->assertStatus(200);
        $categoryId = $response->json('data.id');
        $this->assertDatabaseHas('categories', ['name' => 'Villa']);

        // 2. Update Category with another image upload
        $response = $this->actingAs($admin, 'sanctum')->postJson("/api/admin/categories/{$categoryId}", [
            '_method' => 'PUT',
            'name' => 'Mansion',
            'image' => UploadedFile::fake()->image('mansion.jpg'),
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('categories', ['name' => 'Mansion']);

        // 3. Delete Category
        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/admin/categories/{$categoryId}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', ['id' => $categoryId]);

        // 4. Create Amenity with file
        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/admin/amenities', [
            'name' => 'Swimming Pool',
            'image' => UploadedFile::fake()->image('pool.jpg'),
        ]);
        $response->assertStatus(200);
        $amenityId = $response->json('data.id');
        $this->assertDatabaseHas('amenities', ['name' => 'Swimming Pool']);

        // 5. Update Amenity
        $response = $this->actingAs($admin, 'sanctum')->postJson("/api/admin/amenities/{$amenityId}", [
            '_method' => 'PUT',
            'name' => 'Heated Pool',
            'image' => UploadedFile::fake()->image('heated_pool.jpg'),
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('amenities', ['name' => 'Heated Pool']);

        // 6. Delete Amenity
        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/admin/amenities/{$amenityId}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('amenities', ['id' => $amenityId]);
    }

    /** @test */
    public function it_sends_new_message_notification_with_inbox_action_url()
    {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        $message = new \App\Models\Message([
            'user_id' => $sender->id,
            'content' => 'Hello there!',
            'type' => 'text',
        ]);

        $notification = new NewMessageNotification($sender, $message);
        $mailData = $notification->toMail($user);

        $this->assertEquals('New Message Received', $mailData->subject);
        $this->assertEquals(rtrim(config('app.frontend_url'), '/') . '/inbox', $mailData->actionUrl);
    }
}

