<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Mail\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class ContactController extends Controller
{
    /**
     * Store a contact message from the website visitor.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        // Save the message
        Contact::create($validated);

        // Send email to multiple recipients
        try {
            Mail::to([
                'info@stmark.sc.ug',
                'robstech10@gmail.com',
                'nakimroseline@gmail.com',
            ])->send(new ContactMessage($validated));
        } catch (\Exception $e) {
            \Log::error('Email sending failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Message sent successfully.');
    }

    /**
     * Admin: List all contact messages.
     */
    public function index()
    {
        $contacts = Contact::latest()->get();

        return Inertia::render('Admin/Contacts/Index', [
            'contacts' => $contacts,
        ]);
    }

    /**
     * Admin: Mark a message as read.
     */
    public function markRead(Contact $contact)
    {
        $contact->update([
            'is_read' => true,
        ]);

        return back();
    }

    /**
     * Admin: Delete a message.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return back()->with('success', 'Message deleted.');
    }
}