<x-layout>
        <x-slot:title>Editar tienda</x-slot:title>

        <main class="content">
            <div class="cards">
                <div class="card card-center">
                    <div class="card-body">
                        <h1>Editar tienda</h1>

                        @if($errors->any())
                            <div class="errors">
                                <p><strong>El formulario tiene errores, por favor corregilos y volvé a enviarlo:</strong></p>
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('datos.update', ['id' => $dato->id]) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <label for="shop" class="field-label">Tienda: </label>
                            <input type="text" name="shop" id="shop" value="{{ old('shop', $dato->shop) }}" class="field-input @error('shop') field-error @enderror">
                            @error('shop')
                                <p class="error-message">{{ $message }}</p>
                            @enderror

                            <label for="fApiUsr" class="field-label">Usuario API:</label>
                            <input type="text" name="fApiUsr" id="fApiUsr" value="{{ old('fApiUsr', $dato->fApiUsr) }}" class="field-input @error('fApiUsr') field-error @enderror">
                            @error('content')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                            <label for="fApiClave" class="field-label">Clave API:</label>
                            <input type="text" name="fApiClave" id="fApiClave" value="{{ old('fApiClave', $dato->fApiClave) }}" class="field-input @error('fApiClave') field-error @enderror">
                            @error('content')
                                <p class="error-message">{{ $message }}</p>
                            @enderror

                            <button type="submit" class="btn btn-primary">Actualizar tienda</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
</x-layout>
