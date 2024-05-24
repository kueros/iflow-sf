<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Nueva Tienda</title>
	<link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
	<div class="wrap">
		<header class="head" style="background-color: orange;padding-left:0px; padding-right:15px; ">
			<!--a href="#" class="logo"></a-->
			<img src="{{ asset('../img/logoIf.png') }}" style=" height:4rem; padding-right:25px; padding-left:25px; background-color: black; " />
			<span style="padding-left:15px;">Nueva tienda</span>
		</header>





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





	</div>
</body>

</html>