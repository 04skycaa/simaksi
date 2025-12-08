(function() {
    // This script assumes that 'window.supabaseClient' has been initialized by config.js
    // It will not run if the testimonial slider section is not on the page.
    if (!document.getElementById('testimoni-container')) {
        return;
    }

    // --- INITIALIZATION ---
    document.addEventListener('DOMContentLoaded', () => {
        if (window.supabaseClient) {
            initializeSlider();
        } else {
            console.error('Supabase client not found for sliding-komentar.js. Check script order.');
            showKomentarError();
        }
    });

    /**
     * Initializes all functionality for the testimonial slider.
     */
    async function initializeSlider() {
        await loadAllKomentar();
        setupManualNavigation();
        setupAutoRotation();
        setupRatingSystem(); // Assuming rating form is part of this component
        subscribeToAuthChanges(); // To show/hide comment form
        checkLoginStatus(); // Initial check
        
        const komentarForm = document.getElementById('komentarForm');
        if (komentarForm) {
            komentarForm.addEventListener('submit', handleKomentarSubmit);
        }

        window.addEventListener('resize', () => {
            updateSlidePosition();
        });
    }

    /**
     * Fetches comments from Supabase and triggers the UI update.
     */
    async function loadAllKomentar() {
        try {
            const { data: komentarList, error } = await window.supabaseClient
                .from('komentar')
                .select('id_komentar, komentar, rating, dibuat_pada, profiles(nama_lengkap)')
                .order('dibuat_pada', { ascending: false });
            
            if (error) throw error;
            updateSlidingTestimonials(komentarList);

        } catch (err) {
            console.error('Error loading comments:', err);
            showKomentarError();
        }
    }

    /**
     * Renders the testimonial slider UI.
     */
    function updateSlidingTestimonials(komentarList) {
        const container = document.getElementById('testimoni-container');
        const dotsContainer = document.getElementById('testimonial-dots');
        if (!container) return;

        container.innerHTML = '';
        if (dotsContainer) dotsContainer.innerHTML = '';

        if (!komentarList || komentarList.length === 0) {
            container.innerHTML = `<div class="w-full text-center py-12"><p class="text-gray-600 text-lg">Belum ada komentar.</p></div>`;
            updateStats(0, 0);
            return;
        }

        updateStats(
            komentarList.length,
            komentarList.reduce((sum, k) => sum + k.rating, 0) / komentarList.length
        );

        komentarList.forEach((komentar, index) => {
            const card = createTestimonialCard(komentar);
            container.appendChild(card);

            if (dotsContainer) {
                const dot = document.createElement('button');
                dot.className = `testimonial-dot w-4 h-4 rounded-full ${index === 0 ? 'bg-primary' : 'bg-gray-300'} transition-colors`;
                dot.setAttribute('data-index', index);
                dot.addEventListener('click', () => goToSlide(index));
                dotsContainer.appendChild(dot);
            }
        });

        window.totalSlides = komentarList.length;
        window.currentSlide = 0;
        updateSlidePosition();
        resetAutoRotation(); // Reset rotation after loading
    }
    
    function createTestimonialCard(komentar) {
        const date = new Date(komentar.dibuat_pada).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
        const namaLengkap = komentar.profiles ? komentar.profiles.nama_lengkap : 'Pendaki';
        const inisial = namaLengkap.charAt(0).toUpperCase();

        const card = document.createElement('div');
        card.className = 'testimonial-card w-full flex-shrink-0 px-4';
        card.style.minWidth = '100%';
        card.innerHTML = `
            <div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl shadow-2xl p-8 h-full">
                <div class="flex items-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-r from-accent to-primary rounded-full flex items-center justify-center text-white font-bold text-2xl mr-6">${inisial}</div>
                    <div>
                        <h4 class="font-bold text-2xl text-gray-800">${namaLengkap}</h4>
                        <div class="flex items-center mt-2">
                            <div class="rating-display text-yellow-400 flex text-2xl">${generateStars(komentar.rating)}</div>
                            <span class="ml-3 text-gray-600 text-lg">${komentar.rating}/5</span>
                        </div>
                    </div>
                </div>
                <p class="text-gray-700 text-xl italic mb-6 leading-relaxed">"${komentar.komentar}"</p>
                <div class="text-gray-500 text-lg">- ${date}</div>
            </div>`;
        return card;
    }


    function updateStats(totalComments, avgRating) {
        const totalEl = document.getElementById('total-komentar');
        const avgEl = document.getElementById('avg-rating');
        const satisfactionEl = document.getElementById('total-pendaki-rating');
        if(totalEl) totalEl.textContent = totalComments;
        if(avgEl) avgEl.textContent = avgRating ? avgRating.toFixed(1) + '/5' : '0/5';
        if(satisfactionEl) {
            const percentage = avgRating ? Math.round((avgRating / 5) * 100) : 0;
            satisfactionEl.textContent = totalComments > 0 ? percentage + '%' : '0%';
        }
    }

    function generateStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) stars += `<span class="${i <= rating ? 'text-yellow-400' : 'text-gray-300'}">★</span>`;
        return stars;
    }

    function showKomentarError() {
        const container = document.getElementById('testimoni-container');
        if (container) container.innerHTML = '<div class="w-full text-center py-12"><p class="text-red-500 text-lg">Gagal memuat komentar.</p></div>';
    }

    // --- SLIDER CONTROLS ---
    let currentSlide = 0;
    let totalSlides = 0;
    let autoRotationInterval;

    function updateSlidePosition() {
        const container = document.getElementById('testimoni-container');
        if (!container || totalSlides === 0) return;
        container.style.transform = `translateX(${-currentSlide * 100}%)`;

        document.querySelectorAll('.testimonial-dot').forEach((dot, index) => {
            dot.classList.toggle('bg-primary', index === currentSlide);
            dot.classList.toggle('bg-gray-300', index !== currentSlide);
        });
    }
    
    function goToSlide(slideIndex) {
        currentSlide = slideIndex;
        updateSlidePosition();
        resetAutoRotation();
    }

    function setupManualNavigation() {
        document.getElementById('next-testimonial')?.addEventListener('click', () => {
            if (totalSlides > 1) {
                currentSlide = (currentSlide + 1) % totalSlides;
                updateSlidePosition();
                resetAutoRotation();
            }
        });
        document.getElementById('prev-testimonial')?.addEventListener('click', () => {
            if (totalSlides > 1) {
                currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
                updateSlidePosition();
                resetAutoRotation();
            }
        });
    }

    function setupAutoRotation() {
        clearInterval(autoRotationInterval);
        if (totalSlides > 1) {
            autoRotationInterval = setInterval(() => {
                currentSlide = (currentSlide + 1) % totalSlides;
                updateSlidePosition();
            }, 5000);
        }
    }

    function resetAutoRotation() {
        clearInterval(autoRotationInterval);
        setupAutoRotation();
    }

    // --- AUTH & FORM LOGIC ---
    function setupRatingSystem() { /* ... rating logic ... */ }

    async function checkLoginStatus() {
        const form = document.getElementById('komentar-form-section');
        if(!form) return;
        try {
            const { data: { session } } = await window.supabaseClient.auth.getSession();
            if (session) {
                const { data: profile } = await window.supabaseClient.from('profiles').select('peran').eq('id', session.user.id).single();
                form.classList.toggle('hidden', !profile || profile.peran !== 'pendaki');
            } else {
                form.classList.add('hidden');
            }
        } catch (e) {
            form.classList.add('hidden');
        }
    }

    function subscribeToAuthChanges() {
        window.supabaseClient.auth.onAuthStateChange((event, session) => {
            checkLoginStatus();
        });
    }

    async function handleKomentarSubmit(event) {
        event.preventDefault();
        const errorMsg = document.getElementById('komentar-error-message');
        const successMsg = document.getElementById('komentar-success-message');
        
        try {
            const { data: { session } } = await window.supabaseClient.auth.getSession();
            if (!session) throw new Error('Anda harus login untuk memberikan komentar.');
            
            const komentar = document.getElementById('isi-komentar').value.trim();
            const ratingInput = document.querySelector('input[name="rating"]:checked');
            if (!komentar || !ratingInput) throw new Error('Komentar dan Rating wajib diisi.');
            
            const { error } = await window.supabaseClient.from('komentar').insert([{ id_pengguna: session.user.id, komentar, rating: parseInt(ratingInput.value) }]);
            if (error) throw error;
            
            showMessage(successMsg, 'Komentar berhasil dikirim!');
            document.getElementById('komentarForm').reset();
            document.getElementById('rating-value').textContent = 'Pilih rating';
            await loadAllKomentar();

        } catch(error) {
            showMessage(errorMsg, error.message);
        }
    }

    function showMessage(element, message) {
        if(element) {
            element.textContent = message;
            element.classList.remove('hidden');
            setTimeout(() => element.classList.add('hidden'), 5000);
        }
    }

})();
