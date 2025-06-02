<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Support\Facades\Log;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase; // Though not strictly necessary for this controller, good practice

    #[Test]
    public function it_can_display_contact_page(): void
    {
        $response = $this->get(route('contact')); // Changed from contact.show

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Contact'));
    }

    public function it_can_submit_contact_form_with_valid_data(): void
    {
        Log::spy();

        $formData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message.',
            '_token' => csrf_token(),
        ];

        $response = $this->post(route('contact.store'), $formData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Thank you for your message. We will get back to you soon!');

        Log::shouldHaveReceived('info')->once()->withArgs(function (array $args) use ($formData) {
            if (count($args) < 2) return false;
            $message = $args[0];
            $context = $args[1];
            return $message === 'Contact form submission' &&
                   is_array($context) &&
                   isset($context['name']) && $context['name'] === $formData['name'] &&
                   isset($context['email']) && $context['email'] === $formData['email'] &&
                   isset($context['subject']) && $context['subject'] === $formData['subject'] &&
                   isset($context['message']) && $context['message'] === $formData['message'];
        });
    }


    #[Test]
    public function contact_form_submission_requires_valid_data(): void
    {
        // Changed to 'contact.store' for consistency
        $response = $this->post(route('contact.submit'), [
            'name' => '', // Invalid
            'email' => 'not-an-email', // Invalid
            'subject' => '', // Invalid
            'message' => '', // Invalid
            '_token' => csrf_token(),
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }

    #[Test]
    public function contact_form_message_has_max_length_validation(): void
    {
        // Changed to 'contact.store' for consistency
        $response = $this->post(route('contact.submit'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'Long Message Test',
            'message' => str_repeat('a', 1001), // Exceeds max:1000
            '_token' => csrf_token(),
        ]);
        $response->assertSessionHasErrors('message');
    }
} 