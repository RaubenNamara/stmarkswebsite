<?php

declare(strict_types=1);

use StMarks\Backend\Controllers\Admin\BoardMemberController;
use StMarks\Backend\Controllers\Admin\CampusVoiceController;
use StMarks\Backend\Controllers\Admin\ChaplaincyController;
use StMarks\Backend\Controllers\Admin\ChristmasCantataController;
use StMarks\Backend\Controllers\Admin\ClubController;
use StMarks\Backend\Controllers\Admin\CoCurricularController;
use StMarks\Backend\Controllers\Admin\ContactController;
use StMarks\Backend\Controllers\Admin\FeeStructureController;
use StMarks\Backend\Controllers\Admin\GalleryController;
use StMarks\Backend\Controllers\Admin\GirlBoyTalkController;
use StMarks\Backend\Controllers\Admin\HighAchieverController;
use StMarks\Backend\Controllers\Admin\InspirationNightController;
use StMarks\Backend\Controllers\Admin\JobApplicationController;
use StMarks\Backend\Controllers\Admin\MediaController;
use StMarks\Backend\Controllers\Admin\MentorshipController;
use StMarks\Backend\Controllers\Admin\NewsController;
use StMarks\Backend\Controllers\Admin\PerformanceController;
use StMarks\Backend\Controllers\Admin\PostController;
use StMarks\Backend\Controllers\Admin\SlideController;
use StMarks\Backend\Controllers\Admin\SmosaAlumniController;
use StMarks\Backend\Controllers\Admin\SmosaFeedbackController;
use StMarks\Backend\Controllers\Admin\StaffController;
use StMarks\Backend\Controllers\Admin\StudentLeadershipController;
use StMarks\Backend\Controllers\AuthController;
use StMarks\Shared\Support\Router;

// Health check - no auth, useful for confirming the front controller/router pipeline works.
Router::get('/api/health', function (): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => 'ok']);
    exit;
});

Router::post('/api/auth/login', AuthController::class . '@login', ['rate_limit:login,10,60']);

Router::group(['prefix' => '/api', 'middleware' => ['auth']], function (): void {
    Router::get('/auth/me', AuthController::class . '@me');
    Router::post('/auth/logout', AuthController::class . '@logout', ['csrf']);
});

// Admin CRUD routes. Reads are ['auth'] only; mutations add ['csrf']. Create/update use POST
// (not PUT) throughout, since PHP doesn't populate $_FILES for PUT/PATCH bodies - see
// Admin/NewsController's docblock, the reference pattern every other domain in Phase 3 follows.
Router::group(['prefix' => '/api/admin', 'middleware' => ['auth']], function (): void {
    Router::get('/news', NewsController::class . '@index');
    Router::get('/news/{id}', NewsController::class . '@show');
    Router::post('/news', NewsController::class . '@store', ['csrf']);
    Router::post('/news/{id}', NewsController::class . '@update', ['csrf']);
    Router::delete('/news/{id}', NewsController::class . '@destroy', ['csrf']);

    Router::get('/slides', SlideController::class . '@index');
    Router::post('/slides', SlideController::class . '@store', ['csrf']);
    Router::delete('/slides/{id}', SlideController::class . '@destroy', ['csrf']);

    Router::get('/posts', PostController::class . '@index');
    Router::get('/posts/{id}', PostController::class . '@show');
    Router::post('/posts', PostController::class . '@store', ['csrf']);
    Router::post('/posts/{id}', PostController::class . '@update', ['csrf']);
    Router::delete('/posts/{id}', PostController::class . '@destroy', ['csrf']);

    Router::get('/media', MediaController::class . '@index');
    Router::post('/media', MediaController::class . '@store', ['csrf']);
    Router::delete('/media/{id}', MediaController::class . '@destroy', ['csrf']);

    Router::get('/staff', StaffController::class . '@index');
    Router::post('/staff', StaffController::class . '@store', ['csrf']);
    Router::post('/staff/{id}', StaffController::class . '@update', ['csrf']);
    Router::delete('/staff/{id}', StaffController::class . '@destroy', ['csrf']);
    Router::post('/staff-reorder', StaffController::class . '@reorder', ['csrf']);

    Router::get('/board-members', BoardMemberController::class . '@index');
    Router::post('/board-members', BoardMemberController::class . '@store', ['csrf']);
    Router::post('/board-members/{id}', BoardMemberController::class . '@update', ['csrf']);
    Router::delete('/board-members/{id}', BoardMemberController::class . '@destroy', ['csrf']);

    Router::get('/applications', JobApplicationController::class . '@index');
    Router::delete('/applications/{id}', JobApplicationController::class . '@destroy', ['csrf']);

    Router::get('/contacts', ContactController::class . '@index');
    Router::post('/contacts/{id}/mark-read', ContactController::class . '@markRead', ['csrf']);
    Router::delete('/contacts/{id}', ContactController::class . '@destroy', ['csrf']);

    Router::get('/fee-structures', FeeStructureController::class . '@index');
    Router::post('/fee-structures', FeeStructureController::class . '@store', ['csrf']);
    Router::post('/fee-structures/{id}', FeeStructureController::class . '@update', ['csrf']);
    Router::delete('/fee-structures/{id}', FeeStructureController::class . '@destroy', ['csrf']);

    Router::get('/gallery', GalleryController::class . '@index');
    Router::post('/gallery', GalleryController::class . '@store', ['csrf']);
    Router::post('/gallery/{id}', GalleryController::class . '@update', ['csrf']);
    Router::post('/gallery/{id}/add-images', GalleryController::class . '@addImages', ['csrf']);
    Router::delete('/gallery/image/{imageId}', GalleryController::class . '@destroyImage', ['csrf']);
    Router::delete('/gallery/{id}', GalleryController::class . '@destroy', ['csrf']);

    Router::get('/clubs', ClubController::class . '@index');
    Router::get('/clubs/{id}', ClubController::class . '@show');
    Router::post('/clubs', ClubController::class . '@store', ['csrf']);
    Router::post('/clubs/{id}', ClubController::class . '@update', ['csrf']);
    Router::delete('/clubs/image/{imageId}', ClubController::class . '@destroyImage', ['csrf']);
    Router::delete('/clubs/{id}', ClubController::class . '@destroy', ['csrf']);

    Router::get('/co-curricular', CoCurricularController::class . '@index');
    Router::post('/co-curricular', CoCurricularController::class . '@store', ['csrf']);
    Router::post('/co-curricular/{id}', CoCurricularController::class . '@update', ['csrf']);
    Router::delete('/co-curricular/{id}', CoCurricularController::class . '@destroy', ['csrf']);

    Router::get('/chaplaincy', ChaplaincyController::class . '@index');
    Router::post('/chaplaincy', ChaplaincyController::class . '@store', ['csrf']);
    Router::post('/chaplaincy/{id}', ChaplaincyController::class . '@update', ['csrf']);
    Router::delete('/chaplaincy/{id}', ChaplaincyController::class . '@destroy', ['csrf']);

    Router::get('/mentorship', MentorshipController::class . '@index');
    Router::post('/mentorship', MentorshipController::class . '@store', ['csrf']);
    Router::post('/mentorship/{id}', MentorshipController::class . '@update', ['csrf']);
    Router::delete('/mentorship/{id}', MentorshipController::class . '@destroy', ['csrf']);

    Router::get('/performances', PerformanceController::class . '@index');
    Router::post('/performances', PerformanceController::class . '@store', ['csrf']);
    Router::post('/performances/{id}', PerformanceController::class . '@update', ['csrf']);
    Router::delete('/performances/{id}', PerformanceController::class . '@destroy', ['csrf']);

    Router::get('/high-achievers', HighAchieverController::class . '@index');
    Router::post('/high-achievers', HighAchieverController::class . '@store', ['csrf']);
    Router::post('/high-achievers/{id}', HighAchieverController::class . '@update', ['csrf']);
    Router::delete('/high-achievers/{id}', HighAchieverController::class . '@destroy', ['csrf']);

    Router::get('/student-leadership', StudentLeadershipController::class . '@index');
    Router::post('/student-leadership', StudentLeadershipController::class . '@store', ['csrf']);
    Router::post('/student-leadership/{id}', StudentLeadershipController::class . '@update', ['csrf']);
    Router::delete('/student-leadership/{id}', StudentLeadershipController::class . '@destroy', ['csrf']);

    Router::get('/girlboytalk', GirlBoyTalkController::class . '@index');
    Router::post('/girlboytalk', GirlBoyTalkController::class . '@store', ['csrf']);
    Router::post('/girlboytalk/{id}', GirlBoyTalkController::class . '@update', ['csrf']);
    Router::delete('/girlboytalk/{id}', GirlBoyTalkController::class . '@destroy', ['csrf']);

    Router::get('/inspiration', InspirationNightController::class . '@index');
    Router::post('/inspiration', InspirationNightController::class . '@store', ['csrf']);
    Router::post('/inspiration/{id}', InspirationNightController::class . '@update', ['csrf']);
    Router::delete('/inspiration/{id}', InspirationNightController::class . '@destroy', ['csrf']);

    Router::get('/christmas-cantata', ChristmasCantataController::class . '@index');
    Router::post('/christmas-cantata', ChristmasCantataController::class . '@store', ['csrf']);
    Router::post('/christmas-cantata/{id}', ChristmasCantataController::class . '@update', ['csrf']);
    Router::delete('/christmas-cantata/{id}', ChristmasCantataController::class . '@destroy', ['csrf']);

    Router::get('/smosa', SmosaAlumniController::class . '@index');
    Router::post('/smosa', SmosaAlumniController::class . '@store', ['csrf']);
    Router::post('/smosa/{id}', SmosaAlumniController::class . '@update', ['csrf']);
    Router::delete('/smosa/{id}', SmosaAlumniController::class . '@destroy', ['csrf']);

    Router::get('/smosa-feedback', SmosaFeedbackController::class . '@index');
    Router::delete('/smosa-feedback/{id}', SmosaFeedbackController::class . '@destroy', ['csrf']);

    Router::get('/campus-voices', CampusVoiceController::class . '@index');
    Router::get('/campus-voices/{id}', CampusVoiceController::class . '@show');
    Router::post('/campus-voices', CampusVoiceController::class . '@store', ['csrf']);
    Router::post('/campus-voices/{id}', CampusVoiceController::class . '@update', ['csrf']);
    Router::post('/campus-voices/{id}/toggle-featured', CampusVoiceController::class . '@toggleFeatured', ['csrf']);
    Router::delete('/campus-voices/{id}', CampusVoiceController::class . '@destroy', ['csrf']);
});
