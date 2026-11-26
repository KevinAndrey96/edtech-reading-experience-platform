@extends('layouts.dashboard')

@section('navbar')
    <div style="margin-left: 30%">
        
    <div class="card mb-3 text-dark bg-white m-2" style="width: 90%; margin: 2%;">
        
        <h3 class="mx-5" style="margin-top: 10px;">Foro de experiencias pedagógicas</h3>
        <p class="mx-5" style="align: justify;">¡Bienvenido al fascinante mundo de los libros regionales de Colombia! Este espacio está diseñado para que profesores como tú compartan sus conocimientos y experiencias sobre la riqueza literaria de nuestras diferentes regiones.</p>
    </div>


    <h3 class="m-4">Comentarios de otros profes</h3>

    @foreach ($experiences as $experience)
    <div class="card mt-2" style="max-width: 80%" >
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-warning shadow-primary border-radius-lg pt-4 pb-3">
            <h6 class="text-white text-capitalize ps-3">{{ $experience->user->name }} · {{ $experience->book->name }}</h6>
            <p class="mb-0 text-white ps-3">
            </p>
          </div>
        </div>
        <div class="card-body">
          <p>{{ $experience->content }}</p>
          @if ($experience->user_id === Auth::id())
          <!-- Botones de editar y eliminar solo para el usuario actual -->
          <div style="display: flex;" >
              <form action="{{ route('experiences.destroy', $experience->id) }}" method="POST">
                  @method('DELETE')
                  @csrf
                  <i class="material-icons text-2xl">face</i>
                  <input  style="margin:5px " type="submit" width="100%" class="btn btn-danger" value="Eliminar">
              </form>
              <a href="{{ route('experiences.edit', $experience) }}" class="btn btn-outline-secondary" style="margin:5px">Editar</a>
          </div>
      @endif
        </div>
      </div>
       <br><br>
       

    @endforeach
</div>
@endsection
