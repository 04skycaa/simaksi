// assets/js/main.js - Main landing page functionality
(function() {
    // This script assumes that 'window.supabaseClient' has been initialized by config.js
    
    /**
     * Primary initialization function. Runs after the DOM is loaded.
     */
    function initializeMain() {
        if (!window.supabaseClient) {
            console.error('Supabase client not found. Make sure config.js is loaded first.');
            showAnnouncementError();
            return;
        }

        // Load non-auth content immediately.
        loadActiveAnnouncements();

        // This listener is the single source of truth for the auth UI.
        // It fires on load with the initial session and on any auth state change.
        window.supabaseClient.auth.onAuthStateChange((_event, session) => {
            updateAuthUI(session);
        });
    }

    /**
     * Fetches and displays active announcements.
     */
    async function loadActiveAnnouncements() {
        try {
            const now = new Date().toISOString();
            const { data, error } = await window.supabaseClient
                .from('pengumuman')
                .select('id_pengumuman, judul, konten, start_date, end_date, profiles(nama_lengkap)')
                .lte('start_date', now)
                .gte('end_date', now)
                .eq('telah_terbit', true)
                .order('dibuat_pada', { ascending: false });

            if (error) throw error;
            updateAnnouncementsUI(data);
        } catch (err) {
            console.error('Error loading announcements:', err);
            showAnnouncementError();
        }
    }

    /**
     * Renders announcements into the DOM.
     * @param {Array} announcements - The array of announcement objects from Supabase.
     */
    function updateAnnouncementsUI(announcements) {
        const container = document.getElementById('pengumuman-content');
        const loadingElement = document.getElementById('pengumuman-loading');
        const emptyElement = document.getElementById('pengumuman-empty');
        if (!container || !loadingElement || !emptyElement) return;

        loadingElement.classList.add('hidden');
        container.innerHTML = '';
        
        if (!announcements || announcements.length === 0) {
            emptyElement.classList.remove('hidden');
            return;
        }

        emptyElement.classList.add('hidden');
        announcements.forEach(announcement => {
            // Determine announcement type based on title or content for visual tags
            let announcementType = 'info';
            if (announcement.judul.toLowerCase().includes('peringatan') ||
                announcement.judul.toLowerCase().includes('penting') ||
                announcement.konten.toLowerCase().includes('wajib') ||
                announcement.konten.toLowerCase().includes('peringatan')) {
                announcementType = 'penting';
            } else if (announcement.judul.toLowerCase().includes('event') ||
                       announcement.judul.toLowerCase().includes('acara') ||
                       announcement.konten.toLowerCase().includes('event')) {
                announcementType = 'event';
            }

            const card = document.createElement('div');
            card.className = 'announcement-card';
            card.innerHTML = `
                <div class="announcement-header">
                    <h3 class="announcement-title">${announcement.judul}</h3>
                    <div class="announcement-date">
                        ${new Date(announcement.start_date).toLocaleDateString('id-ID')}
                    </div>
                </div>
                <div class="announcement-tag tag-${announcementType}">${announcementType === 'penting' ? 'Penting' : announcementType === 'event' ? 'Event' : 'Info'}</div>
                <div class="announcement-content">${announcement.konten}</div>
                <div class="announcement-author">
                    <i class="fas fa-user"></i> Oleh: ${announcement.profiles.nama_lengkap}
                </div>
                <div class="announcement-meta">
                    <small class="text-gray-500">
                        Berlaku: ${new Date(announcement.start_date).toLocaleDateString('id-ID')} -
                        ${new Date(announcement.end_date).toLocaleDateString('id-ID')}
                    </small>
                </div>`;
            container.appendChild(card);
        });
    }

    /**
     * Displays an error message in the announcement section.
     */
    function showAnnouncementError() {
        const container = document.getElementById('pengumuman-content');
        if(container) container.innerHTML = '<p class="text-red-500 text-center">Gagal memuat pengumuman.</p>';
    }

    /**
     * Handles the client-side and server-side logout process and then refreshes the page.
     */
    async function handleLogout(e) {
        e.preventDefault();
        try {
            // Sign out from Supabase (clears client-side session)
            await window.supabaseClient.auth.signOut();
            
            // Clear the server-side PHP session in the background
            await fetch('./auth/logout.php', { method: 'POST' });

        } catch (error) {
            console.error('Error during logout process:', error);
        } finally {
            // Reload the page to guarantee a clean, logged-out state for the UI
            window.location.reload();
        }
    }

    /**
     * Updates the header UI based on the Supabase session, respecting the initial server-render.
     * @param {object|null} session - The Supabase auth session object.
     */
    async function updateAuthUI(session) {
        const authDisplay = document.getElementById('auth-display');
        if (!authDisplay) return;

        const serverLogoutLink = authDisplay.querySelector('#logout-link-header');

        if (!session && serverLogoutLink) {
            serverLogoutLink.addEventListener('click', handleLogout);
            return; 
        }

        if (session) {
            try {
                const { data: profile, error } = await window.supabaseClient
                    .from('profiles')
                    .select('nama_lengkap')
                    .eq('id', session.user.id)
                    .single();

                if (error) throw error;
                const userName = profile ? profile.nama_lengkap : 'Pendaki';

                authDisplay.innerHTML = `
                    <span id="greeting-text" class="text-sm" data-translate-key="greeting_text">Selamat datang,</span>
                    <span id="user-fullname" class="font-semibold text-sm">${userName}</span>
                    <a href="#" id="logout-link-header" class="border border-white px-3 py-1 rounded-full text-xs hover-bg-white text-white transition-default" data-translate-key="logout_button_header">
                        Logout
                    </a>`;
                
                authDisplay.querySelector('#logout-link-header').addEventListener('click', handleLogout);

            } catch (error) {
                console.error("Error fetching profile for UI update:", error);
                authDisplay.innerHTML = `<a href="#" id="logout-link-header" class="border border-white px-3 py-1 rounded-full text-xs">Logout</a>`;
                authDisplay.querySelector('#logout-link-header').addEventListener('click', handleLogout);
            }
        } else {
            authDisplay.innerHTML = `
                <a href="auth/login.php" class="login-button border border-white px-4 py-2 rounded-full text-sm hover-bg-white text-white transition-default" data-translate-key="login_button">
                    Login
                </a>`;
        }
    }

    // --- Start Execution ---
    document.addEventListener('DOMContentLoaded', initializeMain);

})();
