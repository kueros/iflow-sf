<x-layout>
        <x-slot:title>Listado de tiendas</x-slot:title>

        <main class="content">
            <div class="cards">
                @forelse($shopifyDatos as $shopifyDato)
                    <div class="card card-small">
                        <div class="card-body">
                            <h4>{{ $shopifyDato->shop }}</h4>
                            <p>
                                {{ $shopifyDato->fApiUsr }}
                            </p>
                            <p>
                                {{ $shopifyDato->fApiClave }}
                            </p>
                            <form method="POST" action="{{ route('shopify.destroy', $shopifyDato) }}">
                                @method('DELETE')
                                @csrf
                                <a class="action-link">
                                    <button><i class="icon icon-trash "></button></i>
                                </a>
                                &nbsp;&nbsp;
                                <a href="{{ $shopifyDato->editUrl() }}" class="action-link action-edit">
                                    <i class="icon icon-pen"></i>
                                </a>
                            </form>
                        </div>
                        <footer class="card-footer">
                        </footer>
                    </div>
                @empty
                    <p>No hay tiendas</p>
                @endforelse
            </div>
        </main>
</x-layout>
