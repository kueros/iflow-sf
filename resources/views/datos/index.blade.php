<x-layout>
        <x-slot:title>Listado de tiendas</x-slot:title>

        <main class="content">
            <div class="cards">
                @forelse($datos as $dato)
                    <div class="card card-small">
                        <div class="card-body">
                            <h4>{{ $dato->shop }}</h4>
                            <p>
                                {{ $dato->fApiUsr }}
                            </p>
                            <p>
                                {{ $dato->fApiClave }}
                            </p>
                            <form method="POST" action="{{ route('datos.destroy', $dato) }}">
                                @method('DELETE')
                                @csrf
                                <a class="action-link">
                                    <button><i class="icon icon-trash "></button></i>
                                </a>
                                &nbsp;&nbsp;
                                <a href="{{ $dato->editUrl() }}" class="action-link action-edit">
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
