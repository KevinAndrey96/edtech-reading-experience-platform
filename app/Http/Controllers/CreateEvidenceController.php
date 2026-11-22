<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CreateEvidenceController extends Controller
{
    protected EvidenceController $evidenceController;

    public function __construct(EvidenceController $evidenceController)
    {
        $this->evidenceController = $evidenceController;
    }

    /**
     * Show the evidence creation view.
     *
     * @return View
     */
    public function create(): View
    {
        return $this->evidenceController->create();
    }
}
