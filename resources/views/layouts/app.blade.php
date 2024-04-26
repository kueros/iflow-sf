<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>{{ $title ?? 'Shopify Tiendas' }}</title>
	<link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
	<div class="wrap">
		<header class="head">
			<a href="#" class="logo"></a>
			<nav class="main-nav">
				<ul class="main-nav-list">
					<li class="main-nav-item">
						<a href="{{ route('datos.index') }}" class="main-nav-link">
							<i class="icon icon-th-list"></i>
							<span>Ver tiendas</span>
						</a>
					</li>
					<li class="main-nav-item active">
						<a href="{{ route('datos.create') }}" class="main-nav-link">
							<i class="icon icon-pen"></i>
							<span>Nueva tienda</span>
						</a>
					</li>
				</ul>
			</nav>
		</header>
		{{ $slot }}
		<footer id="footer" class="foot-iflow">
			<div class="ad">
				<h6>
					<span style="font-family: Gotham, 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9px;">
						<img class="alignnone wp-image-2560" src="https://beta.iflow21.com/wp-content/uploads/2020/11/IflowLogosolo.png" alt="" width="71" height="28" />
						<br>
						<a href="https://www.google.com/maps/dir/-34.5808186,-58.4972602/-34.57027,-58.50054/@-34.5764618,-58.5081894,15z/data=!3m1!4b1!4m4!4m3!1m1!4e1!1m0" target="_blank" rel="noopener">Lavoisier 494 (B1616AWJ) - Pablo Nogués - Buenos Aires - Argentina | <br> Copyright 2023 | IFLOW | All Rights Reserved |</a> 
						<a href="https://www.iflow21.com/terminos-y-condiciones" target="_blank" rel="noopener">VER TÉRMINOS Y CONDICIONES</a>
					</span>
				</h6>
			</div>
		</footer> <!-- #footer -->
	</div>
</body>

</html>