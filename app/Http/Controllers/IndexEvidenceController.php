<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class IndexEvidenceController extends Controller
{
    protected EvidenceController $evidenceController;

    public function __construct(EvidenceController $evidenceController)
    {
        $this->evidenceController = $evidenceController;
    }

    /**
     * Display a listing of evidences.
     *
     * @return View
     */
    public function index(): View
    {
        return $this->evidenceController->index();
    }
}
