<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;

/**
 * Read-only counterpart to Admin\AbstractImageVideoController / SinglePhotoContentService-backed
 * controllers - the ~10 "flat table, optional image/video" domains have no public detail page,
 * just a listing, so this is the entire public surface for each of them.
 */
abstract class AbstractPublicListController extends Controller
{
    protected mixed $service;
    protected string $collectionKey;

    public function index(): void
    {
        $this->success([$this->collectionKey => $this->service->all()]);
    }
}
