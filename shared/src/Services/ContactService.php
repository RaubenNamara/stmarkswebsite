<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Config\Config;
use StMarks\Shared\Models\Contact;

/**
 * Handles both the public /contact form submission and the admin view/mark-read/delete side.
 * A notification email is sent to CONTACT_RECIPIENTS (shared/.env) after saving - but the
 * submission itself always succeeds and is saved regardless of whether the email send works,
 * matching the old app's behavior of never failing the form on a mail error.
 */
class ContactService extends Service
{
    public function __construct(
        private Contact $model = new Contact(),
        private MailService $mailService = new MailService()
    ) {
    }

    public function submit(array $data): array
    {
        $errors = $this->validate($data, [
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email'],
            'message' => ['required'],
        ]);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $row = $this->sanitize([
            'name' => $data['name'],
            'telephone' => $data['telephone'] ?? null,
            'email' => $data['email'],
            'message' => $data['message'],
        ]);
        $row['is_read'] = 0;

        $id = $this->model->create($row);
        if ($id === false) {
            return ['ok' => false, 'errors' => ['general' => 'Failed to send message']];
        }

        $this->notifyRecipients($row);

        return ['ok' => true, 'id' => $id];
    }

    private function notifyRecipients(array $row): void
    {
        $recipients = array_filter(explode(',', (string) Config::get('CONTACT_RECIPIENTS', '')));
        if (!$recipients) {
            return;
        }

        $body = sprintf(
            '<p><strong>New contact form submission</strong></p><p><strong>Name:</strong> %s</p><p><strong>Email:</strong> %s</p><p><strong>Telephone:</strong> %s</p><p><strong>Message:</strong><br>%s</p>',
            htmlspecialchars($row['name']),
            htmlspecialchars($row['email']),
            htmlspecialchars($row['telephone'] ?? '-'),
            nl2br(htmlspecialchars($row['message']))
        );

        $this->mailService->sendToMany($recipients, 'New Contact Form Submission - St Mark\'s College', $body);
    }

    public function paginate(int $page, int $limit): array
    {
        return $this->model->paginate($page, $limit);
    }

    public function markRead(int $id): bool
    {
        return $this->model->exists($id) && $this->model->update($id, ['is_read' => 1]);
    }

    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }
}
