<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegionController extends Controller
{
    /**
     * Display a listing of geographical and cultural regions.
     *
     * @return View
     */
    public function index(): View
    {
        $regions = Region::withCount('books')->orderBy('name')->get();
        $books = Book::all();

        return view('regions.index', compact('regions', 'books'));
    }

    /**
     * Show the form for creating a new region.
     *
     * @return View
     */
    public function create(): View
    {
        $regions = Region::orderBy('name')->get();

        return view('regions.create', compact('regions'));
    }

    /**
     * Store a newly created region in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'blog_url' => 'nullable|url|max:500',
        ]);

        Region::create($validated);

        return redirect()->route('regions.index')->with('success', 'Región registrada exitosamente.');
    }

    /**
     * Display the specified region and its associated books.
     *
     * @param int|string $regionID
     * @return View
     */
    public function show(int|string $regionID): View
    {
        $region = Region::findOrFail($regionID);
        $books = Book::where('region_id', $regionID)->with('region')->get();

        return view('regions.show', compact('books', 'region'));
    }

    /**
     * Show the form for editing the specified region.
     *
     * @param Region $region
     * @return View
     */
    public function edit(Region $region): View
    {
        return view('regions.edit', compact('region'));
    }

    /**
     * Update the specified region in storage.
     *
     * @param Request $request
     * @param Region $region
     * @return RedirectResponse
     */
    public function update(Request $request, Region $region): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'blog_url' => 'nullable|url|max:500',
        ]);

        $region->update($validated);

        return redirect()->route('regions.index')->with('success', 'Región actualizada exitosamente.');
    }

    /**
     * Remove the specified region from storage.
     *
     * @param Region $region
     * @return RedirectResponse
     */
    public function destroy(Region $region): RedirectResponse
    {
        $region->delete();

        return redirect()->route('regions.index')->with('success', 'Región eliminada.');
    }
}
