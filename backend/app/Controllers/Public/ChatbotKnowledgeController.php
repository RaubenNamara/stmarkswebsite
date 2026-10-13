<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\ChatbotKnowledgeService;

/**
 * Read-only feed for the website chatbot: public content from the database plus the text of
 * uploaded fee/programme PDFs. See ChatbotKnowledgeService for exactly what is (and is not) included.
 */
class ChatbotKnowledgeController extends Controller
{
    public function index(): void
    {
        // Short browser/CDN cache: fresh enough for admin edits, cheap for repeat visitors.
        header('Cache-Control: public, max-age=300');
        $this->success([
            'generated_at' => date('c'),
            'sources' => (new ChatbotKnowledgeService())->sources(),
        ]);
    }
}
