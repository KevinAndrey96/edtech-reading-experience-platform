@extends('layouts.dashboard')

@section('navbar')
    <div class="container py-4">
        <div class="card text-dark bg-white" style="max-width: 900px; margin: 0 auto;">
            <div class="card-body">
                <h3 class="mb-3">Editar experiencia pedagógica</h3>

                <form action="{{ route('experiences.update', $experience) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="book_id" class="form-label">Libro</label>
                        <select id="book_id" name="book_id" class="form-select" required>
                            @foreach ($books as $book)
                                <option value="{{ $book->id }}" {{ old('book_id', $experience->book_id) == $book->id ? 'selected' : '' }}>{{ $book->name }}</option>
                            @endforeach
                        </select>
                        @error('book_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Reflexión</label>
                        <textarea id="content" name="content" class="form-control border px-2" rows="6" required>{{ old('content', $experience->content) }}</textarea>
                        @error('content')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-warning">Guardar cambios</button>
                    <a href="{{ route('experiences.show', $experience->book_id) }}" class="btn btn-outline-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
@endsection
