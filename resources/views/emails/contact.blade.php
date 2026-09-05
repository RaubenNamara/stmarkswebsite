<x-mail::message>
# New Contact Message

You have received a new contact message from the website.

## Sender Information

**Name:** {{ $contact['name'] }}

**Email:** {{ $contact['email'] }}

@if($contact['telephone'])
**Telephone:** {{ $contact['telephone'] }}
@endif

## Message

{{ $contact['message'] }}

---

This message was sent from the St Marks College Namagoma website contact form.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
