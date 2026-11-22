<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Evidence;
use App\Models\Region;
use App\Services\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookController extends Controller
{
    protected MediaUploadService $mediaUploadService;

    public function __construct(MediaUploadService $mediaUploadService)
    {
        $this->mediaUploadService = $mediaUploadService;
    }

    /**
     * Display a listing of books.
     *
     * @return View
     */
    public function index(): View
    {
        $regions = Region::orderBy('name')->get();
        $books = Book::with('region')->get();

        return view('books.index', compact('books', 'regions'));
    }

    /**
     * Show the form for creating a new book.
     *
     * @return View
     */
    public function create(): View
    {
        $regions = Region::orderBy('name')->get();

        return view('books.create', compact('regions'));
    }

    /**
     * Store a newly created book in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'nullable|string|max:255',
            'planet_image' => 'nullable|string|max:500',
            'circle_audio' => 'nullable|string|max:500',
            'tv_video' => 'nullable|string|max:500',
            'face_video' => 'nullable|string|max:500',
            'message_text' => 'nullable|string',
            'message_tex' => 'nullable|string',
            'diamond_text' => 'nullable|string',
            'front_page' => 'nullable|file|max:10240',
            'eye_image' => 'nullable|file|max:10240',
            'diamond_image' => 'nullable|file|max:10240',
            'message_image' => 'nullable|file|max:10240',
            'pencil_audio' => 'nullable|file|max:10240',
        ]);

        $book = new Book();
        $book->region_id = (int) $request->input('region_id');
        $book->name = $request->input('name', '');
        $book->planet_image = $request->input('planet_image', '');
        $book->circle_audio = $request->input('circle_audio', '');
        $book->tv_video = $request->input('tv_video', '');
        $book->face_video = $request->input('face_video', '');
        $book->message_text = $request->input('message_text', $request->input('message_tex', ''));
        $book->diamond_text = $request->input('diamond_text', '');
        $book->front_page = '';
        $book->eye_image = '';
        $book->diamond_image = '';
        $book->message_image = '';
        $book->pencil_audio = '';
        $book->save();

        // Process media uploads
        $mediaFields = [
            'front_page' => 'front_page',
            'eye_image' => 'eye_image',
            'diamond_image' => 'diamond_image',
            'message_image' => 'message_image',
            'pencil_audio' => 'pencil_audio',
        ];

        foreach ($mediaFields as $inputKey => $folder) {
            if ($request->hasFile($inputKey)) {
                $book->{$inputKey} = $this->mediaUploadService->upload(
                    $request->file($inputKey),
                    $folder,
                    $book->id
                );
            }
        }

        $book->save();

        return redirect()->route('books.index')->with('success', 'Libro creado exitosamente.');
    }

    /**
     * Display the specified book.
     *
     * @param Book $book
     * @return View
     */
    public function show(Book $book): View
    {
        $book->load(['region', 'experiences.user', 'evidences']);

        return view('books.show', compact('book'));
    }

    /**
     * Filter books by geographical region.
     *
     * @param int|string $regionID
     * @return View
     */
    public function booksByRegionID(int|string $regionID): View
    {
        $region = Region::findOrFail($regionID);
        $books = Book::where('region_id', $regionID)->with('region')->get();

        return view('books.index', compact('books', 'region'));
    }

    /**
     * List learning evidences submitted for a specific book.
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
     * Show the form for editing the specified book.
     *
     * @param int|string $id
     * @return View
     */
    public function edit(int|string $id): View
    {
        $book = Book::findOrFail($id);
        $regions = Region::orderBy('name')->get();

        return view('books.edit', compact('book', 'regions'));
    }

    /**
     * Update the specified book in storage.
     *
     * @param Request $request
     * @param int|string $id
     * @return RedirectResponse
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        $book = Book::findOrFail($id);

        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'nullable|string|max:255',
            'planet_image' => 'nullable|string|max:500',
            'circle_audio' => 'nullable|string|max:500',
            'tv_video' => 'nullable|string|max:500',
            'face_video' => 'nullable|string|max:500',
            'message_text' => 'nullable|string',
            'message_tex' => 'nullable|string',
            'diamond_text' => 'nullable|string',
            'front_page' => 'nullable|file|max:10240',
            'eye_image' => 'nullable|file|max:10240',
            'diamond_image' => 'nullable|file|max:10240',
            'message_image' => 'nullable|file|max:10240',
            'pencil_audio' => 'nullable|file|max:10240',
        ]);

        $book->region_id = (int) $request->input('region_id');
        $book->name = $request->input('name', $book->name);
        $book->planet_image = $request->input('planet_image', $book->planet_image);
        $book->circle_audio = $request->input('circle_audio', $book->circle_audio);
        $book->tv_video = $request->input('tv_video', $book->tv_video);
        $book->face_video = $request->input('face_video', $book->face_video);
        $book->diamond_text = $request->input('diamond_text', $book->diamond_text);

        if ($request->filled('message_text')) {
            $book->message_text = $request->input('message_text');
        } elseif ($request->filled('message_tex')) {
            $book->message_text = $request->input('message_tex');
        }

        // Process media updates
        $mediaFields = [
            'front_page' => 'front_page',
            'eye_image' => 'eye_image',
            'diamond_image' => 'diamond_image',
            'message_image' => 'message_image',
            'pencil_audio' => 'pencil_audio',
        ];

        foreach ($mediaFields as $inputKey => $folder) {
            if ($request->hasFile($inputKey)) {
                $book->{$inputKey} = $this->mediaUploadService->upload(
                    $request->file($inputKey),
                    $folder,
                    $book->id
                );
            }
        }

        $book->save();

        return redirect()->route('books.index')->with('success', 'Libro actualizado exitosamente.');
    }

    /**
     * Remove the specified book from storage.
     *
     * @param int|string $id
     * @return RedirectResponse
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $book = Book::findOrFail($id);

        // Delete associated experiences and evidences
        $book->experiences()->delete();
        $book->evidences()->delete();
        $book->delete();

        return back()->with('success', 'Libro eliminado exitosamente.');
    }
}
