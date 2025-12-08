(function() {
    // This script assumes that 'window.supabaseClient' has been initialized by config.js
    // It will not run if the testimonial container is not on the page.
    if (!document.getElementById('testimoni-container')) {
        return;
    }

    const TABLE_NAME = 'komentar';

    /**
     * Fetches testimonials from the database and updates the UI.
     */
    async function loadTestimonials() {
        const container = document.getElementById('testimoni-container');
        const loadingMessage = document.getElementById('loading-message');
        if (!container || !loadingMessage) return;

        container.innerHTML = ''; // Clear previous content
        loadingMessage.classList.remove('hidden');
        container.appendChild(loadingMessage);

        try {
            if (!window.supabaseClient) {
                throw new Error('Supabase client not initialized.');
            }

            const { data, error } = await window.supabaseClient
                .from(TABLE_NAME)
                .select('id_komentar, komentar, rating, dibuat_pada, profiles(nama_lengkap)')
                .order('dibuat_pada', { ascending: false })
                .limit(10);

            if (loadingMessage.parentNode === container) {
                container.removeChild(loadingMessage);
            }

            if (error) throw error;

            if (data.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center w-full">Belum ada testimonial. Jadilah yang pertama!</p>';
                return;
            }

            data.forEach(item => {
                const testimonialCard = createTestimonialCard(item);
                container.appendChild(testimonialCard);
            });

            // Re-initialize the slider logic if it exists
            if (typeof initSlider === 'function') {
                initSlider();
            }

        } catch (error) {
            console.error('Error memuat data testimonial:', error);
            const container = document.getElementById('testimoni-container');
            if(container) container.innerHTML = `<p class="text-red-500 text-center w-full">Gagal memuat testimonial.</p>`;
        }
    }

    /**
     * Creates an HTML element for a single testimonial card.
     */
    function createTestimonialCard(data) {
        const date = new Date(data.dibuat_pada).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
        const userName = data.profiles ? data.profiles.nama_lengkap : 'Anonim';
        const renderStars = (rating) => {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                stars += `<i class="fas fa-star ${i <= rating ? 'text-yellow-400' : 'text-gray-300'}"></i>`;
            }
            return stars;
        };

        const card = document.createElement('div');
        card.className = 'testimonial-card flex-shrink-0 px-4';
        card.innerHTML = `
            <div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl shadow-2xl p-8 h-full">
                <div class="flex-display justify-between items-start mb-4">
                    <div class="text-2xl">${renderStars(data.rating)}</div>
                    <div class="text-sm text-gray-500">${date}</div>
                </div>
                <p class="text-gray-800 text-lg mb-6 leading-relaxed">"${data.komentar}"</p>
                <div class="border-t border-gray-200 pt-4">
                    <p class="font-semibold text-primary">Pengguna: ${userName}</p>
                    <p class="text-sm text-gray-500">ID Komentar: ${data.id_komentar}</p>
                </div>
            </div>`;
        return card;
    }

    /**
     * Handles the submission of the comment form.
     */
    async function handleFormSubmit(event) {
        event.preventDefault();
        const errorMsg = document.getElementById('komentar-error-message');
        const successMsg = document.getElementById('komentar-success-message');
        errorMsg.classList.add('hidden');
        successMsg.classList.add('hidden');

        try {
            if (!window.supabaseClient) {
                throw new Error('Supabase client not initialized.');
            }
            const { data: { user } } = await window.supabaseClient.auth.getUser();
            if (!user) {
                throw new Error('Anda harus login untuk mengirim komentar.');
            }

            const komentarInput = document.getElementById('isi-komentar');
            const ratingInput = document.querySelector('input[name="rating"]:checked');
            const komentar = komentarInput.value.trim();
            const rating = ratingInput ? parseInt(ratingInput.value) : null;

            if (!komentar || !rating) {
                throw new Error('Komentar dan Rating wajib diisi.');
            }

            const newComment = { id_pengguna: user.id, komentar: komentar, rating: rating };
            const { error: insertError } = await window.supabaseClient.from(TABLE_NAME).insert([newComment]);
            if (insertError) throw insertError;

            successMsg.textContent = 'Terima kasih! Komentar Anda berhasil dikirim.';
            successMsg.classList.remove('hidden');
            komentarInput.value = '';
            if (ratingInput) ratingInput.checked = false;
            document.getElementById('rating-value').textContent = 'Pilih rating';
            
            // Reload testimonials to show the new one
            await loadTestimonials();

        } catch (error) {
            console.error('Error saat mengirim komentar:', error);
            errorMsg.textContent = error.message;
            errorMsg.classList.remove('hidden');
        }
    }

    // --- INITIALIZATION ---
    document.addEventListener('DOMContentLoaded', () => {
        // Check if the supabase client is ready before setting up listeners
        if (window.supabaseClient) {
            loadTestimonials();
            const komentarForm = document.getElementById('komentarForm');
            if (komentarForm) {
                komentarForm.addEventListener('submit', handleFormSubmit);
            }
        } else {
            console.error('Supabase client not found on DOMContentLoaded. Check script loading order.');
        }
    });

})();
