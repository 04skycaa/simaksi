(function() {
    // This script assumes that 'window.supabaseClient' has been initialized by config.js
    // It will run for both the old testimonial section and the new forum comment section.

    const TABLE_NAME = 'komentar';

    /**
     * Fetches comments from the database and updates the UI.
     */
    async function loadComments() {
        // Check if we have the new forum-style comment section (higher priority)
        const forumContainer = document.getElementById('daftar-komentar');
        const loadingElement = document.getElementById('loading-comments');
        const noCommentsElement = document.getElementById('no-comments');

        // If we have the new forum section, use that
        if (forumContainer) {
            if (loadingElement) loadingElement.classList.remove('hidden');
            if (noCommentsElement) noCommentsElement.classList.add('hidden');

            try {
                if (!window.supabaseClient) {
                    throw new Error('Supabase client not initialized.');
                }

                // Get comments with user profile information
                const { data, error } = await window.supabaseClient
                    .from(TABLE_NAME)
                    .select('id_komentar, id_pengguna, komentar, rating, dibuat_pada, profiles(nama_lengkap)')
                    .order('dibuat_pada', { ascending: false });

                if (loadingElement) loadingElement.classList.add('hidden');

                if (error) throw error;

                if (data.length === 0) {
                    if (noCommentsElement) noCommentsElement.classList.remove('hidden');
                    return;
                }

                // Clear the container
                forumContainer.innerHTML = '';

                // Add the comments to the container
                data.forEach(item => {
                    const commentElement = createCommentElement(item);
                    forumContainer.appendChild(commentElement);
                });

                // Update stats
                updateStats(data.length, calculateAverageRating(data));

            } catch (error) {
                console.error('Error memuat komentar:', error);
                if (loadingElement) loadingElement.classList.add('hidden');
                forumContainer.innerHTML = `<p class="text-red-500 text-center w-full py-12">Gagal memuat komentar. ${error.message}</p>`;
            }
        }
        // Otherwise, try the old testimonial section for backward compatibility
        else {
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
    }

    /**
     * Creates an HTML element for a single comment in the forum style.
     */
    function createCommentElement(data) {
        const date = new Date(data.dibuat_pada).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        const userName = data.profiles ? data.profiles.nama_lengkap : 'Pendaki';
        const userInitial = userName.charAt(0).toUpperCase();

        const commentElement = document.createElement('div');
        commentElement.className = 'bg-white rounded-2xl shadow-lg p-6 border border-gray-100';
        commentElement.innerHTML = `
            <div class="flex items-start">
                <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-blue-500 rounded-full flex items-center justify-center text-white font-bold mr-4 flex-shrink-0">
                    ${userInitial}
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-gray-800">${userName}</h4>
                            <div class="flex items-center mt-1">
                                <div class="rating-display text-yellow-400 flex text-xl">${generateStars(data.rating)}</div>
                                <span class="ml-2 text-gray-600 text-sm">${data.rating}/5</span>
                            </div>
                        </div>
                        <span class="text-gray-500 text-sm">${date}</span>
                    </div>
                    <p class="mt-3 text-gray-700 leading-relaxed">${data.komentar}</p>
                </div>
            </div>
        `;
        return commentElement;
    }

    /**
     * Creates an HTML element for a single testimonial card (for backward compatibility).
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
     * Generates star HTML based on rating.
     */
    function generateStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<span class="${i <= rating ? 'text-yellow-400' : 'text-gray-300'}">★</span>`;
        }
        return stars;
    }

    /**
     * Calculates the average rating from a list of comments.
     */
    function calculateAverageRating(comments) {
        if (comments.length === 0) return 0;
        const totalRating = comments.reduce((sum, comment) => sum + comment.rating, 0);
        return totalRating / comments.length;
    }

    /**
     * Updates the statistics display.
     */
    function updateStats(totalComments, avgRating) {
        const totalEl = document.getElementById('total-komentar');
        const avgEl = document.getElementById('avg-rating');
        const satisfactionEl = document.getElementById('total-pendaki-rating');

        if(totalEl) {
            totalEl.textContent = totalComments;
        }
        if(avgEl) {
            avgEl.textContent = avgRating ? avgRating.toFixed(1) + '/5' : '0/5';
        }
        if(satisfactionEl) {
            const percentage = avgRating ? Math.round((avgRating / 5) * 100) : 0;
            satisfactionEl.textContent = totalComments > 0 ? percentage + '%' : '0%';
        }
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

            let user = null;
            try {
                const { data, error } = await window.supabaseClient.auth.getSession();
                if (error) {
                    console.error('Error getting session from Supabase:', error.message);
                    throw new Error('Sesi login Anda tidak valid. Silakan login kembali.');
                }

                if (data && data.session && data.session.user) {
                    user = data.session.user;
                } else {
                    throw new Error('Silakan login terlebih dahulu untuk mengirim komentar.');
                }
            } catch (getSessionError) {
                console.error('Error getting session:', getSessionError);
                throw new Error('Silakan login terlebih dahulu untuk mengirim komentar.');
            }

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

            // Reload comments to show the new one
            await loadComments();

        } catch (error) {
            console.error('Error saat mengirim komentar:', error);
            errorMsg.textContent = error.message || 'Gagal mengirim komentar. Silakan coba lagi.';
            errorMsg.classList.remove('hidden');
        }
    }

    // --- INITIALIZATION ---
    document.addEventListener('DOMContentLoaded', async () => {
        // Check if the supabase client is ready before setting up listeners
        if (window.supabaseClient) {
            await loadComments();
            const komentarForm = document.getElementById('komentarForm');
            if (komentarForm) {
                komentarForm.addEventListener('submit', handleFormSubmit);
            }

            // Add event listener for sort dropdown if it exists
            const sortSelect = document.getElementById('sort-comments');
            if (sortSelect) {
                sortSelect.addEventListener('change', loadComments);
            }
        } else {
            console.error('Supabase client not found on DOMContentLoaded. Check script loading order.');
        }
    });

})();
