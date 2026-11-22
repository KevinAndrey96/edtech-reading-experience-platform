<?php

namespace App\Http\Controllers;

use App\Services\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreEvidenceController extends Controller
{
    protected EvidenceController $evidenceController;

    public function __construct(EvidenceController $evidenceController)
    {
        $this->evidenceController = $evidenceController;
    }

    /**
     * Store a newly created evidence.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        return $this->evidenceController->store($request);
    }
}
