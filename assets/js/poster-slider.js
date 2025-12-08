(function() {
    // This script assumes that 'window.supabaseClient' has been initialized by config.js
    // It will not run if the poster slider container is not on the page.
    if (!document.getElementById('poster-slider')) {
        return;
    }

    // --- INITIALIZATION ---
    document.addEventListener('DOMContentLoaded', () => {
        if (window.supabaseClient) {
            initializePosterSlider();
        } else {
            console.error('Supabase client not found. Make sure config.js is loaded first.');
            showPosterError();
        }
    });

    /**
     * Initializes all functionality for the poster slider.
     */
    async function initializePosterSlider() {
        await loadAllPosters();
        setupManualNavigation();
        setupAutoRotation();
    }

    /**
     * Fetches poster data from Supabase and updates the UI.
     */
    async function loadAllPosters() {
        try {
            const { data: posterList, error } = await window.supabaseClient
                .from('promosi_poster')
                .select('id_poster, judul_poster, deskripsi_poster, url_gambar, url_tautan, urutan')
                .eq('is_aktif', true)
                .order('urutan', { ascending: true });

            if (error) throw error;
            
            updateSlidingPosters(posterList);

        } catch (err) {
            console.error('Error loading posters:', err);
            showPosterError();
        }
    }

    /**
     * Renders the poster slides and dots based on the fetched data.
     */
    function updateSlidingPosters(posterList) {
        const container = document.getElementById('poster-container');
        const dotsContainer = document.getElementById('poster-dots');

        if (!container) return;
        container.innerHTML = '';
        if (dotsContainer) dotsContainer.innerHTML = '';

        if (!posterList || posterList.length === 0) {
            container.innerHTML = '<div class="w-full text-center py-12"><p class="text-gray-600 text-lg">Tidak ada poster promosi saat ini</p></div>';
            return;
        }

        posterList.forEach((poster, index) => {
            const card = document.createElement('div');
            card.className = 'poster-card w-full flex-shrink-0 px-4 card-hover';
            card.style.minWidth = '100%';
            
            let imageUrl = poster.url_gambar;
            // The logic to get public URL from storage is complex without the full client.
            // Assuming `url_gambar` stores the full public URL for now.
            
            card.innerHTML = poster.url_tautan ?
                `<a href="${poster.url_tautan}" target="_blank" rel="noopener noreferrer">
                    <div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl shadow-2xl p-8 h-full">
                        <div class="relative overflow-hidden rounded-2xl mb-6">
                            <img src="${imageUrl}" alt="${poster.judul_poster}" class="w-full h-64 object-cover transition-transform duration-500 hover:scale-105">
                        </div>
                        <div class="text-center">
                            <h4 class="font-bold text-2xl text-gray-800 mb-3">${poster.judul_poster}</h4>
                            <p class="text-gray-700 text-lg mb-4">${poster.deskripsi_poster}</p>
                        </div>
                    </div>
                </a>` :
                `<div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl shadow-2xl p-8 h-full">
                    <div class="relative overflow-hidden rounded-2xl mb-6">
                        <img src="${imageUrl}" alt="${poster.judul_poster}" class="w-full h-64 object-cover">
                    </div>
                    <div class="text-center">
                        <h4 class="font-bold text-2xl text-gray-800 mb-3">${poster.judul_poster}</h4>
                        <p class="text-gray-700 text-lg mb-4">${poster.deskripsi_poster}</p>
                    </div>
                </div>`;
            container.appendChild(card);

            if (dotsContainer) {
                const dot = document.createElement('button');
                dot.className = `poster-dot w-4 h-4 rounded-full ${index === 0 ? 'bg-primary' : 'bg-gray-300'} transition-colors`;
                dot.setAttribute('data-index', index);
                dot.addEventListener('click', () => goToSlide(index));
                dotsContainer.appendChild(dot);
            }
        });

        window.posterCurrentSlide = 0;
        window.posterTotalSlides = posterList.length;
        updateSlidePosition();
    }

    function showPosterError() {
        const container = document.getElementById('poster-container');
        if (container) {
            container.innerHTML = '<div class="w-full text-center py-12"><p class="text-red-500 text-lg">Gagal memuat poster promosi.</p></div>';
        }
    }

    // --- SLIDER LOGIC ---
    let posterCurrentSlide = 0;
    let posterTotalSlides = 0;
    let posterAutoRotationInterval = null;

    function updateSlidePosition() {
        const container = document.getElementById('poster-container');
        if (!container) return;
        posterTotalSlides = container.children.length;
        const offset = -posterCurrentSlide * 100;
        container.style.transform = `translateX(${offset}%)`;

        const dots = document.querySelectorAll('.poster-dot');
        dots.forEach((dot, index) => {
            dot.classList.toggle('bg-primary', index === posterCurrentSlide);
            dot.classList.toggle('bg-gray-300', index !== posterCurrentSlide);
        });
    }

    function goToSlide(slideIndex) {
        if (slideIndex >= 0 && slideIndex < posterTotalSlides) {
            posterCurrentSlide = slideIndex;
            updateSlidePosition();
            resetAutoRotation();
        }
    }

    function nextSlide() {
        posterCurrentSlide = (posterCurrentSlide + 1) % posterTotalSlides;
        updateSlidePosition();
    }

    function prevSlide() {
        posterCurrentSlide = (posterCurrentSlide - 1 + posterTotalSlides) % posterTotalSlides;
        updateSlidePosition();
    }

    function setupAutoRotation() {
        clearInterval(posterAutoRotationInterval);
        if (posterTotalSlides > 1) {
            posterAutoRotationInterval = setInterval(nextSlide, 6000);
        }
    }

    function resetAutoRotation() {
        clearInterval(posterAutoRotationInterval);
        setupAutoRotation();
    }

    function setupManualNavigation() {
        document.getElementById('next-poster')?.addEventListener('click', () => {
            if (posterTotalSlides > 1) {
                nextSlide();
                resetAutoRotation();
            }
        });
        document.getElementById('prev-poster')?.addEventListener('click', () => {
            if (posterTotalSlides > 1) {
                prevSlide();
                resetAutoRotation();
            }
        });
    }
})();
