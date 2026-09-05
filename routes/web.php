<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SlideController;

/* Admin controllers (aliased where helpful) */
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\BoardMemberController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\CoCurricularController;
use App\Http\Controllers\Admin\PerformanceController as AdminPerformanceController;
use App\Http\Controllers\Admin\ChaplaincyController as AdminChaplaincyController;
use App\Http\Controllers\Admin\AdminJobApplicationController;
use App\Http\Controllers\Admin\FeeStructureController as AdminFeeStructureController;
use App\Http\Controllers\Admin\MentorshipController;
use App\Http\Controllers\Admin\GirlBoyTalkController;
use App\Http\Controllers\Admin\InspirationNightController as AdminInspirationNightController;
use App\Http\Controllers\Admin\SmosaAlumniController;
use App\Http\Controllers\Admin\ChristmasCantataController;
use App\Http\Controllers\Admin\StudentLeadershipController as AdminStudentLeadershipController;
use App\Http\Controllers\Admin\CampusVoiceController as AdminCampusVoiceController;

/* Public / shared controllers */
use App\Http\Controllers\NewsController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\InspirationNightController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SmosaFeedbackController;
use App\Http\Controllers\PageViewController;
use App\Http\Controllers\StaffClientController;
use App\Http\Controllers\PerformanceController; // public performance controller
use App\Http\Controllers\ChaplaincyController;   // public chaplaincy controller
use App\Http\Controllers\ChristmasCantataController as PublicChristmasCantataController;
use App\Http\Controllers\CampusVoiceController;


/* Models used in closures */
use App\Models\CoCurricular;
use App\Models\Media;
use App\Models\Post;
use App\Models\Slide;
use App\Models\News;
use App\Models\Staff;
use App\Models\BoardMember;
use App\Models\HighAchiever;
use App\Models\GirlBoyTalk;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Shared Public Data
|--------------------------------------------------------------------------
| Shared props used across many public pages (for Inertia).
*/
$publicShared = [
    'canLogin'       => Route::has('login'),
    'canRegister'    => Route::has('register'),
    'laravelVersion' => Application::VERSION,
    'phpVersion'     => PHP_VERSION,
];

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/
Route::get('/', function () use ($publicShared) {
   $slides = Slide::where('is_active', true)
    ->orderBy('order')
    ->get()
    ->map(function ($slide) {
        return [
            'id' => $slide->id,
            'title' => $slide->title,
            'caption' => $slide->caption,
            'type' => $slide->type,

            // ✅ IMAGE
            'image_url' => $slide->image_path
                ? asset('storage/' . $slide->image_path)
                : null,

            // ✅ VIDEO
            'video_url' => $slide->video_path
                ? asset('storage/' . $slide->video_path)
                : null,
        ];
    });
    $latestNews = News::where('is_published', true)
        ->latest()
        ->take(15)
        ->get()
        ->map(function ($news) {
            return [
                'id'         => $news->id,
                'title'      => $news->title,
                'slug'       => $news->slug,
                'excerpt'    => $news->excerpt,
                'content'    => $news->content,
                'image_url'  => $news->image_path ? asset('storage/' . $news->image_path) : null,
                'date'       => $news->created_at->format('M d, Y'),
                'created_at' => $news->created_at,
            ];
        });

    $latestPosts = Post::where('is_published', true)
        ->latest()
        ->take(15)
        ->get()
        ->map(function ($post) {
            return [
                'id'         => $post->id,
                'title'      => $post->title,
                'slug'       => $post->slug,
                'excerpt'    => $post->excerpt,
                'content'    => $post->content,
                'image_url'  => $post->image_path ? asset('storage/' . $post->image_path) : null,
                'date'       => $post->created_at->format('M d, Y'),
                'created_at' => $post->created_at,
            ];
        });

    $media = Media::where('is_active', true)
        ->latest()
        ->get()
        ->map(function ($item) {
            return [
                'id'       => $item->id,
                'title'    => $item->title,
                'type'     => $item->type,
                'video_url'=> $item->video_url,
                'file_url' => $item->file_path ? asset('storage/' . $item->file_path) : null,
            ];
        });

    return Inertia::render('Home', array_merge($publicShared, [
        'pageTitle'   => 'Welcome to St Marks College Namagoma',
        'slides'      => $slides,
        'newsItems'   => $latestNews,
        'latestPosts' => $latestPosts,
        'mediaItems'  => $media,
    ]));
})->name('home');

/*
|--------------------------------------------------------------------------
| NEWS (public)
|--------------------------------------------------------------------------
*/
Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');

/*
|--------------------------------------------------------------------------
| CAMPUS VOICES (public)
|--------------------------------------------------------------------------
*/
Route::get('/campus-voices/{slug}', [CampusVoiceController::class, 'show'])->name('campus-voices.show');

/*
|--------------------------------------------------------------------------
| POSTS (public)
|--------------------------------------------------------------------------
*/
Route::get('/posts', function () use ($publicShared) {
    $posts = Post::where('is_published', true)
        ->latest()
        ->take(10)
        ->get()
        ->map(function ($p) {
            return [
                'id'        => $p->id,
                'title'     => $p->title,
                'slug'      => $p->slug,
                'excerpt'   => $p->excerpt,
                'image_url' => $p->image_path ? asset('storage/' . $p->image_path) : null,
                'date'      => $p->created_at->format('M d, Y'),
            ];
        });

    return Inertia::render('PostsIndex', array_merge($publicShared, [
        'pageTitle' => 'Posts',
        'posts'     => $posts,
    ]));
})->name('posts.index');

Route::get('/posts/{post:slug}', function (Post $post) {
    abort_unless($post->is_published, 404);

    return Inertia::render('PostShow', [
        'post' => $post,
    ]);
})->name('posts.show');

/*
|--------------------------------------------------------------------------
| CLUBS (public)
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| CLUBS (public) ✅ FIXED LIKE OTHERS
|--------------------------------------------------------------------------
*/
Route::get('/clubs', function () use ($publicShared) {

    $clubs = \App\Models\Club::with('images')->latest()->get()->map(function ($club) {

        return [
            'id' => $club->id,
            'title' => $club->title,
            'slug' => $club->slug,
            'content' => $club->content,

            // ✅ IMPORTANT FIX
            'images' => $club->images->map(function ($img) {
                return [
                    'url' => asset('storage/' . $img->image_path),
                    'caption' => $img->caption
                ];
            }),
        ];
    });

    return Inertia::render('Clubs/Index', array_merge($publicShared, [
        'pageTitle' => 'School Clubs',
        'clubs' => $clubs
    ]));

})->name('clubs.index');


// OPTIONAL (keep if you want single club page)
Route::get('/clubs/{club:slug}', [ClubController::class, 'show'])->name('clubs.show');

/*
|--------------------------------------------------------------------------
| STATIC / ABOUT / ACADEMICS PAGES
|--------------------------------------------------------------------------
*/
Route::get('/about', fn () => Inertia::render('About', array_merge($publicShared, [
    'pageTitle' => 'About Us',
])))->name('about');

Route::get('/academics', fn () => Inertia::render('Academics', array_merge($publicShared, [
    'pageTitle' => 'Academics',
])))->name('academics');

Route::get('/academics/curriculum', fn () => Inertia::render('Academics/Curriculum'))->name('academics.curriculum');

Route::get('/academics/co-curricular', function () {
    return Inertia::render('Academics/CoCurricular', [
        'items' => CoCurricular::latest()->get()
    ]);
})->name('academics.co-curricular');

Route::get('/academics/uneb-results', fn () => Inertia::render('Academics/UnebResults'))->name('academics.uneb-results');
Route::get('/academics/circulars', fn () => Inertia::render('Academics/Circulars'))->name('academics.circulars');

Route::get('/academics/high-achievers', function () {
    $achievers = HighAchiever::latest()->get();

    return Inertia::render('Academics/HighAchievers', [
        'achievers' => $achievers
    ]);
})->name('academics.high-achievers');

Route::get('/academics/school-calendar', fn () => Inertia::render('Academics/SchoolCalendar'))->name('academics.school-calendar');

Route::get('/admissions', fn () => Inertia::render('Admissions'))->name('admissions');

/*
|--------------------------------------------------------------------------
| DIGITAL CAMPUS PAGES
|--------------------------------------------------------------------------
*/
Route::get('/elearning', fn () => Inertia::render('Elearning', array_merge($publicShared, ['pageTitle' => 'eLearning Platform'])))->name('elearning');
Route::get('/evoting', fn () => Inertia::render('Evoting', array_merge($publicShared, ['pageTitle' => 'eVoting System'])))->name('evoting');
Route::get('/cybermonitor', fn () => Inertia::render('CyberMonitor', array_merge($publicShared, ['pageTitle' => 'Cyber Monitor'])))->name('cybermonitor');
Route::get('/econcerting', fn () => Inertia::render('Econcerting', array_merge($publicShared, ['pageTitle' => 'eConcerting Suite'])))->name('econcerting');

/*
|--------------------------------------------------------------------------
| PUBLIC STAFF & BOARD MEMBERS
|--------------------------------------------------------------------------
*/
Route::get('/staff', function () use ($publicShared) {

    $staff = Staff::all()->map(function ($item) {

        $category = strtolower(trim($item->category));

        // ✅ Normalize category
        if ($category === 'administrator' || $category === 'administrators') {
            $item->category = 'Administrators';
        } elseif (
            $category === 'head of department' ||
            $category === 'heads of department' ||
            $category === 'heads of departments'
        ) {
            $item->category = 'Heads of Departments';
        } elseif (
            $category === 'support staff' ||
            $category === 'non teaching staff' ||
            $category === 'non-teaching staff'
        ) {
            $item->category = 'Non Teaching Staff';
        } elseif ($category === 'teaching staff') {
            $item->category = 'Teaching Staff';
        } else {
            $item->category = $item->category ?: 'Uncategorized';
        }

        return $item;
    });

    // ✅ Sort properly
    $staff = $staff->sort(function ($a, $b) {

        $order = [
            'Administrators' => 1,
            'Heads of Departments' => 2,
            'Teaching Staff' => 3,
            'Non Teaching Staff' => 4,
            'Uncategorized' => 5,
        ];

        $catA = $order[$a->category] ?? 5;
        $catB = $order[$b->category] ?? 5;

        if ($catA !== $catB) {
            return $catA <=> $catB;
        }

        $orderA = (int) ($a->sort_order ?? 0);
        $orderB = (int) ($b->sort_order ?? 0);

        if ($orderA !== $orderB) {
            return $orderA <=> $orderB;
        }

        return $a->id <=> $b->id;
    })->values();

    // ✅ Group AFTER fixing category
    $groupedStaff = $staff->groupBy('category');

    return Inertia::render('Staff', array_merge($publicShared, [
        'groupedStaff' => $groupedStaff
    ]));

})->name('staff');

Route::get('/board-members', function () use ($publicShared) {
    $members = BoardMember::latest()->get()->map(function ($m) {
        return [
            'id'       => $m->id,
            'name'     => $m->name,
            'position' => $m->position,
            'photo'    => $m->photo,
        ];
    });

    return Inertia::render('BoardMembers', array_merge($publicShared, [
        'pageTitle' => 'Board Members',
        'members'   => $members,
    ]));
})->name('board');


/*
|--------------------------------------------------------------------------
| OTHER STATIC PAGES
|--------------------------------------------------------------------------
*/
Route::get('/core-values', fn () => Inertia::render('CoreValues', array_merge($publicShared, ['pageTitle' => 'Our Core Values'])))->name('core-values');
Route::get('/school-anthem', fn () => Inertia::render('Anthem', array_merge($publicShared, ['pageTitle' => 'School Anthem'])))->name('anthem');
Route::get('/college-name', fn () => Inertia::render('CollegeName', array_merge($publicShared, ['pageTitle' => 'College Name Meaning'])))->name('college-name');
Route::get('/core-values', fn () => Inertia::render('CoreValues', array_merge($publicShared, ['pageTitle' => 'Our Core Values'])))->name('core-values');

Route::get('/headteacher', function () use ($publicShared) {return Inertia::render('HeadTeacher', array_merge($publicShared, ['pageTitle' => 'Message from the Headteacher'
    ]));
    })->name('headteacher');

Route::get('/director1', function () use ($publicShared) {return Inertia::render('Director1', array_merge($publicShared, ['pageTitle' => 'Director'
    ]));
})->name('director1');

Route::get('/director2', function () use ($publicShared) {return Inertia::render('Director2', array_merge($publicShared, ['pageTitle' => 'Deputy Director'
    ]));
})->name('director2');

/*
|--------------------------------------------------------------------------
| EMPOWERMENT (public landing and child pages)
|--------------------------------------------------------------------------
*/

Route::get('/empowerment-programmes', fn () => Inertia::render(
    'Empowerment',
    array_merge($publicShared, [
        'pageTitle' => 'Empowerment Programmes',
    ])
))->name('empowerment');


Route::prefix('empowerment')->name('empowerment.')->group(function () use ($publicShared) {

    // Chaplaincy
    Route::get('/chaplaincy', [ChaplaincyController::class, 'index'])->name('chaplaincy');

    Route::post('/chaplaincy', [ChaplaincyController::class, 'store'])
        ->name('chaplaincy.store')
        ->middleware('auth');

    Route::delete('/chaplaincy/{chaplaincy}', [ChaplaincyController::class, 'destroy'])
        ->name('chaplaincy.destroy')
        ->middleware('auth');


    // Mentorship
   // Mentorship
Route::get('/mentorship', function () use ($publicShared) {

    $mentorships = \App\Models\Mentorship::latest()->get()->map(function ($item) {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'caption' => $item->caption,
            'description' => $item->description,

            // ✅ FIX (IMPORTANT)
            'image_url' => $item->image
                ? asset('storage/mentorship/' . $item->image)
                : null,

            'video_url' => $item->video
                ? asset('storage/mentorship/' . $item->video)
                : null,

            'video_link' => $item->video_link,
        ];
    });

    return Inertia::render('Empowerment/Mentorship', array_merge($publicShared, [
        'pageTitle' => 'Mentorship Programme',
        'mentorships' => $mentorships
    ]));

})->name('mentorship');

    // Girl Boy Talk
Route::get('/girl-boy-talk', function () use ($publicShared) {

    $talks = GirlBoyTalk::latest()->get()->map(function ($item) {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,

            // ✅ FIX (SAME AS MENTORSHIP)
            'image_url' => $item->image
                ? asset('storage/girlboytalk/' . $item->image)
                : null,

            'video_url' => $item->video
                ? asset('storage/girlboytalk/' . $item->video)
                : null,

            'video_link' => $item->video_link,
        ];
    });

    return Inertia::render('Empowerment/GirlBoyTalk', array_merge($publicShared, [
        'pageTitle' => 'Girl-Boy Talk',
        'talks' => $talks
    ]));

})->name('girl-boy-talk');


    // Inspiration Night
    Route::get('/inspiration-night', [InspirationNightController::class, 'index'])
        ->name('inspiration-night');


    // SMOSA Alumni (DATABASE)
  // SMOSA Alumni (PUBLIC FIX)
Route::get('/smosa-alumni', function () use ($publicShared) {

    $alumni = \App\Models\SmosaAlumni::latest()->get()->map(function ($a) {
        return [
            'id' => $a->id,
            'name' => $a->name,
            'profession' => $a->profession,
            'message' => $a->message,
            'video' => $a->video,

            // ✅ IMPORTANT FIX
            'photo_url' => $a->photo
                ? asset('storage/smosa/' . $a->photo)
                : null,
        ];
    });

    return Inertia::render('Empowerment/SmosaAlumni', array_merge($publicShared, [
        'pageTitle' => 'SMOSA Alumni',
        'alumni' => $alumni
    ]));

})->name('smosa-alumni');

// SMOSA Homecoming Dinner 2026 - Feedback form (PUBLIC)
Route::get('/smosa-feedback', function (\Illuminate\Http\Request $request) use ($publicShared) {
    return Inertia::render('SmosaFeedback', array_merge($publicShared, [
        'pageTitle' => 'SMOSA Homecoming Dinner 2026 - Feedback',
        'alreadySubmitted' => $request->hasCookie(SmosaFeedbackController::SUBMITTED_COOKIE),
    ]));
})->name('smosa-feedback');

Route::post('/smosa-feedback', [SmosaFeedbackController::class, 'store'])->name('smosa-feedback.store');


    // Christmas Cantata (public)
  // Christmas Cantata (PUBLIC FIX)
Route::get('/christmas-cantata', function () use ($publicShared) {

    $cantatas = \App\Models\ChristmasCantata::latest()->get()->map(function ($c) {
        return [
            'id' => $c->id,
            'title' => $c->title,
            'choir' => $c->choir,
            'date' => $c->date ? $c->date->format('Y-m-d') : null,
            'description' => $c->description,
            'video' => $c->video,

            // ✅ IMPORTANT FIX
            'image_url' => $c->image
                ? asset('storage/cantatas/' . $c->image)
                : null,
        ];
    });

    return Inertia::render('Empowerment/ChristmasCantata', array_merge($publicShared, [
        'pageTitle' => 'Christmas Cantata',
        'cantatas' => $cantatas
    ]));

})->name('christmas-cantata');
});
/*
|--------------------------------------------------------------------------
| APPLY / CONTACT / FEES / PERFORMANCE
|--------------------------------------------------------------------------
*/
Route::get('/apply', fn () => Inertia::render('Apply', array_merge($publicShared, [
    'pageTitle' => 'Apply for Admission'
])))->name('apply');

Route::post('/apply', [JobApplicationController::class, 'store'])->name('apply.store');


/* CONTACT PAGE */
Route::get('/contact', fn () => Inertia::render('Contact', array_merge($publicShared, [
    'pageTitle' => 'Contact Us'
])))->name('contact');

/* SEND CONTACT MESSAGE */
Route::post('/contact', [ContactController::class, 'store'])
    ->name('contact.store');


/* PAGE VIEW TRACKING */
Route::get('/api/page-view', [PageViewController::class, 'store'])
    ->name('page-view.store');

Route::get('/api/page-view/stats/{pageType}/{pageId}', [PageViewController::class, 'getStats'])
    ->name('page-view.stats');

Route::get('/api/page-view/stats', [PageViewController::class, 'getGeneralStats'])
    ->name('page-view.stats.general');


/* FEES */
Route::get('/fee-structures', [FeeStructureController::class, 'index'])
    ->name('fee-structures.index');

Route::get('/fee-structures/{id}/pdf', [FeeStructureController::class, 'pdf'])
    ->name('fee-structures.pdf');


/* PERFORMANCE */
Route::get('/performance', [PerformanceController::class, 'index'])
    ->name('performance.index');
/*
|--------------------------------------------------------------------------
| EXPLORE (nav)
|--------------------------------------------------------------------------
*/
Route::prefix('explore')->name('explore.')->group(function () use ($publicShared) {
    Route::get('/career', fn () => Inertia::render('Explore/Career', array_merge($publicShared, ['pageTitle' => 'Career Guidance'])))->name('career');
    Route::get('/fees', [FeeStructureController::class, 'index'])->name('fees');
    Route::get('/personal-needs', fn () => Inertia::render('Explore/PersonalNeeds', array_merge($publicShared, ['pageTitle' => 'Personal Needs'])))->name('personal-needs');
    Route::get('/uniform', fn () => Inertia::render('Explore/Uniform', array_merge($publicShared, ['pageTitle' => 'Uniform'])))->name('uniform');
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
   Route::get('/student-leadership', [\App\Http\Controllers\StudentLeadershipController::class, 'index'])
    ->name('student-leadership');
    Route::get('/campus-voices', [CampusVoiceController::class, 'index'])->name('campus-voices');
});

/*
|--------------------------------------------------------------------------
| DASHBOARD (auth + verified)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
   $slides = Slide::orderBy('order')->get()->map(function ($slide) {
    return [
        'id' => $slide->id,
        'title' => $slide->title,
        'caption' => $slide->caption,
        'type' => $slide->type,
        'image_url' => $slide->image_path ? asset('storage/' . $slide->image_path) : null,
        'video_url' => $slide->video_path ? asset('storage/' . $slide->video_path) : null,
    ];
});

    return Inertia::render('Dashboard', [
        'slides' => $slides,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');


/*
|--------------------------------------------------------------------------
| ADMIN (consolidated)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Admin index
    Route::get('/', function () {
        return Inertia::render('Admin/Index');
    })->name('index');

    // Posts
    Route::resource('posts', PostController::class);

    // News
    Route::get('/news', [AdminNewsController::class, 'index'])->name('news.index');
    Route::post('/news', [AdminNewsController::class, 'store'])->name('news.store');
    Route::delete('/news/{news}', [AdminNewsController::class, 'destroy'])->name('news.destroy');

    // Page Views
    Route::get('/page-views', fn () => Inertia::render('Admin/PageViews/Index'))->name('page-views.index');

    // Media
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])
    ->name('media.destroy');

    // Staff
    Route::resource('staff', StaffController::class)->except(['show']);
    Route::post('/staff/reorder', [StaffController::class, 'reorder'])
    ->name('staff.reorder');
    
  Route::resource('student-leadership', AdminStudentLeadershipController::class);
    // Board members
    Route::resource('board-members', BoardMemberController::class)->except(['show']);

    // SMOSA Alumni
    Route::resource('smosa', SmosaAlumniController::class);

    // Job applications
    Route::get('/applications', [AdminJobApplicationController::class, 'index'])->name('applications.index');
    Route::delete('/applications/{application}', [AdminJobApplicationController::class, 'destroy'])->name('applications.destroy');

    // Fee structures
    Route::resource('fee-structures', AdminFeeStructureController::class)->except(['create', 'edit', 'show']);

    // Gallery
    Route::resource('gallery', AdminGalleryController::class)->only(['index','store','update','destroy']);
    Route::post('/gallery/{event}/add-images', [AdminGalleryController::class, 'addImages'])->name('gallery.add-images');
    Route::delete('/gallery/image/{image}', [AdminGalleryController::class, 'destroyImage'])->name('gallery.image.destroy');

    // Clubs
    Route::resource('clubs', \App\Http\Controllers\Admin\ClubController::class);

    // Co-Curricular
    Route::resource('co-curricular', CoCurricularController::class);

    // Performances
Route::get('/performances', [AdminPerformanceController::class, 'index'])->name('performances.index');
Route::post('/performances', [AdminPerformanceController::class, 'store'])->name('performances.store');
Route::delete('/performances/{performance}', [AdminPerformanceController::class, 'destroy'])->name('performances.destroy');

    // Contact Messages
    Route::get('/contacts', [ContactController::class, 'index'])
        ->name('contacts.index');

    Route::post('/contacts/{contact}/mark-read', [ContactController::class, 'markRead'])
        ->name('contacts.markRead');

    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])
        ->name('contacts.destroy');

    // SMOSA Feedback
    Route::get('/smosa-feedback', [SmosaFeedbackController::class, 'index'])->name('smosa-feedback.index');
    Route::delete('/smosa-feedback/{smosaFeedback}', [SmosaFeedbackController::class, 'destroy'])->name('smosa-feedback.destroy');

    // High Achievers
    Route::get('/high-achievers', [\App\Http\Controllers\Admin\HighAchieverController::class, 'index'])->name('high-achievers.index');
    Route::post('/high-achievers', [\App\Http\Controllers\Admin\HighAchieverController::class, 'store'])->name('high-achievers.store');
    Route::put('/high-achievers/{highAchiever}', [\App\Http\Controllers\Admin\HighAchieverController::class, 'update'])->name('high-achievers.update');
    Route::delete('/high-achievers/{highAchiever}', [\App\Http\Controllers\Admin\HighAchieverController::class, 'destroy'])->name('high-achievers.destroy');

    // Chaplaincy
    Route::get('/chaplaincy', [AdminChaplaincyController::class, 'index'])->name('chaplaincy.index');
    Route::post('/chaplaincy', [AdminChaplaincyController::class, 'store'])->name('chaplaincy.store');
    Route::delete('/chaplaincy/{chaplaincy}', [AdminChaplaincyController::class, 'destroy'])->name('chaplaincy.destroy');

     // Mentorship
    Route::get('/mentorship', [MentorshipController::class,'index'])->name('mentorship.index');
    Route::get('/mentorship/create', [MentorshipController::class,'create'])->name('mentorship.create');
    Route::post('/mentorship/store', [MentorshipController::class,'store'])->name('mentorship.store');
    Route::get('/mentorship/edit/{mentorship}', [MentorshipController::class,'edit'])->name('mentorship.edit');
    Route::put('/mentorship/update/{mentorship}', [MentorshipController::class,'update'])->name('mentorship.update');
    Route::delete('/mentorship/delete/{mentorship}', [MentorshipController::class,'destroy'])->name('mentorship.destroy');

    // Girl Boy Talk
    Route::resource('girlboytalk', GirlBoyTalkController::class);
    // Inspiration Night
    Route::resource('inspiration', AdminInspirationNightController::class);

   // Christmas Cantata
  Route::resource('christmas-cantata', ChristmasCantataController::class)
    ->parameters([
        'christmas-cantata' => 'christmasCantata'
    ]);

    // Campus Voices
    Route::resource('campus-voices', AdminCampusVoiceController::class)
        ->parameters([
            'campus_voices' => 'campusVoice'
        ]);
    Route::post('/campus-voices/{campusVoice}/toggle-featured', [AdminCampusVoiceController::class, 'toggleFeatured'])
        ->name('campus-voices.toggle-featured');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED (non-admin) ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/slides', [SlideController::class, 'store'])->name('slides.store');
    Route::delete('/slides/{slide}', [SlideController::class, 'destroy'])->name('slides.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';


Route::get('/performance/{id}/pdf', [PerformanceController::class, 'pdf'])
    ->name('performance.pdf');


/*
|--------------------------------------------------------------------------
| FALLBACK - 404 (Inertia)
|--------------------------------------------------------------------------
*/
Route::fallback(function () use ($publicShared) {
    return Inertia::render('NotFound', array_merge($publicShared, [
        'pageTitle' => 'Page Not Found',
    ]))->toResponse(request())->setStatusCode(404);
});