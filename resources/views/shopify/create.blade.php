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

                        <form action="{{ route('shopify.store') }}" method="POST">
                            @csrf

                            <label for="shop" class="field-label">Tienda: </label>
                            <input type="text" name="shop" id="shop" value="{{ old('shop') }}" class="field-input @error('shop') field-error @enderror">
                            @error('shop')
                                <p class="error-message">{{ $message }}</p>
                            @enderror

                            <label for="fapiusr" class="field-label">User API:</label>
                            <input type="text" name="fapiusr" id="fapiusr" value="{{ old('fapiusr') }}" class="field-input @error('fapiusr') field-error @enderror">
                            @error('fapiusr')
                                <p class="error-message">{{ $message }}</p>
                            @enderror

                            <label for="fapiclave" class="field-label">Clave API:</label>
                            <input type="text" name="fapiclave" id="fapiclave" value="{{ old('fapiclave') }}" class="field-input @error('fapiclave') field-error @enderror">
                            @error('fapiclave')
                                <p class="error-message">{{ $message }}</p>
                            @enderror

                            <label for="cuit" class="field-label">CUIT:</label>
                            <input type="text" name="cuit" id="cuit" value="{{ old('cuit') }}" class="field-input @error('cuit') field-error @enderror">
                            @error('cuit')
                                <p class="error-message">{{ $message }}</p>
                            @enderror

                            <button type="submit" class="btn btn-primary">Crear tienda</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
</x-layout>
