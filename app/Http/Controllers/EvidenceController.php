<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Evidence;
use App\Models\Region;
use App\Models\User;
use App\Services\MediaUploadService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EvidenceController extends Controller
{
    private const ATTACHMENT_RULE = 'nullable|file|mimes:jpg,jpeg,png,webp,gif,pdf,mp3,mp4|max:10240';

    protected MediaUploadService $mediaUploadService;

    public function __construct(MediaUploadService $mediaUploadService)
    {
        $this->mediaUploadService = $mediaUploadService;
    }

    /**
     * Display a listing of student learning evidences.
     */
    public function index(): View
    {
        $books = Book::orderBy('name')->get();

        return view('evidences.index', compact('books'));
    }

    /**
     * Show the form for creating a new evidence.
     */
    public function create(): View
    {
        $books = Book::orderBy('name')->get();
        $users = $this->assignableUsers();
        $evidences = Evidence::latest()->get();

        return view('evidences.create', compact('evidences', 'users', 'books'));
    }

    /**
     * Store a newly created evidence in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'student_name' => 'required|string|max:255',
            'class_room' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'book_id' => 'nullable|exists:books,id',
            'attachment_media' => self::ATTACHMENT_RULE,
        ]);

        $evidence = new Evidence;
        $evidence->user_id = Auth::user()->isAdmin() && $request->filled('user_id')
            ? (int) $request->input('user_id')
            : (int) Auth::id();
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

        return back()->with('success', 'Evidencia pedagógica registrada exitosamente.');
    }

    /**
     * Display evidences for a specific book.
     */
    public function show(int|string $id): View
    {
        $book = Book::findOrFail($id);
        $users = $this->assignableUsers();
        $evidences = Evidence::where('book_id', $id)->with(['user', 'book'])->get();

        return view('evidences.show', compact('book', 'users', 'evidences', 'id'));
    }

    /**
     * Filter evidences by geographical region.
     */
    public function evidencesByRegionID(int|string $regionID): View
    {
        $region = Region::findOrFail($regionID);
        $books = $region->books()->orderBy('name')->get();

        return view('evidences.index', compact('books', 'region'));
    }

    /**
     * Filter evidences by specific book for the authenticated user.
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
     */
    public function edit(Evidence $evidence): View
    {
        $books = Book::all();
        $users = User::all();

        return view('evidences.edit', compact('evidence', 'books', 'users'));
    }

    /**
     * Update the specified evidence in storage.
     */
    public function update(Request $request, Evidence $evidence): RedirectResponse
    {
        $request->validate([
            'student_name' => 'required|string|max:255',
            'class_room' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'book_id' => 'nullable|exists:books,id',
            'attachment_media' => self::ATTACHMENT_RULE,
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
     */
    public function destroy(Evidence $evidence): RedirectResponse
    {
        $evidence->delete();

        return redirect()->route('evidences.index')->with('success', 'Evidencia eliminada exitosamente.');
    }

    /**
     * Administrators may attribute an evidence to any teacher; teachers only to themselves.
     */
    private function assignableUsers(): Collection
    {
        return Auth::user()->isAdmin() ? User::orderBy('name')->get() : User::whereKey(Auth::id())->get();
    }
}
