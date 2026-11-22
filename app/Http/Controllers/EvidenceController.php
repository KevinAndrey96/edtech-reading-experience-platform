<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Evidence;
use App\Models\Region;
use App\Models\User;
use App\Services\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EvidenceController extends Controller
{
    protected MediaUploadService $mediaUploadService;

    public function __construct(MediaUploadService $mediaUploadService)
    {
        $this->mediaUploadService = $mediaUploadService;
    }

    /**
     * Display a listing of student learning evidences.
     *
     * @return View
     */
    public function index(): View
    {
        $evidences = Evidence::with(['user', 'book'])->latest()->get();

        return view('evidences.index', compact('evidences'));
    }

    /**
     * Show the form for creating a new evidence.
     *
     * @return View
     */
    public function create(): View
    {
        $books = Book::orderBy('name')->get();
        $users = User::all();
        $evidences = Evidence::latest()->get();

        return view('evidences.create', compact('evidences', 'users', 'books'));
    }

    /**
     * Store a newly created evidence in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'student_name' => 'required|string|max:255',
            'class_room' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'book_id' => 'nullable|exists:books,id',
            'attachment_media' => 'nullable|file|max:10240',
        ]);

        $evidence = new Evidence();
        $evidence->user_id = (int) ($request->input('user_id') ?: Auth::id());
        $evidence->book_id = $request->filled('book_id') ? (int) $request->input('book_id') : null;
        $evidence->student_name = $request->input('student_name');
        $evidence->class_room = $request->input('class_room');
        $evidence->attachment_media_url = '';
        $evidence->save();

        if ($request->hasFile('attachment_media')) {
            $evidence->attachment_media_url = $this->mediaUploadService->upload(
                $request->file('attachment_media'),
                'evidences_images',
                $evidence->id
            );
            $evidence->save();
        }

        return redirect()->route('evidences.index')->with('success', 'Evidencia pedagógica registrada exitosamente.');
    }

    /**
     * Display evidences for a specific book.
     *
     * @param int|string $id
     * @return View
     */
    public function show(int|string $id): View
    {
        $book = Book::findOrFail($id);
        $users = User::all();
        $books = Book::all();
        $evidences = Evidence::where('book_id', $id)->with(['user', 'book'])->get();

        return view('evidences.show', compact('book', 'users', 'evidences', 'id'));
    }

    /**
     * Filter evidences by geographical region.
     *
     * @param int|string $regionID
     * @return View
     */
    public function evidencesByRegionID(int|string $regionID): View
    {
        $region = Region::findOrFail($regionID);
        $bookIds = Book::where('region_id', $regionID)->pluck('id');
        $evidences = Evidence::whereIn('book_id', $bookIds)->with(['user', 'book'])->get();

        return view('evidences.index', compact('evidences', 'region'));
    }

    /**
     * Filter evidences by specific book for the authenticated user.
     *
     * @param int|string $bookID
     * @return View
     */
    public function evidencesByBookID(int|string $bookID): View
    {
        $book = Book::findOrFail($bookID);
        $evidences = Evidence::where('book_id', $bookID)
            ->where('user_id', Auth::id())
            ->with(['user', 'book'])
            ->get();

        return view('evidences.byBooks', compact('evidences', 'book'));
    }

    /**
     * Show the form for editing the specified evidence.
     *
     * @param Evidence $evidence
     * @return View
     */
    public function edit(Evidence $evidence): View
    {
        $books = Book::all();
        $users = User::all();

        return view('evidences.edit', compact('evidence', 'books', 'users'));
    }

    /**
     * Update the specified evidence in storage.
     *
     * @param Request $request
     * @param Evidence $evidence
     * @return RedirectResponse
     */
    public function update(Request $request, Evidence $evidence): RedirectResponse
    {
        $request->validate([
            'student_name' => 'required|string|max:255',
            'class_room' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'book_id' => 'nullable|exists:books,id',
            'attachment_media' => 'nullable|file|max:10240',
        ]);

        $evidence->user_id = (int) $request->input('user_id');
        if ($request->filled('book_id')) {
            $evidence->book_id = (int) $request->input('book_id');
        }
        $evidence->student_name = $request->input('student_name');
        $evidence->class_room = $request->input('class_room');

        if ($request->hasFile('attachment_media')) {
            $evidence->attachment_media_url = $this->mediaUploadService->upload(
                $request->file('attachment_media'),
                'evidences_images',
                $evidence->id
            );
        }

        $evidence->save();

        return redirect()->route('evidences.index')->with('success', 'Evidencia actualizada exitosamente.');
    }

    /**
     * Remove the specified evidence from storage.
     *
     * @param Evidence $evidence
     * @return RedirectResponse
     */
    public function destroy(Evidence $evidence): RedirectResponse
    {
        $evidence->delete();

        return redirect()->route('evidences.index')->with('success', 'Evidencia eliminada exitosamente.');
    }
}
