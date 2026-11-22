<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Experience;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExperienceController extends Controller
{
    /**
     * Display a listing of pedagogical reading experiences.
     *
     * @return View
     */
    public function index(): View
    {
        $experiences = Experience::with(['book', 'user'])->latest()->get();
        $users = User::all();
        $books = Book::orderBy('name')->get();

        return view('experiences.index', compact('experiences', 'users', 'books'));
    }

    /**
     * Show the form for creating a new experience.
     *
     * @return View
     */
    public function create(): View
    {
        $users = User::all();
        $books = Book::orderBy('name')->get();

        return view('experiences.create', compact('users', 'books'));
    }

    /**
     * Store a newly created experience in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string',
            'book_id' => 'required|exists:books,id',
        ]);

        $experience = new Experience();
        $experience->content = $request->input('content');
        $experience->user_id = Auth::id();
        $experience->book_id = (int) $request->input('book_id');
        $experience->save();

        return redirect()->route('experiences.index')->with('success', 'Experiencia pedagógica compartida exitosamente.');
    }

    /**
     * Show the form for editing the specified experience.
     *
     * @param Experience $experience
     * @return View
     */
    public function edit(Experience $experience): View
    {
        // Check ownership or admin privilege
        if (Auth::id() !== $experience->user_id && !Auth::user()->isAdmin()) {
            abort(403, 'Acceso no autorizado para editar esta experiencia.');
        }

        $users = User::all();
        $books = Book::orderBy('name')->get();

        return view('experiences.edit', compact('experience', 'users', 'books'));
    }

    /**
     * Update the specified experience in storage.
     *
     * @param Request $request
     * @param Experience $experience
     * @return RedirectResponse
     */
    public function update(Request $request, Experience $experience): RedirectResponse
    {
        if (Auth::id() !== $experience->user_id && !Auth::user()->isAdmin()) {
            abort(403, 'Acceso no autorizado para modificar esta experiencia.');
        }

        $request->validate([
            'content' => 'required|string',
            'book_id' => 'required|exists:books,id',
        ]);

        $experience->content = $request->input('content');
        $experience->book_id = (int) $request->input('book_id');
        $experience->save();

        return redirect()->route('experiences.index')->with('success', 'Experiencia pedagógica actualizada.');
    }

    /**
     * Remove the specified experience from storage.
     *
     * @param Experience $experience
     * @return RedirectResponse
     */
    public function destroy(Experience $experience): RedirectResponse
    {
        if (Auth::id() !== $experience->user_id && !Auth::user()->isAdmin()) {
            abort(403, 'Acceso no autorizado para eliminar esta experiencia.');
        }

        $experience->delete();

        return redirect()->route('experiences.index')->with('success', 'Experiencia eliminada exitosamente.');
    }

    /**
     * Display experiences associated with a specific book.
     *
     * @param int|string $id
     * @return View
     */
    public function show(int|string $id): View
    {
        $book = Book::with(['experiences.user', 'region'])->findOrFail($id);
        $experiences = $book->experiences;

        return view('experiences.show', compact('book', 'experiences'));
    }
}
