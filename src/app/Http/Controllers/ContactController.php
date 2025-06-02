<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function show()
    {
        return Inertia::render('Contact');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        // TODO: Implement email sending logic
        // For now, we'll just log the contact request
        Log::info('Contact form submission', $request->only('name', 'email', 'subject', 'message'));

        return back()->with('success', 'Thank you for your message. We will get back to you soon!');
    }
} 