
<!DOCTYPE html>
<html lang="en" class="dark">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<title>@yield('title') - {{ $settings->site_name }}</title>

	<link rel="icon" href="{{ asset('storage/app/public/' . $settings->favicon) }}" type="image/png" />

	<!-- Google font -->
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

	<!-- Font Awesome Icon -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

	<!-- Tailwind CSS -->
	<script src="https://cdn.tailwindcss.com"></script>
	<script>
		tailwind.config = {
			darkMode: 'class',
			theme: {
				extend: {
					colors: {
						primary: { DEFAULT: 'rgb(154, 217, 83)', dark: 'rgb(154, 217, 83)', light: 'rgb(154, 217, 83)' },
						danger: { DEFAULT: '#ef4444', dark: '#dc2626', light: '#f87171' },
						dark: { 100: '#0D0E10', 200: '#1e1e1e', 300: '#121212', 400: '#121212', 500: '#0D0E10' }
					}
				}
			}
		}
	</script>

	<!-- Custom styles -->
	<style>
		body {
			font-family: 'Inter', sans-serif;
			background: linear-gradient(135deg, #121212 0%, #1e1e1e 50%, #0D0E10 100%);
			background-size: 400% 400%;
			animation: gradientShift 15s ease infinite;
		}

		@keyframes gradientShift {
			0%, 100% { background-position: 0% 50%; }
			50% { background-position: 100% 50%; }
		}

		@keyframes float {
			0%, 100% { transform: translateY(0px); }
			50% { transform: translateY(-20px); }
		}

		.float-animation {
			animation: float 3s ease-in-out infinite;
		}

		@keyframes pulse {
			0%, 100% { opacity: 1; }
			50% { opacity: 0.5; }
		}

		.pulse-animation {
			animation: pulse 2s ease-in-out infinite;
		}

		.btn-hover {
			transition: all 0.3s ease;
		}

		.btn-hover:hover {
			transform: translateY(-2px);
			box-shadow: 0 10px 30px rgba(34, 197, 94, 0.3);
		}
	</style>
</head>

<body class="antialiased min-h-screen flex items-center justify-center px-4 py-8">
	<div class="text-center max-w-2xl mx-auto">
		<!-- Logo -->
		<div class="mb-8">
			<img src="{{ asset('storage/app/public/' . $settings->logo) }}" alt="{{ $settings->site_name }}" class="h-12 mx-auto opacity-80">
		</div>

		<!-- Error Card -->
		<div class="bg-dark-200 rounded-2xl shadow-2xl border border-dark-100 p-8 sm:p-12 backdrop-blur-xl bg-opacity-80">
			<!-- Icon -->
			<div class="mb-6 float-animation">
				<div class="inline-flex items-center justify-center w-24 h-24 bg-primary bg-opacity-10 rounded-full">
					<i class="fas fa-exclamation-triangle text-4xl text-primary"></i>
				</div>
			</div>

			<!-- Error Code -->
			<div class="text-8xl font-black text-white mb-4">
				@yield('code')
			</div>

			<!-- Title -->
			<h1 class="text-2xl sm:text-3xl font-bold text-white mb-4">
				@yield('title')
			</h1>

			<!-- Accent Line -->
			<div class="h-1 w-24 bg-gradient-to-r from-primary to-transparent rounded-full mb-6 mx-auto"></div>

			<!-- Message -->
			<p class="text-lg text-gray-400 mb-8 leading-relaxed">
				@yield('message')
			</p>

			<!-- Buttons -->
			<div class="flex flex-col sm:flex-row gap-4 justify-center">
				<a href="{{ url('/') }}" class="btn-hover inline-flex items-center justify-center gap-2 px-8 py-4 bg-primary text-white font-semibold rounded-xl shadow-lg hover:bg-primary-dark">
					<i class="fas fa-home"></i>
					<span>Go Home</span>
				</a>
				<button onclick="window.history.back()" class="btn-hover inline-flex items-center justify-center gap-2 px-8 py-4 bg-dark-300 text-gray-300 font-semibold rounded-xl border border-dark-100 shadow-lg hover:bg-dark-100 hover:text-white">
					<i class="fas fa-arrow-left"></i>
					<span>Go Back</span>
				</button>
			</div>
		</div>

		<!-- Footer -->
		<div class="mt-8">
			<p class="text-sm text-gray-500">
				Need help? Contact us at 
				<a href="mailto:{{ $settings->contact_email }}" class="text-primary hover:text-primary-light transition-colors">
					{{ $settings->contact_email }}
				</a>
			</p>
		</div>
	</div>
</body>
</html>

