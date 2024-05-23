<x-layout>
        <x-slot:title>Editar tienda3</x-slot:title>

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

                        <form action="{{ route('shopify.update', ['id' => $dato->id]) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <label for="shop" class="field-label">Tienda: </label>
                            <input type="text" name="shop" id="shop" value="{{ old('shop', $dato->shop) }}" class="field-input @error('shop') field-error @enderror">
                            @error('shop')
                                <p class="error-message">{{ $message }}</p>
                            @enderror

                            <label for="fapiusr" class="field-label">Usuario API:</label>
                            <input type="text" name="fapiusr" id="fapiusr" value="{{ old('fapiusr', $dato->fapiusr) }}" class="field-input @error('fapiusr') field-error @enderror">
                            @error('content')
                                <p class="error-message">{{ $message }}</p>
                            @enderror

                            <label for="fapiclave" class="field-label">Clave API:</label>
                            <input type="text" name="fapiclave" id="fapiclave" value="{{ old('fapiclave', $dato->fapiclave) }}" class="field-input @error('fapiclave') field-error @enderror">
                            @error('content')
                                <p class="error-message">{{ $message }}</p>
                            @enderror

                            <label for="cuit" class="field-label">Clave API:</label>
                            <input type="text" name="cuit" id="cuit" value="{{ old('cuit', $dato->cuit) }}" class="field-input @error('cuit') field-error @enderror">
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
