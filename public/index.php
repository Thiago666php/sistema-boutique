<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Celeste Boutique</title>
    <link rel="shortcut icon" type="image/png" href="../img/icono2.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #253E63 0%, #8FB7C7 100%);
        }
        .slide { display: none; }
        .slide.active {
            display: block;
            animation: fadeIn 0.9s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>

<body class="font-sans" style="background:#EDEDED; color:#1a1a1a;">

    <nav style="background:#D6E0E4;" class="sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <img src="../img/logo1.png" alt="Logo" class="w-28 h-16">
                </div>
                <div class="hidden md:flex space-x-8 font-medium">
                    <a href="#inicio" style="color:#253E63;" class="hover:text-white transition tracking-widest text-sm">Inicio</a>
                    <a href="#nosotros" style="color:#253E63;" class="hover:text-white transition tracking-widest text-sm">Nosotros</a>
                    <a href="#colecciones" style="color:#253E63;" class="hover:text-white transition tracking-widest text-sm">Colecciones</a>
                    <a href="../views/usuarios/login.php"
                        style="background:#8FB7C7; color:#253E63;"
                        class="px-5 py-2 rounded-lg font-semibold hover:bg-white transition text-sm tracking-widest">Ingresar</a>
                </div>
            </div>
        </div>
    </nav>

    <section id="inicio" class="relative h-[520px] overflow-hidden text-white">
        <div class="slide active relative h-full">
            <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=1600&q=80"
                class="absolute inset-0 w-full h-full object-cover brightness-50" alt="Moda boutique">
            <div class="relative z-10 flex flex-col items-center justify-center h-full text-center px-4">
                <h1 class="text-4xl md:text-5xl font-light mb-4 tracking-widest uppercase">Moda que te define</h1>
                <p class="text-lg max-w-xl opacity-90 leading-relaxed">Descubre piezas únicas seleccionadas para expresar tu elegancia en cada momento especial.</p>
            </div>
        </div>
        <div class="slide relative h-full">
            <img src="https://images.unsplash.com/photo-1469334031218-e382a71b716b?auto=format&fit=crop&w=1600&q=80"
                class="absolute inset-0 w-full h-full object-cover brightness-50" alt="Nueva colección">
            <div class="relative z-10 flex flex-col items-center justify-center h-full text-center px-4">
                <h1 class="text-4xl md:text-5xl font-light mb-4 tracking-widest uppercase">Nueva Colección 2026</h1>
                <p class="text-lg max-w-xl opacity-90 leading-relaxed">Tendencias exclusivas que combinan sofisticación y comodidad en cada prenda.</p>
            </div>
        </div>
        <div class="slide relative h-full">
            <img src="https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?auto=format&fit=crop&w=1600&q=80"
                class="absolute inset-0 w-full h-full object-cover brightness-50" alt="Estilo propio">
            <div class="relative z-10 flex flex-col items-center justify-center h-full text-center px-4">
                <h1 class="text-4xl md:text-5xl font-light mb-4 tracking-widest uppercase">Tu estilo, tu esencia</h1>
                <p class="text-lg max-w-xl opacity-90 leading-relaxed">Encuentra la prenda que habla por ti. En Celeste Boutique cada detalle importa.</p>
            </div>
        </div>
        <button onclick="changeSlide(-1)"
            class="absolute left-4 top-1/2 z-20 p-3 rounded-full hover:bg-black/50 transition"
            style="background:rgba(37,62,99,0.45); border:1px solid rgba(143,183,199,0.4);">
            <i class="fas fa-chevron-left text-white"></i>
        </button>
        <button onclick="changeSlide(1)"
            class="absolute right-4 top-1/2 z-20 p-3 rounded-full hover:bg-black/50 transition"
            style="background:rgba(37,62,99,0.45); border:1px solid rgba(143,183,199,0.4);">
            <i class="fas fa-chevron-right text-white"></i>
        </button>
    </section>

    <section id="nosotros" class="py-20 max-w-7xl mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-12">
            <div class="bg-white p-8 rounded-2xl shadow-sm" style="border-top: 4px solid #253E63;">
                <div class="text-3xl mb-4" style="color:#253E63;"><i class="fas fa-gem"></i></div>
                <h2 class="text-2xl font-medium mb-4 tracking-wide" style="color:#253E63;">Nuestra Misión</h2>
                <p class="text-gray-500 leading-relaxed text-sm">
                    Ofrecer una experiencia de moda personalizada, conectando a cada clienta con prendas que reflejen su identidad y estilo único, con atención cercana y productos de alta calidad seleccionados con amor.
                </p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm" style="border-top: 4px solid #8FB7C7;">
                <div class="text-3xl mb-4" style="color:#8FB7C7;"><i class="fas fa-eye"></i></div>
                <h2 class="text-2xl font-medium mb-4 tracking-wide" style="color:#253E63;">Nuestra Visión</h2>
                <p class="text-gray-500 leading-relaxed text-sm">
                    Ser la boutique de referencia en la región, reconocida por su curaduría impecable, trato exclusivo y la capacidad de convertir la moda en una forma de expresión personal y auténtica.
                </p>
            </div>
        </div>
    </section>

    <section id="colecciones" class="py-20" style="background:#BED2DA;">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-light text-white tracking-widest uppercase" style="color:#1a2d47;">Lo que nos distingue</h2>
                <div class="w-16 h-0.5 mx-auto mt-4" style="background:#1a2d47;"></div>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-8 rounded-2xl" style="background:#1a2d47; border:1px solid rgba(143,183,199,0.25);">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto mb-4"
                        style="background:#8FB7C7; color:#253E63;">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3 class="font-medium text-sm mb-3 tracking-widest uppercase" style="color:#8FB7C7;">Exclusividad</h3>
                    <p class="text-sm leading-relaxed" style="color:rgba(237,237,237,0.7);">Piezas de diseñadores emergentes y marcas de autor que no encontrarás en otro lugar.</p>
                </div>
                <div class="text-center p-8 rounded-2xl" style="background:#1a2d47; border:1px solid rgba(143,183,199,0.25);">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto mb-4"
                        style="background:#8FB7C7; color:#253E63;">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3 class="font-medium text-sm mb-3 tracking-widest uppercase" style="color:#8FB7C7;">Asesoría de imagen</h3>
                    <p class="text-sm leading-relaxed" style="color:rgba(237,237,237,0.7);">Nuestro equipo de estilistas te guía para encontrar el look perfecto para cada ocasión.</p>
                </div>
                <div class="text-center p-8 rounded-2xl" style="background:#1a2d47; border:1px solid rgba(143,183,199,0.25);">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto mb-4"
                        style="background:#8FB7C7; color:#253E63;">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h3 class="font-medium text-sm mb-3 tracking-widest uppercase" style="color:#8FB7C7;">Colecciones frescas</h3>
                    <p class="text-sm leading-relaxed" style="color:rgba(237,237,237,0.7);">Renovamos nuestro catálogo cada temporada para mantenerte a la vanguardia de la moda.</p>
                </div>
            </div>
        </div>
    </section>

    <footer style="background:#1a2d47; color:#8FB7C7;" class="py-12">
        <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-3 gap-12">
            <div>
                <h4 class="text-white text-sm font-medium mb-4 tracking-widest uppercase">Celeste Boutique</h4>
                <p class="text-xs leading-loose" style="color:rgba(143,183,199,0.65);">Moda con alma. Cada prenda cuenta una historia, y la tuya merece ser contada con elegancia y estilo propio.</p>
            </div>
            <div>
                <h4 class="text-white text-sm font-medium mb-4 tracking-widest uppercase">Accesos Rápidos</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="#" class="hover:text-white transition">Catálogo en línea</a></li>
                    <li><a href="#" class="hover:text-white transition">Guía de tallas</a></li>
                    <li><a href="#" class="hover:text-white transition">Política de cambios</a></li>
                    <li><a href="#" class="hover:text-white transition">Preguntas frecuentes</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white text-sm font-medium mb-4 tracking-widest uppercase">Contacto</h4>
                <p class="text-xs"><i class="fas fa-envelope mr-2"></i> hola@celesteboutique.com</p>
                <p class="text-xs mt-2"><i class="fas fa-phone mr-2"></i> +57 315 000 0000</p>
                <div class="flex space-x-3 mt-4">
                    <a href="#" class="w-9 h-9 rounded-full flex items-center justify-center text-sm hover:bg-blue-400 hover:text-white transition"
                        style="border:1px solid rgba(143,183,199,0.4);"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="w-9 h-9 rounded-full flex items-center justify-center text-sm hover:text-white transition"
                        style="border:1px solid rgba(143,183,199,0.4);"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="w-9 h-9 rounded-full flex items-center justify-center text-sm hover:text-white transition"
                        style="border:1px solid rgba(143,183,199,0.4);"><i class="fab fa-pinterest-p"></i></a>
                </div>
            </div>
        </div>
        <div class="border-t mt-10 pt-6 text-center text-xs tracking-widest"
            style="border-color:rgba(143,183,199,0.2); color:rgba(143,183,199,0.4);">
            &copy; 2026 CELESTE BOUTIQUE — TODOS LOS DERECHOS RESERVADOS
        </div>
    </footer>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');

        function changeSlide(direction) {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + direction + slides.length) % slides.length;
            slides[currentSlide].classList.add('active');
        }

        setInterval(() => changeSlide(1), 5000);
    </script>
</body>
</html>