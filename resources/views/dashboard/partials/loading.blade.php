<div id="loadingSpinner" 
     style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
    
    <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
            <div style="width: 3rem; height: 3rem; border: 4px solid #e2e8f0; border-top-color: #2563eb; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="font-size: 0.875rem; font-weight: 600; color: #374151; margin: 0;">Memproses data...</p>
        </div>
    </div>
</div>

<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<script>
    window.loadingSpinner = {
        show() {
            document.getElementById('loadingSpinner').style.display = 'flex';
        },
        hide() {
            document.getElementById('loadingSpinner').style.display = 'none';
        }
    };

    window.addEventListener('load', () => {
        loadingSpinner.hide();
    });

    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (link && link.href && !link.href.includes('#') && !link.target) {
            loadingSpinner.show();
        }
    });

    document.addEventListener('submit', (e) => {
        const form = e.target;
        loadingSpinner.show();
        
        // DETECT EXPORT: jika formaction ada export
        const submitBtn = e.submitter;
        if (submitBtn && submitBtn.hasAttribute('formaction')) {
            const formAction = submitBtn.getAttribute('formaction');
            if (formAction.includes('export')) {
                // Hide setelah 2 detik (file selesai download)
                setTimeout(() => {
                    loadingSpinner.hide();
                }, 2000);
            }
        }
    });
</script>