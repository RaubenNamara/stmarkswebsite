<?php

declare(strict_types=1);

namespace StMarks\PublicSite\Controllers;

use StMarks\PublicSite\Support\View;
use StMarks\Shared\Services\NewsService;

/** Static/near-static pages - real copy ported from the old app's Vue page components. */
class PagesController
{
    public function home(): void
    {
        $news = (new NewsService())->published(3);

        View::render('home', ['latestNews' => $news], meta: [
            'title' => "St Mark's College Namagoma",
            'description' => "St Mark's College Namagoma - The Higher Achiever's College.",
        ]);
    }

    public function about(): void
    {
        View::render('about', [], meta: ['title' => 'About Us']);
    }

    public function academics(): void
    {
        View::render('academics/index', [], meta: ['title' => 'Academics']);
    }

    public function admissions(): void
    {
        View::render('admissions', [], meta: ['title' => 'Admissions']);
    }

    public function empowermentProgrammes(): void
    {
        View::render('empowerment-programmes', [], meta: ['title' => 'Empowerment Programmes']);
    }

    public function anthem(): void
    {
        View::render('anthem', [], meta: ['title' => 'School Anthem']);
    }

    public function collegeName(): void
    {
        View::render('college-name', [], meta: ['title' => 'The College Name']);
    }

    public function headteacher(): void
    {
        View::render('headteacher', [], meta: ['title' => "Head Teacher's Message"]);
    }

    public function director1(): void
    {
        View::render('director1', [], meta: ['title' => 'Director']);
    }

    public function director2(): void
    {
        View::render('director2', [], meta: ['title' => 'Director']);
    }

    public function coreValues(): void
    {
        View::render('core-values', [], meta: ['title' => 'Core Values']);
    }

    public function elearning(): void
    {
        $this->digitalCampus('eLearning', 'Access class notes, assignments and virtual lessons through the eSpace learning portal.');
    }

    public function evoting(): void
    {
        $this->digitalCampus('eVoting', 'Secure digital voting for student leadership and school elections.');
    }

    public function cybermonitor(): void
    {
        $this->digitalCampus('CyberMonitor', "Keeping our students safe online through the school's digital monitoring programme.");
    }

    public function econcerting(): void
    {
        $this->digitalCampus('eConcerting', 'Livestreamed and recorded school concerts, performances and events.');
    }

    private function digitalCampus(string $title, string $description): void
    {
        View::render('digital-campus', ['pageTitle' => $title, 'description' => $description], meta: ['title' => $title]);
    }

    public function career(): void
    {
        View::render('explore/career', [], meta: ['title' => 'Career Guidance']);
    }

    public function personalNeeds(): void
    {
        View::render('explore/personal-needs', [], meta: ['title' => 'Personal Needs']);
    }

    public function uniform(): void
    {
        View::render('explore/uniform', [], meta: ['title' => 'Uniform']);
    }
}
