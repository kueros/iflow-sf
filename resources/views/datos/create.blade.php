<x-layout>
        <x-slot:title>Nueva tienda</x-slot:title>

        <main class="content">
            <div class="cards">
                <div class="card card-center">
                    <div class="card-body">
                        <h1>Nueva tienda</h1>

                        @if($errors->any())
                            <div class="errors">
                                <p><strong>El formulario contiene errores, por favor corrígelos e intenta nuevamente:</strong></p>
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('datos.store') }}" method="POST">
                            @csrf

                            <label for="shop" class="field-label">Tienda: </label>
                            <input type="text" name="shop" id="shop" value="{{ old('shop') }}" class="field-input @error('shop') field-error @enderror">
                            @error('shop')
                                <p class="error-message">{{ $message }}</p>
                            @enderror

                            <label for="fApiUsr" class="field-label">User API:</label>
                            <input type="text" name="fApiUsr" id="fApiUsr" value="{{ old('fApiUsr') }}" class="field-input @error('fApiUsr') field-error @enderror">
                            @error('fApiUsr')
                                <p class="error-message">{{ $message }}</p>
                            @enderror

                            <label for="fApiClave" class="field-label">Clave API:</label>
                            <input type="text" name="fApiClave" id="fApiClave" value="{{ old('fApiClave') }}" class="field-input @error('fApiClave') field-error @enderror">
                            @error('fApiClave')
                                <p class="error-message">{{ $message }}</p>
                            @enderror

                            <button type="submit" class="btn btn-primary">Crear tienda</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
</x-layout>
