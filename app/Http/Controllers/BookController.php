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
    private const IMAGE_RULE = 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:10240';

    private const MEDIA_FIELDS = ['front_page', 'eye_image', 'diamond_image', 'message_image', 'pencil_audio'];

    private const RULES = [
        'region_id' => 'required|exists:regions,id',
        'name' => 'nullable|string|max:255',
        'planet_image' => 'nullable|string|max:500',
        'circle_audio' => 'nullable|string|max:500',
        'tv_video' => 'nullable|string|max:500',
        'face_video' => 'nullable|string|max:500',
        'message_text' => 'nullable|string',
        'message_tex' => 'nullable|string',
        'diamond_text' => 'nullable|string',
        'front_page' => self::IMAGE_RULE,
        'eye_image' => self::IMAGE_RULE,
        'diamond_image' => self::IMAGE_RULE,
        'message_image' => self::IMAGE_RULE,
        'pencil_audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240',
    ];

    protected MediaUploadService $mediaUploadService;

    public function __construct(MediaUploadService $mediaUploadService)
    {
        $this->mediaUploadService = $mediaUploadService;
    }

    /**
     * Display a listing of books.
     */
    public function index(): View
    {
        $regions = Region::orderBy('name')->get();
        $books = Book::with('region')->get();

        return view('books.index', compact('books', 'regions'));
    }

    /**
     * Show the form for creating a new book.
     */
    public function create(): View
    {
        $regions = Region::orderBy('name')->get();

        return view('books.create', compact('regions'));
    }

    /**
     * Store a newly created book in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(self::RULES);

        $book = new Book;
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

        $this->storeMedia($request, $book);

        $book->save();

        return redirect()->route('books.index')->with('success', 'Libro creado exitosamente.');
    }

    /**
     * Display the specified book.
     */
    public function show(Book $book): RedirectResponse
    {
        return redirect()->route('regions.show1', $book->region_id);
    }

    /**
     * Filter books by geographical region.
     */
    public function booksByRegionID(int|string $regionID): View
    {
        $region = Region::findOrFail($regionID);
        $books = Book::where('region_id', $regionID)->with('region')->get();

        return view('books.index', compact('books', 'region'));
    }

    /**
     * List learning evidences submitted for a specific book.
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
     */
    public function edit(int|string $id): View
    {
        $book = Book::findOrFail($id);
        $regions = Region::orderBy('name')->get();

        return view('books.edit', compact('book', 'regions'));
    }

    /**
     * Update the specified book in storage.
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        $book = Book::findOrFail($id);

        $request->validate(self::RULES);

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

        $this->storeMedia($request, $book);

        $book->save();

        return redirect()->route('books.index')->with('success', 'Libro actualizado exitosamente.');
    }

    /**
     * Remove the specified book from storage.
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

    private function storeMedia(Request $request, Book $book): void
    {
        foreach (self::MEDIA_FIELDS as $field) {
            if ($request->hasFile($field)) {
                $book->{$field} = $this->mediaUploadService->upload($request->file($field), $field, $book->id);
            }
        }
    }
}
