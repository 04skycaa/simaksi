// Config file for Supabase connection

// This script assumes the main Supabase library has been loaded from the CDN
// and that a `window.supabase` object is available.

(function() {
    if (!window.supabase) {
        console.error('Supabase library not found. Make sure it is included in your HTML before this config file.');
        return;
    }

    const supabaseUrl = 'https://kitxtcpfnccblznbagzx.supabase.co';
    const supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtpdHh0Y3BmbmNjYmx6bmJhZ3p4Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTk1ODIxMzEsImV4cCI6MjA3NTE1ODEzMX0.OySigpw4AWI3G7JW_8r8yXu7re0Mr9CYv8u3d9Fr548'; // anon key

    // Create a single, shared Supabase client instance
    const supabaseClient = window.supabase.createClient(supabaseUrl, supabaseKey);

    // Make the single client instance available globally for all other scripts
    window.supabaseClient = supabaseClient;

})();