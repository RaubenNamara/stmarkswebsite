<?php

declare(strict_types=1);

use StMarks\PublicSite\Controllers\CampusVoiceController;
use StMarks\PublicSite\Controllers\ClubController;
use StMarks\PublicSite\Controllers\ContactController;
use StMarks\PublicSite\Controllers\FeeStructureController;
use StMarks\PublicSite\Controllers\JobApplicationController;
use StMarks\PublicSite\Controllers\NewsController;
use StMarks\PublicSite\Controllers\PagesController;
use StMarks\PublicSite\Controllers\PageViewController;
use StMarks\PublicSite\Controllers\PerformanceController;
use StMarks\PublicSite\Controllers\PostController;
use StMarks\PublicSite\Controllers\SitemapController;
use StMarks\PublicSite\Controllers\SmosaFeedbackController;
use StMarks\PublicSite\Controllers\StaffController;
use StMarks\PublicSite\Support\View;
use StMarks\Shared\Services\BoardMemberService;
use StMarks\Shared\Services\ChaplaincyService;
use StMarks\Shared\Services\ChristmasCantataService;
use StMarks\Shared\Services\CoCurricularService;
use StMarks\Shared\Services\GalleryService;
use StMarks\Shared\Services\GirlBoyTalkService;
use StMarks\Shared\Services\HighAchieverService;
use StMarks\Shared\Services\InspirationNightService;
use StMarks\Shared\Services\MentorshipService;
use StMarks\Shared\Services\SmosaAlumniService;
use StMarks\Shared\Services\StudentLeadershipService;
use StMarks\Shared\Support\Router;

// ---- Home & News ----
Router::get('/', [PagesController::class, 'home']);
Router::get('/news', [NewsController::class, 'index']);
Router::get('/news/{slug}', [NewsController::class, 'show']);

// ---- Static / near-static pages ----
Router::get('/about', [PagesController::class, 'about']);
Router::get('/academics', [PagesController::class, 'academics']);
Router::get('/admissions', [PagesController::class, 'admissions']);
Router::get('/empowerment-programmes', [PagesController::class, 'empowermentProgrammes']);
Router::get('/core-values', [PagesController::class, 'coreValues']);
Router::get('/school-anthem', [PagesController::class, 'anthem']);
Router::get('/college-name', [PagesController::class, 'collegeName']);
Router::get('/headteacher', [PagesController::class, 'headteacher']);
Router::get('/director1', [PagesController::class, 'director1']);
Router::get('/director2', [PagesController::class, 'director2']);
Router::get('/elearning', [PagesController::class, 'elearning']);
Router::get('/evoting', [PagesController::class, 'evoting']);
Router::get('/cybermonitor', [PagesController::class, 'cybermonitor']);
Router::get('/econcerting', [PagesController::class, 'econcerting']);

Router::get('/academics/curriculum', fn () => View::render('academics/curriculum', [], meta: ['title' => 'Curriculum']));
Router::get('/academics/uneb-results', fn () => View::render('academics/uneb-results', [], meta: ['title' => 'UNEB Results']));
Router::get('/academics/circulars', fn () => View::render('academics/circulars', [], meta: ['title' => 'Circulars']));
Router::get('/academics/school-calendar', fn () => View::render('academics/school-calendar', [], meta: ['title' => 'School Calendar']));

Router::get('/explore/career', [PagesController::class, 'career']);
Router::get('/explore/personal-needs', [PagesController::class, 'personalNeeds']);
Router::get('/explore/uniform', [PagesController::class, 'uniform']);

// ---- Simple DB-backed listings sharing templates/pages/listings/media-grid.php ----
Router::get('/board-members', function (): void {
    View::render('listings/media-grid', [
        'items' => (new BoardMemberService())->all(),
        'pageTitle' => 'Board Members',
        'titleField' => 'name',
        'textFields' => ['position' => 'Position'],
        'imageField' => 'photo_url',
        'videoField' => 'video_url',
    ], meta: ['title' => 'Board Members']);
});

Router::get('/academics/co-curricular', function (): void {
    View::render('listings/media-grid', [
        'items' => (new CoCurricularService())->all(),
        'pageTitle' => 'Co-Curricular Activities',
        'titleField' => 'title',
        'textFields' => ['content' => 'Content'],
    ], meta: ['title' => 'Co-Curricular']);
});

Router::get('/academics/high-achievers', function (): void {
    View::render('listings/media-grid', [
        'items' => (new HighAchieverService())->all(),
        'pageTitle' => 'High Achievers',
        'titleField' => 'name',
        'textFields' => ['exam' => 'Exam', 'division' => 'Division', 'description' => 'About'],
        'imageField' => 'photo_url',
        'videoField' => 'video_url',
    ], meta: ['title' => 'High Achievers']);
});

Router::get('/empowerment/mentorship', function (): void {
    View::render('listings/media-grid', [
        'items' => (new MentorshipService())->all(),
        'pageTitle' => 'Mentorship',
        'titleField' => 'title',
        'textFields' => ['caption' => 'Caption', 'description' => 'Description'],
    ], meta: ['title' => 'Mentorship']);
});

Router::get('/empowerment/girl-boy-talk', function (): void {
    View::render('listings/media-grid', [
        'items' => (new GirlBoyTalkService())->all(),
        'pageTitle' => 'Girl - Boy Talk',
        'titleField' => 'title',
        'textFields' => ['description' => 'Description'],
    ], meta: ['title' => 'Girl - Boy Talk']);
});

Router::get('/empowerment/inspiration-night', function (): void {
    View::render('listings/media-grid', [
        'items' => (new InspirationNightService())->all(),
        'pageTitle' => 'Inspiration Night',
        'titleField' => 'title',
        'textFields' => ['speaker' => 'Speaker', 'description' => 'Description'],
    ], meta: ['title' => 'Inspiration Night']);
});

Router::get('/empowerment/smosa-alumni', function (): void {
    View::render('listings/media-grid', [
        'items' => (new SmosaAlumniService())->all(),
        'pageTitle' => 'SMOSA Alumni',
        'titleField' => 'name',
        'textFields' => ['profession' => 'Profession', 'message' => 'Message'],
        'imageField' => 'photo_url',
        'videoField' => 'video_url',
    ], meta: ['title' => 'SMOSA Alumni']);
});

Router::get('/empowerment/christmas-cantata', function (): void {
    View::render('listings/media-grid', [
        'items' => (new ChristmasCantataService())->all(),
        'pageTitle' => 'Christmas Cantata',
        'titleField' => 'title',
        'textFields' => ['choir' => 'Choir', 'description' => 'Description'],
    ], meta: ['title' => 'Christmas Cantata']);
});

Router::get('/empowerment/chaplaincy', function (): void {
    View::render('listings/media-grid', [
        'items' => (new ChaplaincyService())->all(),
        'pageTitle' => 'Chaplaincy',
        'titleField' => 'title',
        'textFields' => ['content' => 'Content'],
    ], meta: ['title' => 'Chaplaincy']);
});

Router::get('/explore/student-leadership', function (): void {
    View::render('listings/media-grid', [
        'items' => (new StudentLeadershipService())->all(),
        'pageTitle' => 'Student Leadership',
        'titleField' => 'title',
        'textFields' => ['content' => 'Content'],
        'imageField' => 'image_path_url',
        'videoField' => 'video_path_url',
    ], meta: ['title' => 'Student Leadership']);
});

// ---- Bespoke DB-backed pages ----
Router::get('/staff', [StaffController::class, 'index']);
Router::get('/clubs', [ClubController::class, 'index']);
Router::get('/clubs/{slug}', [ClubController::class, 'show']);
Router::get('/campus-voices', [CampusVoiceController::class, 'index']);
Router::get('/campus-voices/{slug}', [CampusVoiceController::class, 'show']);
Router::get('/explore/campus-voices', [CampusVoiceController::class, 'index']);
Router::get('/posts', [PostController::class, 'index']);
Router::get('/posts/{slug}', [PostController::class, 'show']);

Router::get('/explore/gallery', function (): void {
    View::render('gallery/index', ['events' => (new GalleryService())->all()], meta: ['title' => 'Gallery']);
});

Router::get('/fee-structures', [FeeStructureController::class, 'index']);
Router::get('/fee-structures/{id}/pdf', [FeeStructureController::class, 'pdf']);
Router::get('/explore/fees', [FeeStructureController::class, 'index']);

Router::get('/performance', [PerformanceController::class, 'index']);
Router::get('/performance/{id}/pdf', [PerformanceController::class, 'pdf']);

// ---- Forms ----
Router::get('/contact', [ContactController::class, 'show']);
Router::post('/contact', [ContactController::class, 'store']);

Router::get('/apply', [JobApplicationController::class, 'show']);
Router::post('/apply', [JobApplicationController::class, 'store']);

Router::get('/smosa-feedback', [SmosaFeedbackController::class, 'show']);
Router::post('/smosa-feedback', [SmosaFeedbackController::class, 'store']);

// ---- Analytics ----
Router::get('/api/page-view', [PageViewController::class, 'store']);

// ---- SEO ----
Router::get('/sitemap.xml', [SitemapController::class, 'index']);
